<?php
 
  include("config.php");
  $code = $_GET['code'];
  $userId = $_GET['db_id'];

  $conn = new PDO("mysql:host=".$hostname.";dbname=".$databaseName, $userName, $password);
	
  $stmt = $conn->prepare("update `users` set active = 1 where user_id = :userId and activationcode = :code");

  $stmt->bindParam(":userId",$userId);
  $stmt->bindParam(":code",$code);

  $stmt->execute();
  header("location:index.php");

?>