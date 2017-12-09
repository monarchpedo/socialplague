<?php
  define("PREFIX_CONSTANT","P@ssraja");
  define("SUFFIX_CONSTANT","P#ssraja");
  define("DIR","D:/log/media/");
  $host = "127.0.0.1";
  $hostname = "localhost";
  $port = 3006;
  $userName = "root";
  $password = "raja";
  $databaseName = "socialplague";

  /*
  @Author Raja Bose
  @it is used to connect database and close the connection to database to maintain pool feature on apache2.
  @Copyright socialplague
  */
  class DbConnect{
   
    private $conn;
    private $host = "127.0.0.1";
    private $hostname = "localhost";
    private $port = 3006;
    private $userName = "root";
    private $password = "raja";
    private $databaseName = "socialplague";

    public function __construct(){

    }

    public function connect(){
         $this->conn = new PDO("mysql:host=".$this->hostname.";dbname=".$this->databaseName, $this->userName, $this->password);
         $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         return $this->conn;
    }

    public  function close(){
         return $this->conn->close();
    }
  }
?>