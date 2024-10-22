<?php
function checkAdminSession() {
    if (!isset($_GET['uid']) || empty($_GET['uid'])) {
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

// Initialize uid variable
$uid = "";

// Check if 'uid' parameter is present in the URL
if(isset($_GET['uid'])) {
    $uid = $_GET['uid'];
}

// Retrieve alert message if present
$alertMessage = isset($_GET['alert_message']) ? $_GET['alert_message'] : '';

// Check if 'uid' parameter is present in the URL
if(isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    // Query to fetch the username and profile image corresponding to the uid
    $query = "SELECT username, profile_image FROM users WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_username = $user['username'];
        $profile_image_path = $user['profile_image']; // Fetch profile image path
        
        // Set the username for display
        $user_username_display = $user_username;
    } else {
        // Display default values if user data is not found
        $user_username_display = "Username";
        $profile_image_path = "uploads/default.jpg"; // Default profile image
    }
    // Close statement
    $stmt->close();
}

// Check if an alert message is present
$alertMessage = "";
if(isset($_GET['alert_message'])) {
    $alertMessage = $_GET['alert_message'];
}

// Fetch data from inventory table
$query = "SELECT i.bid, i.title, i.status, f.uid 
          FROM inventory i 
          LEFT JOIN fav f ON i.bid = f.bid AND f.uid = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

// Close database connection
$mysqli->close();
?>


<?php
// Include database connection
include 'config.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve UID and BID from the request
    $uid = $_POST['uid'];
    $bid = $_POST['bid'];

    // Prepare and execute the SQL statement to insert into the fav table
    $stmt = $mysqli->prepare("INSERT INTO fav (uid, bid) VALUES (?, ?)");
    $stmt->bind_param("ii", $uid, $bid);
    $stmt->execute();

    // Check if insertion was successful
    if ($stmt->affected_rows > 0) {
        // Return a success response
        http_response_code(200);
    } else {
        // Return an error response
        http_response_code(500);
    }

    // Close statement and database connection
    $stmt->close();
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Reserve/Borrow</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/user_rsrv.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg">
    <div class="navbar">
        <div class="navbar-container">
            <img src="css/pics/logop.png" alt="Logo" class="logo">
            <p style="margin-left: 7%;">EasyLib: Library User Experience and Management Through Integrated Monitoring Systems</p>
        </div>
</div>

    <div class="sidebar">
        <div>
            <!-- Display Profile Image -->
            <a href="user_pf.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>"><img src="<?php echo $profile_image_path; ?>" alt="Profile Image" class="profile-image" style="width:100px; height:100px; border-radius:50%;"></a>
        </div>
        <div class="hell">Hello, <?php echo $user_username_display; ?>!</div>
        <a href="user_dash.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Dashboard</a>
        <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item active">Reserve/Borrow</a>
        <a href="user_ovrd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Overdue</a>
        <a href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Favorites</a>
        <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">E-Books</a>
        <a href="login.php" class="logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item active">Reserve Books</a>
            <a href="user_brwd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item">Borrowed</a>
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
        <button type="button" id="cancelSearchButton" style="background-color:crimson; color:white;">Cancel</button>
    </div>

        <form id="reserveForm" action="handle_reservation.php" method="POST">
        <input type="hidden" name="uid" value="<?php echo isset($uid) ? $uid : ''; ?>">
        <table>
    <thead>
        <tr>
            <th>#</th>
            <th hidden>Bid</th>
            <th>Title</th>
            <th>Author</th>
            <th>Status</th>
            <th>Reserve</th>
            <th>Favorite</th>
            <th>More Info:</th>
        </tr>
    </thead>
    <tbody>
    <?php
// Fetch data from inventory table
$query = "SELECT i.bid, i.title, i.author, i.status, f.uid 
          FROM inventory i 
          LEFT JOIN fav f ON i.bid = f.bid AND f.uid = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $counter++ . "</td>";
        echo "<td style='display: none;'>" . $row['bid'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['author'] . "</td>";
        echo "<td style='text-align:center;'>" . $row['status'] . "</td>";
        echo "<td style='text-align:center;'><button type='button' class='reserve-btn' data-bid='" . $row['bid'] . "' data-title='" . $row['title'] . "'>Reserve</button></td>";
        echo "<td style='text-align:center;'>";
        if ($row['uid']) {
            echo "<button type='button' class='favorite-btn' data-uid='" . $uid . "' data-bid='" . $row['bid'] . "'><i class='fas fa-heart'></i></button>";
        } else {
            echo "<button type='button' class='favorite-btn' data-uid='" . $uid . "' data-bid='" . $row['bid'] . "'><i class='far fa-heart'></i></button>";
        }
        echo "</td>";
        echo "<td style='text-align:center;'><button type='button' class='more-details-btn' data-bid='" . $row['bid'] . "'>More Details</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No books available.</td></tr>";
}

$mysqli->close();
?>
    </tbody>
</table>
            <div class="cart-header">Cart: </div>
            <div class="cart-content">
                <textarea name="cart_items" id="cart_items" placeholder="Add items to your cart..." readonly></textarea>
                <button type="submit" name="reserve_submit">Submit</button>
                <button type="button" id="cancelButton">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Include SweetAlert library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Function to handle the click event of the Reserve button
    document.addEventListener('DOMContentLoaded', function() {
    // Function to handle the click event of the Reserve button
    const reserveButtons = document.querySelectorAll('.reserve-btn');
    reserveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bid = this.getAttribute('data-bid');
            const title = this.getAttribute('data-title');
            const status = this.parentNode.previousElementSibling.textContent.trim(); // Get the status from the previous cell

            // Check if the status is "Not Reservable"
            if (status === 'Not Reservable') {
                // Show alert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'This book is not available for reservation.',
                });
            } else if (status === 'Reserved') {
                // Show alert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'This book is already reserved.',
                });
            } else if (status === 'Overdue') {
                // Show alert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'This book is currently overdued by another user.',
                });
            } else {
                const cartItemsTextarea = document.getElementById('cart_items');
                const currentCartItems = cartItemsTextarea.value;

                // Check if the book is already in the cart
                if (currentCartItems.includes(bid)) {
                    // Show alert
                    Swal.fire({
                        icon: 'info',
                        title: 'Attention',
                        text: 'This book is already in the cart.',
                    });
                } else {
                    // Append the selected book's bid and title to the cart textarea
                    cartItemsTextarea.value = currentCartItems ? currentCartItems + ',' + bid + ',' + title : bid + ',' + title;
                }
            }
        });
    });

    // Function to handle the click event of the Submit button
    const reserveForm = document.getElementById('reserveForm');
    const reserveSubmitButton = document.querySelector('[name="reserve_submit"]');
    reserveSubmitButton.addEventListener('click', function(event) {
        // Prevent the default form submission
        event.preventDefault();

        // Check if the cart is empty
        const cartItemsTextarea = document.getElementById('cart_items');
        const cartItems = cartItemsTextarea.value.trim();
        if (cartItems === '') {
            // Show alert if cart is empty
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Your cart is empty. Please add books to your cart before submitting.',
            });
        } else {
            // Show reservation success alert
            Swal.fire({
                icon: 'success',
                title: 'Reservation Pending',
                text: 'Your reservation is now pending. After librarian confirmation, book is reserved for 3 days for you to claim.',
                showConfirmButton: false, // Remove the confirm button
                timer: 1500, // Timer for 1.5 seconds
            }).then(() => {
                // Submit the form after showing the alert
                reserveForm.submit();
            });
        }
    });

    // Function to clear the cart textarea when the cancel button is clicked
    const cancelButton = document.getElementById('cancelButton');
    cancelButton.addEventListener('click', function() {
        const cartItemsTextarea = document.getElementById('cart_items');
        cartItemsTextarea.value = '';
    });

    // Function to handle the click event of the Favorite button
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const uid = this.getAttribute('data-uid');
            const bid = this.getAttribute('data-bid');
            const icon = this.querySelector('i'); // Get the icon element

            // Send AJAX request to check if the combination of uid and bid already exists
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_favorites.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.exists) {
                            // Display alert if the combination already exists
                            Swal.fire({
                                icon: 'info',
                                title: 'Book is already in Favorites.',
                            });
                        } else {
                            // If combination does not exist, add to favorites
                            addToFavorites(uid, bid, icon); // Call addToFavorites function
                        }
                    } else {
                        // Display error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to check if book is in favorites.',
                        });
                    }
                }
            };
            xhr.send('uid=' + uid + '&bid=' + bid);
        });
    });

    // Function to add to favorites
    function addToFavorites(uid, bid, icon) {
        // Send AJAX request to insert into fav table
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'handle_add_to_favorites.php', true); // Adjusted URL
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Change the icon to a solid star
                    icon.classList.remove('far'); // Remove the 'far' class
                    icon.classList.add('fas'); // Add the 'fas' class
                    // Show success alert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Book added to Favorites.',
                    });
                } else {
                    // Display error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to add to favorites.',
                        showConfirmButton: false, // Remove the confirm button
                        timer: 1500, // Timer for 1.5 seconds
                    }).then((result) => {
                        // Reload the page
                        location.reload();
                    });
                }
            }
        };
        xhr.send('uid=' + uid + '&bid=' + bid);
    }
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
    
    updateTime();// Call the function to update time immediately
    setInterval(updateTime, 1000);// Update time every second
</script>
<script>
    // Add event listener for "More Details" buttons
document.addEventListener('DOMContentLoaded', function() {
    const moreDetailsButtons = document.querySelectorAll('.more-details-btn');
    moreDetailsButtons.forEach(button => {
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
});

</script>
</body>
</html>
