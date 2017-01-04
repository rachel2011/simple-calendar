// For our purposes, we can keep the current month in a variable in the global scope
var today = new Date();
var currentMonth = new Month(today.getFullYear(), today.getMonth()); // October 2012
 
// Change the month when the "next" button is pressed
document.getElementById("next_month_btn").addEventListener("click", function(event){
	currentMonth = currentMonth.nextMonth(); // Previous month would be currentMonth.prevMonth()
	updateCalendar(); // Whenever the month is updated, we'll need to re-render the calendar in HTML
	alert("The new month is "+currentMonth.month+" "+currentMonth.year);
}, false);
 
