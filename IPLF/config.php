<?php

// ������� �������
$wfconfig_access_areas = Array(
'manager' => 'manage'
);

$wfconfig_access_areas_ids = Array(
'0'=>'public',
'1'=>'manager'
);

// �������������� GET �������
$wfconfig_supported_get = Array(
'0' => '&utm_'
);

// ��� �����������
define("WFCONFIG_ARCHITECTURE", 'PFCS');

// ���������� ������������ �����������
define("WFCONFIG_COMPONENTS_FOLDER", 'components');

// ���������� ������������ ��������
define("WFCONFIG_VIEW_FOLDER", 'view');

// ���� url ���������� ����������� (��� ���������� /)
define("WFCONFIG_COMPONENTS_URI", 'components');

// ���� url ���������� �������� (��� ���������� /)
define("WFCONFIG_VIEW_URI", 'view');

// ������������� ����������� sql ������

// ������������ ����������
define("WFCONFIG_USE_ALIASES_TABLE", false);

// ��������� ������� ���������
define("WFCONFIG_GET_SETTINGS_TABLE", true);

// ������������ ��� ���������� ���� ����������� ����������� �������
define("WFCONFIG_USE_SECTIONS_TABLE", true);

// ����������� � ������������

// ��������� ������������ �� �����������
define("WFCONFIG_INITIATE_REGISTRED_USER", true);

// ���������� �������� ��������
define("WFCONFIG_USER_AVATAR_FOLDER", "img".DIRECTORY_SEPARATOR."avatars");

// ���������� ��������
define("WFCONFIG_USER_AVATAR_EXTENSION", ".png");

// �������� ���� ������ ������������
define("WFCONFIG_USER_PASS_FIELD", "pass");

// �������� ���� ����� ������������
define("WFCONFIG_USER_KEY_FIELD", "key");

?>