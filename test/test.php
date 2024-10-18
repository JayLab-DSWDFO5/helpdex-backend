<?php
$host = '35.201.222.190';       // Your Cloud SQL public IP
$db_name = 'dswd-helpdex';      // Your database name
$username = 'dswdfo5-helpdex';  // Your database username
$password = 'DSWDfo5';           // Your database password

// Establish the connection
$conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected to the database successfully.";
?>

