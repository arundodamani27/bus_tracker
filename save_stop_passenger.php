<?php
include 'db_connect.php';

// Collect passenger input
$stop_name = $_POST['stop_name'] ?? '';
$arrival_time = $_POST['arrival_time'] ?? '';

if (empty($stop_name) || empty($arrival_time)) {
    die("<div class='alert alert-danger text-center mt-5'>Stop name and time are required!</div>");
}

// 1️⃣ Find which trip this time belongs to
$query = "SELECT trip_id FROM trips WHERE start_time <= ? AND end_time >= ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $arrival_time, $arrival_time);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $trip_id = $row['trip_id'];

    // 2️⃣ Insert the stop under that trip
    $insert = $conn->prepare("INSERT INTO stops (trip_id, stop_name, arrival_time) VALUES (?, ?, ?)");
    $insert->bind_param("iss", $trip_id, $stop_name, $arrival_time);

    if ($insert->execute()) {
        echo "<div class='alert alert-success text-center mt-5'>
                ✅ Stop added successfully to trip #$trip_id!
              </div>
              <div class='text-center mt-3'>
                <a href='index.php' class='btn btn-primary'>Back to Tracker</a>
              </div>";
    } else {
        echo "<div class='alert alert-danger text-center mt-5'>Error adding stop!</div>";
    }
} else {
    echo "<div class='alert alert-warning text-center mt-5'>
            ⚠️ No active trip found for the given time!
          </div>
          <div class='text-center mt-3'>
            <a href='index.php' class='btn btn-secondary'>Back</a>
          </div>";
}
?>
