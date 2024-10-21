<?php

function getConnection()
{
    $host = '35.201.222.190';       // Your Cloud SQL public IP
    $db_name = 'dswd-helpdex';      // Your database name
    $username = 'dswdfo5-helpdex';  // Your database username
    $password = 'DSWDfo5';           // Your database password

    // Establish the connection using MySQLi
    $conn = mysqli_connect($host, $username, $password, $db_name);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}
