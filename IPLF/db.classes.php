<?php

/*

*** SQL Database Works Class
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.0

*** Copyright (C) 2013 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/db/

*/


class DB
{
	public $link_error_include;
	public $log_errors = true;
	public $error_prefix = 'Не могу выполнить запрос';
	private $link;

	function __construct($main = true, $error_file = ''){
		if ( $error_file == '' ) {
			if ( defined('BASE_PATH') ) {
				$this->link_error_include = BASE_PATH.'/sqlerror.php';
			}
			else {
				$this->link_error_include = false;
			}
		}
		else {
			$this->link_error_include = $error_file;
		}
		if ( $main ) { $this->connect(); }
		return true;
	}

	public function Connect($db_host=DB_HOST, $db_user=DB_USER, $db_pass=DB_PASS, $db_base=DB_BASE){
		if (!@$this->link= mysql_connect($db_host, $db_user, $db_pass)) {
			if ( $this->link_error_include ) {
				include($this->link_error_include);
			}
			else {
				echo 'SQL server connection error';
			}
			die();
		}
		if (!mysql_select_db($db_base, $this->link)) {
			if ( $this->link_error_include ) {
				include($this->link_error_include);
			}
			else {
				echo 'SQL server connection error';
			}
			die();
		}
		mysql_query("SET NAMES '".DB_CHARSET."'");
		mysql_query("SET collation_connection = '".COLLATION_CHARSET."'");
		mysql_query("SET collation_server = '".COLLATION_CHARSET."'");
		mysql_query("SET character_set_client = '".DB_CHARSET."'");
		mysql_query("SET character_set_connection = '".DB_CHARSET."'");
		mysql_query("SET character_set_results = '".DB_CHARSET."'");
		mysql_query("SET character_set_server = '".DB_CHARSET."'");
		return true;
	}

	private function ErrorOut($query,$error) {
		$mes = $this->error_prefix." (".$query.")<br />". mysql_error();
		if ( FRAME_CORE::Parameter('show_errors')==1 ) {
			echo "<br />".$mes."<br />";
		}
		if ( $this->log_errors ) {
			$log=new LOGER("sql_errors.log");
			$log->PutLine($_SERVER['REQUEST_URI'].' --- '.$mes);
			$log->close();
		}
		exit();
	}

	public function GetFirstResult($query) {
		if (!$result=mysql_query($query, $this->link)) { $this->ErrorOut($query,mysql_error()); }
		$rows=mysql_num_rows($result);
		if ($rows>0) {
			$row = mysql_fetch_assoc($result);
			return $row;
		}
		else {
			return false;
		}
	}

	public function GetResult($query) {
		if (!$result=mysql_query($query, $this->link)) { $this->ErrorOut($query,mysql_error()); }
		$rows=mysql_num_rows($result);
		if ($rows>0) {
			while($rows = mysql_fetch_assoc($result)){
				$row[] = $rows;
			}
			return $row;
		}
		else {
			return false;
		}
	}

	public function RowsCountByConditions($table,$conditions) {
		$query = 'SELECT COUNT(*) as num FROM '.DB_PREFIX.$table.' '.$conditions;
		if (!$result=mysql_query($query, $this->link)) { $this->ErrorOut($query,mysql_error()); }
		$rows=mysql_num_rows($result);
		if ($rows>0) {
			$row = mysql_fetch_assoc($result);
			return $row['num'];
		}
		else {
			return false;
		}
	}

	public function RowsCount($query) {
		if (!$result=mysql_query($query, $this->link)) { $this->ErrorOut($query,mysql_error()); }
		return mysql_num_rows($result);
	}

	public function Go($query) {
		if (!$result=mysql_query($query, $this->link)) { $this->ErrorOut($query,mysql_error()); }
		return true;
	}

	public function GetSource($query) {
		if (!$result=mysql_query($query, $this->link)) { $this->ErrorOut($query,mysql_error()); }
		return $result;
	}

	public function MultyGo($query) {
		$mass=explode(';;',$query);
		foreach ($mass as $d) {
			$this->go($d);
		}
		return true;
	}

	public function CheckForErrors($query) {
		if (!$result=mysql_query($query, $this->link)) { return false; } else { return true; }
	}

	public function TableExists($table) {
		$query="SHOW TABLES LIKE '".$table."'";
		if ( $this->RowsCount($query)>0 ) { return true; }
		return false;
	}

	public function FieldExists($table, $field) {
		$query="SHOW FIELDS FROM `".$table."` LIKE '".$field."'";
		if ( RowsCount($query)>0 ) { return true; }
		return false;
	}

}


?>