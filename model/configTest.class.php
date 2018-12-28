<?php

/**
 * 
 */
class ModelConfigTest{

	public $rules;
	public $templates;
	public $notif_email;

	private $logErrors;
	private $logWarning;

	public function run($and = null){
		$rules = ($and == null) ? $this->rules : $and['value'];
		if(count($rules)>0){
			foreach ($rules as $key => $value) {
				switch ($key) {
					case 'subject':
						$this->checkSubject($key,$value,$and['param']);
						$this->checkAnd($key,$value);
						break;
					case 'capslock':
						$this->checkCapslock($key,$value,$and['param']);
						$this->checkAnd($key,$value);
						break;
					case 'keywords':
						$this->checkKeywords($key,$value,$and['param']);
						$this->checkAnd($key,$value);
						break;
					case 'sender':
						$this->checkSender($key,$value,$and['param']);
						$this->checkAnd($key,$value);
						break;
					case 'mes_number':
						$this->checkMesnumber($key,$value,$and['param']);
						$this->checkAnd($key,$value);
						break;
				}
			}
		}
		else{
			$this->addToLog("w","Правила не используются.");
		}
	}
	public function getLogs(){
		return array(
			'warning' => $this->logWarning,
			'error'	=> $this->logErrors
		);
	}


	private function checkSubject($key,$value,$and = null){
		if(!empty($value['pattern'])){
			if($and == null){
				if(empty($value['template_id'])){
					$this->addToLog("e","template_id в $key не установлен!!");
				}
				else $this->checkTemplate($value['template_id']);
				if(empty($value['notification']) || $value['notification'] == 1){
					if(empty($value['notification_template_id'])){
						$this->addToLog("e","notification_template_id в $key не установлен!!");
					}
				}
			}
		}
		else{
			$this->addToLog("w","pattern в $key не установлен. Правило не сработает. ");
		}
	}
	private function checkCapslock($key,$value,$and = null){
		if($and == null){
			if(empty($value['template_id'])){
				$this->addToLog("e","template_id в $key не установлен!!");
			}
			else $this->checkTemplate($value['template_id']);
			if(empty($value['notification']) || $value['notification'] == 1){
				if(empty($value['notification_template_id'])){
					$this->addToLog("e","notification_template_id в $key не установлен!!");
				}
			}
		}
	}
	private function checkKeywords($key,$value,$and = null){
		if(!empty($value['pattern'])){
			if($and == null){
				if(empty($value['template_id'])){
					$this->addToLog("e","template_id в $key не установлен!!");
				}
				else $this->checkTemplate($value['template_id']);
				if(empty($value['notification']) || $value['notification'] == 1){
					if(empty($value['notification_template_id'])){
						$this->addToLog("e","notification_template_id в $key не установлен!!");
					}
				}
			}
		}
		else{
			$this->addToLog("w","pattern в ".$key." не установлен. Правило не сработает.");
		}
	}
	private function checkSender($key,$value,$and = null){
		if(!empty($value['pattern'])){
			if($and == null){
				if(empty($value['template_id'])){
					$this->addToLog("e","template_id в $key не установлен!!");
				}
				else $this->checkTemplate($value['template_id']);
				if(empty($value['notification']) || $value['notification'] == 1){
					if(empty($value['notification_template_id'])){
						$this->addToLog("e","notification_template_id в $key не установлен!!");
					}
				}
			}
		}
		else{
			$this->addToLog("w","pattern в $key не установлен. Правило не сработает.");
		}
	}
	private function checkMesnumber($key,$value,$and = null){
		$params = array("equal","more","less");

		if(!empty($value['pattern'])){
			if($and == null){
				for ($i=0; $i < count($value['pattern']); $i++) { 
					$trig_param = false;
					foreach ($value['pattern'][$i] as $k_pattern => $v_pattern) {
						if(!$trig_param){
							if(in_array($k_pattern, $params)){
								$trig_param = true;
								if(!preg_match('/[0-9]+/', $v_pattern))
									$this->addToLog("e","Ошибка в $key. Невалидное зачение параметра - $k_pattern.");
								if(empty($value['pattern'][$i]['template_id'])){
									$this->addToLog("e","template_id в $key параметра - $k_pattern не установлен!!");
								}
								else $this->checkTemplate($value['pattern'][$i]['template_id']);
							}
							else if($k_pattern!="template_id"){
								$this->addToLog("e","Ошибка в $key. Задан неизвестный параметр - $k_pattern.");
							}
						}
					}
				}
				if(empty($value['notification']) || $value['notification'] == 1){
					if(empty($value['notification_template_id'])){
						$this->addToLog("e","notification_template_id в $key не установлен!!");
					}
				}
			}
		}
		else{
			$this->addToLog("w","pattern в $key не установлен. Правило не сработает.");
		}
	}


	private function addToLog($category,$text){
		switch ($category) {
			case 'e':
				$this->logErrors[] = $text;
				break;
			case 'w':
				$this->logWarning[] = $text;
				break;
		}
	}
	private function checkTemplate($name){
		if(!empty($this->templates[$name])){
			if(!empty($this->templates[$name]['template'])){
				$file = $this->templates[$name]['template'].".tpl";
				if(!file_exists(DIR."/view/mail_templates/".$file) && !file_exists(DIR."/view/mail_templates/".$file)){
					$this->addToLog("e","Файл $file для шаблона сообщения $name отсутсвует");
				}
			}
			else $this->addToLog("e","template для шаблона ответа - $name отсутсвует");
		}
		else $this->addToLog("e","Шаблон ответа - $name отсутсвует");
	}
	private function checkAnd($key,$value){
		if(array_key_exists("and",$value)){
			if(empty($value['and']))	$this->addToLog("w","Логическое И в $key создано, но пустое");
			else $this->run(array(
				'param' => true,
				'value' => $value['and']
			));

		}
	}
}