<?php
	mysql_connect("localhost", "root", "");
	$db_name = "database";
	mysql_query("CREATE DATABASE $db_name");
	//Check if ! should be there or not
	if(!mysql_select_db($db_name)) {
		echo mysql_error();
	}
	//Create table for data
	mysql_query("CREATE TABLE studentdetails (simply INT NOT NULL AUTO_INCREMENT,rollno INT(10) NOT NULL,name TEXT NOT NULL,dept VARCHAR(50) NOT NULL,year VARCHAR(6) NOT NULL,email VARCHAR(254) NOT NULL,passwd VARCHAR(254) NOT NULL,id BIGINT(10) NOT NULL,PRIMARY KEY(simply))");
		//mysql_query("ALTER TABLE userdata AUTO_INCREMENT=100000008");
	
	function valid_ate($variable) {
		$makecorrect = "";
		$makecorrect = htmlspecialchars($variable);
		return $makecorrect;
	}
	//Checking if the ID has already been set before
	$previousIDresult = mysql_query("SELECT * FROM studentdetails  ORDER BY simply DESC LIMIT 1");
	$previousID = mysql_result($previousIDresult, 0, 'id');
	if( $previousID < 100000008) {
		$previousID = 100000007;
	}
	else {
		$previousID = intval($previousID) + 1;
	}
	//Generating the id
	function genID($num) {
		$opnum = $num;
		$sum = 0;
		$unit = $num % 10;
		$num = $num / 10;
		$tens = $num % 10;
		$num = $num / 10;
		$sum = intval($unit) + intval($tens);
		$condDoub = 1;
		while($num > 0) {
			if($condDoub == 1) {
				$toDoub = $num % 10;
				$num = $num / 10;
				$doub = $toDoub * 2;
				if($doub > 9) {
					$unit = $doub % 10;
					$doub = $doub / 10;
					$tens = $doub % 10;
					$doub = $doub / 10;
					$sum = intval($sum) + intval($unit) + intval($tens);
				}
				else {
					$sum = intval($sum) + intval($doub);
				}
				$condDoub = 0;
			}
			else {
				$unit = $num % 10;
				$num = $num / 10;
				$sum = intval($sum) + intval($unit);
				$condDoub = 1;
			}
		}
		if($sum % 10 == 0) {
			return 1;
		}
		else {
			return 0;
		}
	}
	//Checking if it's a valid id, if not, generating it
	while( !genID($previousID) ) {
		$previousID = intval($previousID) + 1;
	} 
	//Recaptcha verification
	$urlRC = 'https://www.google.com/recaptcha/api/siteverify';
	$fields = array('secret' => urlencode('6LeLrAgTAAAAAFVPaARfw-MBN8xH1zHxg5AkzK5g'), 'response' => urlencode($_POST["g-recaptcha-response"]));
	$fields_string = '';
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
		
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $urlRC);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	//Only if Captcha verification is a success the code will add information to the server
	if($result["success"] == false) {
		echo " try again if you're human";
		header( 'refresh:5; url=index.html' );
	}
	else {
		$rollno = valid_ate($_POST["rollno"]);
		$name = valid_ate($_POST["name"]);
		$dept = valid_ate($_POST["dept"]);
		$year = valid_ate($_POST["year"]);
		$email = valid_ate($_POST["email"]);
		$not_hashed_passwd = valid_ate($_POST["passwd"]);
		if(strlen($not_hashed_passwd) < 4) {
			echo " enter a valid password of atleast 4 characters";
		  	header( 'refresh:5; url=index.html' );
		}
		$hashed_passwd = sha1($not_hashed_passwd);
		if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
		  	echo " enter a valid email id";
		  	header( 'refresh:5; url=index.html' );
		}
		//Upload of profile picture section
		$target_dir = $_POST["rollno"] . "/";
		$target_file = $target_dir . basename($_FILES["profpic"]["name"]);
		$sizechk = 1;
		$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
		//Checking if the file actually arrived
		if(isset($_POST["submit"])) {
			$check = getimagesize($_FILES["profpic"]["tmp_name"]);
			if($check !== false) {
				$sizechk = 1;
			}
			else {
				$sizechk = 0;
			}
		}
		if(strlen($not_hashed_passwd) < 4) {
			echo "enter a valid password of atleast 4 characters";
		  	$sizechk = 0;
		}
		$hashed_passwd = sha1($not_hashed_passwd);
		if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
		  	echo "enter a valid email id";
		  	$sizechk = 0;
		}
		//Checking the size
		if($_FILES["profpic"]["size"] > 500000) {
			$sizechk = 0;
		}
		//Checking the file type
		if($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png" && $imageFileType != "gif") {
			$sizechk = 0;
		}
		//Function to delete a directory and the file in it
		function rmdirContents($dir) {
			$files = glob($dir . '/*');
			foreach($files as $file) {
				is_dir($file) ? rmdirContents($file) : unlink($file);
			}
			rmdir($dir);
			return;
		}
		if($sizechk == 0) {
			echo "The image wasn't uploaded, try again in 5 seconds";
			header( 'refresh:5; url=index.html' );
		}
		else {
			//Checking if the roll number was already entered
			if(is_dir($target_dir)) {
				rmdirContents($target_dir);
			}
			if(mkdir($target_dir, 0777) && move_uploaded_file($_FILES["profpic"]["tmp_name"],$target_file)) {
				mysql_query("INSERT INTO studentdetails  (rollno,name,dept,year,email,passwd,id) VALUES ('" . mysql_real_escape_string($rollno) . "','" . mysql_real_escape_string($name) . "','" . mysql_real_escape_string($dept) . "','" . mysql_real_escape_string($year) . "','" . mysql_real_escape_string($email) . "','" . mysql_real_escape_string($hashed_passwd) . "',$previousID)" ) or die(mysql_error());
				echo "Thank's for registering";
				header( 'refresh:5; url=index.html' );
			}
			else {
				echo "The image wasn't uploaded either";
				header( 'refresh:5; url=index.html' );
			}
		}
		
		mysql_close();
	}
?>