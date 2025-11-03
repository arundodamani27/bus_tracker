<?php
session_start();
// Check if the driver is already logged in
if (isset($_SESSION['driver_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error_message = ''; // Variable to hold any login error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Include Database Connection
    include 'db_connect.php'; 

    // 2. Collect and Sanitize Input
    $phone = $_POST['phone'];
    $otp = $_POST['otp'];
    
    // We are using a hardcoded OTP as the requirement for this mini-project
    $DEMO_OTP = '123456'; 

    // 3. OTP Validation
    if ($otp !== $DEMO_OTP) {
        $error_message = "Invalid OTP. Please use the Demo OTP: 123456.";
    } else {
        // 4. Check Database for Phone Number
        // Use prepared statements for security
        $stmt = $conn->prepare("SELECT driver_id FROM drivers WHERE phone_number = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // 5. Successful Login
            $driver = $result->fetch_assoc();
            
            // Set session variable and redirect
            $_SESSION['driver_id'] = $driver['driver_id'];
            
            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            // Driver not found
            $error_message = "Phone number not registered. Please contact administration.";
        }
        $stmt->close();
    }
    $conn->close();
}
// End of PHP block
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .login-card { max-width: 400px; margin-top: 50px; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); background-color: white; }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="login-card">
            <h3 class="text-center mb-4">Driver Login</h3>
            <p class="text-center text-muted mb-4">Access your driver dashboard</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <p class="fw-bold"><i class="fas fa-sign-in-alt"></i> Login with OTP</p>
                <p class="text-muted small">Enter your phone number to receive OTP</p>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter 10-digit phone number" required maxlength="10">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="otp" class="form-label">OTP</label>
                    <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter 6-digit OTP" required maxlength="6">
                    <small class="text-muted">Demo OTP: 123456</small>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2" style="background: linear-gradient(to right, #007bff, #28a745); border: none;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script> 
</body>
</html>