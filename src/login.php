<?php
  include("config.php"); 
  error_reporting(1);
  $error = array();

  class AccountService{
    
     private $conn=null;

     public function __construct(){
        require_once("config.php");
        $db =  new DbConnect();
        $this->conn = $db->connect();
     }


     public function login(){
        $email=$_POST['username'];
        $pass=$_POST['password'];
       
        $this->validateEmail($email);

        $checkResult = $this->checkUsername($email);
        if(count($checkResult)==0){
          header("location:/socialplague/index.php");
        }
    
        $salt = $checkResult["salt"];
        $password = sha1($salt.$pass);

        $stmt = $this->conn->prepare("select * from `users` where email = :email and password = :password and active = 1");
        $stmt->bindParam(":email",$email);
        $stmt->bindParam(":password",$password);

        $stmt-execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conn->close();
        if(count($result)>0){
            session_start();
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $result['name'];
            $_SESSION['DOB'] = $result['Birthday_Date'];
            $_SESSION['gender'] = $result['Gender'];
            $_SESSION['join_date'] = $result['join_date'];
            $_SESSION['id'] = $result['user_id'];
            header("location:/socialplague/src/feed.php");
        }
        else{
            header("location:/socialplague/index.php");         
       }  
     }

     public function logout(){
         unset($_SESSION['id']);
         header("/socialplague/index.php");
     }

     private function checkUsername($email=null){
       $emailCheck = "select * from `users` where email = :email";
       $stmt = $this->conn->prepare($emailCheck);
       $stmt->bindParam(":email",$email);
       $stmt->execute();
       $checkResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
       return $checkResult; 
     }

     private function saveCookie(){

     }

     private function getCookie(){

     }

     private function rememberMe(){

     }

     private function validateEmail($email=null){
        $emailValidation = filter_var($email, FILTER_VALIDATE_EMAIL)!==false?true:false;
        if($emailValidation == false){
        header("location:index.php"); 
    }
     }
  
  }


    if(isset($_POST['login'])){ 
       $loginService = new AccountService();
       $loginService->login();    
    }
?>