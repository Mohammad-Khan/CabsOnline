<!DOCTYPE html>

<html XMLns="http://www.w3.org/1999/xHTML">

<?php

	/* admin.php
	   This page is dedicated for admin use, it populates the list of bookings that has a pick up time of 2 hours and facilitates the admin
	   to assign the cabs to relevant booking requests.
	   Author: Mohammad Khan
	   Date: 02 Feb 2015
	*/

?>

<head>
	<meta charset="utf-8" /> 
	<meta name="description" content="Assignment 1" />
	<meta name="keywords" content="PHP, MySql" /> 
	<meta name="author" content="Mohammad Khan">
	<link rel="stylesheet" type="text/css" href="style.css">
	<title>CabsOnline Admin</title>
</head>


<body>

<h1> Admin page of CabsOnline </h1>

	<!--Following is the HTML form to faciltate the admin to retrieve the booking requests list-->

	<form method = "POST"> 1. Click the button below to search for all unassigned booking requests with a pick-up time within 2 hours. <br/>
	<input name ="submit_list" type="submit" value="List all" />
	</form>
	
	<!--Following is the HTML form to faciltate the admin to assign the cabs to relevant booking requests-->
	<form method = "POST"> 2. Input a reference below and click "update" to assign a taxi to that request.
	<label>Reference #: <input type="text" name="reference" /></label>
	<br/>
	<input name="submit_update" type="submit" value="Update" />
	</form>

</body>

<?php


	require_once ("settings.php"); //collecting mySql connection information from another page named as settings.php
	$conn = @mysqli_connect($host, $user, $pwd,	 $sql_db) or die("Failed to connect to MySQL: " . mysql_error()); //Feeding all the acquired information to establish a MySQL connection
		
	/*Making sure if the database exist, 
	if not the php will exit right away and show the message that database does not exist.
	*/
		
	if (@!mysqli_select_db($conn, $sql_db))
	{
		echo "<p>The $sql_db database does not exist</p>";
		exit();
	} 	
	
	if (!$conn) //Making sure if the MySQL connection has been established properly, if not then error message will be shown and php will be exited
	{
		echo "<p>Connection with database can not be established, Server might be temporarily down</p>";
		exit();
	}
		
		
	if (isset($_POST['submit_list'])) //Ensuring that this php code runs only if the list all button has been hit
	{
		//The following query will retrieve data from the booking table and uses a foreign key to retrieve some other data from customer table
		$query = "select booking.*, customer.customer_name from booking, customer where status = 'unassigned' and customer.e_mail = booking.e_mail";
		$result = @mysqli_query($conn, $query);


		
		//The following code generates a table and arranges the retrieved data in a proper format to populate a list of booking requests
		echo "<table border=\"1\">"; 
		echo "<tr>" 
		."<th scope=\"col\">Reference #</th>" 
		."<th scope=\"col\">Customer Name</th>" 
		."<th scope=\"col\">Passenger Name</th>" 
		."<th scope=\"col\">Passenger Phone</th>"
	    ."<th scope=\"col\">Pick Up Address</th>"
		."<th scope=\"col\">Destination Suburb</th>"
		."<th scope=\"col\">Pick Up Time</th>"
		."</tr>";
		
		while ($row = @mysqli_fetch_assoc($result)) //retrieving row by row by data from booking and customer tables
		{
			
		$pickup_time = strtotime($row ['pick_up']); //converting pick up time from MySQL table to Unix Time Stamp
		
		$now_time = time(); //taking current time of server
		
		$time_diff = $pickup_time - $now_time; //calculating time difference between current time and pick up time requested by customer
		
		$unitstreet_number = "";
		
		if ($row['unit_number'] == 0) //arranging half of address string as per specification, combining unit number and street number
		{
			$unitstreet_number = $row['street_number'];
		} else
		{
			$unitstreet_number = array($row['unit_number'], $row['street_number']);
			$unitstreet_number = implode("/", $unitstreet_number); //combining the unit number with street number and as specified putting / as separator
		}
		
		$streetnumber_name = array ($unitstreet_number, $row['street_name']); //arranging the another half of the address string as per specification of assignment requirement
		$streetnumber_name = implode (" ", $streetnumber_name);
		
		$address_array = array ($streetnumber_name, $row['suburb_name'] );
		$address = implode (", ", $address_array); //arranging full address string as per specification
		
		if ($time_diff <= 7200 && $time_diff > 0) //if the time difference is less than 2 hours take those entries and populate them in a table format
		{
			$pickup_time_disp = date("d M H:i", strtotime($row['pick_up']));
			echo "<tr>"; 
			echo "<td>",$row["booking_number"],"</td>"; 
			echo "<td>",$row["customer_name"],"</td>"; 
			echo "<td>",$row["name"],"</td>"; 
			echo "<td>",$row["phone"],"</td>";
			echo "<td>",$address,"</td>";
			echo "<td>",$row["destination_suburb"],"</td>";
			echo "<td>",$pickup_time_disp,"</td>";
			echo "</tr>"; 
		}
		
		}
		
		@mysqli_close($conn); //Closing database connection
		
		echo "</table>";
		
		

	} elseif(isset($_POST['submit_update'])) //Ensuring that this php code runs only if the update button has been hit and reference number is provided
	{
		if (empty($_POST ['reference']))
		{
			echo "<p> Please provide a reference number to proceed with assigning a cab </p>";
			exit();
		}
		$reference_number = $_POST ['reference'];
		$query = "update booking set status = 'assigned' where booking_number = $reference_number"; //query to assign the cab to provided reference number
		$result = @mysqli_query ($conn, $query);
		
		
		@mysqli_free_result($result);
		$query = "select status from booking where booking_number = '$reference_number'"; //to retrived the status after assigning a cab, so that it could be checked if it has been properly assigned
		$result = @mysqli_query ($conn, $query);
		$row = @mysqli_fetch_assoc($result);
		
		$status = trim($row['status']);
		if($status == "assigned") //if retrieved satus after the assigning is changed to assign then the message will be shown to the admin that it has been properly assigned otherwise it shows the message no match was found for this reference number in booking table
		{ 
			echo "The booking request $reference_number has been properly assigned.";
		} else
		{
			echo "The reference number you entered is incorrect, no match found for this reference number";
		}
		
		@mysqli_close($conn); //Closing database connection
	}


?>

</html>