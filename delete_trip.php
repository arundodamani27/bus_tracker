<?php
session_start();
// Security check
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['trip_id'])) {
    
    $trip_id = $_POST['trip_id'];
    $driver_id = $_SESSION['driver_id'];

    // SQL DELETE Query (CRUD - DELETE)
    // IMPORTANT: We check driver_id to ensure a driver can only delete their own trips.
    $stmt = $conn->prepare("DELETE FROM trips WHERE trip_id = ? AND driver_id = ?");
    $stmt->bind_param("ii", $trip_id, $driver_id);

    if ($stmt->execute()) {
        // Success: Redirect back to My Trips page with a success status
        header("Location: my_trips.php?status=deleted");
        exit;
    } else {
        // Failure: Redirect with an error status
        header("Location: my_trips.php?status=error&message=" . urlencode("Could not delete trip."));
        exit;
    }
    
    $stmt->close();
    $conn->close();
} else {
    // If accessed directly without POST data
    header("Location: dashboard.php");
    exit;
}
?>