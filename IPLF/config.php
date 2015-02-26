<?php

// access areas
$wfconfig_access_areas = Array(
);

$wfconfig_access_areas_ids = Array(
'0'=>'public'
);

// supported GET requests
$wfconfig_supported_get = Array(
'0' => '&utm_'
);

// architecture type
define("WFCONFIG_ARCHITECTURE", 'PFCS');

// components folder name
define("WFCONFIG_COMPONENTS_FOLDER", 'components');

// templates folder name
define("WFCONFIG_VIEW_FOLDER", 'view');

// url of the components location (without the last "/" and base url)
define("WFCONFIG_COMPONENTS_URI", 'components');

// url of the templats location (without the last "/" and base url)
define("WFCONFIG_VIEW_URI", 'view');

// using standard sql tables

// use aliases
define("WFCONFIG_USE_ALIASES_TABLE", false);

// read settings table
define("WFCONFIG_GET_SETTINGS_TABLE", true);

// standard table will be used to build components cache
define("WFCONFIG_USE_SECTIONS_TABLE", true);

// registration and users

// validate the user on the registration
define("WFCONFIG_INITIATE_REGISTRED_USER", true);

// sessions and cookies life time
define("WFCONFIG_SESSION_TIME", 259200);

// use cookie for authorization
define("WFCONFIG_USE_COOKIE_AUTHORIZATION", true);

// directory for storage of avatars
define("WFCONFIG_USER_AVATAR_FOLDER", "img".DIRECTORY_SEPARATOR."avatars");

// avatars extension
define("WFCONFIG_USER_AVATAR_EXTENSION", ".png");

// user password field name
define("WFCONFIG_USER_PASS_FIELD", "pass");

// user key field name
define("WFCONFIG_USER_KEY_FIELD", "key");

?>
