<?php

/**
* 
*/
class MainController{
	protected $var = array();
	protected $db = null;

	protected $get = null;

	function __construct(){
		$lang = $this->getlang();
		if(empty($lang)) $this->setlang("ru");
	}
	public function getlang(){
		if(empty($_SESSION['lang'])){
			return null;
		}
		else{
			return $_SESSION['lang'];
		}
	}
	public function setlang($lang){
		if(is_dir("language/".$lang)){
			$_SESSION['lang'] = $lang;
		}
	}
	public function route($path=null){
		if(empty($path)){
			if(empty($_SERVER['argv']) || count($_SERVER['argv']) < 2) $this->get = $_GET;
			else {
				$this->get['page'] = (!empty($_SERVER['argv'][1])) ? $_SERVER['argv'][1] : "";
				$this->get['action'] = (!empty($_SERVER['argv'][2])) ? $_SERVER['argv'][2] : "";
			}

			if(!empty($this->get['page'])){
				$this->loadController($this->get['page']);
			}
			else{
				$this->loadController("home");
			}
		}
		else{
			header("Location ".$_SERVER['REQUEST_SCHEME']."//".$_SERVER['SERVER_NAME']."/".$path);
		}
	}

	protected function loadlanguage($template){
		if(file_exists(DIR."/language/".$this->getlang()."/".$template.".lang.php")){

			include(DIR."/language/".$this->getlang()."/".$template.".lang.php");
			return $data;

		}
	}
	protected function loadController($controller){
		if(file_exists(DIR."/controller/".$controller.".controller.php")){
			include_once(DIR."/controller/".$controller.".controller.php");
			$className = "Controller".ucfirst($controller);
			$obj = new $className();
			$obj->index($this->get); /** controll post/get n controllers
			
			/*if($controller=='header' || $controller=='footer'){
				$obj->index();
			}
			else{
				if(empty($_GET['action'])){
					$obj->index();
				}
				else{				
					if(method_exists($obj,$_GET['action'])){
						$obj->$_GET['action']();	
					}			
					else{
						$this->outputContent("404");
					}										
				}	
			}*/
		}	
		else{
			$this->outputContent("404");
		}
	}

	protected function loadModel($model){
		if(file_exists(DIR."/model/".$model.".class.php")){
			include_once(DIR."/model/".$model.".class.php");
			$modelName = "Model".ucfirst($model);
			return new $modelName();
		}
	}

	protected function outputContent($template,$arr=null){
		if(!empty($arr)){
			extract($arr,EXTR_SKIP);
		}
		
		include_once(DIR."/view/".$template.".tpl");
	}

	protected function getConfig($arr){
		extract($arr,EXTR_SKIP);
		include(DIR."/mail_config.php");
		return array(
			'rules_config' => $rules_config,
			'template_config' => $template_config,
			'notification_email_config' => $notification_email_config
		);
	}
	

	protected function db_connect(){
		$this->db = $this->loadModel("db");
		$this->db->host = DB_HOST;
		$this->db->user = DB_USER_NAME;
		$this->db->password = DB_USER_PASSWORD;
		$this->db->database = DB_NAME;
		$this->db->connect();
	}

}