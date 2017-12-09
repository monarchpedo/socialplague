<?php
 include'config.php';
 error_reporting(1);
 
 $error = array();
 $error['count'] = 0;
 
 Class Register{

    private $conn = null;

    public function __construct(){
        $db =  new DbConnect();
        $this->conn = $db->connect();
    }

    public function registerUesr(){
        
          $this->conn->beginTransaction();       	  

       	  try{

            $email=trim($_POST['email']);
	        $pass=trim($_POST['password']);
	        $firstName = trim($_POST['fname']);
	        $lastName  = trim($_POST['lname']);
	        $mobileNumber = $_POST['mobileNumber'];
	        file_put_contents("D:/log/logfile",$mobileNumber."\n",FILE_APPEND);

	        $dob = "1994-12-25";
	        $name = $firstName." ".$lastName;
	        $gender = trim($_POST['gender']);
	        $joinDate = date('y-m-d  H:i:s');
	        $salt = md5(uniqid().mt_rand().microtime());
	        $salt = PREFIX_CONSTANT.$salt.SUFFIX_CONSTANT;
	        $password = sha1($salt.$pass);

	        $emailValidation = filter_var($email, FILTER_VALIDATE_EMAIL)!==false?true:false;

	        if($emailValidation == false){
	           header("location:/index.php"); 
            }
    
            $userCheck = $this->conn->prepare("select * from `users` where Email = :email or MobileNumber = :mobileNumber");
            $userCheck->bindParam(":email",$email);
            $userCheck->bindParam(":mobileNumber",$mobileNumber);
            $userCheck->execute();
            $countUserAccount  = $userCheck->fetchAll(PDO::FETCH_ASSOC);
            if(count($countUserAccount) > 0){
    	       header("location:/socialplague/index.php");
            }

           $stmt = $this->conn->prepare("Insert into `users`(`Name`,`Email`,`Password`,`Gender`,`Birthday_Date`,`MobileNumber`,`join_date`,`salt`,`activationcode`,active) values(:name,:email,:password,:gender,:dob,:MobileNumber,:joinDate,:salt,:activationcode,:active)");

           $code=substr(md5(mt_rand()),0,15);
           $stmt->bindParam(":name",$name);
           $stmt->bindParam(":email",$email);
           $stmt->bindParam(":password",$password);
           $stmt->bindParam(":gender",$gender);
           $stmt->bindParam(":dob",$dob);
           $stmt->bindParam(":joinDate",$joinDate);
           $stmt->bindParam(":MobileNumber",$mobileNumber);
           $stmt->bindParam(":salt",$salt);
           $stmt->bindParam(":activationcode",$code);
           $active = 1;
           $stmt->bindParam(":active",$active,PDO::PARAM_INT);
           $stmt->execute();
           $insertedId  = $this->conn->lastInsertId();
      
           
           $error['success'] = "registration has been done. we have sent an activation link to your mail.";
           $this->conn->commit();

           header("location:/socialplague/src/activation.php");
            
       	  }catch(Exception $e){
       	  	$this->conn->rollBack();
            file_put_contents("D:/log/logfile",$e->getMessage()."\n",FILE_APPEND);
            $error['error'] = "something wrong happened";
            header("location:/socialplague/index.php");
       	  } 
    }

    private function validateEmail(){

    }

    private function generatePassword(){

    }

    private function sendMail(){
           $to=$email;
           $subject="Activation Code For socialplague";
           $from = 'rbosemonarch@gmail.com';
           $body='Your Activation Code has been sent by socialplague.com<\br>'.' Please Click On This link <a href="http:://localhost:80/socialplague/src/verification.php?code=$code&id=$insertedId">activation link'.'</a>to activate your account.';
           $headers = "From:".$from;
           mail($to,$subject,$body,$headers);
    }

    private function emailCheck(){

    }

 }


 if(isset($_POST['submit'])){
     $Register = new Register();
     $Register->registerUesr();
 }


?>