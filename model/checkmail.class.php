<?php



/**
* Class for explode mail and search needed keywords
*/
class ModelCheckmail{

	//private $keywords = [];

	public $hostname; // свойство класса - путь подключения imap
	public $username; // свойство класса - имя пользователя
	public $password; // свойство класса - пароль пользователя

	public $mail;

	public $linkSMTP = null;
	public $linkDB = null;

	private $connect = null; 
	/**
		Подключаемся к почте используя данные входа
	*/
	public function _connectToGmail(){
		$this->connect = imap_open($this->hostname,$this->username,$this->password);
		return $this->connect;
	}
	/**
		получаем непрочитанные сообщения
	*/
	public function getMessages($param = null){
		if($this->connect != null){
			$option = ($param != null) ? $param : "UNSEEN";
			$msgs = imap_search($this->connect,$option);
			$result = $this->getInfoByMsgId($msgs);
			return $result;
		}
	}
	/**
		отправляем письмо используя сервер SMTP
	*/
	public function sendMail(){
		if($this->linkSMTP != null){
		    $this->linkSMTP->Port = 465;
		    $this->linkSMTP->IsSMTP();
		    $this->linkSMTP->SMTPAuth = true;
		    $this->linkSMTP->From = $this->linkSMTP->Username;

		    $this->linkSMTP->Send();
		}
	}
	/**
		проверяем письмо на наши установленные правила
	*/
	public function checkMail(){
		if($this->mail !=null){
			$_temp = explode('@', $this->mail['from']['email']);
			//if(($_temp[1]) == "marketplace.amazon.com"){
			if(($_temp[1]) == "gmail.com"){
				return true;
			}
			return false;
		}
		return false;
	}
	public function addCustomer(){
		$date = date("Y-m-d");
		$this->linkDB->insert("customers",array($this->mail['from']['email'],'',$this->mail['message'],$date),array("email","order_id","mail","date"));
	}
	public function getEmailCount(){
		$search_result = $this->linkDB->select("count(*)","customers","`email` like '".$this->mail['from']['email']."'");
		return $search_result[0]['count(*)'];
	}
	
	/**
		задаем задачу на выполение
	*/
	public function setTask($arr){
		if($this->mail !=null && $arr != null){
			$time = strtotime("+2 minutes");
			$email = (empty($arr['email'])) ? $this->mail['from']['email'] : $arr['email'];
			//$subject = $this->mail['subject'];
			$subject = $arr['email_subject'];
			//$id_email_from_send = (int)$arr['id_email_from_send'];
			$mail = mysqli_real_escape_string($this->linkDB->mysql_connect,$arr['email_body']);

			//$this->linkDB->insert("tasks",array($email,$subject,$mail,$id_email_from_send,$time),array("email","subject","mail","id_email_from_send","time_to_run"));
			$this->linkDB->insert("tasks",array($email,$subject,$mail,$time),array("email","subject","mail","time_to_run"));
		}
	}
	/**
		выборка всех задач, чье время пришло..((
	*/
	public function getTasks(){
		$result = array();
		$time = time();
		$mails = $this->linkDB->select("*","tasks","`time_to_run` < '".$time."'");
		for($i=0;$i<count($mails);$i++){
			//$from = $this->linkDB->select("*","access_data","`id` like '".$mails[$i]['id_email_from_send']."'");
			$result[] = array(
				'id' =>$mails[$i]['id'],
				'email'=>$mails[$i]['email'],
				'subject'=>$mails[$i]['subject'],
				'mail'=>$mails[$i]['mail'],
				//'from'=>$from[0]
			);
		}
		return $result;
	}
	/**
		Удаление задач по их id
	*/
	public function deleteTask($id){
		if($id != null){
			$this->linkDB->delete('tasks',"`id` = '".$id."'");
		}
	}
	/**
		выборка всех аккаунтов
	*/
	public function getAllAccounts(){
		
		return $this->linkDB->select("*","access_data");
	}
	public function linkNoResp(){
		if(!empty($this->mail['noresplink'])){
			file_get_contents($this->mail['noresplink']);
		}
	}
	public function testcaps(){
		$message = $this->mail['message'];
		$testarr = explode(" ",$message);
		$findtext = false;
		foreach($testarr as $text){
			if(strlen($text) > 3 && ctype_upper($text)){
				$findtext = true;
				break;
			}
		}
		return $findtext;
	}
	public function testkeywords($keywords){
		$message = $this->mail['message'];
		$testarr = explode(" ",$message);
		$findtext = false;
		foreach($testarr as $text){
			if(!$findtext){
				foreach ($keywords as $key) {
					if(strcasecmp($text,$key) == 0){
						$findtext = true;
						break;
					}
				}
			}
			else break;
		}
		return $findtext;
	}
	public function testsubject($pattern){
		$subject = $this->mail['subject'];
		if(!empty($pattern)){
			if(preg_match("/".$pattern."/", $subject)){
				return true;
			}
			else return false;
		}
		else return false;
	}
	public function testsender($senders){

		$find = false;
		if(in_array($this->mail['from']['email'], $senders)){
			$find = true;
		}

		return $find;
	}
	public function testmesnumber($pattern){
		if(count($pattern)>0){
			$find = array(
				'status' => false,
				'item' => ''
			);
			$count = $this->getEmailCount();
			for ($i=0; $i < count($pattern); $i++) { 
				$tempkeys = array_keys($pattern[$i]);
				if(!$find['status']){
					switch ($tempkeys[0]) {
						case 'equal':
							if($count == $pattern[$i][$tempkeys[0]]){
								$find['status'] = true;
								$find['template_id'] = $pattern[$i][$tempkeys[1]];
							}
							break;
						case 'more':
							if($count > $pattern[$i][$tempkeys[0]]){
								$find['status'] = true;
								$find['template_id'] = $pattern[$i][$tempkeys[1]];
							}
							break;
						case 'less':
							if($count < $pattern[$i][$tempkeys[0]]){
								$find['status'] = true;
								$find['template_id'] = $pattern[$i][$tempkeys[1]];
							}
							break;
					}
				}
				else{
					break;
				}
			}
			if($find['status']){
				return $find;
			}	
			return false;
		}
		return false;
	}
	public function checkCount($count){
		if($count!=null){
			switch($count-1){
				case 0:{
					return 'empty';
					break;
				}
				case 1:{
					return 'hasone';
					break;
				}
				default:{
					return 'noresp';
					break;
				}
			}
		}
		else return false;
	}
	/**
		получения информации о письме  (тема, сообщение, от кого пришло)
	*/
	private function getInfoByMsgId($msgs){
		if($this->connect != null && $msgs !=null){
			
			$result = array();

			foreach ($msgs as $nmsg) {
				$overview = imap_headerinfo($this->connect,$nmsg);
				$_mes = $this->getBodyMessage($nmsg);
				$result[] =  array(
					'subject'=>$this->getSubject($overview),
					'from'=>$this->getFrom($overview),
					'message'=>$_mes['message'],
					'noresplink'=>$_mes['noresplink']
				);
			}
			return $result;
		}
	}
	/**
		закрываем подключение
	*/
	public function closeConnect(){
		if($this->connect != null){
			imap_close($this->connect);
			$this->connect = null;
		}
	}
	private function getSubject($overview){
		return imap_utf8($overview->subject);
	}
	private function getFrom($overview){
		$personal = imap_utf8($overview->from[0]->personal);
		$_name = explode(" ", $personal);
		$name = (count($_name)>1) ? $_name[0] : $personal;
		return array(
			'name'=> $name,
			'email'=>$overview->from[0]->mailbox."@".$overview->from[0]->host
			);
	}
	private function getBodyMessage($nmsg){
		if($this->connect != null && $nmsg !=null){
			/*print_r(imap_bodystruct($this->connect, $nmsg, 1));
			echo "<br>";
			echo imap_base64(imap_fetchbody($this->connect,$nmsg,1));
			echo "<br>";
			echo imap_fetchbody($this->connect,$nmsg,1);
			echo "<br>";*/
						//print_r(imap_bodystruct($this->connect, $nmsg, 1));
			//echo imap_fetchbody($this->connect,$nmsg,1.2);
			/*return array(
				'message' => mysql_real_escape_string(imap_fetchbody($this->connect,$nmsg,1.2)),
				'noresplink' => $this->getNoRespLink(imap_fetchbody($this->connect,$nmsg,1.1))
			);*/

			$struct = imap_bodystruct($this->connect, $nmsg, 1);
			switch($struct->type){
				case '0':{
					$ps_mes = preg_split("/-* End message -*/", imap_fetchbody($this->connect,$nmsg,1));
					return array(
						'message' => mysqli_real_escape_string($this->linkDB->mysql_connect,trim($ps_mes[0])),
						'noresplink' => $this->getNoRespLink(imap_fetchbody($this->connect,$nmsg,1))
					);
					break;
				}
				case '1':{
					return array(
						'message' => mysqli_real_escape_string($this->linkDB->mysql_connect,trim(imap_fetchbody($this->connect,$nmsg,1.2))),
						'noresplink' => $this->getNoRespLink(imap_fetchbody($this->connect,$nmsg,1.1))
					);
					break;
				}
			}
		}
	}
	
	private function getNoRespLink($message){
		preg_match('/http:\/\/www\.amazon\.com\/gp\/communication-manager\/no-response-needed\.html\?\S*/', stripcslashes($message), $preg_result);
		if(count($preg_result)>0){
			return $preg_result[0];
		}
		else return false;
	}
}