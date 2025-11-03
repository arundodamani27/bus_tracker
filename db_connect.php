<?php

// Database configuration
$servername = "localhost";
$username = "root";       // Default for local development
$password = "";           // Default for local development (no password)
$dbname = "bus_tracker_db"; // The name of the database you created

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If the connection fails, stop execution and display an error
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set the character set
$conn->set_charset("utf8mb4");

?>