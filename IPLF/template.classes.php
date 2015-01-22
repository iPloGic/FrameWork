<?php

/*

*** Templates Works Class
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.0

*** Copyright (C) 2014 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/views/

*/

class VIEW
{
	public $from_tpl;
	protected $com_var = Array();

	function __construct($file = '') {		if ( $file != '' ) { $this->ConstructFromFile($file); }
		return true;	}

	public function ConstructFromFile($file){		if ( $this->from_tpl = @file_get_contents(VIEW_PATH.$file) ) {
			return true;
		}
		else {
			$this->from_tpl = $text;
			return false;
		}
	}

	public function ConstructFromString($text){
		if ( $text != '' ) {
			$this->from_tpl = $text;
			return true;
		}
		else {
			$this->from_tpl = $text;
			return false;
		}
	}

	public function GetGeneral() {
		$cuted=$this->from_tpl;
		$cuted=$this->GetIf($cuted);
		$cuted=$this->GetForeach($cuted);
		$cuted=$this->GetPHP($cuted);
		$cuted=$this->PutComVars($cuted);
		return $cuted;
	}

	public function GetFragment($fragment) {
		$from='##SET_FRAGMENT '.$fragment.'##';
		$to='##SET_FRAGMENT '.$fragment.' END##';
		$first_pos=mb_strpos($this->from_tpl,$from)+mb_strlen($from);
		$lenght=mb_strpos($this->from_tpl,$to)-$first_pos;
		$cuted=mb_substr($this->from_tpl,$first_pos,$lenght,DB_CHARSET);
		$cuted=$this->GetIf($cuted);
		$cuted=$this->GetForeach($cuted);
		$cuted=$this->GetPHP($cuted);
		$cuted=$this->PutComVars($cuted);
		return $cuted;
	}

	public function SetVar($var, $val) {
		$this->com_var[$var] = $val;
		return true;
	}

	private function GetIf($str) {
		$not_end=false;
		do {
			if(substr_count($str,'<[IF ')>0) {
				$not_end=true;
				$var_first_pos=mb_strpos($str,'<[IF ')+5;
				$var_=mb_substr($str,$var_first_pos,mb_strlen($str),DB_CHARSET);
				$var_=mb_substr($var_,0,mb_strpos($var_,']'),DB_CHARSET);
				$from='<[IF '.$var_.']>';
				$to='<[IF '.$var_.' END]>';
				$first_pos=mb_strpos($str,$from);
				$lenght=mb_strpos($str,$to)-$first_pos+mb_strlen($to);
				$cuted=mb_substr($str,$first_pos,$lenght,DB_CHARSET);
				$carray = explode(' ',$var_);
				$var = $carray[0];
				if ( isset($carray[1]) ) {
					$condition = $carray[1];
					$sample = $carray[2];
					if ( $sample[0]=="'" || $sample[0]=='"' ) { $sample = substr($sample,1,strlen($sample)-2); }
				}
				else {
					$condition = '';
				}
				$accordance = false;
				switch ($condition) {					case '': if ( $this->com_var[$var] != '' ) { $accordance = true; } break;
					case '=': if ( $this->com_var[$var] == $sample ) { $accordance = true; } break;
					case '>': if ( $this->com_var[$var] > $sample ) { $accordance = true; } break;
					case '<': if ( $this->com_var[$var] < $sample ) { $accordance = true; } break;
					case '>=': if ( $this->com_var[$var] >= $sample ) { $accordance = true; } break;
					case '<=': if ( $this->com_var[$var] <= $sample ) { $accordance = true; } break;
				}
				if ($accordance) {
					$str=str_replace($from,'',$str);
					$str=str_replace($to,'',$str);
				}
				else {
					$str=str_replace($cuted,'',$str);
				}
			}
			else {
				$not_end=false;
			}
		}
		while ($not_end);
		return $str;
	}

	private function GetForeach($str) {
		$not_end=false;
		do {
			if(substr_count($str,'<[FOREACH ')>0) {
				$not_end=true;
				$var_first_pos=mb_strpos($str,'<[FOREACH ')+10;
				$var_=mb_substr($str,$var_first_pos,mb_strlen($str),DB_CHARSET);
				$var_=mb_substr($var_,0,mb_strpos($var_,']'),DB_CHARSET);
				$from='<[FOREACH '.$var_.']>';
				$to='<[FOREACH '.$var_.' END]>';
				$first_pos=mb_strpos($str,$from);
				$lenght=mb_strpos($str,$to)-$first_pos+mb_strlen($to);
				$cuted=mb_substr($str,$first_pos,$lenght,DB_CHARSET);
				foreach($this->com_var[$var_] as $val) {					$block.=str_replace("<[".$var_."]>",$val,$cuted);
				}
				$block=str_replace($from,'',$block);
				$block=str_replace($to,'',$block);
				$str=str_replace($cuted,$block,$str);
			}
			else {
				$not_end=false;
			}
		}
		while ($not_end);
		return $str;
	}

	private function GetPHP($str) {
		$not_end=false;
		do {
			if(substr_count($str,'<[PHP ')>0) {
				$not_end=true;
				$var_first_pos=mb_strpos($str,'<[PHP ')+6;
				$var_=mb_substr($str,$var_first_pos,mb_strlen($str),DB_CHARSET);
				$var_=mb_substr($var_,0,mb_strpos($var_,']'),DB_CHARSET);
				$from='<[PHP '.$var_.']>';
				$to='<[PHP '.$var_.' END]>';
				$first_pos=mb_strpos($str,$from);
				$lenght=mb_strpos($str,$to)-$first_pos+mb_strlen($to);
				$cuted=mb_substr($str,$first_pos,$lenght,DB_CHARSET);
				$cuted_=str_replace($from,'',$cuted);
				$cuted_=str_replace($to,'',$cuted_);
				eval($cuted_);
				$str=str_replace($cuted,$result,$str);
			}
			else {
				$not_end=false;
			}
		}
		while ($not_end);
		return $str;
	}

	protected function PutComVars($res) {
		foreach(($this->com_var) as $ind => $val) {
			$res=str_replace('<['.$ind.']>',$val,$res);
		}
		return $res;
	}

	public function ClearComVars() {
		$this->com_var = Array();
		return true;
	}

}

?>