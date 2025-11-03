<?php
session_start();
// Security check
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';

$driver_id = $_SESSION['driver_id'];
$trips = [];
$error_message = '';

// SQL SELECT Query (CRUD - READ)
$stmt = $conn->prepare("SELECT trip_id, route_number, bus_name, start_location, end_location, start_time, end_time, status FROM trips WHERE driver_id = ? ORDER BY start_time ASC");
$stmt->bind_param("i", $driver_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    // Fetch all results into the $trips array
    while ($row = $result->fetch_assoc()) {
        $trips[] = $row;
    }
} else {
    $error_message = "Error fetching trips: " . $stmt->error;
}
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bus Trips</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f0f8ff;
        }

        .list-container {
            max-width: 800px;
            margin: 30px auto;
        }

        .trip-card {
            border-left: 5px solid #007bff;
            border-radius: 8px;
            transition: box-shadow 0.2s;
        }

        .trip-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            font-size: 0.85em;
            padding: .4em .6em;
            border-radius: .3rem;
            font-weight: bold;
        }

        .status-Running {
            background-color: #28a745;
            color: white;
        }

        .status-Scheduled {
            background-color: #ffc107;
            color: #343a40;
        }

        .status-Inactive {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container list-container">
        <a href="dashboard.php" class="text-decoration-none mb-3 d-inline-block"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <h2 class="h4 mb-4">My Bus Trips</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (empty($trips)): ?>
            <div class="alert alert-info text-center">No trips found. Please add a new trip.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($trips as $trip): ?>
                    <div class="col-12 mb-3">
                        <div class="card trip-card p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1 text-primary"><?php echo htmlspecialchars($trip['bus_name']); ?></h5>
                                    <p class="mb-1 text-muted small">Route No: <?php echo htmlspecialchars($trip['route_number']); ?></p>
                                </div>
                                <span class="status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($trip['status'])); ?>">
                                    <?php echo htmlspecialchars($trip['status']); ?>
                                </span>
                            </div>

                            <hr class="my-2">

                            <div class="d-flex justify-content-between small">
                                <div>
                                    <i class="fas fa-location-dot me-1 text-success"></i> Start:
                                    <strong><?php echo htmlspecialchars($trip['start_location']); ?></strong> (<?php echo date('h:i A', strtotime($trip['start_time'])); ?>)
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <div>
                                    <i class="fas fa-location-dot me-1 text-danger"></i> End:
                                    <strong><?php echo htmlspecialchars($trip['end_location']); ?></strong> (<?php echo date('h:i A', strtotime($trip['end_time'])); ?>)
                                </div>
                            </div>

                            <!-- SHOW STOPS -->
                            

                            <div class="mt-3 text-end">
                                <a href="manage_trip.php?trip_id=<?php echo $trip['trip_id']; ?>" class="btn btn-sm btn-outline-secondary me-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" action="delete_trip.php" class="d-inline">
                                    <input type="hidden" name="trip_id" value="<?php echo $trip['trip_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this trip?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>