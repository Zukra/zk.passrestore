<?php
use \Bitrix\Main\Localization\Loc;

$langs = CLanguage::GetList($b = "", $o = "");

while ($lang = $langs->Fetch()) {
    $lid = $lang["LID"];

    IncludeModuleLangFile(__FILE__, $lid);

    $et = new CEventType;
    $et->Add(array(
        "LID"         => $lid,
        "EVENT_NAME"  => "PSW_RESTORE",
        "NAME"        => Loc::getMessage("PSW_USER_PASS_REQUEST_TYPE_NAME"),
        "DESCRIPTION" => Loc::getMessage("PSW_USER_INFO_TYPE_DESC"),
    ));

    $arSites = array();
    $sites = CSite::GetList($b = "", $o = "", Array("LANGUAGE_ID" => $lid));
    while ($site = $sites->Fetch())
        $arSites[] = $site["LID"];

    if (count($arSites) > 0) {
        $emess = new CEventMessage;
        $emess->Add(array(
            "ACTIVE"     => "Y",
            "EVENT_NAME" => "PSW_RESTORE",
            "LID"        => $arSites,
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO"   => "#EMAIL#",
            "SUBJECT"    => Loc::getMessage("PSW_USER_PASS_REQUEST_EVENT_NAME"),
            "MESSAGE"    => Loc::getMessage("PSW_USER_PASS_REQUEST_EVENT_DESC"),
        ));
    }
}