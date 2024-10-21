<?php
// Cloud SQL connection details
$host = '35.201.222.190';       // Replace with your Cloud SQL public IP address
$db_name = 'dswd-helpdex';      // Your database name
$username = 'dswdfo5-helpdex';  // Your database username
$password = 'DSWDfo5';           // Your database password

// Directly establish the connection using procedural MySQLi
$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected to the database successfully.";
