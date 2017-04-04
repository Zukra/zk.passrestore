<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;


Loc::loadMessages(__FILE__);

if (class_exists('zk_passrestore')) {
    return;
}

class zk_passrestore extends CModule {
    // var $MODULE_ID = "zk.passrestore";
    // var $MODULE_VERSION;
    // var $MODULE_VERSION_DATE;
    // var $MODULE_NAME;
    // var $MODULE_DESCRIPTION;
    // var $PARTNER_NAME; 
    // var $PARTNER_URI;

    public function __construct() {
        $this->MODULE_ID = 'zk.passrestore';

        $arModuleVersion = array();
        include(__DIR__ . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("PSW_RESTORE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("PSW_RESTORE_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("PSW_RESTORE_PARTNER_NAME");
        $this->PARTNER_URI = "http://";
    }
    
    public function installDB($arParams = array()) {
        EventManager::getInstance()
            ->registerEventHandler('main', 'OnBeforeUserSendPassword'
                , $this->MODULE_ID, 'PassRest', 'OnBeforeUserSendPassword');

        return true;
    }

    public function uninstallDB($arParams = array()) {
        global $APPLICATION;

        EventManager::getInstance()->unRegisterEventHandler(
            'main', 'OnBeforeUserSendPassword'
            , $this->MODULE_ID, 'PassRest', 'OnBeforeUserSendPassword');

        return true;
    }

    public function installDB_lib($arParams = array()) {
        global $APPLICATION;

        if (Loader::includeModule($this->MODULE_ID)) {
            EventManager::getInstance()
                ->registerEventHandler('main', 'OnBeforeUserSendPassword'
                    , $this->MODULE_ID, '\Zk\Passrestore\PassRestore', 'OnBeforeUserSendPassword');
        } else {
            $APPLICATION->ThrowException(implode("<br>", 'errors)'));
            
            return false;
        }

        return true;
    }

    public function uninstallDB_lib($arParams = array()) {
        global $APPLICATION;

       if (Loader::includeModule($this->MODULE_ID)) {
        EventManager::getInstance()->unRegisterEventHandler(
            'main', 'OnBeforeUserSendPassword'
            , $this->MODULE_ID, '\Zk\Passrestore\PassRestore', 'OnBeforeUserSendPassword');
       } else {
           $APPLICATION->ThrowException(implode("<br>", 'errors)'));

           return false;
       }

        return true;
    }

    function InstallEvents() {
        global $DB;
        $sIn = "'PSW_RESTORE'";
        $rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (" . $sIn . ") ", false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
        $ar = $rs->Fetch();
        if ($ar["C"] <= 0) {
            include(dirname(__DIR__) . "/install/events.php");
        }

        return true;
    }

    function UnInstallEvents() {
        global $DB;

        $sIn = "'PSW_RESTORE'";
        $DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME IN (" . $sIn . ") ", false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
        $DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME IN (" . $sIn . ") ", false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

        return true;
    }

    public function doInstall() {
        global $APPLICATION;

        ModuleManager::registerModule($this->MODULE_ID);

        if ($this->InstallDB()) {
            $this->InstallEvents();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage("PSW_RESTORE_INSTALL_ERROR"));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("PSW_RESTORE_INSTALL_TITLE")
            , dirname(__DIR__) . "/install/step.php");

    }

    public function doUninstall() {
        global $APPLICATION;

        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unregisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage("PSW_RESTORE_UNINSTALL_TITLE")
            , dirname(__DIR__) . "/install/unstep.php");

    }
}
