<?php
session_start();
// Security check: If the driver is NOT logged in, redirect them to the login page.
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection (though not strictly needed yet, it's good practice)
include 'db_connect.php';

// Fetch driver's name for a personalized welcome message (Optional but nice)
$driver_name = "Driver"; // Default
$stmt = $conn->prepare("SELECT name FROM drivers WHERE driver_id = ?");
$stmt->bind_param("i", $_SESSION['driver_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $driver = $result->fetch_assoc();
    $driver_name = $driver['name'];
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles to mimic the application's look and feel */
        body { background-color: #f0f8ff; }
        .dashboard-container { max-width: 600px; margin-top: 30px; }
        .card { border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); margin-bottom: 20px; }
        .action-icon { font-size: 2rem; }
        .action-btn { transition: transform 0.2s; }
        .action-btn:hover { transform: scale(1.02); }
        .gps-error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container dashboard-container">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4">Driver Dashboard</h2>
            <p class="text-end">Welcome back! <br> **<?php echo htmlspecialchars($driver_name); ?>**</p>
            <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
        </header>
        
        <div class="card p-3 action-btn">
            <a href="manage_trip.php" class="text-decoration-none text-dark d-flex align-items-center">
                <i class="fa-solid fa-circle-plus action-icon text-success me-3"></i>
                <div>
                    <h5 class="mb-0">Add New Trip</h5>
                    <small class="text-muted">Create a new bus route</small>
                </div>
            </a>
        </div>

        <div class="card p-3 action-btn">
            <a href="my_trips.php" class="text-decoration-none text-dark d-flex align-items-center">
                <i class="fa-solid fa-list-ul action-icon text-primary me-3"></i>
                <div>
                    <h5 class="mb-0">My Trips</h5>
                    <small class="text-muted">View and manage trips</small>
                </div>
            </a>
        </div>

        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-muted"><i class="fa-solid fa-bus"></i> Trip Status</h5>
                <span class="badge bg-secondary">Inactive</span>
            </div>
            <button class="btn btn-success w-100 py-2">
                <i class="fa-solid fa-play"></i> Start Trip (Future Implementation)
            </button>
        </div>

        <div class="gps-error">
            <p class="mb-1 fw-bold text-danger">GPS Status: GPS Error</p>
            <p class="mb-0">Timeout expired</p>
            <small>Please enable location services in your browser</small>
        </div>
    </div>
</body>
</html>