<?php

namespace Zk\Passrestore;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\UserTable;
use CEvent;
use CMain;
use CUser;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

Loc::loadMessages(__FILE__);

class PassRestore {

    const MODULE_ID = 'zk.passrestore';

    static $eventType = "PSW_RESTORE";
    static $passwordLength = array('MIN' => 7, 'MAX' => 10);

    public static function OnBeforeUserSendPassword(&$arFields) {

        $login = $arFields['LOGIN'];
        $email = $arFields['EMAIL'];
        $lid = $arFields['SITE_ID'];

        $elemUser = static::getUser($login, $email)[0];

        if (count($elemUser) > 0) {
            $elemUser["PASSWORD"] = static::randomPassword($elemUser["ID"], static::$passwordLength); // new random password
            $elemUser["MESSAGE"] = '';
            self::var_dump($elemUser["PASSWORD"]);

            $result = static::changePassword($elemUser);
            if ($result["TYPE"] === 'OK') {
                $elemUser['MESSAGE'] = $result['MESSAGE'];
                if (!static::sendEmail($elemUser, static::$eventType, $lid)) {
                    $GLOBALS['APPLICATION']->throwException(Loc::getMessage('PSW_EMAIL_ERROR'));
                };
            } elseif ($result["TYPE"] === 'ERROR') {
                $GLOBALS['APPLICATION']->throwException($result['MESSAGE']);
            }
        } else {
            $GLOBALS['APPLICATION']->throwException(Loc::getMessage("DATA_NOT_FOUND"));
        }

        return false;
    }

    public static function getUser($login = '', $email = '') {

        $filter = array();
        if (!empty($login)) {
            $filter["LOGIN"] = $login;
        } elseif (!empty($email)) {
            $filter["EMAIL"] = $email;
        } else {
            $filter["LOGIN"] = "none";
        }
        $filter["ACTIVE"] = "Y";

        $result = UserTable::getList([
                'select' => ["LOGIN", 'EMAIL', 'ID', 'PASSWORD', 'ACTIVE'
                    , "LAST_NAME", "SECOND_NAME", "NAME", "LID"],
                'filter' => $filter
            ]
        );

        $user = array();
        while ($row = $result->fetch()) {
            $user[] = $row;
        }

        return $user;
    }

    private function sendEmail($user, $event, $lid, $immediate = true) {
        $cEvent = new CEvent;
        $arFields = array(
            "USER_ID"     => $user["ID"],
            "LOGIN"       => $user["LOGIN"],
            "EMAIL"       => $user["EMAIL"],
            "NAME"        => $user["NAME"],
            "LAST_NAME"   => $user["LAST_NAME"],
            "SECOND_NAME" => $user["SECOND_NAME"],
            "PASSWORD"    => $user["PASSWORD"],
            "STATUS"      => ($user["ACTIVE"] == "Y" ? Loc::getMessage("STATUS_ACTIVE") : Loc::getMessage("STATUS_BLOCKED")),
            "MESSAGE"     => $user['MESSAGE'],
            "URL_LOGIN"   => urlencode($user["LOGIN"]),
        );

        if ($immediate) {
            $result = $cEvent->SendImmediate($event, $lid, $arFields);
        } else {
            $result = $cEvent->Send($event, $lid, $arFields);
        }

        return $result ? true : false;
    }

    public static function var_dump($obj) {
        echo '<pre>';
        var_dump($obj);
        echo '</pre>';

    }

    private function changePassword($user) {

        $result_message = array("MESSAGE" => Loc::getMessage('PSW_PASSWORD_CHANGE_OK'), "TYPE" => "OK");

        // change the password
        $cUser = new CUser();
        if (!$cUser->Update($user["ID"], array("PASSWORD" => $user["PASSWORD"]))) {
//        if (!static::Update($user["ID"], array("PASSWORD" => $user["PASSWORD"]))) {
            $result_message = array("MESSAGE" => Loc::getMessage('PSW_UPDATE_USER_ERROR') . "<br>", "TYPE" => "ERROR");
        }

//        CUser::SendUserInfo($user["ID"], $user["LID"], GetMessage('CHANGE_PASS_SUCC'), true, 'PSW_RESTORE');

        return $result_message;
    }

    private function Update($ID, $arFields) {
        /** @global CUserTypeManager $USER_FIELD_MANAGER */
        global $DB, $USER_FIELD_MANAGER, $CACHE_MANAGER;
        //euKDT1If5d34aee4e5ca94d2c4bbb2b9460ef5b5  zEgjqgwDcb0305b494d8850d8805d516387a6150

        $ID = intval($ID);
        unset($arFields["ID"]);

        $original_pass = $arFields["PASSWORD"];

        if (is_set($arFields, "PASSWORD")) {
            $salt = randString(8, array(
                "abcdefghijklnmopqrstuvwxyz",
                "ABCDEFGHIJKLNMOPQRSTUVWXYZ",
                "0123456789",
                ",.<>/?;:[]{}\\|~!@#\$%^&*()-_+=",
            ));
            $arFields["PASSWORD"] = $salt . md5($salt . $arFields["PASSWORD"]);

//            $rUser = CUser::GetByID($ID);
//            if ($arUser = $rUser->Fetch()) {
//                if ($arUser["PASSWORD"] != $arFields["PASSWORD"])
//                    $DB->Query("DELETE FROM b_user_stored_auth WHERE USER_ID=" . $ID);
//            }
//            if (COption::GetOptionString("main", "event_log_password_change", "N") === "Y")
//                CEventLog::Log("SECURITY", "USER_PASSWORD_CHANGED", "main", $ID);
        }

        $checkword = '';
        if (!is_set($arFields, "CHECKWORD")) {
            if (is_set($arFields, "PASSWORD") || is_set($arFields, "EMAIL") || is_set($arFields, "LOGIN") || is_set($arFields, "ACTIVE")) {
                $salt = randString(8);
                $checkword = md5(CMain::GetServerUniqID() . uniqid());
                $arFields["CHECKWORD"] = $salt . md5($salt . $checkword);
            }
        } else {
            $salt = randString(8);
            $checkword = $arFields["CHECKWORD"];
            $arFields["CHECKWORD"] = $salt . md5($salt . $checkword);
        }

        $strUpdate = $DB->PrepareUpdate("b_user", $arFields);

        if (!is_set($arFields, "TIMESTAMP_X"))
            $strUpdate .= ($strUpdate <> "" ? "," : "") . " TIMESTAMP_X = " . $DB->GetNowFunction();

        $strSql = "UPDATE b_user SET " . $strUpdate . " WHERE ID=" . $ID;

        ////////////////////////
//        "OnBeforeUserLogin"   "OnUserLoginExternal"
        $strSql = "UPDATE b_user SET `PASSWORD` = '" . $arFields["PASSWORD"] . "'"
            . ", `CHECKWORD` = '" . $arFields["CHECKWORD"] . "'"
            . ", `PASS` = '" . $original_pass . "'"
            . ",  TIMESTAMP_X = now() WHERE ID=3";

//        $strSql = "UPDATE b_user SET `PASSWORD_NEW` = '" . $arFields["PASSWORD"] . "'"
//            . ", `CHECKWORD_NEW` = '" . $arFields["CHECKWORD"] . "'"
//            . ", `PASS` = '" . $original_pass . "'"
//            . ",  TIMESTAMP_X = now() WHERE ID=3";
        ///////////////////////

        self::var_dump($strSql);

        $DB->Query($strSql, false, "FILE: " . __FILE__ . "<br> LINE: " . __LINE__);

        $Result = true;
//        $arFields["CHECKWORD"] = $checkword;
//        $arFields["ID"] = $ID;
//        $arFields["RESULT"] = &$Result;

        return $Result;
    }

    public static function randomPassword($gid, $passLength) {
        if (!is_array($gid) && is_numeric($gid) && $gid > 0) {
            $gid = array($gid);
        }

        $policy = CUser::GetGroupPolicy($gid);
        $length = rand($passLength['MIN'], $passLength['MAX']);

        /*
                $length = $policy['PASSWORD_LENGTH'];
                $maxLength = $policy['MAX_STORE_NUM'];
                if ($length <= 0) { $length = 10; }
        */

        $alphabet = Random::ALPHABET_ALPHAUPPER
            | Random::ALPHABET_ALPHALOWER
            | Random::ALPHABET_NUM;

        if ($policy['PASSWORD_PUNCTUATION'] == 'Y') {
            $alphabet |= Random::ALPHABET_SPECIAL;
        }

        return Random::getStringByAlphabet($length, $alphabet);
    }

    public static function GetPath($notDocumentRoot = false) {
        if ($notDocumentRoot)
            return str_ireplace($_SERVER['DOCUMENT_ROOT'], '', dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    public static function isVersionD7() {
        return CheckVersion(SM_VERSION, '14.00.00');
    }

}