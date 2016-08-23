<!DOCTYPE html>

<html XMLns="http://www.w3.org/1999/xHTML">

<?php

	/* register.php
	   This page is dedicated to be used for the registration purpose of CabsOnline
	   User feed this page with required information to complete registration with CabsOnline
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
	<title>CabsOnline Regitration</title>
</head>

<body>

<h1> Register to CabsOnline </h1>

	<p> Please fill the fields below to complete your registration </p>
	
	<!--Following is the HTML form to collect required information from user for CabsOnline registration-->
	
	<form method="post">
			<label> Name:	<input type="text" name="name"> </label> 
			<br/>
			<label> Password:	<input type="password" name="password"> </label> 
			<br/>
			<label> Confirm Password:	<input type="password" name="cpassword"> </label> 
			<br/>
			<label> Email:	<input type="email" name="email"> </label> 
			<br/>
			<label> Phone:	<input type="text" name="phone"> </label> 
			<br/><br/>
			<input id="submit" type="submit" name ="submit" value="Register" />
	</form>

	
</body>

<?php

	if (isset($_POST ['submit'])) //Ensuring that this php code runs only if the form above has been submitted
	{
		
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
		
		$result = false;
		
		
			if (!$conn) //Making sure if the MySQL connection has been established properly, if not then error message will be shown and php will be exited
			{
				echo "<p>Connection with database can not be established, Server might be temporarily down</p>";
				exit();
			} else //If MySQL connection is properly established
			{
				
				/*
				Taking all the input values from HTML form and assigning them to php variables
				*/
				
				$name = $_POST['name'];
				$password= $_POST['password'];
				$cpassword= $_POST['cpassword'];
				$email= $_POST['email'];
				$phone= $_POST['phone'];
				
				if ($password != $cpassword) //To check if the user have set a password, and confirming the password
				{
					echo "<p> Password does not match, Please re-enter details</p>";
					$result = false;
				} else if (empty($name) || empty($password) || empty($cpassword) || empty($email) || empty($phone)) //Checking if user left any required info blank
				{
					echo "<p> Please enter all details </p>";
					$result = false;
				} else  //If all the details are provided and the passwords match
				{
					
					
					/*The following chunk of code checks if the table exist in database,
					if not then the code automatically creates a new table named customer with required fields in MySQL in given database.
					*/
					
					$query = "show tables like 'customer'";
					$ifTableExist = @mysqli_query($conn, $query);
					$row = @mysqli_fetch_assoc($ifTableExist);
					$size = sizeof($row);

					if ($size < 1) //This condition determines if the table exist or not by checking the array size of row.
					{
						$query = "CREATE TABLE customer (e_mail varchar(30) NOT NULL PRIMARY KEY, customer_name varchar(30) NOT NULL, password varchar (15) NOT NULL, phone varchar (15) NOT NULL)"; //Creating new table, if the required table does not exist in database
						$result = @mysqli_query($conn, $query) or die("Table can not be created");
						@mysqli_free_result($result);
					}
					
					$query = "insert into customer (e_mail, customer_name, password, phone)
								values ('$email', '$name', '$password', '$phone')"; //Once it is established that the either table already exist or created now, this query would insert the values in that table.
					$result = @mysqli_query ($conn, $query);
					
					if (!$result) //If the primary key is overlapping. i.e. the primary key email already exist in the table, already registered.
					{
						echo "<p> This email is already registered </p>";
					}
				}
				
			}
			
			@mysqli_close($conn); //Done working in database, closing connection
			
		if ($result) //Checking if the last query was a success, i.e. values are inserted successfully in corresponding customer table
		{
			echo "<p> Successfully registered</p>";
		} else
		{
			echo "<p> Registration unsuccessful</p>";
		}
	}
	


?>
	<!--This line provides a link to the login.php page, in case user is already registered and looking to log in -->
	<p> Already Registered? <a href="login.php"> Login here </a> 

</html>