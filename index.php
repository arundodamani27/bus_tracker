<?php
include 'db_connect.php';

$search_results = [];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['from_stop'])) {

    $from_stop = trim($_GET['from_stop']);
    $to_stop = trim($_GET['to_stop']);
    $search_time = $_GET['time'] ?? date('H:i');

    // ✅ Query: find trips that have both from_stop & to_stop
    $sql = "SELECT 
            t.trip_id, 
            t.bus_name, 
            t.route_number, 
            t.start_time, 
            t.end_time, 
            t.status,
            s1.stop_name AS from_stop, 
            s1.stop_time AS from_stop_time,
            s2.stop_name AS to_stop, 
            s2.stop_time AS to_stop_time
        FROM trips t
        JOIN stops s1 ON t.trip_id = s1.trip_id
        JOIN stops s2 ON t.trip_id = s2.trip_id
        WHERE 
            s1.stop_name = ?
            AND s2.stop_name = ?
            AND s1.stop_time >= ?      -- ✅ Show only buses that haven't passed the stop yet
            AND s1.stop_time < s2.stop_time
        ORDER BY s1.stop_time ASC";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $from_stop, $to_stop, $search_time);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }

        if (empty($search_results)) {
            $message = "No buses found for the given stops and time.";
        }
    } else {
        $message = "Database error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Live Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #f0f8ff; }
        .tracker-container { max-width: 650px; margin: 50px auto; }
        .search-card, .driver-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .bus-header { color: #28a745; font-weight: bold; }
        .result-card { border-left: 5px solid #28aa45; margin-bottom: 15px; }
        .badge-time { background: #007bff; }
    </style>
</head>

<body>
<div class="container tracker-container">
    <h1 class="text-center bus-header mb-4"><i class="fas fa-bus"></i> Bus Live Tracker</h1>
    <p class="text-center text-muted mb-5">Track your bus by stop and time</p>

    <!-- 🔍 Search Form -->
    <div class="search-card">
        <form method="GET" action="">
            <div class="mb-3">
                <label for="from_stop" class="form-label small text-muted">From Stop</label>
                <input type="text" class="form-control" id="from_stop" name="from_stop" placeholder="Enter boarding stop" required value="<?php echo htmlspecialchars($_GET['from_stop'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="to_stop" class="form-label small text-muted">To Stop</label>
                <input type="text" class="form-control" id="to_stop" name="to_stop" placeholder="Enter destination stop" required value="<?php echo htmlspecialchars($_GET['to_stop'] ?? ''); ?>">
            </div>

            <div class="mb-4">
                <label for="time" class="form-label small text-muted">Current Time</label>
                <input type="time" class="form-control" id="time" name="time" value="<?php echo htmlspecialchars($_GET['time'] ?? date('H:i')); ?>">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" style="background: linear-gradient(to right, #28a745, #007bff); border: none;">
                <i class="fas fa-search"></i> Search Bus
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="add_stop_passenger.php" class="btn btn-success w-100 py-2">
                <i class="fas fa-plus-circle"></i> Add Stop
            </a>
        </div>
    </div>

    <!-- 🚌 Results -->
    <div class="mb-4">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php foreach ($search_results as $trip): ?>
            <div class="card result-card p-3">
                <h5 class="mb-1"><?php echo htmlspecialchars($trip['bus_name']); ?></h5>
                <p class="text-muted small">Route No: <?php echo htmlspecialchars($trip['route_number']); ?></p>

                <p><i class="fas fa-route text-success me-2"></i>
                    <?php echo htmlspecialchars($trip['from_stop']); ?> → <?php echo htmlspecialchars($trip['to_stop']); ?>
                </p>

                <p>
                    <i class="fas fa-clock text-warning me-2"></i>
                    Bus Timing: <?php echo date('h:i A', strtotime($trip['start_time'])); ?> - <?php echo date('h:i A', strtotime($trip['end_time'])); ?>
                </p>

                <!-- 🕒 Passenger's From Stop Arrival Time -->
                <p>
                    <span class="badge badge-time">
                        🕒 Arrives at <strong><?php echo htmlspecialchars($trip['from_stop']); ?></strong>: 
                        <?php echo date('h:i A', strtotime($trip['from_stop_time'])); ?>
                    </span>
                </p>

                <p>
                    <span class="badge bg-<?php echo ($trip['status'] == 'Running Now') ? 'success' : 'secondary'; ?>">
                        <?php echo htmlspecialchars($trip['status']); ?>
                    </span>
                </p>
            </div>
        <?php endforeach; ?>

        <?php if (isset($_GET['from_stop']) && empty($search_results)): ?>
            <div class="alert alert-warning text-center">No buses found for your search.</div>
        <?php endif; ?>
    </div>

    <!-- 🚍 Conductor Login -->
    <div class="driver-card bg-primary text-center">
        <h5 class="text-white mb-3">Are you a driver or conductor?</h5>
        <a href="login.php" class="btn btn-light driver-login-btn">Driver Login</a>
    </div>
</div>
</body>
</html>
