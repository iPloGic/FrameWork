<?php

/*

*** Users work Classes
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.0

*** Copyright (C) 2014 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/users/

*/


class USER_STATIC
{

	static function PassHash($pass, $user_key) {
		$pass = trim($pass);
		$site_key = trim(SECURITY_KEY);
		$user_key = trim($user_key);
		$key='';
		$pre='';
		$c = strlen($user_key);
		if (strlen($pass)>$c) { $c = strlen($pass); }
		for ($i=0; $i<$c; $i++) { @$key .= $user_key[$i].$pass[$i]; }
		$c = strlen($key);
		for ($i=1; $i<$c; $i=$i+2) { @$pre .= $key[$i].$key[$i-1].$site_key[($i-1)/2]; }
		return md5($pre);
	}

	static function GenerateKey() {
		$key='';
		$length = mt_rand(18, 22);
		$for_key=Array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M');
		for($i=1; $i<=$length; $i++) {
			shuffle($for_key);
			$key.=$for_key[0];
		}
		return $key;
	}

	static function Login($name, $pass, $field='login') {
		$sql = "SELECT * FROM `".DB_PREFIX."users` WHERE `".$field."`='".trim($name)."'";
		$row = FRAME_CORE::DB()->GetFirstResult($sql);
		if ($row) {
			if ($row['approved']==1 && $row['active']==1) {
				if (USER_STATIC::PassHash($pass, $row[WFCONFIG_USER_KEY_FIELD])==trim($row[WFCONFIG_USER_PASS_FIELD])) {
					if ( WFCONFIG_USE_COOKIE_AUTHORIZATION && isset($_COOKIE) ) {
						$started = false;
						if ( isset($_COOKIE['session']) ) {
							$id = $_COOKIE['session'];
							$sql = "SELECT * FROM `".DB_PREFIX."sessions` WHERE `sid`='".$id."' AND `user`='".$row['id']."' ORDER BY `started` DESC";
							$session = FRAME_CORE::DB()->GetFirstResult($sql);
							if ($session) {
								session_id($id);
								session_start();
								$_SESSION = unserialize($session['data']);
								$started = true;
								USER_STATIC::UpdateSessionTable();
								setcookie ('session', session_id(), time()+WFCONFIG_SESSION_TIME, '/');
							}
						}
						if ( !$started ) {
							session_start();
							$sql = "DELETE FROM `".DB_PREFIX."sessions` WHERE `sid`='".session_id()."'";
							FRAME_CORE::DB()->Go($sql);
							$sql = "INSERT INTO `".DB_PREFIX."sessions` VALUES ('".session_id()."','".$row['id']."','".$data."','".time()."','".(time()+WFCONFIG_SESSION_TIME)."')";
							FRAME_CORE::DB()->Go($sql);
							setcookie ('session', session_id(), time()+WFCONFIG_SESSION_TIME, '/');
							$_SESSION['authorized_user']=$row['id'];
							USER_STATIC::UpdateSessionTable();
						}
					}
					return 0;                // success
				}
				else  {  return 1;  }        // wrong password
			}
			else  {  return 2;   }           // user not active
		}
		else  {  return 3;   }               // user does not exsists
	}

	static function Logout() {
		unset($_SESSION['authorized_user']);
		unset($_SESSION['authorization']);
		if ( WFCONFIG_USE_COOKIE_AUTHORIZATION && isset($_COOKIE) ) {
			if ( isset($_COOKIE['session']) ) {
				$sql = "DELETE FROM `".DB_PREFIX."sessions` WHERE `sid`='".$_COOKIE['session']."'";
				FRAME_CORE::DB()->Go($sql);
				setcookie('session', "", time()-3600,'/');
			}
		}
		session_destroy();
		return true;
	}

	static function UpdateSessionTable() {
		$data = serialize($_SESSION);
		$sql = "UPDATE `".DB_PREFIX."sessions` SET `data`='".$data."', `expire`='".(time()+WFCONFIG_SESSION_TIME)."' WHERE `sid`='".session_id()."'";
		FRAME_CORE::DB()->Go($sql);
		return true;
	}

	static function StartSession($id) {
		$sql = "SELECT * FROM `".DB_PREFIX."sessions` WHERE `sid`='".$id."' ORDER BY `started` DESC";
		$session = FRAME_CORE::DB()->GetFirstResult($sql);
		if ($session) {
			session_start($id);
			$_SESSION = unserialize($session['data']);
			USER_STATIC::UpdateSessionTable();
			setcookie ('session', session_id(), time()+WFCONFIG_SESSION_TIME, '/');
			return true;
		}
		return false;
	}

}


class USER
{
	public $id;
	public $name;
	public $login;
	public $email;
	public $group_id = 0;
	public $group;
	public $group_active;
	public $group_description;
	public $group_name;
	public $area_access = true;
	public $section_access = 0;
	public $avatar;
	public $registration_date;
	public $parameters = Array();
	private $table_row = Array();

	public function ConstructFromTable($ident,$r='i') {
		if ($r=='l' || $r=='i') {
			if ($r=='l') { $field = "login"; }
			if ($r=='i') { $field = "id"; }
			$q = "SELECT * FROM `".DB_PREFIX."users` WHERE ".$field."='".$ident."'";
			$m = FRAME_CORE::DB()->GetFirstResult($q);
			if ($m) {
				$this->table_row = $m;
				if ($this->GetBaseParameters()) {
					return true;
				}
				else { return false; }
			}
			else { return false; }
		}
		else { return false; }
	}

	public function ConstructFromArray($array) {
		if ($r=='a') {
			if (is_array($array)) {
				$this->table_row = $array;
				if ($this->GetBaseParameters()) {
					return true;
				}
				else { return false; }
			}
			else { return false; }
		}
	}

	private function GetBaseParameters() {
		if (is_array($this->table_row)) {
			$this->id = $this->table_row['id'];
			$this->name = $this->table_row['name'];
			$this->login = $this->table_row['login'];
			$this->email = $this->table_row['email'];
			$this->group_id = $this->table_row['group'];
			$this->registration_date = $this->table_row['registration_date'];
			if (file_exists(BASE_URL.WFCONFIG_USER_AVATAR_FOLDER.DIRECTORY_SEPARATOR.$this->table_row['id'].WFCONFIG_USER_AVATAR_EXTENSION)){
				$this->avatar = BASE_URL.WFCONFIG_USER_AVATAR_FOLDER.DIRECTORY_SEPARATOR.$this->table_row['id'].WFCONFIG_USER_AVATAR_EXTENSION;
			}
			return true;
		}
		else { return false; }
	}

	public function AddParameter($name,$value) {
		$this->parameters[$name] = $value;
	}

	public function GetGroup() {
		$q = "SELECT * FROM `".DB_PREFIX."users_groups` WHERE `id`='".$this->group_id."'";
		$m = FRAME_CORE::DB()->GetFirstResult($q);
		if ($m) {
			$this->group = $m['identifier'];
			if ( $m['active']==1 ) { $this->group_active = true; } else { $this->group_active = false; }
			$this->group_description = $m['description'];
			$this->group_name = $m['name'];
		}
		else { return false; }
		return true;
	}

	public function GetAccess() {
		$q = "SELECT * FROM `".DB_PREFIX."users_groups` WHERE `id`='".$this->group_id."' AND `areas` LIKE '%/".FRAME_CORE::AccessAreaID()."/%'";
		$m = FRAME_CORE::DB()->GetFirstResult($q);
		if (!$m && FRAME_CORE::AccessArea()!='public') {
			$this->area_access = false;
		}
		$q = "SELECT * FROM `".DB_PREFIX."users_groups_access` WHERE `group`='".$this->group_id."' AND `area`='".FRAME_CORE::AccessAreaID()."' AND `section`='".FRAME_CORE::Section()."'";
		$m = FRAME_CORE::DB()->GetFirstResult($q);
		if ($m) {
			$this->section_access = $m['access'];
		}
		return true;
	}

}


?>