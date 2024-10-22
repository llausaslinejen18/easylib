<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

// Check if 'aid' parameter is present in the URL
if(isset($_GET['aid'])) {
    $aid = $_GET['aid'];
} else {
    // Redirect if 'aid' parameter not found
    header("Location: admin_attd.php");
    exit;
}

// Check if 'id' parameter is present in the URL
if(isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch data of the selected entry
    $query = "SELECT * FROM chkin WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $entry = $result->fetch_assoc();
        $info = $entry['info'];
        $idnum = $entry['idnum'];
        $user_type = $entry['user_type'];
        $date = $entry['date'];
        $time_in = $entry['timein'];
        $time_out = $entry['timeout'];
        $purpose = $entry['purpose'];
    } else {
        // Redirect if entry not found
        header("Location: admin_attd.php");
        exit;
    }

    $stmt->close();
} else {
    // Redirect if 'id' parameter not found
    header("Location: liblogs.php");
    exit;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['timeout_update']) && $_POST['timeout_update'] == 'true') {
        // Handle timeout update request
        $current_time = date("h:i:s A");

        // Update timeout in the database
        $query = "UPDATE chkin SET timeout=? WHERE id=?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $current_time, $id);
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->affected_rows >= 0) {
            $response = array("success" => true);
        } else {
            $response = array("success" => false);
        }        

        // Close statement
        $stmt->close();

        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // Handle regular form submission
        $info = $_POST['info'];
        $idnum = $_POST['idnum'];
        $user_type = $_POST['user_type'];
        $date = $_POST['date'];
        $time_in = $_POST['time_in'];
        $purpose = $_POST['purpose'];

        // Update entry in the database except for the timeout field
        $query = "UPDATE chkin SET info=?, idnum = ?, user_type=?, date=?, timein=?, purpose=? WHERE id=?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssssi", $info, $idnum, $user_type, $date, $time_in, $purpose, $id);
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->affected_rows >= 0) {
            $response = array("success" => true);
        } else {
            $response = array("success" => false);
        }

        // Close statement
        $stmt->close();

        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Information</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/edit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body class="bg">
<nav class="navbar">
    <div class="navbar-container">
        <img src="css/pics/logop.png" alt="Logo" class="logo">
    </div>
</nav>

<div class="wrap">
    <div class="form-container">
        <h2 style="text-align:center">Edit Information</h2>
        <form id="editForm" method="POST" action="edit_logs.php?id=<?php echo $id; ?>&aid=<?php echo $aid; ?>">
            <input type="hidden" name="aid" value="<?php echo $aid; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <label for="info">User Info:</label>
            <input type="text" id="info" name="info" value="<?php echo $info; ?>"><br>

            <label for="idnum">ID Number:</label>
            <input type="text" id="idnum" name="idnum" value="<?php echo $idnum; ?>"><br>

            <label for="user_type">User Type:</label>
            <center>
            <select id="user_type" name="user_type" style="width:100%;">
                <option value="">Choose User Type</option>
                <option value="Student" <?php if ($user_type === "Student") echo "selected"; ?>>Student</option>
                <option value="Faculty" <?php if ($user_type === "Faculty") echo "selected"; ?>>Faculty</option>
                <option value="Staff" <?php if ($user_type === "Staff") echo "selected"; ?>>Staff</option>
                <option value="Visitor" <?php if ($user_type === "Visitor") echo "selected"; ?>>Visitor</option>
            </select><br>
            </center>

            <label for="date">Date:</label>
            <input type="text" id="date" name="date" value="<?php echo $date; ?>" readonly><br>

            <label for="time_in">Time In:</label>
            <input type="text" id="time_in" name="time_in" value="<?php echo $time_in; ?>" readonly><br>

            <label for="time_out">Time Out:</label>
            <input type="text" id="time_out" name="time_out" value="<?php echo $time_out; ?>" readonly><br>

            <label for="purpose">Purpose:</label>
            <center>
            <select id="purpose" name="purpose" style="width:100%;">
                <option value="">Choose User Purpose</option>
                <option value="Study" <?php if ($purpose === "Study") echo "selected"; ?>>Study</option>
                <option value="Research" <?php if ($purpose === "Research") echo "selected"; ?>>Research</option>
                <option value="Printing" <?php if ($purpose === "Printing") echo "selected"; ?>>Printing</option>
                <option value="Clearance" <?php if ($purpose === "Clearance") echo "selected"; ?>>Clearance</option>
                <option value="Borrow Book(s)" <?php if($purpose == 'Borrow Book(s)') echo "selected"; ?>>Borrow Book(s)</option>
                <option value="Return Book(s)" <?php if($purpose == 'Return Book(s)') echo "selected"; ?>>Return Book(s)</option>
            </select><br><br>
            </center>

            <div class="button-container">
                <button id="saveBtn" style="width: 100px; background-color: green;" type="submit">Save</button>
                <a href="admin_attd.php?aid=<?php echo $aid; ?>"><button style="width: 100px; background-color: red;" type="button">Cancel</button></a>
            </div>
            <br>
            <center>
            <button id="timeOutBtn" type="button" style="width:100%;">Time Out <i class="fa-solid fa-right-from-bracket"></i></button></center>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("editForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent the default form submission
        
        // Perform an asynchronous (Ajax) form submission
        var formData = new FormData(this);

        fetch(this.action, {
            method: this.method,
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Information updated successfully.',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "admin_attd.php?aid=<?php echo $aid; ?>";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill out required inputs.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong! Please try again.'
            });
        });
    });

    document.getElementById("timeOutBtn").addEventListener("click", function () {
        // Perform an asynchronous (Ajax) request to update timeout
        var formData = new FormData();
        formData.append("timeout_update", "true");

        fetch("edit_logs.php?id=<?php echo $id; ?>&aid=<?php echo $aid; ?>", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Time out recorded successfully.',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "admin_attd.php?aid=<?php echo $aid; ?>";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to record time out. Please try again.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong! Please try again.'
            });
        });
    });
});
</script>
</body>
</html>
