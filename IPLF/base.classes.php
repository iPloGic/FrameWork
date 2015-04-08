<?php

/*

*** Base Classes
*** Is a part of iPloGic IPLF FrameWork 1.x
*** Version 1.0

*** Copyright (C) 2014 iPloGic, LLC. All rights reserved.
*** License GNU/GPL http://www.iplogic.ru/licenses/gpl/
*** Link http://www.iplogic.ru, info@iplogic.ru.

*** Manual page http://www.iplogic.ru/framework/manual/base/

*/


class FRAME_CORE
{
	public $access_folder = '';
	public $component;
	public $content;
	public $tpl;
	public $parameters=Array();
	private $section_name;
	private $database;
	private $access_area = 'public';
	private $user;

	static function getInstance() {
		static $me;
		if (is_object($me) == true) {
			return $me;
		}
		$me = new FRAME_CORE;
		return $me;
	}

	public function DBInit() {
		$this->database = new DB(true);
		if ( is_object($this->database) ) {
			return true;
		}
		else {
			return false;
		}
	}

	public function Init($script=false) {
		global $wfconfig_access_areas, $wfconfig_supported_get, $wfconfig_access_areas_ids;
		define("BASE_URL",WEB_PROTOCOL.$_SERVER['HTTP_HOST'].SCRIPT_FOLDER);
		define("BASE_PATH",str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,$_SERVER['DOCUMENT_ROOT'].str_replace('/',DIRECTORY_SEPARATOR,SCRIPT_FOLDER)));
		define("COMPONENTS_PATH",BASE_PATH.WFCONFIG_COMPONENTS_FOLDER.DIRECTORY_SEPARATOR);
		define("COMPONENTS_URL",BASE_URL.WFCONFIG_COMPONENTS_URI."/");
		$this->parameters['show_errors'] = false;
		if ( ini_get('display_errors')==1 ) { $this->parameters['show_errors'] = true; }
		$this->parameters['access_areas_array'] = $wfconfig_access_areas_ids;
		$this->parameters['header_dynamic_tags'] = '';
		// get base parameters
		if (WFCONFIG_GET_SETTINGS_TABLE) {
			$sql = "SELECT * FROM `".DB_PREFIX."settings`";
			$rows=$this->database->GetResult($sql);
			if ($rows) {
				foreach($rows as $par){
					$this->parameters[$par['name']]=$par['value'];
				}
			}
		}
		// get access area
		foreach ($wfconfig_access_areas as $key=>$val) {
			if (substr(str_replace(SCRIPT_FOLDER,'',$_SERVER['REQUEST_URI']),0,strlen($val))==$val) {
				$this->access_area = $key;
				$this->access_folder = $val;
			}
		}
		// page url handling
		$this->parameters['pagination_position']=1;
		if (!$script) {
			$this->UriDecode();
		}
		// get section name
		$this->section_name = $this->parameters['url_variables'][1];
		if ($this->section_name == '') { $this->section_name = 'home'; }
		// get templates paths
		$this->DefineViewConstants();
		// initiate user and user rights
		if (WFCONFIG_INITIATE_REGISTRED_USER) {
			$this->parameters['authorized']=false;
			$this->user = new USER();
			if (isset($_SESSION['authorized_user']) && $_SESSION['authorized_user']!="") {
				if ($this->user->ConstructFromTable($_SESSION['authorized_user'])) {
					$this->parameters['authorized']=true;
				}
			}
			$this->user->GetAccess();
			if ( !$this->user->area_access ) {
				if (file_exists(BASE_PATH.$this->access_folder.DIRECTORY_SEPARATOR.'area_authorization.php')) {
					include(BASE_PATH.$this->access_folder.DIRECTORY_SEPARATOR.'area_authorization.php');  die();
				}
				else { echo "<h1>Access to this area is prohibited</h1>"; die(); }
			}
		}
		return true;
	}

	private function UriDecode() {
		global $wfconfig_supported_get;
		$ar_urlv=explode("/", $_SERVER['REQUEST_URI']);
		$ar_last=end($ar_urlv);
		$sup_get = false;
		foreach ( $wfconfig_supported_get as $p ) {
			if (substr_count($ar_last,$p)>0) {
				$sup_get = true;
			}
		}
		if ($sup_get) {
			array_pop($ar_urlv);
			$ar_urlv[]='';
		}
		$_SERVER['REQUEST_URI'] = implode('/',$ar_urlv);
		$urilen=strlen($_SERVER['REQUEST_URI']);
		if ( $this->access_folder != '' ) { $lbase = SCRIPT_FOLDER.$this->access_folder.'/'; }
		else { $lbase = SCRIPT_FOLDER; }
		if (substr($_SERVER['REQUEST_URI'],$urilen-strlen(REF_END))!=REF_END && $_SERVER['REQUEST_URI']!=$lbase) {
			header("Location: ".$_SERVER['REQUEST_URI'].REF_END." ");
		}
		$uri= str_replace(REF_END,'/',$_SERVER['REQUEST_URI']);
		$fl=strlen(SCRIPT_FOLDER)-1;
		$uri=substr($uri,$fl);
		if ($this->access_area!='public') {
			$uri=str_replace('/'.$this->access_folder,'',$uri);
		}
		$urlv=explode("/", $uri);
		if ($urlv[1]=="print") {
			$this->parameters['print_page']=true;
			$uri=str_replace('/print','',$uri);
			$urlv=explode("/", $uri);
		}
		if (substr($urlv[count($urlv)-2],0,2)=="p=") {
			$this->parameters['pagination_position']=substr($urlv[count($urlv)-2],2);
		}
		$this->parameters['url_variables_real'] = $urlv;
		if ( WFCONFIG_USE_ALIASES_TABLE ) {
			$this->parameters['url_variables'] = $this->AliasDecode($this->parameters['url_variables_real']);
		}
		else {
			$this->parameters['url_variables'] = $this->parameters['url_variables_real'];
		}
		return true;
	}

	private function AliasDecode($urlv) {
		$sql = "SELECT * FROM ".DB_PREFIX."aliases WHERE alias='".$urlv[1]."' OR alias LIKE '".$urlv[1]."/%'";
		$rows=$this->database->GetResult($sql);
		if ($rows) {
			foreach($rows as $row) {
				$aaliases[$row['alias']]=$row['url'];
			}
			$talies = $urlv[1];
			$alias = $urlv[1];
			for($i=2; $i<=count($urlv); $i++) {
				$talies .= '/'.$urlv[$i];
				if (isset($aaliases[$talies])) {
					$alias = $talias;
				}
			}
			$uri=$aaliases[$alias];
			$urlv=explode("/", $uri);
			return $urlv;
		}
		else {
			return $urlv;
		}
	}

	protected function DefineViewConstants() {
		if ($this->access_area!='public') {
			define("VIEW_PATH",BASE_PATH.$this->access_folder.DIRECTORY_SEPARATOR.WFCONFIG_VIEW_FOLDER.DIRECTORY_SEPARATOR);
			define("VIEW_URL",BASE_URL.$this->access_folder."/".WFCONFIG_VIEW_URI."/");
		}
		else {
			define("VIEW_PATH",BASE_PATH.WFCONFIG_VIEW_FOLDER.DIRECTORY_SEPARATOR);
			define("VIEW_URL",BASE_URL.WFCONFIG_VIEW_URI."/");
		}
		return true;
	}

	public static function DB() {
		return self::getInstance()->database;
	}

	public static function AccessArea() {
		$access_area = self::getInstance()->access_area;
		if ($access_area && $access_area!='') { return $access_area; }
		else { return false; }
	}

	public static function AccessAreaID() {
		$area = array_search(self::getInstance()->access_area, self::getInstance()->parameters['access_areas_array']);
		if ($area && $area!='') { return $area; }
		else { return false; }
	}

	public static function Section() {
		$section = self::getInstance()->section_name;
		if ($section && $section!='') { return $section; }
		else { return false; }
	}

	public static function Parameter($par,$key='') {
		$parameter = self::getInstance()->parameters[$par];
		if ($key=='') { return $parameter; }
		else { return $parameter[$key]; }
	}

	public static function SetParameter($val,$par,$key='',$add=false) {
		if ($key=='') {
			if ( $add ) { self::getInstance()->parameters[$par] .= $val; }
			else { self::getInstance()->parameters[$par] = $val; }
		}
		else {
			if ( $add ) { self::getInstance()->parameters[$par][$key] .= $val; }
			else { self::getInstance()->parameters[$par][$key] = $val; }
		}
		return true;
	}

	public static function User() {
		$user = self::getInstance()->user;
		if (is_object($user)) { return $user; }
		else { return false; }
	}

	public function RunController() {
		if(!CONTROLLER::Initiate($this->section_name)) {
			FRAME_CORE::Error404();
			die();
		}
		$this->component = new Component();
		return true;
	}

	public function LayoutBuild() {
		$this->content = $this->component->content;
		$layout=$this->component->layout;
		@$header=file_get_contents(VIEW_PATH.'layouts'.DIRECTORY_SEPARATOR.'header.tpl');
		@$footer=file_get_contents(VIEW_PATH.'layouts'.DIRECTORY_SEPARATOR.'footer.tpl');
		$this->tpl = new VIEW('layouts'.DIRECTORY_SEPARATOR.$layout);
		$this->tpl->SetVar('header',$header);
		$this->tpl->SetVar('footer',$footer);
		$this->tpl->from_tpl = str_replace( '<[header]>', $header, $this->tpl->from_tpl );
		$this->tpl->from_tpl = str_replace( '<[footer]>', $footer, $this->tpl->from_tpl );
		$this->tpl->from_tpl = str_replace( '<[header_dynamic_tags]>',$this->parameters['header_dynamic_tags'],$this->tpl->from_tpl );
		return true;
	}

	public function Render() {
		$this->tpl->SetVar('content',$this->content);
		$this->tpl->SetVar('title',htmlspecialchars(strip_tags($this->component->title)));
		$this->tpl->SetVar('description',htmlspecialchars(strip_tags($this->component->description)));
		$this->tpl->SetVar('keywords',htmlspecialchars(strip_tags($this->component->keywords)));
		$html_result = $this->tpl->GetGeneral();
		$html_result = str_replace('<[base_url]>',BASE_URL,$html_result);
		return $html_result;
	}

	static function Error404() {
		if ( file_exists( BASE_URL."404.php" ) ) {
			header("Location: ".BASE_URL."404.php ");
		}
		else {
			echo "Error 404. Page not found. Check URL, or try later.";
		}
		die();
	}

	public function Execute() {
		$this->RunController();
		$this->LayoutBuild();
		return $this->Render();
	}

}


class CONTROLLER
{
	public $layout = 'general.tpl';
	public $content = '';
	public $title = '';
	public $description = '';
	public $keywords = '';
	private $template;

	static function Initiate($section) {
		$f = WFCONFIG_ARCHITECTURE.'Initiate';
		return CONTROLLER::$f($section);
	}

	static function PFCSInitiate($section) {
		$cache_content = CONTROLLER::ReadComponentsCache();
		$area = FRAME_CORE::AccessArea();
		$cia = unserialize($cache_content);
		unset($cache_content);
		$name = $cia['component_by_section'][$area][$section];
		unset($cia);
		if ($name != '') {
			if (file_exists(COMPONENTS_PATH.$name.DIRECTORY_SEPARATOR.'init.php')) {
				include(COMPONENTS_PATH.$name.DIRECTORY_SEPARATOR.'init.php');
				$model = $models[$area][$section];
				if (file_exists(COMPONENTS_PATH.$name.DIRECTORY_SEPARATOR.$model)) {
					include(COMPONENTS_PATH.$name.DIRECTORY_SEPARATOR.$model);
					return true;
				}
			}
		}
		return false;
	}

	static function PFSInitiate($section) {
		if (file_exists(COMPONENTS_PATH.$section.php)) {
			include(COMPONENTS_PATH.$section.php);
			return true;
		}
		return false;
	}

	static function ReadComponentsCache() {
		if (file_exists(COMPONENTS_PATH.'components.cch')) {
			return file_get_contents(COMPONENTS_PATH.'components.cch');
		}
		else {
			CONTROLLER::CacheComponents();
			CONTROLLER::ReadComponentsCache();
		}
		return true;
	}

	static function CacheComponents() {
		if (WFCONFIG_USE_SECTIONS_TABLE) {
			$str = CONTROLLER::CacheFromTables();
		}
		else {
			$str = CONTROLLER::CacheFromFiles();
		}
		$file=fopen(COMPONENTS_PATH.'components.cch','w');
		if ($file && $str) {
			fputs($file,$str);
			fclose($file);
		}
		else {
			echo "Components cache error"; die();
		}
		return true;
	}

	static function CacheFromTables() {
		$ar = Array();
		$sql = "SELECT ".DB_PREFIX."sections.component,
		               ".DB_PREFIX."sections.area as area,
		               ".DB_PREFIX."sections.section as section,
		               ".DB_PREFIX."components.component as component,
		               ".DB_PREFIX."components.id,
		               ".DB_PREFIX."components.available,
		               ".DB_PREFIX."sections.available
		               FROM ".DB_PREFIX."components, ".DB_PREFIX."sections
		               WHERE ".DB_PREFIX."components.available = 1 AND
		               ".DB_PREFIX."sections.available = 1 AND
		               ".DB_PREFIX."components.id = ".DB_PREFIX."sections.component";
		$row = FRAME_CORE::DB()->GetResult($sql);
		if ($row) {
			foreach($row as $s) {
				if($s['component'] != '') {
					$ar['component_by_section'][FRAME_CORE::Parameter('access_areas_array',$s['area'])][$s['section']] = $s['component'];
				}
			}
			return serialize($ar);
		}
		else {
			return false;
		}
	}

	static function CacheFromFiles() {
		$ar = Array();
		$dirHandle = opendir(COMPONENTS_PATH);
		while (false !== ($file = readdir($dirHandle))) {
			if (is_dir(COMPONENTS_PATH.$file) && $file!='.' && $file!='..') {
				if (file_exists(COMPONENTS_PATH.$file.DIRECTORY_SEPARATOR.'init.php')) {
					unset($controllers);
					include(COMPONENTS_PATH.$file.'/init.php');
					foreach($controllers as $area=>$sects) {
						foreach($sects as $sect=>$f) {
							$ar['component_by_section'][$area][$sect] = $file;
						}
					}
				}
			}
		}
		closedir($dirHandle);
		if (count($ar)) {
			return serialize($ar);
		}
		else {
			return false;
		}
	}

}


?>
