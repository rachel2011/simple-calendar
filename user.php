<?php
	require 'database.php';
	ini_set("session.cookie_httponly", 1);
	session_start();
	header("Content-Type: application/json");

	function CheckUserInfoEmpty($username,$password) {
	    if($username ===""){
			echo json_encode(array(
				"success" => false,
				"message" => "Invalid username!"
			));
			exit;
		}
		else if($password ===""){
			echo json_encode(array(
				"success" => false,
				"message" => "Invalid password!"
			));
			exit;
		}
	}

	//login
	if($_POST['option'] === "login"){

		$userName = htmlentities($_POST['username']);
		$password = htmlentities($_POST['password']);

		//check if username and password is empty
		CheckUserInfoEmpty($userName,$password);

		//connect sql
		$stmt = $mysqli->prepare("SELECT COUNT(*), username, password FROM users WHERE username=?");

		//check query prep
		if(!$stmt){
			echo json_encode(array(
				"success" => false,
				"message" => "Query Prep Failed: "
			));
			exit;
		}

		//
		$stmt->bind_param('s', $userName);
		$stmt->execute();
		$stmt->bind_result($count, $username, $hashpwd);
		$stmt->fetch();

		if($count === 1 && crypt($password, $hashpwd) === $hashpwd){
			session_destroy();
            ini_set("session.cookie_httponly", 1);
            session_start();

			$_SESSION['username']=$username;
			//generate a 10 character random session token
			$_SESSION['token']=substr(md5(rand()),0,10);
			echo json_encode(array(
				"success" => true,
				"token" => $_SESSION['token'],
				"message" => "Login successfully!"
			));
			exit;
		}else{
			echo json_encode(array(
				"success" => false,
				"messange" => "Incorrect username or password!"
			));
			exit;
		}
	}else if($_POST['option'] === "register"){
		if(isset($_POST['username']) && isset($_POST['password'])){
			$userName = htmlentities($_POST['username']);
			$password = htmlentities($_POST['password']);


			//check if username and password is empty
			CheckUserInfoEmpty($userName,$password);

			$stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE username=?");

			//check query prep
			if(!$stmt){
				echo json_encode(array(
					"success" => false,
					"message" => "Query prep failed: "+ $mysqli->error
				));
				exit;
			}

			$stmt->bind_param('s',$userName);
			$stmt->execute();
			$stmt->bind_result($count);
			$stmt->fetch();
			$stmt->close();

			//check if the username already exists
			if($count ===1){
				echo json_encode(array(
					"success" => false,
					"message" => "Username already exists!"
				));
				exit;
			}

			//sign up info
			$hashpwd=crypt($password);
			$name = $_POST['name'];
			$email = $_POST['email'];

			//insert user info into mysql
			$stmt = $mysqli->prepare("INSERT INTO users (username,password,name,email) VALUES (?,?,?,?)");
			if(!$stmt){
				echo json_encode(array(
					"success" => false,
					"message" => "Query prep failed."
				));
				exit;
			}

			$stmt->bind_param('ssss',$userName,$hashpwd,$name,$email);
			$stmt->execute();
			$stmt->close();

			$_SESSION['username']=$userName;
			$_SESSION['token']=substr(md5(rand()),0,10);

			echo json_encode(array(
                "success" => true,
                "token" => $_SESSION['token'],
                "message" => "Register successfully!"
                ));



            exit;
        }
    }else if($_POST['option'] === "logout"){
    	unset($_SESSION['username']);
        unset($_SESSION['token']);
        session_destroy();

        echo json_encode(array(
        	"success" => true,
        	"message" => "Logout sucessfully!"
        ));
        exit;
    }else if($_POST['option']==="checkLoginInfo"){
		if(isset($_SESSION['username'])){
    		echo json_encode(array("success" => true,
    			                   "user" => $_SESSION['username'],
    			                   "token" => $_SESSION['token']));
    	}else{
    		echo json_encode(array("success" => false));
    	}
    
	}else if($_POST['option'] == "find_user"){
    	$result = "";
    	$self = $_SESSION['username'];
    	$stmt = $mysqli->prepare("select username from users where username != ?");
    	if(!$stmt){
				printf("Query Prep Failed: %s\n", $mysqli->error);
				echo json_encode(array(
					"success" => false,
					"message" => "database error"
					));
				exit;
			}

			$stmt->bind_param('s', $self);            
			$stmt->execute();     
			$stmt->bind_result($user_result);


			while($stmt->fetch()){
				$result .= sprintf("<option value='%s'> %s </option>",
											htmlspecialchars($user_result),
											htmlspecialchars($user_result));
			}

			echo json_encode(array(
					"success" => true,
			        "users" => $result
			));
			$stmt->close();
			exit;
    }















?>

