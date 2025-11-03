<?php
include 'db_connect.php';

$searchResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $start = trim($_POST['start']);
    $end = trim($_POST['end']);
    $route = trim($_POST['route']);

    $sql = "SELECT trip_id, route_number, bus_name, start_location, end_location 
            FROM trips 
            WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($start)) {
        $sql .= " AND start_location LIKE ?";
        $params[] = "%$start%";
        $types .= "s";
    }

    if (!empty($end)) {
        $sql .= " AND end_location LIKE ?";
        $params[] = "%$end%";
        $types .= "s";
    }

    if (!empty($route)) {
        $sql .= " AND route_number LIKE ?";
        $params[] = "%$route%";
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
}

// ✅ Save stop when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_stop'])) {
    $trip_id = $_POST['trip_id'];
    $stop_name = trim($_POST['stop_name']);
    $stop_time = trim($_POST['stop_time']);

    if (!empty($trip_id) && !empty($stop_name) && !empty($stop_time)) {
        $stmt = $conn->prepare("INSERT INTO stops (trip_id, stop_name, stop_time) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $trip_id, $stop_name, $stop_time);
        $stmt->execute();
        echo "<script>alert('Stop added successfully!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Trip (Passenger)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            font-family: 'Poppins', sans-serif;
        }
        .search-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
        }
        .bus-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 15px;
            margin-bottom: 15px;
        }
        .bus-card:hover {
            transform: scale(1.02);
            transition: 0.3s;
        }
        .btn-primary {
            background: linear-gradient(to right, #28a745, #007bff);
            border: none;
        }
        .stop-form {
            display: none;
            margin-top: 10px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="search-card">
        <h3 class="text-center mb-4"><i class="fas fa-bus"></i> Add Stop (Passengers)</h3>

        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Start Destination</label>
                    <input type="text" name="start" class="form-control" placeholder="e.g. Bondel" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Destination</label>
                    <input type="text" name="end" class="form-control" placeholder="e.g. Mangaladevi" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Route Number</label>
                    <input type="text" name="route" class="form-control" placeholder="e.g. 23A" required>
                </div>
            </div>

            <button type="submit" name="search" class="btn btn-primary w-100 py-2">
                <i class="fas fa-search"></i> Search Buses
            </button>
        </form>
    </div>

    <?php if (!empty($searchResults)) : ?>
        <h4 class="text-center mt-5 mb-3 text-white">Available Buses</h4>
        <?php foreach ($searchResults as $bus): ?>
            <div class="bus-card">
                <h5><i class="fas fa-bus"></i> <?php echo htmlspecialchars($bus['bus_name']); ?></h5>
                <p>
                    <strong>Route:</strong> <?php echo htmlspecialchars($bus['route_number']); ?><br>
                    <strong>From:</strong> <?php echo htmlspecialchars($bus['start_location']); ?> → 
                    <strong>To:</strong> <?php echo htmlspecialchars($bus['end_location']); ?>
                </p>

                <button class="btn btn-success" onclick="toggleForm(<?php echo $bus['trip_id']; ?>)">
                    <i class="fas fa-plus-circle"></i> Add Stop
                </button>

                <!-- Hidden Add Stop Form -->
                <div id="form_<?php echo $bus['trip_id']; ?>" class="stop-form">
                    <form method="POST" action="">
                        <input type="hidden" name="trip_id" value="<?php echo $bus['trip_id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <input type="text" name="stop_name" class="form-control" placeholder="Stop Name" required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <input type="time" name="stop_time" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="save_stop" class="btn btn-primary w-100">Save</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        <?php endforeach; ?>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])): ?>
        <div class="alert alert-warning text-center mt-4">
            No buses found for your search.
        </div>
    <?php endif; ?>
</div>

<script>
function toggleForm(id) {
    document.querySelectorAll('.stop-form').forEach(f => f.style.display = 'none');
    const form = document.getElementById('form_' + id);
    if (form) form.style.display = 'block';
}
</script>

</body>
</html>
