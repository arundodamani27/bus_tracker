<?php
session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

// Initialize default trip data
$trip_data = [
    'trip_id' => null,
    'route_number' => '',
    'bus_name' => '',
    'from_stop' => '',
    'to_stop' => '',
    'start_time' => '',
    'end_time' => '',
    'status' => 'Inactive'
];

$page_title = "Add New Bus Trip";
$trip_id_to_edit = null;

// Load existing trip (if editing)
if (isset($_GET['trip_id'])) {
    $trip_id_to_edit = $_GET['trip_id'];

    $stmt = $conn->prepare("SELECT * FROM trips WHERE trip_id = ? AND driver_id = ?");
    $stmt->bind_param("ii", $trip_id_to_edit, $_SESSION['driver_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $trip = $result->fetch_assoc();
        $trip_data = array_merge($trip_data, [
            'trip_id' => $trip['trip_id'],
            'route_number' => $trip['route_number'],
            'bus_name' => $trip['bus_name'],
            'from_stop' => $trip['start_location'],
            'to_stop' => $trip['end_location'],
            'start_time' => $trip['start_time'],
            'end_time' => $trip['end_time'],
            'status' => $trip['status']
        ]);
        $page_title = "Edit Bus Trip";
    } else {
        header("Location: my_trips.php");
        exit;
    }
    $stmt->close();
}

if (isset($_GET['delete_stop_id'])) {
    $delete_stop_id = intval($_GET['delete_stop_id']);
    $trip_id = intval($_GET['trip_id']);

    // Delete stop from database
    $delete_query = "DELETE FROM stops WHERE stop_id = $delete_stop_id";
    mysqli_query($conn, $delete_query);

    // Redirect back to the same trip to refresh stop list
    header("Location: manage_trip.php?trip_id=$trip_id");
    exit();
}

// Handle save trip
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_trip'])) {
    $driver_id = $_SESSION['driver_id'];
    $trip_id_post = $_POST['trip_id'] ?? null;
    $route_number = $_POST['route_number'];
    $bus_name = $_POST['bus_name'];
    $from_stop = $_POST['from_stop'];
    $to_stop = $_POST['to_stop'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $status = $_POST['status'];

    if ($trip_id_post) {
        $sql = "UPDATE trips SET route_number=?, bus_name=?, start_location=?, end_location=?, start_time=?, end_time=?, status=? 
                WHERE trip_id=? AND driver_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssii", $route_number, $bus_name, $from_stop, $to_stop, $start_time, $end_time, $status, $trip_id_post, $driver_id);
        $stmt->execute();
        $trip_id = $trip_id_post;
    } else {
        $sql = "INSERT INTO trips (driver_id, route_number, bus_name, start_location, end_location, start_time, end_time, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssss", $driver_id, $route_number, $bus_name, $from_stop, $to_stop, $start_time, $end_time, $status);
        $stmt->execute();
        $trip_id = $conn->insert_id;
    }
    $stmt->close();

    // Insert onqly new driver stops (avoid duplicates)
    if (isset($_POST['stop_name']) && is_array($_POST['stop_name'])) {
        foreach ($_POST['stop_name'] as $index => $name) {
            $time = $_POST['stop_time'][$index] ?? null;
            if (!empty($name)) {
                $check = $conn->prepare("SELECT stop_id FROM stops WHERE trip_id = ? AND stop_name = ? AND stop_time = ?");
                $check->bind_param("iss", $trip_id, $name, $time);
                $check->execute();
                $check->store_result();
                if ($check->num_rows == 0) {
                    $insert_stop = $conn->prepare("INSERT INTO stops (trip_id, stop_name, stop_time, added_by) VALUES (?, ?, ?, 'driver')");
                    $insert_stop->bind_param("iss", $trip_id, $name, $time);
                    $insert_stop->execute();
                    $insert_stop->close();
                }
                $check->close();
            }
        }
    }

    header("Location: my_trips.php?status=" . ($trip_id_post ? "updated" : "added"));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $page_title; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<style>
    body { background-color: #f0f8ff; }
    .form-container { max-width: 600px; margin: 30px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
</style>
</head>
<body>
<div class="container form-container">
<a href="dashboard.php" class="text-decoration-none mb-3 d-inline-block"><i class="fas fa-arrow-left"></i> Back</a>
<h2 class="h4 mb-4"><?php echo $page_title; ?></h2>

<form method="POST" action="manage_trip.php">
    <?php if ($trip_data['trip_id']): ?>
        <input type="hidden" name="trip_id" value="<?php echo $trip_data['trip_id']; ?>">
    <?php endif; ?>

    <!-- Trip Info Card (same as before) -->
    <div class="card p-4 mb-4">
        <h5 class="mb-3">Bus Information</h5>
        <div class="mb-3">
            <label class="form-label">Route Number *</label>
            <input type="text" class="form-control" name="route_number" value="<?php echo htmlspecialchars($trip_data['route_number']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Bus Name *</label>
            <input type="text" class="form-control" name="bus_name" value="<?php echo htmlspecialchars($trip_data['bus_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">From Stop *</label>
            <input type="text" class="form-control" name="from_stop" value="<?php echo htmlspecialchars($trip_data['from_stop']); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">To Stop *</label>
            <input type="text" class="form-control" name="to_stop" value="<?php echo htmlspecialchars($trip_data['to_stop']); ?>" required>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Start Time *</label>
                <input type="time" class="form-control" name="start_time" value="<?php echo htmlspecialchars($trip_data['start_time']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">End Time *</label>
                <input type="time" class="form-control" name="end_time" value="<?php echo htmlspecialchars($trip_data['end_time']); ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Status *</label>
            <select class="form-select" name="status" required>
                <option value="Inactive" <?php echo ($trip_data['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="Scheduled" <?php echo ($trip_data['status'] == 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                <option value="Running Now" <?php echo ($trip_data['status'] == 'Running Now') ? 'selected' : ''; ?>>Running Now</option>
            </select>
        </div>
    </div>

    <!-- Stops Section -->
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Route Stops</h5>
            <button type="button" class="btn btn-success btn-sm" id="add-stop-btn"><i class="fas fa-plus"></i> Add Stop</button>
        </div>

        <div id="route-stops-container">
            <?php
            if (!empty($trip_data['trip_id'])) {
                $trip_id = $trip_data['trip_id'];
                $stmt = $conn->prepare("SELECT stop_id, stop_name, stop_time, added_by FROM stops WHERE trip_id = ? ORDER BY stop_time ASC");
                $stmt->bind_param("i", $trip_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $readonly = ($row['added_by'] === 'passenger') ? 'readonly' : '';
                    echo '
                    <div class="row mb-3 route-stop-item">
                        <div class="col-5"><input type="text" class="form-control" name="stop_name[]" value="' . htmlspecialchars($row['stop_name']) . '" ' . $readonly . '></div>
                        <div class="col-5"><input type="time" class="form-control" name="stop_time[]" value="' . htmlspecialchars($row['stop_time']) . '" ' . $readonly . '></div>
                        <div class="col-2 d-flex align-items-center justify-content-end">
                            <a href="manage_trip.php?trip_id=' . $trip_id . '&delete_stop_id=' . $row['stop_id'] . '" 
   class="btn btn-danger btn-sm"
   onclick="return confirm(\'Are you sure you want to delete this stop?\')">
   <i class="fas fa-trash"></i>
</a>
                        </div>
                    </div>';
                }
                $stmt->close();
            }
            ?>
        </div>
    </div>

    <button type="submit" name="save_trip" class="btn btn-primary w-100 py-2">
        <i class="fas fa-save"></i> Save Bus Trip
    </button>
</form>
</div>

<script>
let stopCount = 1;
$('#add-stop-btn').on('click', function() {
    const newStop = `
        <div class="row mb-3 route-stop-item" data-stop-id="${stopCount}">
            <div class="col-5">
                <input type="text" class="form-control" name="stop_name[]" placeholder="Stop name">
            </div>
            <div class="col-5">
                <input type="time" class="form-control" name="stop_time[]">
            </div>
            <div class="col-2 d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-danger btn-sm remove-stop-btn"><i class="fas fa-trash"></i></button>
            </div>
        </div>`;
    $('#route-stops-container').append(newStop);
    stopCount++;
});

$('#route-stops-container').on('click', '.remove-stop-btn', function() {
    $(this).closest('.route-stop-item').remove();
});
</script>
</body>
</html>
