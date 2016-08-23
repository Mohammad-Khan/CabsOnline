<!DOCTYPE html>

<html XMLns="http://www.w3.org/1999/xHTML">

<?php

	/* booking.php
	   This page is dedicated to be used for booking a taxi from CabsOnline, 
	   This page can only be accessed when the user has properly logged in and the session has started.
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
	<title>CabsOnline Booking</title>
</head>


<body>

<?php

session_start();

	
if (@$_SESSION['logged'] == true) //To make sure this page only runs when the user has logged in properly
{
	$email = @$_SESSION['email'];
	$customer = @$_SESSION['customer'];

?>

<h1> Book a cab </h1>

	<p> Please fill the fields below to book a taxi </p>
	
	<!--Following is the HTML form to collect required information from user for booking a taxi from CabsOnline-->
	
	<form method="post">
			<label> Passenger Name:	<input type="text" name="name"> </label> 
			<br/>
			<label> Contact phone of the passenger:	<input type="text" name="phone"> </label> 
			<br/>
			<fieldset>
			<legend> Pick up address </legend>
				<label> Unit #:	<input type="number" name="unitnumber"> </label> 
				<br/>
				<label> Street #:	<input type="text" name="streetnumber"> </label> 
				<br/>
				<label> Street name:	<input type="text" name="streetname"> </label> 
				<br/>
				<label> Suburb name:	<input type="text" name="suburbname"> </label> 
				<br/>
			</fieldset>
			<label> Destination suburb:	<input type="text" name="dsuburbname"> </label> 
			<br/>
			<label> Pickup date:	<input type="date" name="pick_date"> </label>
			<br/>			
			<label> Pickup time:	<input type="time" name="pick_time"> </label> 
			<br/><br/>
			<input id="submit" type="submit" name="submit" value="Book" />
	</form>

	
<?php

	if (isset($_POST['submit'])) //To make sure this code only runs when the form above is submitted
	{
		/*
		Taking all the input values from HTML form and the session to assign them to corresponding php variables
		*/
		$name = $_POST['name'];
		$phone = $_POST['phone'];
		$unit_number = $_POST['unitnumber'];
		$street_number = $_POST['streetnumber'];
		$street_name = $_POST['streetname'];
		$suburb = $_POST['suburbname'];
		$dsuburb = $_POST['dsuburbname'];
		$booking_time = date("y-m-d H:i:s");
		$status = "";
		$result = false;
		
		
		$pickup_date = @$_POST['pick_date'];
		$pickup_time = @$_POST['pick_time'];

		$pickup1 = $pickup_date. ' ' .$pickup_time; //user provided the date and time separately, this code concatenates those 2 inputs and make one string
		$pickup = strtotime($pickup1); //This line converts the pickup1 string to Unix Time Stamp


		
		$booking_calcu = time(); //This grabs up the current time on server, it will be used in calculation
		$time_diff = 0;
		
		
		$time_diff = $pickup - $booking_calcu; //calculating time difference between booking time and the pick up time, this returns the difference in seconds
		
		if ($time_diff < 3600) //checking if the booking has been made at least one hour before desired pick up time, if not it prints an error if ok the else statement runs
		{
			echo "<p>Kindly provide at least a 60 minutes gap for pick up time </p>";
			exit();
		} else
		{
			$status = "Unassigned";
		}
		
		if (empty($unit_number)) //if unit number is empty, the null will be stored in MySQL booking table
		{
			$unit_number = NULL;
		}
		
		if (empty($email) || empty($name) || empty($phone) || empty($street_number) || empty($street_name) || empty($suburb) || empty($dsuburb) || empty($booking_time) || empty($pickup1) || empty($status)) //making sure that all the must required fields are provided, if any one of them is missing, it will ask to provide all details
		{
			echo "<p>Please provide all details to make booking, If unit number does not apply you can leave it blank</p>";
			exit();
		} else
		{
			require_once ("settings.php"); //collecting MySql connection information from another page named as settings.php
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
			
			
			/*The following chunk of code checks if the table exist in database,
			if not then the code automatically creates a new table named booking with required fields in MySQL in given database.
			*/
					
			$query = "show tables like 'booking'";
			$ifTableExist = @mysqli_query($conn, $query);
			$row = @mysqli_fetch_assoc($ifTableExist);
			$size = sizeof($row);

			if ($size < 1) //This condition determines if the table exist or not by checking the array size of row.
			{
				//Creating new table, if the required table does not exist in database
				
				$query = "CREATE TABLE booking (

												booking_number int (6) NOT NULL AUTO_INCREMENT,
												e_mail varchar(30) NOT NULL,
												name varchar(30) NOT NULL,
												phone varchar (15) NOT NULL,
												unit_number int,
												street_number varchar(3) NOT NULL,
												street_name varchar(15) NOT NULL, 
												suburb_name varchar(15) NOT NULL,
												destination_suburb varchar(15) NOT NULL,
												booking_time datetime NOT NULL,
												pick_up datetime NOT NULL,
												status varchar(15) NOT NULL,

												PRIMARY KEY (booking_number),
												FOREIGN KEY (e_mail) REFERENCES customer (e_mail)

												)";
				$result = @mysqli_query($conn, $query) or die("Table can not be created");
				@mysqli_free_result($result);
				$query = "alter table booking AUTO_INCREMENT=10001";
				$result = @mysqli_query($conn, $query) or die("Field can not be updated");
				@mysqli_free_result($result);
			}
			
			//Once it is established that the either table already exist or created now, following query would insert the values in that table.
			$query = "insert into booking (e_mail, name, phone, unit_number, street_number, street_name, suburb_name, destination_suburb, booking_time, pick_up, status) 
			values ('$email', '$name', '$phone', '$unit_number', $street_number, '$street_name', '$suburb', '$dsuburb', '$booking_time', '$pickup1', '$status')"
			or die("Booking was unsuccessful : " . mysql.error());
			$result = @mysqli_query ($conn, $query);
			
			@mysqli_close($conn); //Done working in database, closing connection
			
		}

	}
	
	
	/*
	When all the operations above are done properly and the last query returns a true,
	which establishes the fact that the fields were inserted properly to the table booking,
	the following code would run to display the user feedback, providing a clear message to user that booking has been successfully
	made and following is the reference number and other booking details
	*/
	if (@$result)
	{
		
		require_once ("settings.php"); //collecting MySql connection information from another page named as settings.php
		$conn = @mysqli_connect($host, $user, $pwd,	 $sql_db) or die("Failed to connect to MySQL: " . mysql_error()); //Feeding all the acquired information to establish a MySQL connection
		
		/*the following query is meant to bring up the unique reference number from the booking table, 
		it uses the clause of time stamp and all the other conditions to avoid any kind of duplication. 
		The reference number was generated in auto increment MySQL to further ensure that duplication is avoided.
		*/
		$query = "select booking_number from booking where name = '$name' and phone = '$phone' and destination_suburb = '$dsuburb' 
					and suburb_name = '$suburb' and e_mail = '$email' and street_name = '$street_name' and street_number = '$street_number' and booking_time = '$booking_time' and pick_up = '$pickup1'"; 
		
		$result2 = @mysqli_query($conn, $query);
		
		$row = @mysqli_fetch_assoc($result2);
		$reference_number = $row ['booking_number'];
		@mysqli_close($conn);
		
		echo "<p>Booking made successfully - Thank you! <br/> Your booking reference number is $reference_number. 
					We will pick up the passenger in front of your provided address at $pickup_time on $pickup_date </p>"; //Displaying booking information to customer.
			
		
		
		// The following code is responsible to send an email to the customer who made booking with all the corresponding information
		$subject = "Your booking request with CabsOnline!";
		$message = "Dear $customer, Thanks for booking with CabsOnline! Your booking reference number is $reference_number. We will pick up the passenger $name in front of your provided address at $pickup_time on $pickup_date.";
		$headers = "From booking@cabsonline.com.au";
		
		mail($email, $subject, $message, $headers, "-r 4974948@student.swin.edu.au");
	}

	} else
	{
		echo "<p> This page is restricted to registered users only</p>"; //This line only runs if the user is not properly logged in.
	}
?>

</body>


</html>