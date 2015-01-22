<?php

// области доступа
$wfconfig_access_areas = Array(
'manager' => 'manage'
);

$wfconfig_access_areas_ids = Array(
'0'=>'public',
'1'=>'manager'
);

// поддерживаемые GET запросы
$wfconfig_supported_get = Array(
'0' => '&utm_'
);

// тип архитектуры
define("WFCONFIG_ARCHITECTURE", 'PFCS');

// директория расположения компонентов
define("WFCONFIG_COMPONENTS_FOLDER", 'components');

// директория расположения шаблонов
define("WFCONFIG_VIEW_FOLDER", 'view');

// путь url директории компонентов (без последнего /)
define("WFCONFIG_COMPONENTS_URI", 'components');

// путь url директории шаблонов (без последнего /)
define("WFCONFIG_VIEW_URI", 'view');

// использование стандартных sql таблиц

// Использовать псевдонимы
define("WFCONFIG_USE_ALIASES_TABLE", false);

// считывать таблицу установок
define("WFCONFIG_GET_SETTINGS_TABLE", true);

// использовать для построения кэша компонентов стандартную таблицу
define("WFCONFIG_USE_SECTIONS_TABLE", true);

// регистрация и пользователи

// проверять пользователя на регистрацию
define("WFCONFIG_INITIATE_REGISTRED_USER", true);

// директория хранения аватаров
define("WFCONFIG_USER_AVATAR_FOLDER", "img".DIRECTORY_SEPARATOR."avatars");

// расширение аватаров
define("WFCONFIG_USER_AVATAR_EXTENSION", ".png");

// название поля пароля пользователя
define("WFCONFIG_USER_PASS_FIELD", "pass");

// название поля ключа пользователя
define("WFCONFIG_USER_KEY_FIELD", "key");

?>