<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

// Fetch all deletion history records
$deleted_items = [];
$sql = "SELECT log_id, item_type, item_id, item_data, deleted_at, purge_date FROM deletion_history ORDER BY deleted_at DESC";
if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deleted_items[] = $row;
    }
    mysqli_free_result($result);
} else {
    error_log("Deletion History page error: " . mysqli_error($link));
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tavern Publico - Deletion History</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <div class="page-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="Tavern.png" alt="Home Icon" class="home-icon">
            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li class="menu-item"><a href="admin.php"><i class="material-icons">dashboard</i> Dashboard</a></li>
                    <li class="menu-item"><a href="update.php"><i class="material-icons">file_upload</i> Upload Management</a></li>
                    <li class="menu-item"><a href="reservation.php"><i class="material-icons">event_note</i> Reservation</a></li>
                </ul>
                <div class="user-management-title">User Management</div>
                <ul class="sidebar-menu user-management-menu">
                     <li class="menu-item"><a href="#"><i class="material-icons">people</i> Notification Control</a></li>
                    <li class="menu-item"><a href="#"><i class="material-icons">security</i> Table Management</a></li>
                     <li class="menu-item"><a href="customer_database.php"><i class="material-icons">people</i> Customer Database</a></li>
                     <li class="menu-item"><a href="reports.php"><i class="material-icons">analytics</i> Reservation Reports</a></li>
                     <li class="menu-item active"><a href="deletion_history.php"><i class="material-icons">history</i> Deletion History</a></li>
                    <li class="menu-item"><a href="logout.php"><i class="material-icons">logout</i> Log out</a></li>
                </ul>
            </nav>
        </aside>

        <div class="admin-content-area">
            <header class="main-header">
                <div class="header-content">
                    <div class="admin-header-right">
                        <img src="images/PEOPLE.jpg" alt="User Avatar" style="width: 40px; height: 40px; border-radius: 50%;">
                        <span><?= htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="admin-role">Admin</span>
                    </div>
                </div>
            </header>

            <main class="dashboard-main-content">
                <div class="reservation-page-header">
                    <h1>Deletion History</h1>
                    <input type="text" id="historySearch" class="search-input" placeholder="Search deleted items...">
                </div>

                <section class="all-reservations-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ITEM TYPE</th>
                                    <th>ITEM DETAILS</th>
                                    <th>DELETED AT</th>
                                    <th>PURGE DATE</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <?php if (empty($deleted_items)): ?>
                                    <tr><td colspan="5" style="text-align: center;">No deleted items found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($deleted_items as $item): 
                                        $item_data = json_decode($item['item_data'], true);
                                        $details = '';
                                        switch($item['item_type']) {
                                            case 'user':
                                                $details = "Username: " . htmlspecialchars($item_data['username'] ?? 'N/A');
                                                break;
                                            case 'reservation':
                                                $details = "Name: " . htmlspecialchars($item_data['res_name'] ?? 'N/A') . " for " . htmlspecialchars($item_data['res_date'] ?? 'N/A');
                                                break;
                                            case 'menu_item':
                                            case 'event':
                                                $details = "Title: " . htmlspecialchars($item_data['name'] ?? $item_data['title'] ?? 'N/A');
                                                break;
                                            case 'gallery_image':
                                                $details = "Image: <img src='" . htmlspecialchars($item_data['image'] ?? '') . "' style='width: 50px; height: auto;' />";
                                                break;
                                        }
                                    ?>
                                        <tr data-log-id="<?= $item['log_id']; ?>">
                                            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $item['item_type']))); ?></td>
                                            <td><?= $details; ?></td>
                                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($item['deleted_at']))); ?></td>
                                            <td><?= htmlspecialchars($item['purge_date']); ?></td>
                                            <td class="actions">
                                                <button class="btn btn-small restore-btn" style="background-color: #28a745;">Restore</button>
                                                <button class="btn btn-small purge-btn" style="background-color: #dc3545;">Delete Permanently</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
    <script src="JS/deletion_history.js"></script>
</body>
</html>
