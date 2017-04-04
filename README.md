##  Bitrix module restore password (zk.passrestore)
### Модуль восстановления пароля на 1с-битрикс

Модуль при восстановлении пароля генерирует новый пароль (длина от 7 до 10 символов),
устанавливает его и отсылает на почту пользователя вместо стандартного уведомления.

Почтовое событие и его шаблон устанавливаются совместно с модулем.

**Обратите внимание:** все языковые файлы в UTF-8 формате.

**Установка:**

  - скопировать каталог архива `./modules/` в каталог сайта `./local/`
  - в файл `./local/php_interface/user_lang/ru/lang.php` добавить строки 
  из файла архива `./php_interface/user_lang/ru/lang.php` для замены сообщений
  на форме восстановления пароля.
    
Cодержимое lang.php :
```
<?php
$MESS['/bitrix/components/bitrix/system.auth.forgotpasswd/templates/.default/lang/ru/template.php']['AUTH_GET_CHECK_STRING'] = "Выслать новый пароль";
$MESS['/bitrix/components/bitrix/system.auth.forgotpasswd/templates/.default/lang/ru/template.php']['AUTH_FORGOT_PASSWORD_1'] = "Если вы забыли пароль, введите логин или E-Mail.<br />Новый пароль, а также ваши регистрационные данные, будут высланы на Ваш E-Mail.";
$MESS['/bitrix/modules/main/lang/ru/classes/general/user.php']["ACCOUNT_INFO_SENT"] = "Новый пароль, а также Ваши регистрационные данные были высланы на Ваш E-Mail.";
```

Установка в каталог сайта ./bitrix/ аналогична, как и в ./local/

Через `Администрирование->Marketplace` модуль устанавливается/удалается


При удалении модуля не забудьте убрать строки из `./local/php_interface/user_lang/ru/lang.php`