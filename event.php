<?php

    //http-only session cookie
    ini_set("session.cookie_httponly", 1);
	session_start();
	require 'database.php';
	header("Content-Type: application/json");
	$username = $_SESSION['username'];
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");

		//check query prep
		if(!$stmt){
			echo json_encode(array(
				"success" => false,
				"message" => "Query Prep Failed: "
			));
			exit;
		}

		//
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result($user_id);
		$stmt->fetch();
		$stmt->close();



    //check if user
    if (!isset($_SESSION['username'])){
        echo json_encode(array("success" => false, "message" => "You are a visitor right now. Register to get start on your own schedule!"));
	exit;
}
    //create an event
	if($_POST['option'] === 'create_event'){	
		$title = $_POST['event_title'];
		$date = $_POST['event_date'];
		$note = $_POST['event_note'];
		$tag = $_POST['event_tag'];
        $users = $_POST['event_to_users'];
		
        //check session token
		if($_SESSION['token'] !== $_POST['token']){
			die("Request forgery detected");
		}
		
      
        //insert into database
		$stmt = $mysqli->prepare("insert into event (event_title, event_note, event_tag, event_date, event_userid) values (?, ?, ?, ?, ?)");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			echo json_encode(array(
				"success" => false,
				"message" => "database error"
				));
			exit;
		}
        $stmt->bind_param('ssssi', $title, $note, $tag, $date, $user_id);
		$stmt->execute();
		$stmt->close();
        
        //success
		echo json_encode(array(
			"success" => true
			));
		exit;
    
	}else if($_POST['option'] === 'search_event'){

        if(!isset($_SESSION['username'])){
        	echo json_encode(array(
        		"inSession" => false,
			    "hasData" => false
           ));
           exit;
        }


		//create an array for loopting through each tag for searching events
		$allTags = array("entertainment"=>false, "business"=>false, "meeting"=>false,"sports"=>false, "other"=>false);
		//create an associative array for containing search results by event tags
		$result = array("entertainment"=>"<h4>Entertainment</h4>  <ul class='list-group'>\n", 
						"business"=>"<h4>Business</h4>  <ul class='list-group'>\n", 
						"meeting"=>"<h4>Meeting</h4>  <ul class='list-group'>\n", 
						"sports"=>"<h4>Sports</h4>  <ul class='list-group'>\n", 
						"other"=>"<h4>Other</h4>  <ul class='list-group'>\n");

		$date = $_POST['event_date'];
		$hasData = false;

		//loop through each event tag for searching 
		//and adding all the searching result together for sending back
		foreach( array_keys($allTags) as $eachTag ){
			$stmt = $mysqli->prepare("select event_id, event_tag, event_title, event_note from event 
										where event_userid = ? and event_date = ? and event_tag = ?");
			if(!$stmt){
				printf("Query Prep Failed: %s\n", $mysqli->error);
				echo json_encode(array(
					"success" => false,
					"message" => "database error"
					));
				exit;
			}

			$stmt->bind_param('iss', $user_id, $date, $eachTag);            
			$stmt->execute();     
			$stmt->bind_result($eventId, $eventTag, $eventTitle, $eventNote);


			while($stmt->fetch()){
				$hasData = true;
				$allTags[$eachTag] = true;
				$result[$eachTag] .= 
					sprintf("\t
                            <li class='list-group-item'>Title: %s</li>
                            <li class='list-group-item'>Details: %s</li>
                            <button type='button' class='btn btn-primary edit_btn' id=%s>Edit</button>
                            <button type='button' class='btn btn-danger delete_btn' id=%s>Delete</button>\n",
                    htmlspecialchars($eventTitle),
                    htmlspecialchars($eventNote),
                    htmlspecialchars($eventId),
                    htmlspecialchars($eventId)
                    );
			}


			$result[$eachTag] .= "</ul>\n\n";
		}

		//after looping through all tags return the final searching result
		echo json_encode(array(
			        "inSession" => isset($_SESSION['username']),
			        "hasData" => $hasData,
			        "tags" => $allTags,
					"entertainment"=>$result['entertainment'],
					"business"=>$result['business'],
					"meeting"=>$result['meeting'],
					"sports"=>$result['sports'],
					"other"=>$result['other']
		));
		$stmt->close();
		exit;



    //edit an event
	}else if($_POST['option'] === 'edit_event'){
		$id = $_POST['event_id'];
		$newTitle = $_POST['event_title'];
		$newNote = $_POST['event_note'];
		//check if user is editing an event that does not belong to him
		$stmt = $mysqli->prepare("select event_userid from event where event_id= ? and event_userid != ?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			echo json_encode(array(
				"success" => false,
				"message" => "database error"
				));
			exit;
		}
		$stmt->bind_param('si', $id, $user_id);
		$stmt->execute();
		$stmt->bind_result($test);
        
        //in this case, a user wants to edit events that do not belong to him
		if($stmt->fetch()){
			die("Request forgery detected");
			exit;
		}
        //pass test, then edit event
		$stmt = $mysqli->prepare("update event set event_title = ?, event_note = ? where event_id = ?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			echo json_encode(array(
				"success" => false,
				"message" => "database error"
				));
			exit;
		}
        
        //check session token
		if($_SESSION['token'] != $_POST['token']){
			die("Request forgery detected");
		}
		
		$stmt->bind_param('sss', $newTitle, $newNote, $id);
		$stmt->execute();
		$stmt->close();
		echo json_encode(array(
			"success" => true
			));
		exit;
    //delete an event
	}else if($_POST['option'] == 'delete_event'){
		$id = $_POST['event_id'];
        //check if user is removing an event that does not belong to him
		$stmt = $mysqli->prepare("select event_userid from event where event_id= ? and event_userid != ?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			echo json_encode(array(
				"success" => false,
				"message" => "database error"
				));
			exit;
		}
		$stmt->bind_param('si', $id, $user_id);
		$stmt->execute();
		$stmt->bind_result($test);
        
        //in this case, a user wants to remove events that do not belong to him
		if($stmt->fetch()){
			die("Request forgery detected");
			exit;
		}
        
        //pass test, then remove event
		$stmt = $mysqli->prepare("delete from event where event_id = ?");
		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			echo json_encode(array(
				"success" => false,
				"message" => "database error"
				));
			exit;
		}
        
        //check session token
		if($_SESSION['token'] != $_POST['token']){
			die("Request forgery detected");
		}
		$stmt->bind_param('s', $id);
		$stmt->execute();
		$stmt->close();
		echo json_encode(array(
			"success" => true
			));
		exit;
	}else if($_POST['option'] == "search_today_event"){
        
        $result = "<ul class='list-group'>";
    	$today_date = $_POST['today_date'];
    	$stmt = $mysqli->prepare("select event_title from event where event_date=? and event_userid=?");

    	if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			echo json_encode(array(
				"success" => false,
				"message" => "database error"
				));
			exit;
		}
		$stmt->bind_param('si', $today_date, $user_id);
		$stmt->execute();
		$stmt->bind_result($title);

		while($stmt->fetch()){
			$result.= sprintf("<li class='list-group-item'>%s</li>", htmlspecialchars($title));
		}
		$result .= "</ul>";

		echo json_encode(array(
			"success" => true,
			"events_today" => $result
		));

		$stmt->close();
		exit;
    }else if($_POST['option'] == 'share_calendar'){
		$share_to = $_POST['share_to'];

		$stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");

		//check query prep
		if(!$stmt){
			echo json_encode(array(
				"success" => false,
				"message" => "Query Prep Failed: "
			));
			exit;
		}

		//
		$stmt->bind_param('s', $share_to);
		$stmt->execute();
		$stmt->bind_result($share_id);
		$stmt->fetch();
		$stmt->close();


		echo $share_id;
		echo $user_id;

		$stmt = $mysqli->prepare("insert into event (event_title, event_note, event_userid, event_tag, event_date) select event_title, event_note, ?, event_tag, event_date from event where event_userid = ?");

		if(!$stmt){
			printf("Query Prep Failed: %s\n", $mysqli->error);
			echo json_encode(array(
				"success" => false,
				"message" => "database error"
				));
			exit;
		}

		$stmt->bind_param('ii', $share_id,$user_id);
		$stmt->execute();
		$stmt->close();

		echo json_encode(array(
			"success" => true
			));
		exit;
    }else{
		echo json_encode(array(
			"success" => false,
			"message" => "unknown request"
			));
		exit;
	}
?>