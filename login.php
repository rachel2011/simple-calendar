<?php
	require 'database.php';
	ini_set("session.cookie_httponly", 1);
	session_start();
	header("Content-Type: application/json");

	//login
	if(isset($_POST['option']) && $_POST['option'] === "login"){

		$userName = (string) htmlentities($_POST['username']);
		$password = (string) htmlentities($_POST['password']);

		//check if username and password is empty
		CheckUserInfoEmpty($userName,$password);

		//connect sql
		$stmt = $mysqli->prepare("SELECT COUNT(*), username, password FROM users WHERE username=?");

		//check query prep
		if(!$stmt){
			echo json_encode(array(
				"success" => false,
				"message" => "Query Prep Failed: ")+$mysqli->error);
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
				"sucess" => true,
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
	}else if(isset($_POST['option']) && $_POST['option'] === "register"){
		if(isset($_POST['username']) && isset($_POST['password'])){
			$userName = (string) htmlentities($_POST['username']);
			$password = (string) htmlentities($_POST['password']);

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
					"message" => "Query prep failed: "+ $mysqli->error
				));
				exit;
			}

			$stmt->bind_param('ssss',$userName,$hashpwd,$name,$email);
			$stmt->execute();
			$stmt->close();

			echo json_encode(array(
                "success" => true,
                "message" => "Register successfully!"
                ));
            exit;
        }
    }else if(isset($_POST['option']) && $_POST['option'] === "logout"){
    	unset($_SESSION['username']);
        unset($_SESSION['token']);
        session_destroy();
        
        echo json_encode(array(
        	"success" => true,
        	"message" => "Logout sucessfully!"
        ));
        exit;
    }


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





























	}