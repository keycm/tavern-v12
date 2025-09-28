<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in AND is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

// Fetch blocked dates for display
$blocked_dates_list = [];
$sql_blocked_list = "SELECT id, block_date FROM blocked_dates ORDER BY block_date ASC";
if ($result = mysqli_query($link, $sql_blocked_list)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $blocked_dates_list[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tavern Publico - Table Management</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <style>
        .calendar-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        .block-date-form, .blocked-dates-list {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .blocked-date-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>

    <div class="page-wrapper">

        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="Tavern.png" alt="Home Icon" class="home-icon">
            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li class="menu-item">
                        <a href="admin.php"><i class="material-icons">dashboard</i> Dashboard</a>
                    </li>
                     <li class="menu-item"><a href="update.php"><i class="material-icons">file_upload</i> Upload Management</a></li>
                    <li class="menu-item">
                        <a href="reservation.php"><i class="material-icons">event_note</i> Reservation</a>
                    </li>
                </ul>
                <div class="user-management-title">User Management</div>
                <ul class="sidebar-menu user-management-menu">
                    <li class="menu-item">
                        <a href="notification_control.php"><i class="material-icons">notifications</i> Notification Control</a>
                    </li>
                    <li class="menu-item active">
                        <a href="table_management.php"><i class="material-icons">table_restaurant</i> Table Management</a>
                    </li>
                    <li class="menu-item">
                        <a href="customer_database.php"><i class="material-icons">people</i> Customer Database</a>
                    </li>
                    <li class="menu-item">
                        <a href="reports.php"><i class="material-icons">analytics</i>Reservation Reports</a>
                    </li>
                    <li class="menu-item"><a href="deletion_history.php"><i class="material-icons">history</i> Deletion History</a></li>
                    <li class="menu-item">
                        <a href="logout.php"><i class="material-icons">logout</i> Log out</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <div class="admin-content-area">
            <header class="main-header">
                <div class="header-content">
                    <div class="admin-header-right">
                        <img src="images/PEOPLE.jpg" alt="User Avatar" style="width: 40px; height: 40px; border-radius: 50%;">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <span class="admin-role">Admin</span>
                    </div>
                </div>
            </header>

            <main class="dashboard-main-content">
                <div class="reservation-page-header">
                    <h1>Table Management</h1>
                </div>

                <div class="calendar-container">
                    <div id="calendar"></div>
                </div>

                <div class="block-date-form">
                    <h2>Block Reservations for a Date</h2>
                    <form id="blockDateForm">
                        <div class="form-group">
                            <label for="block_date">Select Date to Block:</label>
                            <input type="date" id="block_date" name="block_date" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Block Date</button>
                    </form>
                </div>

                <div class="blocked-dates-list">
                    <h2>Currently Blocked Dates</h2>
                    <div id="blocked-dates-container">
                        <?php if (empty($blocked_dates_list)): ?>
                            <p>No dates are currently blocked.</p>
                        <?php else: ?>
                            <?php foreach ($blocked_dates_list as $date): ?>
                                <div class="blocked-date-item" data-id="<?php echo $date['id']; ?>">
                                    <span><?php echo htmlspecialchars($date['block_date']); ?></span>
                                    <button class="btn btn-secondary unblock-date-btn">Unblock</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="JS/table_management.js"></script>

</body>
</html>