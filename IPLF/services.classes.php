<?php

/*

*** Service Classes
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.0

*** Copyright (C) 2013 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/services/

*/


class TIMER
{
	private $start;
	private $stop;

	private function GetCurrent() {
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		return $mtime;
	}

	public function SetStart() {
		$this->srart = $this->GetCurrent();
		return true;
	}

	public function SetStop() {
		$this->stop = $this->GetCurrent();
		return true;
	}

	public function GetResult() {
		$totaltime = round(($this->stop - $this->srart),2);
		return $totaltime;
	}

}


class LOGER
{
	public $log_folder;
	private $log;

	function __construct() {
		$this->log_folder = BASE_PATH.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR;
		return true;
	}

	function Open($file) {
		if ($this->log=fopen($this->log_folder.$file, "a")) {
			return true;
		}
		return false;
	}

	public function PutLine($string) {
		fputs($this->log,"[".date('d.m.Y H:i:s')."] ".$string."
");
		return true;
	}

	public function Close() {
		fclose($this->log);
		return true;
	}

}


?>