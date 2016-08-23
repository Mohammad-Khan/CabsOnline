<!DOCTYPE html>

<html XMLns="http://www.w3.org/1999/xHTML">

<?php

	/* login.php
	   This page facilitates the user to log in to the CabsOnline and be able to book a taxi
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
	<title>CabsOnline Login</title>
</head>


<body>

<h1> Login to CabsOnline </h1>
	
	
	<!--Following is the HTML form to collect required information from user to log in to CabsOnline booking page-->

	<form method="post">
			<label> Email:	<input type="email" name="email"> </label> 
			<br/>
			<label> Password:	<input type="password" name="password"> </label>  
			<br/><br/>
			<input id="submit" type="submit" name="submit" value="Log in" />
	</form>


</body>

<?php

	if (isset($_POST ['submit'])) //Ensuring that this php code runs only if the form above has been submitted
	{
		require_once ("settings.php"); //collecting mySql connection information from another page named as settings.php
		$conn = @mysqli_connect($host, $user, $pwd,	 $sql_db); //Feeding all the acquired information to establish a MySQL connection
		
		
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
		
		
		/*
		Taking all the input values from HTML form and assigning them to php variables
		*/
		
		$email = $_POST['email'];
		$password= $_POST['password'];
		
		$query = "select e_mail, password, customer_name from customer where e_mail = '$email' and password = '$password'"; //This query will bring up the information from MySQL based on the infor provided from user	
		$result = @mysqli_query ($conn, $query);
		$row = @mysqli_fetch_assoc($result); //Fetching the row with corresponding information from the customer table in MySQL
		
		
		/*
		comparing the provided info and info stored in customer table to see if the user is registered and can access the booking page
		*/
		
		if($row['e_mail'] == $email AND ($row['password'] == $password)) //if info matched then starting a session for the user
		{ 
			session_start(); 
			$_SESSION['email'] = $email;
			$_SESSION['customer'] = $row['customer_name'];
			$_SESSION['logged'] = true;
			header ("location: booking.php"); //redirecting user to the booking.php
		} else 
		{ 
			echo "<p>Login Unsuccessful - Either your email or password is incorrect, please re-enter</p>"; 
			
		}
		
	}
	


?>

	<!--This line provides a link to the register.php page, in case user is looking to register with CabsOnline -->
	<p> Not Registered? <a href="register.php"> Register here </a> 

</html>