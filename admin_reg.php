<?php
include('config.php');

$alertMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];

    // Check if passwords match
    if ($password != $repeat_password) {
        $alertMessage = "Passwords do not match.";
    } else {
        // Insert data into the database
        $insert_query = "INSERT INTO admin (name, contact, username, password) VALUES ('$name', '$contact', '$username', '$password')";
        if ($mysqli->query($insert_query) === TRUE) {
            $alertMessage = "Admin registered successfully!";
            echo "<script>
                    setTimeout(function () {
                        window.location.href = 'login.php';
                    }, 2000);
                </script>";
        } else {
            $alertMessage = "Error: " . $mysqli->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_reg.css">
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <center><h1>Admin Registration</h1></center>
            <!-- Display alert message as JavaScript alert using SweetAlert -->
            <?php if (!empty($alertMessage)) : ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: '<?php echo $alertMessage; ?>',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                </script>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="name"></label>
                <input type="text" id="name" name="name" placeholder="Name" required><br>

                <label for="Contact"></label>
                <input type="text" id="contact" name="contact" placeholder="Contact Number" required><br>

                <label for="username"></label>
                <input type="text" id="username" name="username" placeholder="Username" required><br>

                <label for="password"></label>
                <input type="password" id="password" name="password" placeholder="Password" required><br>

                <label for="repeat_password"></label>
                <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat Password" required><br><br>

                <div class="button-container">
                    <!-- Cancel button -->
                     <button style="width: 100px; background-color:green;" type="submit">Register</button>
                    <a href="login.php"><button style="width: 100px; background-color:red;" type="button">Cancel</button></a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
