<?php
function checkAdminSession() {
    if (!isset($_GET['aid']) || empty($_GET['aid'])) {
        header("Location: login.php");
        exit;
    }
}

// Call the function at the top of your files
checkAdminSession();
?>

<?php
// Include database connection
include 'config.php';

// Check if 'aid' parameter is present in the URL
if(isset($_GET['aid'])) {
    $aid = $_GET['aid'];
    // Query to fetch the username corresponding to the aid
    $query = "SELECT username FROM admin WHERE aid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_username = $admin['username'];
        // Display the admin username in the sidebar
        $admin_username_display = $admin_username;
    } else {
        // Display a default message if admin username is not found
        $admin_username_display = "Username";
    }
    // Close statement
    $stmt->close();
}

// Fetch data from the inventory table
$query = "SELECT * FROM inventory";
$result = $mysqli->query($query);

?>
<?php
// Include database connection
include 'config.php';

// Function to handle form submission
function handleReservation($mysqli, $selectedBooks, $qrInfo) {
    // Get the current date in mm-dd-yyyy format
    $currentDate = date("m-d-Y");

    // Convert current date to DateTime object for calculations
    $dateRel = DateTime::createFromFormat('m-d-Y', $currentDate);
    
    // Calculate the due date by adding 7 days
    $dueDate = clone $dateRel;
    $dueDate->modify('+7 days');
    
    // Format the dates back to mm-dd-yyyy
    $dateRelFormatted = $dateRel->format('m-d-Y');
    $dueDateFormatted = $dueDate->format('m-d-Y');

    // Start a transaction
    $mysqli->begin_transaction();

    try {
        // Loop through selected books
        foreach ($selectedBooks as $bid => $title) {
            // Insert selected bid into rsv table
            $insertBidQuery = "INSERT INTO rsv (bid, info, status, date_rel, due_date) VALUES (?, ?, 'Released', ?, ?)";
            $stmtBid = $mysqli->prepare($insertBidQuery);
            // Bind parameters: 'issss' for integers and strings
            $stmtBid->bind_param("isss", $bid, $qrInfo, $dateRelFormatted, $dueDateFormatted); // Corrected number of bind parameters
            $stmtBid->execute();
            $stmtBid->close();

            // Update status to "Reserved" in inventory table
            $updateStatusQuery = "UPDATE inventory SET status = 'Reserved' WHERE bid = ?";
            $stmtStatus = $mysqli->prepare($updateStatusQuery);
            $stmtStatus->bind_param("i", $bid);
            $stmtStatus->execute();
            $stmtStatus->close();
        }

        // Commit the transaction
        $mysqli->commit();
    } catch (Exception $e) {
        // Rollback the transaction on exception
        $mysqli->rollback();
        throw $e;
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserve_submit'])) {
    // Get selected books and QR info from POST
    $selectedBooks = json_decode($_POST['selected_books'], true);
    $qrInfo = $_POST['qr-info'];

    // Call function to handle reservation
    try {
        handleReservation($mysqli, $selectedBooks, $qrInfo);
        // If reservation is successful, set a session variable
        $_SESSION['reservation_success'] = true;
    } catch (Exception $e) {
        // If reservation fails, set a session variable
        $_SESSION['reservation_error'] = true;
    }
    
    // Redirect back to the reservation page with aid parameter if it exists
    $redirectURL = "admin_wres.php";
    if(isset($_GET['aid'])) {
        $redirectURL .= '?aid=' . $_GET['aid'];
    }
    header("Location: $redirectURL");
    exit; // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-in Reservation</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/admin_srch.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script> <!-- Instascan library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg">
<div class="sidebar">
    <span style="margin-left: 25%;"><img src="css/pics/logop.png" alt="Logo" class="logo"></span>
        <?php
        // Check if $admin_username_display is set
        if(isset($admin_username_display)) {
            // Add spaces before the admin username to align it
            echo '<div class="hell">Admin: ' . $admin_username_display . '</span></div>';
        } else {
            // Display a default message if admin username is not found
            echo '<div>Admin: <br>Username</div>';
        }
        ?>
        <a href="admin_dash.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Dashboard</a>
        <a href="admin_pf.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Profile</a>
        <a href="admin_srch.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Accounts</a>
        <a href="admin_attd.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Library Logs</a>
        <a href="admin_stat.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">User Statistics</a>
        <a href="admin_wres.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item active">Walk-in-Borrow</a>
        <a href="admin_preq.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Pending Requests</a>
        <a href="admin_brel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Borrowed Books</a>
        <a href="admin_ob.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Overdue Books</a>
        <div class="sidebar-item dropdown">
            <a href="#" class="dropdown-link" onmouseover="toggleDropdown(event)">Inventory</a>
            <div class="dropdown-content">
                <a href="bk_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Books</a>
                <a href="admin_asts_inv.php<?php if (isset($aid)) echo '?aid=' . $aid; ?>" class="sidebar-item">Assets</a>
            </div>
        </div>
        <a href="login.php" class="sidebar-item logout-btn">Logout</a>
    </div>

    <div class="content">
    <nav class="secondary-navbar">
            <a href="admin_wres.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item active">Reserve Books</a>
            <a href="admin_wrel.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Released Books</a>
            <a href="admin_wlogs.php<?php if(isset($aid)) echo '?aid=' . $aid; ?>" class="secondary-navbar-item">Borrow Logs</a>
        </nav>
    </div>

    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
    <div class="search-bar">
    <input type="text" id="titleInput" placeholder="Search by Title...">
    <input type="text" id="authorInput" placeholder="Search by Author...">
    <button type="button" id="searchButton"><i class="fas fa-search"></i> Search</button>
    <button type="button" id="cancelSearchButton" style="background-color:crimson; color:white;">Clear</button>
    </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>More Info:</th>
                    <th>Status:</th>
                    <th>Reserve:</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td hidden>" . $row["bid"] . "</td>";
                        echo "<td>" . $counter++ . "</td>";
                        echo "<td>" . $row["title"] . "</td>";
                        echo "<td>" . $row["author"] . "</td>";
                        echo "<td style='text-align:center;'><button class='more-info-btn' data-bid='" . $row["bid"] . "'>More Details</button></td>";
                        echo "<td style='text-align:center;' class='status-cell'>" . $row["status"] . "</td>"; // Add class for easier selection in JavaScript
                        echo "<td style='text-align:center;'><button class='reserve-btn'>Reserve</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No books found in inventory.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="cart-header">Cart: <button class="scan-button">Scan User Info</button></div><br>
        <label for="qr-info">User Info:</label>
        <input type="text" id="qr-info" name="qr-info" placeholder="QR Info" required readonly style="background-color:#ddd;"><br>
        <p style="font-size: 15px;"><i> Default submission is due 7 Days</i></p>
        <!-- Table to display selected books -->
        <table id="selected-books-table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                </tr>
            </thead>
            <tbody>
                <!-- Selected books will be dynamically added here -->
            </tbody>
        </table>

        <div class="cart-content">
        <!-- Form for reservation -->
        <form id="reserveForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php if(isset($aid)) echo '?aid=' . $aid; ?>" method="post">
            <input type="hidden" id="selectedBooksInput" name="selected_books">
            <input type="hidden" id="qr-info-input" name="qr-info">
            <input type="hidden" id="admin-id" name="aid" value="<?php echo isset($aid) ? $aid : ''; ?>">
            <button type="submit" name="reserve_submit">Submit</button>
        </form>
            <button type="button" id="cancelButton">Cancel</button>
        </div>
    </div>

<div id="details-popup" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; z-index: 1000;">
    <h3>Book Details</h3>
    <div id="details-content"></div>
    <button id="close-details">Close</button>
</div>

    <div id="details-popup" style="display: none;"></div>

    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <script>
    // Function to update date and time
    function updateTime() {
        var currentDate = new Date();
        var month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 to month since it's zero-based index
        var day = currentDate.getDate().toString().padStart(2, '0');
        var year = currentDate.getFullYear().toString();
        var dateString = month + '-' + day + '-' + year;
        var timeString = currentDate.toLocaleTimeString();
        document.getElementById("current-date").textContent = dateString;
        document.getElementById("current-time").textContent = timeString;
    }
    updateTime(); // Call the function to update time immediately
    setInterval(updateTime, 1000); // Update time every second
    </script>
<script>
// Add event listener for "More Details" buttons
document.querySelectorAll('.more-info-btn').forEach(button => {
    button.addEventListener('click', function() {
        const bid = this.getAttribute('data-bid');
        
        // Fetch book details using AJAX
        fetch(`get_book_details.php?bid=${bid}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    // Show error alert if book details could not be retrieved
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.error,
                    });
                } else {
                    // Show book details in a SweetAlert2 popup
                    Swal.fire({
                        title: data.title,
                        html: `
                        <span style="text-align:left;">
                            <p><strong>Author:</strong> ${data.author}</p>
                            <p><strong>Year:</strong> ${data.year}</p>
                            <p><strong>Genre:</strong> ${data.genre}</p>
                            <p><strong>Dewey Number:</strong> ${data.dew_num}</p>
                            <p><strong>ISBN:</strong> ${data.ISBN}</p>
                            <p><strong>Shelf Number:</strong> ${data.shlf_num}</p>
                            <p><strong>Condition:</strong> ${data.cndtn}</p>
                            <p><strong>Additional Info:</strong> ${data.add_info}</p>
                            <p><strong>Status:</strong> ${data.status}</p>
                        </span>
                        `,
                        confirmButtonText: 'Close'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to fetch book details.',
                });
            });
    });
});
</script>
<script>
    // Initialize selectedBooks object
    const selectedBooks = {};

    document.addEventListener('DOMContentLoaded', function() {
        const reserveButtons = document.querySelectorAll('.reserve-btn');
        let counter = 1; // Counter variable

        // Event listener for reserve buttons
        reserveButtons.forEach(button => {
            button.addEventListener('click', function() {
                const row = this.parentElement.parentElement;
                const bid = row.querySelector('td:first-child').innerText;
                const title = row.querySelector('td:nth-child(3)').innerText;
                const status = row.querySelector('.status-cell').textContent.trim();
                
                // Debugging: check the status value
                console.log("Status: " + status);
                
                // Check if the status is "Available"
                if (status !== 'Available') {
                    // Show alert
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'This book is not available for reservation.',
                    });
                    return; // Exit the function if status is not "Available"
                }

                // Check if the book is already in the cart
                const isAlreadyInCart = Array.from(document.querySelectorAll('#selected-books-table tbody td:nth-child(2)'))
                    .map(td => td.innerText)
                    .includes(bid);
                if (isAlreadyInCart) {
                    // Show alert
                    Swal.fire({
                        icon: 'info',
                        title: 'Attention',
                        text: 'This book is already in the cart.',
                    });
                    return; // Exit the function if book is already in the cart
                }

                // Add selected book to the table
                const tableBody = document.querySelector('#selected-books-table tbody');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>${counter++}</td>
                    <td hidden>${bid}</td>
                    <td>${title}</td>
                `;
                tableBody.appendChild(newRow);
                // Add selected book to the selectedBooks object
                selectedBooks[bid] = title;
            });
        });

        // Cancel button event listener
        document.getElementById('cancelButton').addEventListener('click', function() {
            // Reset counter
            counter = 1;
            // Clear selected books table
            document.getElementById('selected-books-table').querySelector('tbody').innerHTML = '';
            // Clear selected books object
            for (const key in selectedBooks) {
                delete selectedBooks[key];
            }
        });

        // Submit button event listener
        document.getElementById('reserveForm').addEventListener('submit', function(event) {
            // Serialize selectedBooks object and set it as value for selected_books input field
            document.getElementById('selectedBooksInput').value = JSON.stringify(selectedBooks);

            // Check if the cart is empty
            if (Object.keys(selectedBooks).length === 0) {
                // Show error alert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Your cart is empty. Please select at least one book to reserve.',
                });
                // Prevent form submission
                event.preventDefault();
            } else {
                // Get QR info
                const qrInfo = document.getElementById('qr-info').value;
                // Set QR info in the hidden input field
                document.getElementById('qr-info-input').value = qrInfo;
            }
        });

        // QR scanning function
        const scanButton = document.querySelector('.scan-button');
        scanButton.addEventListener('click', function() {
            // Display a sweetalert popup with the scanner
            Swal.fire({
                title: 'Scan QR Code',
                html: '<video id="qr-video" style="width: 100%; height: 100%;"></video>', // Video container
                showCancelButton: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    let scanner = new Instascan.Scanner({ video: document.getElementById('qr-video') });

                    scanner.addListener('scan', function (content) {
                        // Display scanned QR data in the input field
                        document.getElementById("qr-info").value = content;
                        // Close the scanner popup
                        Swal.close();
                        // Display success alert
                        showSuccessAlert();
                    });

                    Instascan.Camera.getCameras().then(function (cameras) {
                        if (cameras.length > 0) {
                            // Start the scanner with the first available camera
                            scanner.start(cameras[0]);
                        } else {
                            console.error('No cameras found.');
                        }
                    }).catch(function (e) {
                        console.error(e);
                    });

                    // Store the scanner instance in a variable accessible from outside
                    window.qrScanner = scanner;
                },
                willClose: () => {
                    // Stop the scanner when closing the popup
                    if (window.qrScanner) {
                        window.qrScanner.stop();
                    }
                }
            });
        });

        // Function to display success alert
        function showSuccessAlert() {
            Swal.fire({
                icon: 'success',
                title: 'QR Code Scanned',
                text: 'QR code successfully scanned!',
                confirmButtonText: 'Close',
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reserveForm = document.getElementById('reserveForm');

        // Event listener for form submission
        reserveForm.addEventListener('submit', function(event) {
            // Get QR info
            const qrInfo = document.getElementById('qr-info').value;
            
            // Check if QR info is empty
            if (!qrInfo) {
                // Show error alert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'QR info is empty. Please scan a QR code.',
                });
                // Prevent form submission
                event.preventDefault();
            }else{
            // Show success alert
                Swal.fire({
                icon: 'success',
                title: 'Reservation completed successfully!',
                confirmButtonText: 'OK'
            });
        }
        });
    });

</script>
<script>
// Update the handleSearch function to include author search
function handleSearch() {
    const titleInput = document.getElementById('titleInput').value.toLowerCase();
    const authorInput = document.getElementById('authorInput').value.toLowerCase(); // Get author search input
    const bookRows = document.querySelectorAll('table tbody tr');

    // Loop through each book row
    bookRows.forEach(row => {
        const titleText = row.querySelector('td:nth-child(3)').innerText.toLowerCase();
        const authorText = row.querySelector('td:nth-child(4)').innerText.toLowerCase(); // Get author text
        // Check if either title or author matches the search input
        if (titleText.includes(titleInput) && authorText.includes(authorInput)) {
            row.style.display = 'table-row'; // Display the row
        } else {
            row.style.display = 'none'; // Hide the row if it doesn't match the search input
        }
    });
}

// Add event listeners to the search button and input fields for both title and author
document.getElementById('searchButton').addEventListener('click', handleSearch);
document.getElementById('titleInput').addEventListener('keyup', function(event) {
    if (event.keyCode === 13) { // Check if Enter key is pressed
        handleSearch();
    }
});
document.getElementById('authorInput').addEventListener('keyup', function(event) {
    if (event.keyCode === 13) { // Check if Enter key is pressed
        handleSearch();
    }
});
</script>
<script>
        // Event listener for cancel search button
        document.getElementById('cancelSearchButton').addEventListener('click', function() {
            // Clear search inputs
            document.getElementById('titleInput').value = '';
            document.getElementById('authorInput').value = '';
            // Reset search results
            const bookRows = document.querySelectorAll('table tbody tr');
            bookRows.forEach(row => {
                row.style.display = 'table-row'; // Display all rows
            });
        });
    </script>
<script>
            // Dropdown script
            function toggleDropdown(event) {
            event.preventDefault();
            var dropdownContent = event.target.nextElementSibling;
            dropdownContent.classList.toggle('show');
        }

        window.onclick = function(event) {
            if (!event.target.matches('.dropdown-link')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
</script>
</body>
</html>

