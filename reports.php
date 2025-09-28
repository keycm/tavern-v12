<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// --- Data Fetching for Reports ---

// NOTE FOR DEVELOPER: The 'source' column is currently simulated. 
// For a production environment, you should add a 'source' column to your 
// 'reservations' table to store actual data (e.g., 'Online', 'Phone', 'Walk-in').
$sql = "SELECT *, 
        CASE 
            WHEN reservation_id % 3 = 0 THEN 'Phone'
            WHEN reservation_id % 3 = 1 THEN 'Online'
            ELSE 'Walk-in' 
        END AS source 
        FROM reservations";

$reservations_result = mysqli_query($link, $sql);
$all_reservations = [];
if ($reservations_result) {
    while ($row = mysqli_fetch_assoc($reservations_result)) {
        $all_reservations[] = $row;
    }
    mysqli_free_result($reservations_result);
}

// --- Prepare data for charts ---

// 1. Source of Business Data
$source_counts = array_count_values(array_column($all_reservations, 'source'));

// 2. Pacing Data
// NOTE FOR DEVELOPER: 'lastYear' data is hardcoded for demonstration. In a real application,
// you should query your database for reservations from the previous year.
$pacing_this_year = array_fill_keys(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], 0);
$pacing_last_year = ['Jan' => 5, 'Feb' => 8, 'Mar' => 12, 'Apr' => 15, 'May' => 10, 'Jun' => 18, 'Jul' => 22, 'Aug' => 20, 'Sep' => 25, 'Oct' => 28, 'Nov' => 30, 'Dec' => 40]; // Simulated data

$bookings_by_month = [];
foreach ($all_reservations as $res) {
    $month = date('M', strtotime($res['res_date']));
    if (isset($pacing_this_year[$month])) {
        $pacing_this_year[$month]++;
    }
    // For "Busiest Month" KPI
    if (!isset($bookings_by_month[$month])) $bookings_by_month[$month] = 0;
    $bookings_by_month[$month]++;
}

// 3. Guest Demographics (New vs. Returning)
$guest_emails = array_column($all_reservations, 'res_email');
$guest_counts = array_count_values($guest_emails);
$new_guests = 0;
$returning_guests = 0;
foreach($guest_counts as $email => $count) {
    if ($count == 1) {
        $new_guests++;
    } else {
        // Count a returning guest once, but add all their visits to the count
        $returning_guests++;
    }
}

// --- Data for KPI Summary Cards ---
$total_reservations = count($all_reservations);
$busiest_month = 'N/A';
if (!empty($bookings_by_month)) {
    arsort($bookings_by_month); // Sort months by booking count descending
    $busiest_month = key($bookings_by_month);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tavern Publico - Reservation Reports</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Added styles for the new layout */
        .report-filters {
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 14px; color: #555; margin-bottom: 5px; }
        .filter-group input { padding: 8px 12px; border-radius: 5px; border: 1px solid #ccc; }
        
        .report-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .report-section {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 0; /* Removed bottom margin since grid gap handles it */
        }
        .report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        .report-header h3 { margin: 0; font-size: 18px; }
        .export-options button { margin-left: 10px; }
        .chart-container { padding-top: 10px; }
        
        @media (max-width: 992px) {
            .report-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <aside class="admin-sidebar">
        <div class="sidebar-header"><img src="Tavern.png" alt="Home Icon" class="home-icon"></div>
        <nav>
            <ul class="sidebar-menu">
                <li class="menu-item"><a href="admin.php"><i class="material-icons">dashboard</i> Dashboard</a></li>
                <li class="menu-item"><a href="update.php"><i class="material-icons">calendar_today</i> Upload Management</a></li>
                <li class="menu-item"><a href="reservation.php"><i class="material-icons">event_note</i> Reservation</a></li>
            </ul>
            <div class="user-management-title">User Management</div>
            <ul class="sidebar-menu user-management-menu">
                <li class="menu-item"><a href="#"><i class="material-icons">people</i> Notification Control</a></li>
                <li class="menu-item"><a href="#"><i class="material-icons">security</i> Table Management</a></li>
                <li class="menu-item"><a href="customer_database.php"><i class="material-icons">settings</i> Customer Database</a></li>
                <li class="menu-item active"><a href="reports.php"><i class="material-icons">analytics</i>Reservation Reports</a></li>
                <li class="menu-item"><a href="deletion_history.php"><i class="material-icons">history</i> Deletion History</a></li>
                <li class="menu-item"><a href="logout.php"><i class="material-icons">logout</i> Log out</a></li>
            </ul>
        </nav>
    </aside>

    <div class="admin-content-area">
        <header class="main-header">
            <div class="header-content">
                <div class="admin-header-right">
                    <img src="images/PEOPLE.jpg" alt="User Avatar">
                    <span><?= htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="admin-role">Admin</span>
                </div>
            </div>
        </header>

        <main class="dashboard-main-content">
            <h1 class="dashboard-heading">Reservation Reports</h1>

            <div class="dashboard-summary">
                <div class="summary-box total">
                    <h3>Total Reservations</h3>
                    <p><?= $total_reservations ?></p>
                    <i class="material-icons box-icon">event_note</i>
                </div>
                <div class="summary-box confirmed">
                    <h3>Busiest Month</h3>
                    <p><?= $busiest_month ?></p>
                    <i class="material-icons box-icon">trending_up</i>
                </div>
                <div class="summary-box pending">
                    <h3>New Guests</h3>
                    <p><?= $new_guests ?></p>
                    <i class="material-icons box-icon">person_add</i>
                </div>
                <div class="summary-box cancelled">
                    <h3>Returning Guests</h3>
                    <p><?= $returning_guests ?></p>
                    <i class="material-icons box-icon">repeat</i>
                </div>
            </div>

            <div class="report-filters">
                <h4>Filter Reports by Date</h4>
                <div class="filter-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" name="startDate">
                </div>
                <div class="filter-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" name="endDate">
                </div>
                <button class="btn view-btn" style="align-self: flex-end;">Apply</button>
            </div>

            <section class="report-section">
                <div class="report-header">
                    <h3>Pacing Report (This Year vs. Last Year)</h3>
                    <div class="export-options">
                        <button class="btn btn-small export-csv" data-target="pacingChart">Export CSV</button>
                        <button class="btn btn-small print-chart" data-target="pacingChart">Print</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="pacingChart"></canvas>
                </div>
            </section>

            <div class="report-grid" style="margin-top: 20px;">
                <section class="report-section">
                    <div class="report-header">
                        <h3>Source of Business</h3>
                        <div class="export-options">
                            <button class="btn btn-small export-csv" data-target="sourceChart">Export CSV</button>
                            <button class="btn btn-small print-chart" data-target="sourceChart">Print</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="sourceChart"></canvas>
                    </div>
                </section>
                
                <section class="report-section">
                    <div class="report-header">
                        <h3>Guest Demographics (New vs. Returning)</h3>
                        <div class="export-options">
                            <button class="btn btn-small export-csv" data-target="demographicsChart">Export CSV</button>
                            <button class="btn btn-small print-chart" data-target="demographicsChart">Print</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="demographicsChart"></canvas>
                    </div>
                </section>
            </div>

        </main>
    </div>
</div>

<script>
    // This data is now generated by the updated PHP code
    const reportData = {
        pacing: {
            labels: <?= json_encode(array_keys($pacing_this_year)); ?>,
            thisYear: <?= json_encode(array_values($pacing_this_year)); ?>,
            lastYear: <?= json_encode(array_values($pacing_last_year)); ?>
        },
        source: {
            labels: <?= json_encode(array_keys($source_counts)); ?>,
            counts: <?= json_encode(array_values($source_counts)); ?>
        },
        demographics: {
            newGuests: <?= $new_guests; ?>,
            returningGuests: <?= $returning_guests; ?>
        }
    };
</script>
<script src="JS/reports.js"></script>
</body>
</html>