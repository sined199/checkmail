<?php

/**
* 
*/
class ControllerHome extends MainController{
	
	function __construct($argument=null)
	{
		# code...
	}
	
	public function index($get=null){
		if(!empty($get['action'])){
			if(method_exists($this,$get['action'])){
				if($_POST){
					$this->$get['action']();
				}
				else{
					//$this->loadController("header");
					$this->$get['action']();
					//$this->loadController("footer");
				}
			}
		}
		else{
			if($_POST){
				$this->viewpage();
			}
			else{
				$this->loadController("header");
				$this->viewpage();
				$this->loadController("footer");
			}
		}
	}
	/**
		Метод запуска проверки всех доступных аккаунтов на наличие новых писем
	*/
	public function runcheck(){
		$chk = $this->loadModel("checkmail"); // класс с методами приема и обработки почты
		$this->db_connect(); // подключение к бд
		$chk->linkDB = $this->db; // присваивание экземпляра объекта свойству класс "checkmail"

		/*$accounts = $chk->getAllAccounts(); 
		foreach ($accounts as $value) {
			$chk->hostname = $value['imap_host']; // путь подключения
			$chk->username = $value['username']; // имя польователя
			$chk->password = $value['password']; // пароль пользователя*/

			$chk->hostname = MAIL_IMAP_HOST; // путь подключения
			$chk->username = MAIL_USER; // имя польователя
			$chk->password = MAIL_PASSWORD; // пароль пользователя*/


			$connect = $chk->_connectToGmail(); // подключение к почте (IMAP)
			if($connect!=null){ // если подключение установленно двигаемся дальше
				$mes = $chk->getMessages(); // выбираем последние не прочитанные сообщения
				if(count($mes)>0){ // если сообщений больше 0
					foreach ($mes as $m){ // перебираем все сообщения$chk->mail = $m; // врменно присваиваем сообщение
						$chk->mail = $m;
						$param = $chk->checkMail(); // проверяем сообщение на окончание @marketplace.amazon.com
						if($param!=false){ // если сообщение подходит под правило
							
							$chk->addCustomer();

							$template_vars = array(
								'count'=>$chk->getEmailCount(),
								'username'=>$chk->username,
								'email'=>$m['from']['email'],
								'mail'=>$m['message'],
								'buyer_name'=>$m['from']['name']
							);
							$trigger = false;
							$send = false;
							$task_vars = array(
								'email_body'=>'',
								'email_subject'=>'',
								'email'=>'',
								//'id_email_from_send'=>$value['id']
							);

							$config = $this->getConfig($template_vars);

							if(count($config['rules_config']) > 0){
								$find = array(
									'status' => false
								);
								foreach ($config['rules_config'] as $rules_key => $rules_value) {
									if(!$find['status']){
										$find = $this->switch_rules($rules_key,$rules_value,$chk);
									}
									else break;
								}
								if($find['status']){
									$trigger = true;
									if($find['send'] == 1){
										$task_vars['email_body'] = $this->obGetTemplate($config['template_config'][$find['item']]['template'],$template_vars);
										$task_vars['email_subject'] = (!empty($config['template_config'][$find['item']]['subject'])) ? "Re: ".$config['template_config'][$find['item']]['subject'] : "Re: ".$m['subject'];
										$task_vars['email'] = $find['email'];
										$send = true;
										$find['noresp'] = 0;
									}
									if($find['noresp'] != 0){
										$chk->linkNoResp();
									}
									
									if($find['notification'] == 1){
										$chk->setTask(array(
											'email_body'=>$this->obGetTemplate($config['template_config'][$find['notification_template']]['template'],$template_vars),
											'email_subject'=>$config['template_config'][$find['notification_template']]['subject'],
											'email' => $config['notification_email_config'],
											//'id_email_from_send'=>$value['id']
										));
									}
								}
							}
							if(!$trigger){
								$param = $chk->checkCount($chk->getEmailCount());
								$task_vars['email_body'] = $this->obGetTemplate($config['template_config'][$param]['template'],$template_vars);
								$task_vars['email_subject'] = (!empty($config['template_config'][$param]['subject'])) ? "Re: ".$config['template_config'][$param]['subject'] : "Re: ".$m['subject'];
								if($param == "noresp"){
									$task_vars['email'] = $chk->username;

									$chk->setTask(array(
										'email_body'=>$this->obGetTemplate($config['template_config']['noresp_notification']['template'],$template_vars),
										'email_subject'=>$config['template_config']['noresp_notification']['subject'],
										'email' => $config['notification_email_config'],
										//'id_email_from_send'=>$value['id']
									));

									$chk->linkNoResp();
								}
								$send = true;
							}
							if($send){
								$chk->setTask($task_vars);
							}
						}
						
						$chk->mail = null; // обнуляем временное значение
					}
				}
				$chk->closeConnect(); // закрываем подключение к текущему аккаунту
			}
			
		//}
	}
	/**
		Метод запуска проверки в базе наличии заданий, для отправки писем
	*/
	public function runtask(){
		$chk = $this->loadModel("checkmail");
		$this->db_connect();
		$chk->linkDB = $this->db;

		$emails = $chk->getTasks(); // выборка всех поставленных задач, которые необходимо выполнить
		if(count($emails)>0){ // если задач больше 0
			foreach ($emails as $email) { 
				$chk->linkSMTP = $this->loadModel("PHPMailer"); // подгружаем класс для работой с smtp сервером
				/*$chk->linkSMTP->Username = $email['from']['username']; // имя пользователя (config.php)
				$chk->linkSMTP->Password = $email['from']['password']; // пароль пользователя (config.php)
				$chk->linkSMTP->Host = $email['from']['smtp_host']; // путь подключения к серверу (config.php)*/

				$chk->linkSMTP->Username = MAIL_USER; // имя пользователя (config.php)
				$chk->linkSMTP->Password = MAIL_PASSWORD; // пароль пользователя (config.php)
				$chk->linkSMTP->Host = MAIL_SMTP_HOST; // путь подключения к серверу (config.php)

				$chk->linkSMTP->AddAddress($email['email']); // куда отправляем
				//$chk->linkSMTP->FromName = $email['from']['username']; // от чьего имени
				$chk->linkSMTP->FromName =MAIL_USER; // от чьего имени
			    $chk->linkSMTP->Subject = $email['subject']; // тема письма
			    $chk->linkSMTP->addCustomHeader("MIME-Version: 1.0\r\n");
			    $chk->linkSMTP->addCustomHeader("Content-Type: text/html; charset=utf-8\r\n");
			    $chk->linkSMTP->isHTML(true);
			    $chk->linkSMTP->Body = stripcslashes($email['mail']); // тело письма

			    $chk->deleteTask($email['id']); // удаляем задачу
			    $chk->sendMail(); // отправляем письмо
			    $chk->linkSMTP = null;
			}
			
		}
	}

	public function checkconfig(){

		$arr = array(
			'count'=>"test",
			'username'=>"test",
			'email'=>"test",
			'mail'=>"test",
			'buyer_name'=>"test"
		);

		$result = $this->getConfig($arr);
		$ct = $this->loadModel("configTest");

		$ct->rules = $result['rules_config'];
		$ct->templates = $result['template_config'];
		$ct->notif_email = $result['notification_email_config'];

		$ct->run();

		$logs = $ct->getLogs();

		$view = "";

		if(count($logs['warning'])>0){
			$view .= "Предупреждение:<br><br>";
			for ($i=0; $i < count($logs['warning']); $i++) { 
				$view .= $logs['warning'][$i]."<br>";
			}
		}
		$view .= "<br>";
		if(count($logs['error'])>0){
			$view .= "Ошибки:<br><br>";
			for ($i=0; $i < count($logs['error']); $i++) { 
				$view .= $logs['error'][$i]."<br>";
			}
		}

		echo $view;
		// 


		// if(count($result['rules_config'])>0){
		// 	foreach ($result['rules_config'] as $key => $value) {
		// 		switch ($key) {
		// 			case 'subject':
						
		// 				break;
		// 			case 'capslock':

		// 				break;
		// 			case 'sender':

		// 				break;
		// 			case 'keywords':

		// 				break;
		// 			case 'mes_number':

		// 				break;
		// 		}
		// 		if(!empty($value['pattern']) && $value['pattern']!=""){
		// 			echo $key." !empty<br>";
		// 		}
		// 		else echo $key." empty<br>";
		// 	}
		// }

	}


	private function viewpage(){
	}
	private function obGetTemplate($template,$vars){
		ob_start();
		$this->outputcontent("mail_templates/".$template,$vars);
		$view = ob_get_contents();	
		ob_end_clean();

		return $view;
	}

	private function switch_rules($rules_key,$rules_value,$chk,$and = null){
		$find = array(
			'status' => false
		);
		switch ($rules_key) {
			case 'subject':
				$find['status'] = $chk->testsubject($rules_value['pattern']);
				if($find['status']){
					if($and==null){
						if(!empty($rules_value['template_id'])){
							$find['item'] = $rules_value['template_id'];
							$find['send'] = (!empty($rules_value['send']) && $rules_value['send']!=1) ? $rules_value['send'] : 1;
							$find['email'] = (!empty($rules_value['send_email'])) ? $rules_value['send_email'] : '';
							$find['noresp'] = (!empty($rules_value['noresp']) && $rules_value['noresp']!=0) ? $rules_value['noresp'] : 0;
							$find['notification'] = (!empty($rules_value['notification']) && $rules_value['notification']!=1) ? $rules_value['notification'] : 1;
							if($find['notification'] == 1){
								if(!empty($rules_value['notification_template_id'])) $find['notification_template'] = $rules_value['notification_template_id'];
								else{
									$find['status'] = false;
									break;
								}
							}
						}
						else $find['status'] = false;
					}
					if(!empty($rules_value['and']))	$find['status'] = $this->andFind($find['status'],$rules_value['and'],$chk);
				}
				break;
			case 'capslock':
				$find['status'] = $chk->testcaps();
				if($find['status']){
					if($and==null){
						if(!empty($rules_value['template_id'])){
							$find['item'] = $rules_value['template_id'];
							$find['send'] = (!empty($rules_value['send']) && $rules_value['send']!=1) ? $rules_value['send'] : 1;
							$find['email'] = (!empty($rules_value['send_email'])) ? $rules_value['send_email'] : '';
							$find['noresp'] = (!empty($rules_value['noresp']) && $rules_value['noresp']!=0) ? $rules_value['noresp'] : 0;
							$find['notification'] = (!empty($rules_value['notification']) && $rules_value['notification']!=1) ? $rules_value['notification'] : 1;
							if($find['notification'] == 1){
								if(!empty($rules_value['notification_template_id'])) $find['notification_template'] = $rules_value['notification_template_id'];
								else{
									$find['status'] = false;
									break;
								}
							}
						}
						else $find['status'] = false;
					}
					if(!empty($rules_value['and']))	$find['status'] = $this->andFind($find['status'],$rules_value['and'],$chk);
				}
				break;
			case 'keywords':
				$find['status'] = $chk->testkeywords($rules_value['pattern']);
				if($find['status']){
					if($and==null){
						if(!empty($rules_value['template_id'])){
							$find['item'] = $rules_value['template_id'];
							$find['send'] = (!empty($rules_value['send']) && $rules_value['send']!=1) ? $rules_value['send'] : 1;
							$find['email'] = (!empty($rules_value['send_email'])) ? $rules_value['send_email'] : '';
							$find['noresp'] = (!empty($rules_value['noresp']) && $rules_value['noresp']!=0) ? $rules_value['noresp'] : 0;
							$find['notification'] = (!empty($rules_value['notification']) && $rules_value['notification']!=1) ? $rules_value['notification'] : 1;
							if($find['notification'] == 1){
								if(!empty($rules_value['notification_template_id'])) $find['notification_template'] = $rules_value['notification_template_id'];
								else{
									$find['status'] = false;
									break;
								}
							}
						}
						else $find['status'] = false;
					}
					if(!empty($rules_value['and']))	$find['status'] = $this->andFind($find['status'],$rules_value['and'],$chk);
				}
				break;
			case 'sender':
				$find['status'] = $chk->testsender($rules_value['pattern']);
				if($find['status']){
					if($and==null){
						if(!empty($rules_value['template_id'])){
							$find['item'] = $rules_value['template_id'];
							$find['send'] = (!empty($rules_value['send']) && $rules_value['send']!=1) ? $rules_value['send'] : 1;
							$find['email'] = (!empty($rules_value['send_email'])) ? $rules_value['send_email'] : '';
							$find['noresp'] = (!empty($rules_value['noresp']) && $rules_value['noresp']!=0) ? $rules_value['noresp'] : 0;
							$find['notification'] = (!empty($rules_value['notification']) && $rules_value['notification']!=1) ? $rules_value['notification'] : 1;
							if($find['notification'] == 1){
								if(!empty($rules_value['notification_template_id'])) $find['notification_template'] = $rules_value['notification_template_id'];
								else{
									$find['status'] = false;
									break;
								}
							}
						}
						else $find['status'] = false;
					}
					if(!empty($rules_value['and']))	$find['status'] = $this->andFind($find['status'],$rules_value['and'],$chk);
				}
				break;
			case 'mes_number':
				$mesnumber = $chk->testmesnumber($rules_value['pattern']);
				$find['status'] = $mesnumber['status'];
				if($find['status']){
					if($and==null){
						if(!empty($mesnumber['template_id'])){
							$find['item'] = $mesnumber['template_id'];
							$find['send'] = (!empty($rules_value['send']) && $rules_value['send']!=1) ? $rules_value['send'] : 1;
							$find['email'] = (!empty($rules_value['send_email'])) ? $rules_value['send_email'] : '';
							$find['noresp'] = (!empty($rules_value['noresp']) && $rules_value['noresp']!=0) ? $rules_value['noresp'] : 0;
							$find['notification'] = (!empty($rules_value['notification']) && $rules_value['notification']!=1) ? $rules_value['notification'] : 1;
							if($find['notification'] == 1){
								if(!empty($rules_value['notification_template_id'])) $find['notification_template'] = $rules_value['notification_template_id'];
								else{
									$find['status'] = false;
									break;
								}
							}
						}
						else $find['status'] = false;
					}
					if(!empty($rules_value['and']))	$find['status'] = $this->andFind($find['status'],$rules_value['and'],$chk);
				}
				break;
		}
		return $find;
	}
	private function andFind($status,$and,$chk){
		if($status){
			if(count($and)>0){
				$andFind = true;
				foreach ($and as $key => $value) {
					
					$andFind = $this->switch_rules($key,$value,$chk,true);
					if(!$andFind['status']) break;
				}

				return $andFind['status'];
			}
			else return $status;
		}
		else return $status;
	}
}