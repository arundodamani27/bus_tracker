<?php
include 'db_connection.php';

if (isset($_POST['trip_id']) && isset($_POST['stop_name']) && isset($_POST['stop_time'])) {
    $trip_id = $_POST['trip_id'];
    $stop_name = $_POST['stop_name'];
    $stop_time = $_POST['stop_time'];

    $stmt = $conn->prepare("INSERT INTO stops (trip_id, stop_name, stop_time) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $trip_id, $stop_name, $stop_time);
    $stmt->execute();

    header("Location: manage_trip.php"); // reload page
}
?>
