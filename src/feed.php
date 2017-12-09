<?php

 session_start();

 if(!isset($_SESSION['id'])){
 	header("location:/socialplague/src/index.php");
 }

 error_reporting(1);
 
 include("config.php");
 
/*
 @Author Raja Bose
 @description: it is designed to read and write media file to disk and post content to database.
 @Copyright socialplague.com

*/

 class FeedService{
   
    private $postData;
    private $conn;
    private $dir;


    public function __construct(){
        require_once("config.php");
        $db =  new DbConnect();
        $this->dir = DIR;
        $this->conn = $db->connect();
    }


    // function for saving post in database table name userpost
    public function savePost(){
        //begin transaction of sql operation in one connection
        $this->conn->beginTransaction();
        
        $target_dir = $this->dir."uploads/".$_SESSION['id']."/";
        $date = date('y-m-d H:i:s');
        $mediaContain = isset($_POST['file'])==true?1:0; 

        try{

        	$stmt = $this->conn->prepare("Insert into `userpost`(`userId`,`gender`,`active`,`title`,`content`,`mediaContain`,`dirname`,`likes`,`unlikes`,`createdDate`,`modifiedDate`) values(:userId,:active,:title,:content,:mediaContain,:fileName,:likes,:unlikes,:createdDate,:modifiedDate)");
                

                $stmt->bindParam(":userId",$_SESSION['id']);
                $stmt->bindParam(":gender",$_SESSION['gender']);
                $stmt->bindParam(":active",1);
                $stmt->bindParam(":title",$_POST['title']);
                $stmt->bindParam(":content",$_POST['content']);
                $stmt->bindParam(":mediaContain",$mediaContain);
                $stmt->bindParam(":dirname",$target_dir);
                $stmt->bindParam(":likes",0);
                $stmt->bindParam(":unlikes",0);
                $stmt->bindParam(":createdDate",$date);
                $stmt->bindParam(":modifiedDate",$date);

                $stmt->execute();

                $postId = $this->conn->lastInsertId();

                //save media contnt if post containing media content
                if($mediaContain!=0){
                saveMedia($postId,$postArray,$target_dir);
                }

                $this->conn->commit();
                header("location:/socialplague/src/index.php");
            
        }catch(Exception $e){
           $this->conn->rollBack();
           file_put_contents("D:/log/logfile",$e->getMessage()."\n",FILE_APPEND);
           header("location:/socialplague/src/index.php");
        }  
    }



    public function getPost($offset=0){
    	try{
    	$stmt = $this->conn->prepare("select * from `userpost` where gender = :gender and gender = `shared` active = 1 order by modifiedDate limit $offset,10");
    	$stmt->bindParam(":gender",$_SESSION['gender']);
    	$stmt->execute();
    	$result = $stmt->fecthAll(PDO::FETCH_ASSOC);
    	if(count($result)>0){
    		foreach($result as $key=>$value){
    			$dirname = $value['dirname'];
                $extension = $value['extension'];
                $filename = $dirname."/".$value['id'].".".$extension;
                $result[$key]['image'] = $this->base64_encode_image($fileName,$extension);
    		}
    	}
    	return json_encode($result);
      }catch(Exception $e){
      	 file_put_contents("D:/log/logfile",$e->getMessage()."\n",FILE_APPEND);
      	 return null;
      }
    }


   public function getPostByUserId($userId =0,$offset=0){
    	try{
    	$stmt = $this->conn->query("select * from `userpost` where userId = $userId and active = 1 order by modifiedDate limit $offset,10");
    	$result = $stmt->fecthAll(PDO::FETCH_ASSOC);
    	if(count($result)>0){
    		foreach($result as $key=>$value){
    			$dirname = $value['dirname'];
                $extension = $value['extension'];
                $filename = $dirname."/".$value['id'].".".$extension;
                $result[$key]['image'] = $this->base64_encode_image($fileName,$extension);
    		}
    	}
    	return json_encode($result);
      }catch(Exception $e){
      	 file_put_contents("D:/log/logfile",$e->getMessage()."\n",FILE_APPEND);
      	 return null;
      }
    }

    public function blockPost($postId=0){
      try{
         $query = "update `userpost` set active=0 where id=:id";
         $stmt = $this->conn->prepare($query);
         $stmt->bindParam(":id",$postId);
         $stmt->execute();
         return  1;
      }catch(Exception $e){
      	file_put_contents("D:/log/logfile",$e->getMessage()."\n",FILE_APPEND);
      	return 0;
      }
    }




    public function updatePost(){
         
        try{ 

           $date = date("Y-m-d H:i:s");
           $query = "update `userpost` set "; 
           for($_POST as $key=>$value){
              if($key!="id"){
                 $query .= $key."=:".$key.",";
              }
           }

           $query .= "modifiedDate=:modifiedDate,";
           $query = substr($query,0,strlen($query));
           $query .= "where id=:id";
         
           $stmt = $this->conn->prepare($query);
            foreach($updateArray as $key=>$value){
              $stmt->bindParam(":".$key,$value);
           }
           $stmt->bindParam(":modifiedDate",$date);
           return $stmt->execute();
        }catch(Exception $e){
           file_put_contents("D:/log/logfile",$e->getMessage()."\n",FILE_APPEND);
           return 0;
        }
    }



    public function deletePost($postId=0){
      $query = "delete from `userpost` where id = $postId";
      try{
         return $this->conn->query($query);
      }catch(Exception $e){
         file_put_contents("D:/log/logfile",$e->getMessage()."\n",FILE_APPEND);
         return 0;
      }
    }



    private function saveMedia($postId=0,$postArray=null,$target_dir=null){
    
        try{
           if(!is_dir($target_dir)){
              mkdir($target_dir);
           }

           $targt_file = $target_dir . basename($postArray["file"]["name"]);
           $uploadOk = 1;
   
                $check = getimagesize($postArray["file"]["tmp_name"]);
                
                if($check !== false) {
                      file_put_contents("D:/log/logfile","File is an image - " . $check["mime"] ."\n",FILE_APPEND);
                      $uploadOk = 1;
                      $photo=$postArray['file']['name'];
			          $size=$postArray['file']['size'];
			          $type=$postArray['file']['type'];
			          if($size > 11120000)
			          {
				         throw new Exception("media content is greater than 4 MB", 1);		         
			          }
			          else if($type!="image/jpg" && $type!="image/gif" && $type!="image/png" && $type!="image/jpeg")
			          {
				         throw new Exception("please insert regular media content", 1);	
		 	          }

		 	          $ext = pathinfo($postArray['file']['name'], PATHINFO_EXTENSION);
				      $target=$target_dir.$postId.'.'.$ext;
				
				      move_uploaded_file($postArray["file"]["tmp_name"],$target);

		 	  }
		}catch(Exception $e){
			throw $e;
		} 	         
    }


    private function base64_encode_image($filename=string, $filetype=string){
     if ($filename) {
         $imgbinary = fread(fopen($filename, "r"), filesize($filename));
             return 'data:image/' . $filetype 
                    . ';base64,' . base64_encode($imgbinary);
    }

 }

 //save userpost in database after creating new post
 if(!isset($_POST['submitPost'])){
     $feedService = new FeedService();
      return $feedService->savePost();
 }

//get userpost in json format
 if(isset($_GET['offset'])){
      $gender = $_SESSION['gender'];
 
      $offset = $_GET['offset'];
    
      $feedService = new FeedService();
      if(!isset($_GET['userId'])){
        return $feedService->getPost($offset);     
      }
      else{
        return $feedService->getPostByUserId($_GET['userId'],$offset);
      }

}     

if(!isset($_POST["updatePost"])){
   $feedService = new FeedService();
   return $feedService->updatePost();
} 
  
?>