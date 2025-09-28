<?php
session_start();
require_once 'db_connect.php';

// If the user is not logged in, redirect to the index page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$user = null;
$reservations = [];

// Get user details
$sql_user = "SELECT username, email, created_at FROM users WHERE user_id = ?";
if ($stmt_user = mysqli_prepare($link, $sql_user)) {
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    if (mysqli_stmt_execute($stmt_user)) {
        $result_user = mysqli_stmt_get_result($stmt_user);
        $user = mysqli_fetch_assoc($result_user);
    }
    mysqli_stmt_close($stmt_user);
}

// Get user's reservations
$sql_reservations = "SELECT res_date, res_time, num_guests, status FROM reservations WHERE user_id = ? AND deleted_at IS NULL ORDER BY res_date DESC, res_time DESC";
if ($stmt_reservations = mysqli_prepare($link, $sql_reservations)) {
    mysqli_stmt_bind_param($stmt_reservations, "i", $user_id);
    if (mysqli_stmt_execute($stmt_reservations)) {
        $result_reservations = mysqli_stmt_get_result($stmt_reservations);
        while ($row = mysqli_fetch_assoc($result_reservations)) {
            $reservations[] = $row;
        }
    }
    mysqli_stmt_close($stmt_reservations);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Tavern Publico</title>
    <link rel="stylesheet" href="CSS/main.css">
    <link rel="stylesheet" href="CSS/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php include 'partials/header.php'; ?>

    <main class="profile-page-main">
        <div class="container">
            <div class="profile-header">
                <h1>Welcome, <?= htmlspecialchars($user['username'] ?? 'Guest'); ?>!</h1>
                <p>Here you can view your account details and reservation history.</p>
            </div>

            <div class="profile-content-grid">
                <div class="profile-details-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-edit"></i> My Information</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($user): ?>
                            <div class="info-row">
                                <span class="info-label">Username</span>
                                <span class="info-value"><?= htmlspecialchars($user['username']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value"><?= htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Member Since</span>
                                <span class="info-value"><?= date('F j, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="reservation-history-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Reservation History</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Guests</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($reservations)): ?>
                                        <?php foreach ($reservations as $res): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($res['res_date']); ?></td>
                                                <td><?= htmlspecialchars(date('g:i A', strtotime($res['res_time']))); ?></td>
                                                <td><?= htmlspecialchars($res['num_guests']); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?= strtolower(htmlspecialchars($res['status'])); ?>">
                                                        <?= htmlspecialchars($res['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="no-reservations">You have no past or upcoming reservations.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>
    <?php include 'partials/Signin-Signup.php'; ?>
    <script src="JS/main.js"></script>
</body>
</html>