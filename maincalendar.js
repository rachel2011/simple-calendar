// For our purposes, we can keep the current month in a variable in the global scope
var today = new Date();
var currentMonth = new Month(today.getFullYear(), today.getMonth());
user_status=false;
user_token="";

console.log(document.getElementById("next_month_btn"));
//display next month
document.getElementById("next_month_btn").addEventListener("click", function(event)
{
	currentMonth = currentMonth.nextMonth(); 
	updateCalendar(); 
}, false);

//display previous month
document.getElementById("prev_month_btn").addEventListener("click", function(event)
{
	currentMonth = currentMonth.prevMonth(); 
	updateCalendar(); 
}, false);


//display today
document.getElementById("today_btn").addEventListener("click", function(event)
{
	currentMonth = new Month(today.getFullYear(), today.getMonth());
	updateCalendar(); 

}, false);


//check login info
function checkLoginInfo(){
	var xmlHttp = new XMLHttpRequest();
	xmlHttp.open("POST", "user.php",true);
	xmlHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	xmlHttp.addEventListener("load",checkLoginInfoCallback,false);
	var data="option=checkLoginInfo";
	xmlHttp.send(data);
}

function checkLoginInfoCallback(event){
	var jsonData = JSON.parse(event.target.responseText);

     console.log(jsonData);
     console.log(jsonData.success);
     console.log(jsonData.user);

	//if user doesn't exist, display login and register buttons
	if(!jsonData.success){
		user_status = false;
		$("#nav-btns").html("<button class='btn btn-primary' id='register_btn' onclick=show_register_dialog()>Register</button>");
		$("#nav-btns").append("<button class='btn btn-primary' id='login_btn' onclick=show_login_dialog()>Login</button>");
		

		$("#user_sidebar").html("");
		//add listener to register button
		document.getElementById("register_dialog_btn").addEventListener("click",function(event){
			var username = document.getElementById("reg_username").value;
			var password = document.getElementById("reg_password").value;
			var name = document.getElementById("reg_name").value;
			var email = document.getElementById("reg_email").value;

			var data = "option=register&username=" + encodeURIComponent(username) +
 			"&password=" + encodeURIComponent(password) + "&name=" + encodeURIComponent(name) +
 			"&email=" + encodeURIComponent(email);

 			var xmlHttp = new XMLHttpRequest();
 			xmlHttp.open("POST", "user.php", true);
 			xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
 			xmlHttp.addEventListener("load", function(event)
 			{
 				var jsonData = JSON.parse(event.target.responseText);
				console.log(jsonData.success);
				console.log(jsonData.message);
 				if(jsonData.success){
 					alert("Register successfully!");
 					$("#register_dialog").dialog("close");
 					checkLoginInfo();
					updateCalendar();
 				}else{
 					alert("Signup failed: " + jsonData.message);
 					checkLoginInfo();
 				}
 			},false);
			console.log(data);
 			xmlHttp.send(data);
 		}, false);
		//add listener to login button
		document.getElementById("login_dialog_btn").addEventListener("click",function(event)
        {
 			var username = document.getElementById("login_username").value;
 			var password = document.getElementById("login_password").value;

 			var data = "option=login&username=" + encodeURIComponent(username) + 
 					   "&password=" + encodeURIComponent(password);

 			console.log(username);
 			console.log(password);

 			var xmlHttp = new XMLHttpRequest();
 			xmlHttp.open("POST", "user.php", true);
 			xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
 			xmlHttp.addEventListener("load", function(event){
 				var jsonData = JSON.parse(event.target.responseText);
 				if(jsonData.success){
 					alert("Login successfully!");
					$("#login_dialog").dialog("close");
 					checkLoginInfo();
 					updateCalendar();
 				}else{
 					alert("Login failed: " + jsonData.message);
 					checkLoginInfo();
 				}
 			},false);
 			xmlHttp.send(data);
 		}, false);

	}else{ //user exists
		console.log("aaaa");
		user_status = true;
		user_token = jsonData.token;

		//display 
		$("#nav-btns").html("<button class='btn btn-primary' id='logout_btn'>Log out</button>");

		$("#user_sidebar").html(
			"<button type='button' class='btn btn-danger' onclick=show_create_dialog()>Create event</button>" + 
			"<button type='button' class='btn btn-info' id='show_tag_btn'>Tags</button><br/>"+ 
			"<svg height='10' width='10'><circle cx='5' cy='5' r='4' fill='red'/></svg>Entertainment" + 
			"<svg height='10' width='10'><circle cx='5' cy='5' r='4' fill='blue'/></svg>Business<br/>" + 
			"<svg height='10' width='10'><circle cx='5' cy='5' r='4' fill='yellow'/></svg>Meeting" + 
			"<svg height='10' width='10'><circle cx='5' cy='5' r='4' fill='black'/></svg>Sports" + 
			"<svg height='10' width='10'><circle cx='5' cy='5' r='4' fill='green'/></svg>Other" + 
			//"<h4>Important Events</h4>" + 
			//"<small>This is a list of your important events</small>" + 
			"<ul id='prioritylist' class='nav nav-list'></ul>");
		
		$("#nav-btns").html("<button class='btn btn-primary' id='share_btn'>Share calendar</button><button class='btn btn-primary' id='logout_btn'>Log out</button>");
		$("#show_tag_btn").click(function(){
       	    $(".color_tag").toggle();
        });

        //add listener to logout button
        document.getElementById("logout_btn").addEventListener("click", function(event)
       	{
			var data = "option=logout";
			var xmlHttp = new XMLHttpRequest();
			xmlHttp.open("POST", "user.php", true);
			xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

			xmlHttp.addEventListener("load", function(event){
				var jsonData = JSON.parse(event.target.responseText);
				if(jsonData.success){
					user_status = false;
					user_token = "";
					$("#reg_username").val("");
					$("#reg_password").val("");
					$("#today_event").html("");

					updateCalendar();
					alert("Logout successfully!");
					checkLoginInfo();
				}else{
					alert("Logout failed: " + jsonData.message);
				}
			},false);

			xmlHttp.send(data);
		},false);
		
		
            //add listener to share button
            document.getElementById("share_btn").addEventListener("click", function(event)
            {
            	 var data = "option=find_user";
                 var xmlHttp = new XMLHttpRequest();
                 xmlHttp.open("POST", "user.php", true);
                 xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                 xmlHttp.addEventListener("load", function(event){
                 	var jsonData = JSON.parse(event.target.responseText);
					if(jsonData.success){
						console.log(jsonData.users);
						$("#select_user").html(jsonData.users);
						show_share_dialog();
					}else{
						alert("Database failed: " + jsonData.message);
					}
                 },false);
                 xmlHttp.send(data);
            },false);

	}
}


//check and display user's events
function hasEvent(day, date){

	var dataString = "option=search_event&event_date=" + encodeURIComponent(date);
	var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("POST", "event.php", true);
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
	xmlHttp.addEventListener("load", function(event){
		var jsonData = JSON.parse(event.target.responseText); 
        
        //event exists
		if(jsonData.hasData && jsonData.inSession){

			//display tags with different colors
			var showTags = jsonData.tags;
			var tagColors = ["red", "blue", "yellow", "black", "green"];
			var tagString = "";
			var idx = 0;
			var tags = ["entertainment", "business", "meeting","sports", "other"];

			while(idx < tags.length){
				if(showTags[tags[idx]]){
					tagString += "<svg height='10' width='10' class='color_tag'><circle cx='5' cy='5' r='4' fill='" + tagColors[idx] + "'/></svg>";
				}
				idx++;
			}

			console.log(date);
			document.getElementById(date).innerHTML = day + "&nbsp;<button type='button' class='btn btn-primary btn-mini' id='" + date + "_btn'>Event</button><br />" + tagString;
			document.getElementById(date+"_btn").addEventListener("click", function(event)
			{
				$("#event_entertainment").html(jsonData.entertainment);
				$("#event_meeting").html(jsonData.meeting);
				$("#event_sports").html(jsonData.sports);
				$("#event_business").html(jsonData.business);
				$("#event_other").html(jsonData.other);

                //add listeners to delete button
				var i = 0;
				while(i<$(".delete_btn").length){

					$(".delete_btn").eq(i).click(delete_function);
					i ++;

				}
                
                //add listeners to edit button
				var j = 0;
				while(j<$(".edit_btn").length){

					$(".edit_btn").eq(j).click(edit_function);
				    j++;
				}

				show_event_dialog();
			},false);
		}
	}, false); 
	xmlHttp.send(dataString); 
}


// This updateCalendar() function only alerts the dates in the currently specified month.  You need to write
// it to modify the DOM (optionally using jQuery) to display the days and weeks in the current month.
function updateCalendar(){
	document.getElementById("month_info").textContent = monthToString(currentMonth.month) + " " + currentMonth.year;
	var weeks = currentMonth.getWeeks();
	var htmlParent = document.getElementById("calendar_table");
	var str = "<tr class='info' id='calendar_header'><th>Sunday</th><th>Monday</th><th>Tuesday</th><th>Wednesay</th><th>Thursday</th><th>Friday</th><th>Satday</th></tr>";

	var month_start = false;

	for(var w in weeks){
		if(weeks.hasOwnProperty(w)){
			str += "<tr>";
			var days = weeks[w].getDates();

			for(var d in days){
				if(days.hasOwnProperty(d)){
				if(days[d].getDate() == 1){
					month_start = !month_start;
				}
				if(month_start){
					if(days[d].getDate() == today.getDate() && today.getMonth()==currentMonth.month && today.getFullYear()==currentMonth.year){
						   str = str + "<td class='current_day' id='" + today.toLocaleDateString() + "'>" + days[d].getDate() + "</td>";	
						   
						   //check and display user's events
	                       if(user_token !== ""){
						        hasEvent(today.getDate(), today.toLocaleDateString());
						    }
					}else{ 
					      str = str + "<td class='month_days' id='" + days[d].toLocaleDateString() + "''>"  + days[d].getDate() + "</td>";
				          
				          //check and display user's events
					      if(user_token !== ""){
					         hasEvent(days[d].getDate(),days[d].toLocaleDateString());
					      }
					      
					}
				}else{
					str = str + "<td>" + days[d].getDate() + "</td>";
				}
			}
		}
			str += "</tr>";
		}
	}
	htmlParent.innerHTML = str;
    
    //display today's events on sidebar
    if(user_token !== ""){
		var data_string = "option=search_today_event" + "&today_date=" + encodeURIComponent(today.toLocaleDateString());
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open("POST", "event.php", true);
	    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
		xmlHttp.addEventListener("load", function(event){
			var jsonData = JSON.parse(event.target.responseText); 
			if(jsonData.success){
				$("#today_event").html(jsonData.events_today);
			}else{
				console.log("fail to get today's events");
			}
		},false);
		xmlHttp.send(data_string);
	}

}

//add listener to create event button
document.getElementById("create_btn").addEventListener("click", function(event)
{
	var event_title = document.getElementById("event_title").value;// Get the title from the form
	var event_date = document.getElementById("event_date").value;// Get the date from the form
	var event_tag = document.getElementById("event_tag").value;// Get the tag from the form
	var event_note = document.getElementById("event_note").value;// Get the content from the form
	var event_to_users = $("#event_to_user").val();
	//var token = document.getElementById("token").value;
    // Make a URL-encoded string for passing POST data
    var dataString = 
    "option=create_event" + 
    "&event_title=" + encodeURIComponent(event_title) + 
    "&event_date=" + encodeURIComponent(event_date) + 
    "&event_tag=" + encodeURIComponent(event_tag) + 
    "&event_note=" + encodeURIComponent(event_note) + 
    "&event_to_users=" + encodeURIComponent(event_to_users) + 
    "&token=" + encodeURIComponent(user_token);

    var xmlHttp = new XMLHttpRequest();// Initialize our XMLHttpRequest instance
    xmlHttp.open("POST", "event.php", true);// Starting a POST request (NEVER send passwords as GET variables!!!)
    xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); // It's easy to forget this line for POST requests
	xmlHttp.addEventListener("load", function(event)
	{
		var jsonData = JSON.parse(event.target.responseText); // parse the JSON into a JavaScript object
		if(jsonData.success){  // in PHP, this was the "success" key in the associative array; in JavaScript, it's the .success property of jsonData
			alert("Event created");
			document.getElementById("event_title").value = "";
			document.getElementById("event_note").value = "";
			document.getElementById("event_date").value = "";
			document.getElementById("event_to_user").value="";
			$("#create_dialog").dialog("close"); 
		}else{
			alert("Fail to create event: "+jsonData.message);
		}
	}, false); // Bind the callback to the load event
	xmlHttp.send(dataString); // Send the data
	updateCalendar();
},false);

//functions to display dialogs

function show_login_dialog() {
   $("#login_dialog").dialog();
}

function show_register_dialog() {
  $("#register_dialog").dialog();
}

function show_create_dialog()
{
	$("#create_dialog").dialog({
    modal: true,
    open: function(event, ui) {

        $(".datepicker").datepicker("enable");

    }
});
	//$("#create_dialog").dialog();
}


function show_event_dialog(){
    $("#event_dialog").dialog();
}

function show_edit_dialog(){
    $("#edit_dialog").dialog();
}

function show_share_dialog(){
    $("#share_dialog").dialog();
}

//delete an event
function delete_function(){
	var dataStr1 = "option=delete_event&event_id=" + encodeURIComponent($(this).attr("id")) + "&token=" + encodeURIComponent(user_token);
	var xmlHttp1 = new XMLHttpRequest();
	xmlHttp1.open("POST", "event.php", true);
	xmlHttp1.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
	xmlHttp1.addEventListener("load", function(event){
		var jsonData1 = JSON.parse(event.target.responseText); 
		if(jsonData1.success){  
			$("#event_dialog").dialog("close");
			updateCalendar();
			console.log("Event deleted");
		}else{
			console.log("Fail to delete");
		}
	}, false); 
	xmlHttp1.send(dataStr1);         
}


//edit an event
function edit_function(){
	show_edit_dialog();
	var id = $(this).attr("id");
	document.getElementById("confirm_edit_btn").addEventListener("click", function(event)
	{

		var dataStr2 = "option=edit_event&event_id=" + encodeURIComponent(id) + "&event_title=" + encodeURIComponent($("#edit_event_title").val()) + "&event_note=" + encodeURIComponent($("#edit_event_note").val()) + "&token=" + encodeURIComponent(user_token);
		var xmlHttp2 = new XMLHttpRequest();
		xmlHttp2.open("POST", "event.php", true);
		xmlHttp2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); 
		xmlHttp2.addEventListener("load", function(event)
		{
			var jsonData2 = JSON.parse(event.target.responseText); 
			if(jsonData2.success){  
				$("#edit_dialog").dialog("close");
				$("#edit_event_title").val("");
				$("#edit_event_note").val("");
				$("#event_dialog").dialog("close");
				updateCalendar();
				console.log("Edit successfully");
			}else{
				console.log("Fail to edit");
			}
		}, false); 
		document.getElementById("confirm_edit_btn").addEventListener("click", edit_function, false);
		xmlHttp2.send(dataStr2);   

	},false);
}


//convert month number to string
function monthToString(month) {
    switch(month) {
        case 0:
            return "January";
            
        case 1:
            return "February";
            
        case 2:
            return "March";
            
        case 3:
            return "April";
            
        case 4:
            return "May";
            
        case 5:
            return "June";
            
        case 6:
            return "July";
         
        case 7:
            return "August";
        case 8:
            return "September";
        case 9:
            return "October";
            
        case 10:
            return "November";
            
        case 11:
            return "December";
            
        default:
            return "Unknown Month";
    }
}
