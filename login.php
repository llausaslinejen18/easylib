<?php
// Include database connection
include 'config.php';

// Initialize alert message
$alertMessage = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Initialize query variables for admin and librarian
    $adminQuery = "SELECT * FROM admin WHERE username = ?";
    $librQuery = "SELECT * FROM libr WHERE username = ?";
    
    // Prepare SQL statement for admin table
    $stmt = $mysqli->prepare($adminQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $adminResult = $stmt->get_result();
    
    // Check if user exists in admin table and password matches
    if ($adminResult && $adminResult->num_rows > 0) {
        $user = $adminResult->fetch_assoc();
        if ($user['password'] === $password) {
            $alertMessage = "Login successful. Welcome, " . $user['username'] . "!";
            $redirectUrl = 'admin_dash.php?aid=' . $user['aid'];
            echo "<script>
                    setTimeout(function() {
                        window.location.href = '$redirectUrl';
                    }, 2000);
                </script>";
        } else {
            $alertMessage = "Incorrect password.";
        }
    } else {
        // If not found in admin table, search in librarian table
        $stmt = $mysqli->prepare($librQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $librResult = $stmt->get_result();

        // Check if user exists in librarian table and password matches
        if ($librResult && $librResult->num_rows > 0) {
            $user = $librResult->fetch_assoc();
            if ($user['password'] === $password) {
                $alertMessage = "Login successful. Welcome, " . $user['username'] . "!";
                $redirectUrl = 'libr/admin_dash.php?aid=' . $user['aid'];
                echo "<script>
                        setTimeout(function() {
                            window.location.href = '$redirectUrl';
                        }, 2000);
                    </script>";
            } else {
                $alertMessage = "Incorrect password.";
            }
        } else {
            // If not found in both admin and librarian tables, search in users table
            $userQuery = "SELECT * FROM users WHERE username = ?";
            $stmt = $mysqli->prepare($userQuery);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $userResult = $stmt->get_result();

            // Check if user exists and password matches
            if ($userResult && $userResult->num_rows > 0) {
                $user = $userResult->fetch_assoc();
                if ($user['password'] === $password) {
                    // Check user status
                    if ($user['status'] === 'Pending') {
                        $alertMessage = "Account is still pending for activation.";
                    } elseif ($user['status'] === 'Active') {
                        $alertMessage = "Login successful. Welcome, " . $user['username'] . "!";
                        // Redirect to user_dash.php after displaying alert
                        echo "<script>
                                setTimeout(function() {
                                    window.location.href = 'user_dash.php?uid=" . $user['uid'] . "';
                                }, 2000);
                            </script>";
                    } elseif ($user['status'] === 'Disabled') {
                        $alertMessage = "Account is disabled.";
                    }
                } else {
                    $alertMessage = "Incorrect password.";
                }
            } else {
                $alertMessage = "User not found.";
            }
        }
    }

    // Close statement
    $stmt->close();
}

// Close connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <!-- Include SweetAlert library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg">
    <nav class="navbar">
        <div class="navbar-container">
        <a href="index.php"><img src="css/pics/download.png" alt="Logo" class="logo" style="width:50px; height:50px;"></a>
            <ul class="nav-links">
                <li><a href="#">About Us</a></li>
            </ul>
        </div>
    </nav>
    <img src="css/pics/logop.png" alt="EasyLib Logo" class="elogo">
    <div class="container">
        <h1 style="text-align:center;">User Login</h1>
        
        <!-- Display alert message as SweetAlert -->
        <?php if (!empty($alertMessage)) : ?>
            <script>
                Swal.fire({
                    icon: 'info',
                    title: '<?php echo $alertMessage; ?>',
                    showConfirmButton: false,
                    timer: 2000
                });
            </script>
        <?php endif; ?>
        
        <form action="" method="POST" class="login-form">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <center><button class="login-button" type="submit">Login</button></center>
        </form>
        
        <p>Not yet registered? <a href="register.php">Register here</a></p>
    </div>
</body>

<script>
    // Clear the history and prevent back navigation
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };
</script>
</html>
