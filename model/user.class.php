<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 29.10.2016
 * Time: 15:08
 */
class ModelUser
{
    public $link_db=null;
    public $link_mail=null;

    private $id_user;
    private $login;
    private $permission;

    public function __construct($link_db=null){
        if(!empty($link_db)){
            $this->link_db = $link_db;
            $this->link_db->connect("diplom");
        }
        
    }

    public function login($user_data){
        $email = htmlspecialchars(trim($user_data['email']));
        $password = htmlspecialchars(trim($user_data['password']));

        $checkLogin = $this->check_user_data(array('email'=>$email,'password'=>$password));
        if($checkLogin){

            $_SESSION['user']['permission'] = $this->permission;
            $_SESSION['user']['id'] = $this->id_user;
            $result['error'] = false;
            return $result;
        }
        else{
            $result['error'] = true;
            return $result;
        }
    }

    public function registration($user_data){
        $email = htmlspecialchars(trim($user_data['email']));
        $password = md5(htmlspecialchars(trim($user_data['password'])));

        $result = $this->check_login($email);
        if(!$result){
            $this->link_db->insert("users",array($email,$password,'0','0.00'),array('email','password','permission','bank_account'));
            return true;
        }
        else{
            return $result;
        }
        
       /* $this->link_db->insert("users",array($user_data['login'],$user_data['password'],$user_data['email'],'0'),array('login','password','email','active'));
        $result = $this->link_db->select("id","users","","id DESC","1");
        return $result[0]['id'];*/
    }
    public function send_registration_mail($user_data){
        $result = $this->check_login($user_data['login']);
        if(!$result){
            $result = $this->check_email($user_data['email']);
            if(!$result){
                $key = md5($user_data['login'].":".$user_data['email']);
                $date = date("Y-m-d");
                $last_id = $this->registration($user_data);
                
                $this->link_db->insert("users_keys",array($key,$last_id,$user_data['email'],$date),array("key_reg","id_user","email","date_send"));
                
                $result = $this->link_mail->send_registration_mail($user_data,$key);
                if($result==true){
                    return true;
                }
                else{
                    return $result;
                }

            }
            else{
                return $result;
            }
        }
        else{
            return $result;
        }
    }

    public function check_regkey($key){
        $result = $this->link_db->select("*","users_keys","`key_reg` like '".$key."'");
        if(!empty($result)){
            return $result;
        }
        else{
            return false;
        }
    }
    public function activation($id_user,$password){
        $result = $this->link_db->select("login","users","`id` like '".$id_user."' and `password` like '".$password."'");
        if(!empty($result)){
            $this->link_db->update("users",array("active"),array("1"),"`id` like '".$id_user."'");
            $this->link_db->delete("users_keys","`id_user` like '".$id_user."'");
            return true;
        }
        else{
            return "Password incorrect";
        }
    }
    public function is_login(){
        if(!empty($_SESSION['user'])){
            return true;
        }
        else{
            return false;
        }
    }
    public function getLogin($byId=null){
        if(!empty($byId)){
            $result = $this->link_db->select("login","users","`id` like '".$byId."'");
            return $result[0]['login'];
        }
        else{
            return $_SESSION['user']['login'];
        }
    }
    public function getEmail($id=null){
        if(empty($id)){
            $id = $_SESSION['user']['id'];
        }
        $result = $this->link_db->select("email","users","`id` like '".$id."'");
        return $result[0]['email'];
        
    }
    public function getPermission(){
        return $_SESSION['user']['permission'];
    }
    public function isPermission($id_user){
        $result = $this->link_db->select("permission","users","`id` like '".$id_user."'");
        if($result[0]['permission']==1){
            return true;
        }
        else{
            return false;
        }
    }
    public function checkPassword($id,$password){
        $result = $this->link_db->select("id","users","`id` like '".$id."' AND `password` like '".$password."'");
        if(!empty($result)){
            return true;
        }
        else{
            return false;
        }
    }
    public function changePassword($id,$password){
        $this->link_db->update("users",array("password"),array($password),"`id` like '".$id."'");
    }
    public function getUserId(){
        return $_SESSION['user']['id'];
    }

    public function setVisit(){
        $this->link_db->update("users",array("last_visit"),array(time("Y-m-d H:i:s")),"`id` like '".$this->getUserId()."'");
    }

   //************************
    public function getContactsList(){
        $id_user = $this->getUserId();
        $result = $this->link_db->select("id_user_added","users_contacts","`id_user` like '".$id_user."'");
        return (empty($result)) ? array() : $result;
    }
    public function getContactsByRequest($request){
        $result = $this->link_db->select("id,login","users","`login` like '%".$request."%' AND `active` like '1'");
        return $result;
    }
    public function checkProfileInContactsList($id){
        $id_user = $_SESSION['user']['id'];
        $result = $this->link_db->select("id_user_added","users_contacts","`id_user` like '".$id_user."' AND `id_user_added` like '".$id."'");
        return (!empty($result)) ? true : false;
    }   
    public function addcontact($id){
        $this->link_db->insert("users_contacts",array($this->getUserId(),$id),array('id_user','id_user_added'));
    }
    /**
     * @param $login
     */
    private function check_login($email){
        $result = $this->link_db->select("email","users","`email` like '".$email."'");
        return ($result) ? "This email has been used" : false;
    }
    private function check_email($email){
        $result = $this->link_db->select("email","users_keys","`email` like '".$email."'");
        if(!$result){
            $result = $this->link_db->select("email","users","`email` like '".$email."'");
            return ($result) ? "This email has been used" : false;
        }
        else{
            return "This email has been used";
        }
       
    }
    private function check_user_data($user_data){
        $result = $this->link_db->select("*","users","`email` like '".$user_data['email']."' and `password` like '".md5($user_data['password'])."'");
        if(!empty($result)){
            $this->id_user = $result[0]['id'];
            $this->permission = $result[0]['permission'];
            return true;
        }
        else{
            return false;
        }
    }

}