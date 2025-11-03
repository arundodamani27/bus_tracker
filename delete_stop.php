<?php
include 'db_connect.php';
if (isset($_GET['stop_id'])) {
    $stop_id = $_GET['stop_id'];
    $stmt = $conn->prepare("DELETE FROM stops WHERE stop_id = ?");
    $stmt->bind_param("i", $stop_id);
    $stmt->execute();
    header("Location: manage_trip.php");
    exit;
} else {
    echo "Invalid Request";
}
?>
