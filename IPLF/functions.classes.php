<?php

/*

*** Static functions Classes
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.3

*** Copyright (C) 2014 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/func/

*/


class FUNC
{

	static function GrabRequestVar($method, $vmane, $mn = '', $html = false) {
		$method = strtolower($method);
		if ($method=='post') { if ($mn=='') { $val=$_POST[$vmane]; } else  { $val=$_POST[$vmane][$mn]; } }
		if ($method=='get') { if ($mn=='') { $val=$_GET[$vmane]; } else  { $val=$_GET[$vmane][$mn]; } }
		if (!$html) { $val=htmlspecialchars($val); }
		if (!get_magic_quotes_gpc()) {  $val = addslashes($val); }
		$val=trim($val);
		return $val;
	}

	static function GetItemID( $pos = 2 ) {
		$id = FRAME_CORE::Parameter('url_variables', $pos);
		if ( $id=='' || !FUNC::IsInteger($id) ) { return false; }
		return $id;
	}

	static function GetNextID( $table, $identifier = 'id', $type = 'next', $start = 1 ) {
		if ( $type=='next' ) {
			$sql = "SELECT MAX(".$identifier.") AS id FROM `".DB_PREFIX.$table."`";
			$row = FRAME_CORE::DB()->GetFirstResult($sql);
			$id = $row['id']+1;
		}
		if ( $type=='empty' ) {
			$n = $start;
			$found=false;
			do	{
				$sql = "SELECT * FROM `".DB_PREFIX.$table."` WHERE `".$identifier."`='".$n."'";
				if ( FRAME_CORE::DB()->RowsCount($sql)==0 ) {
					$id = $n;
					$found = true;
				}
				$n++;
			}
			while ($found!=true);
		}
		return $id;
	}

	static function SqlToUnixTime($time = '') {
		$time = str_replace('-', '', $time);
		$time = str_replace(':', '', $time);
		$time = str_replace(' ', '', $time);
		return  mktime(
			substr($time, 8, 2),
			substr($time, 10, 2),
			substr($time, 12, 2),
			substr($time, 4, 2),
			substr($time, 6, 2),
			substr($time, 0, 4)
		);
	}

	static function SqlToRusDate($date) {
		$e=explode('-',$date);
		$p_d=$e[2].".".$e[1].".".$e[0];
		if (FUNC::IsDate($p_d)) {
			return $p_d;
		}
		return false;
	}

	static function RusToSqlDate($date) {
		$e=explode('.',$date);
		$p_d=$e[2]."-".$e[1]."-".$e[0];
		if (FUNC::IsSqlDate($p_d)) {
			return $p_d;
		}
		return false;
	}

	static function TimestampToRus($string, $seconds = true) {
		$m=explode(' ',trim($string));
		$e=explode('-',$m[0]);
		$p_d=$e[2].".".$e[1].".".$e[0];
		if (!FUNC::IsDate($p_d)) { return false; }
		$n = strlen($m[1]);
		$p_t = $m[1];
		if ($seconds && $n==5) {
			$p_t = $m[1].':00';
			if (!FUNC::IsTime($p_t)) { return false; }
		}
		if (!$seconds && $n==8) {
			$p_t = substr($m[1],0,5);
			if (!FUNC::IsTime($p_t,false)) { return false; }
		}
		$stamp = $p_d.' '.$p_t;
		return $stamp;
	}

	static function RusToTimestamp($string, $seconds = true) {
		$m=explode(' ',trim($string));
		$e=explode('-',$m[0]);
		$p_d=$e[2]."-".$e[1]."-".$e[0];
		if (!FUNC::IsSqlDate($p_d)) { return false; }
		$n = strlen($m[1]);
		$p_t = $m[1];
		if ($seconds && $n==5) {
			$p_t = $m[1].':00';
			if (!FUNC::IsTime($p_t)) { return false; }
		}
		if (!$seconds && $n==8) {
			$p_t = substr($m[1],0,5);
			if (!FUNC::IsTime($p_t,false)) { return false; }
		}
		$stamp = $p_d.' '.$p_t;
		return $stamp;
	}

	static function ExtructTime($string, $seconds = true, $check = true) {
		$m=explode(' ',trim($string));
		$e = $m[1];
		$n = strlen($e);
		if ($check) {
			$sec = true;
			if ( $n==5 ) { $sec = false; }
			if ( !FUNC::IsTime($e,$sec) ) { return false; }
		}
		if ($seconds && $n==5) {
			$e = $e.':00';
		}
		if (!$seconds && $n==8) {
			$e = substr($e,0,5);
		}
		return $e;
	}

	static function ExtructDate($string, $sql_date = false, $check = true) {
		$m=explode(' ',trim($string));
		$e = $m[0];
		if ($check) {
			if ( !$sql_date && !FUNC::IsSqlDate($e) ) { return false; }
			if ( $sql_date && !FUNC::IsDate($e) ) { return false; }
		}
		return $e;
	}

	static function ClearDir($path){
		if(file_exists($path) && is_dir($path)) {
			$dirHandle = opendir($path);
			while (false !== ($file = readdir($dirHandle))) {
				if ($file!='.' && $file!='..') {
					$tmpPath=$path.DIRECTORY_SEPARATOR.$file;
					if (is_dir($tmpPath)) {
						Functions::RemoveDir($tmpPath);
					}
					else {
						if(file_exists($tmpPath)) {
							unlink($tmpPath);
						}
					}
				}
			}
			closedir($dirHandle);
			return true;
		}
		return false;
	}

	static function RemoveDir($path){
		if(Functions::RemoveFromDir($path)) {
			rmdir($path);
			return true;
		}
		return false;
	}

	static function CopyDir($from,$to) {
		if ($dir = @opendir($from)) {
			mkdir($to);
			while (($file = readdir($dir)) !== false) {
				if ($file!='.' && $file!='..') {
					if (is_dir($from.DIRECTORY_SEPARATOR.$file)) {
						Functions::CopyDir($from.DIRECTORY_SEPARATOR.$file,$to.DIRECTORY_SEPARATOR.$file);
					}
					if (is_file($from.DIRECTORY_SEPARATOR.$file)) {
						copy($from.DIRECTORY_SEPARATOR.$file, $to.DIRECTORY_SEPARATOR.$file);
					}
				}
			}
			closedir($dir);
			return true;
		}
		return false;
	}

	static function IsValidEmail($email) {
		if (!preg_match("/[0-9a-z_\.\-]+@[0-9a-z_\.\-^\.]+\.[a-z]{2,3}/i", $email))  {
			return false;
		}
		return true;
	}

	static function IsValidFileName($str) {
		if ( trim($str)=='' ) { return false; }
		if ( !preg_match("/[a-zA-Z0-9_\-\.]/i",$str) ) { return false; }
		return true;
	}

	static function IsValidPictureName($str) {
		if ( trim($str)=='' ) { return false; }
		if ( !preg_match("/[a-zA-Z0-9_\-\.]+\.[jpg|jpeg|JPG|JPEG|png|PNG|gif|GIF]/i",$str) ) { return false; }
		return true;
	}

	static function IsInteger($str) {
		if (preg_match("|^[\d]+$|", $str))  {
			return true;
		}
		return false;
	}

	static function IsFloat($str) {
		if (preg_match("|^[\d]*\.[\d]*$|", $str))  {
			return true;
		}
		return false;
	}

	static function IsDate($date) {
		if (preg_match("|^[\d]{2}.[\d]{2}.[\d]{4}$|", $date)) {
			return true;
		}
		return false;
	}

	static function IsSqlDate($date) {
		if (preg_match("|^[\d]{4}-[\d]{2}-[\d]{2}$|", $date)) {
			return true;
		}
		return false;
	}

	static function IsTime($time,$s=true) {
		if ($s) { $match = "|^[\d]{2}:[\d]{2}:[\d]{2}$|"; } else { $match = "|^[\d]{2}:[\d]{2}$|"; }
		if (preg_match($match, $time)) {
			return true;
		}
		return false;
	}
	
	static function IsTimestamp( $string, $seconds=true ) {
		$m=explode(' ',trim($string));
		$e=explode('-',$m[0]);
		$p_d=$e[2].".".$e[1].".".$e[0];
		if (!FUNC::IsDate($p_d)) { return false; }
		$n = strlen($m[1]);
		$p_t = $m[1];
		if ($seconds && $n==5) {
			$p_t = $m[1].':00';
			if (!FUNC::IsTime($p_t)) { return false; }
		}
		if (!$seconds && $n==8) {
			$p_t = substr($m[1],0,5);
			if (!FUNC::IsTime($p_t,false)) { return false; }
		}
		return true;
	}	

	static function TextAreaToHtml($s) {
		$s = nl2br($s);
		$s = str_replace('\r','\n',$s);
		$s = str_replace('\n','',$s);
		return($s);
	}

	static function HtmlToTextArea($s) {
		$s = str_replace('<br />','',$s);
		return($s);
	}

	static function GetWrongSearchVariants($s) {
		$res = Array();
		$res[] = '_'.$s;
		for ($i=0; $i<strlen($s); $i++) {
			$char = $s[$i];
			$sp1 = substr($s,0,$i);
			$sp2 = substr($s,$i+1);
			$res[] = $sp1.'_'.$sp2;
			$res[] = $sp1.$char.'_'.$sp2;
			$res[] = $sp1.$sp2;
		}
		return $res;
	}

	static function PriceSpacer($s,$spacer = ' ') {
		$pricea = explode('.',$s);
		$pricer = strrev($pricea[0]);
		for($i=0;$i<strlen($pricer);$i++) {
			$pricer_ .= $pricer[$i];
			if ((($i+1)%3)==0) { $pricer_ .= $spacer; }
		}
		$price = strrev($pricer_);
		if ( count($pricea)>1 ) { return $price.'.'.$pricea[1]; }
		else { return $price; }
	}

	static function HexToRgb($color) {
		$color = preg_replace("/[^0-9A-Fa-f]/", '', $color);
		$rgb_array = array();
		if (strlen($color) == 6) {
			$rgb_array['red'] = hexdec(substr($color, 0, 2));
			$rgb_array['green'] = hexdec(substr($color, 2, 2));
			$rgb_array['blue'] = hexdec(substr($color, 4, 2));
		} elseif (strlen($color) == 3) {
			$rgb_array['red'] = hexdec(str_repeat(substr($color, 0, 1), 2));
			$rgb_array['green'] = hexdec(str_repeat(substr($color, 1, 1), 2));
			$rgb_array['blue'] = hexdec(str_repeat(substr($color, 2, 1), 2));
		} else {
			return false;
		}
		return $rgb_array;
	}

	static function RgbToHex($r, $g, $b) {
		$r = dechex($r);
		If (strlen($r) < 2) $r = '0'.$r;
		$g = dechex($g);
		If (strlen($g) < 2) $g = '0'.$g;
		$b = dechex($b);
		If (strlen($b) < 2) $b = '0'.$b;
		return "#".$r.$g.$b;
	}

}


?>
