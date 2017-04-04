<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

$MESS ['PSW_RESTORE_MODULE_NAME'] = "Восстановление пароля";
$MESS ['PSW_RESTORE_MODULE_DESC'] = "Модуль восстановления пароля.";
$MESS ['PSW_RESTORE_PARTNER_NAME'] = "Zukra";
$MESS ['PSW_RESTORE_INSTALL_ERROR'] = "Что-то пошло не так";
$MESS ['PSW_RESTORE_INSTALL_TITLE'] = "Установка модуля Восстановление пароля";
$MESS ['PSW_RESTORE_UNINSTALL_TITLE'] = "Удаление модуля Восстановление пароля";

$MESS["PSW_USER_PASS_REQUEST_TYPE_NAME"] = "Запрос на восстановление пароля";
$MESS["PSW_USER_INFO_TYPE_DESC"] = "

#USER_ID# - ID пользователя
#STATUS# - Статус логина
#MESSAGE# - Сообщение пользователю
#LOGIN# - Логин
#PASSWORD# - Пароль
#URL_LOGIN# - Логин, закодированный для использования в URL
#CHECKWORD# - Контрольная строка для смены пароля
#NAME# - Имя
#LAST_NAME# - Фамилия
#EMAIL# - E-Mail пользователя
";

$MESS["PSW_USER_PASS_REQUEST_EVENT_NAME"] = "#SITE_NAME#: Запрос на востановление пароля";
$MESS["PSW_USER_PASS_REQUEST_EVENT_DESC"] = "Информационное сообщение сайта #SITE_NAME#
------------------------------------------
#NAME# #LAST_NAME#,

#MESSAGE#

Ваш новый пароль в системе:  #PASSWORD#

Страница авторизации в системе:  http://#SERVER_NAME#/auth/

Ваша регистрационная информация:

ID пользователя:  #USER_ID#
Статус профиля:   #STATUS#
Login:            #LOGIN#
E-Mail:           #EMAIL#

Сообщение сгенерировано автоматически.";