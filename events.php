<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = null;
$reservations = [];

$sql_user = "SELECT user_id, username, email, created_at, avatar, birthday, mobile FROM users WHERE user_id = ?";
if ($stmt_user = mysqli_prepare($link, $sql_user)) {
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    if (mysqli_stmt_execute($stmt_user)) {
        $result_user = mysqli_stmt_get_result($stmt_user);
        $user = mysqli_fetch_assoc($result_user);
    }
    mysqli_stmt_close($stmt_user);
}

// MODIFIED: Fetch reservation_id and created_at for the new feature
$sql_reservations = "SELECT reservation_id, res_date, res_time, num_guests, status, created_at FROM reservations WHERE user_id = ? AND deleted_at IS NULL ORDER BY created_at DESC";
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
$avatar_path = isset($user['avatar']) && file_exists($user['avatar']) ? $user['avatar'] : 'images/default_avatar.png';
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
                <div class="profile-avatar-container">
                    <img src="<?= htmlspecialchars($avatar_path) ?>" alt="My Avatar" class="profile-avatar">
                    <form action="upload_avatar.php" method="post" enctype="multipart/form-data" class="upload-avatar-form">
                        <label for="avatarFile" class="upload-label"><i class="fas fa-upload"></i> Change Avatar</label>
                        <input type="file" name="avatarFile" id="avatarFile" onchange="this.form.submit()">
                    </form>
                </div>
                <h1>Welcome, <?= htmlspecialchars($user['username'] ?? 'Guest'); ?>!</h1>
                <p>Here you can view your account details and reservation history.</p>
            </div>

            <div class="profile-content-grid">
                <div class="profile-details-card">
                    <div class="card-header">
                        <h3><i class="fas fa-cogs"></i> Settings</h3>
                    </div>
                    <div class="card-body">
                        <form action="update_profile.php" method="post">
                            <h4>Account Information</h4>
                            <div class="info-row">
                                <span class="info-label">ID</span>
                                <input type="text" class="info-value" value="<?= htmlspecialchars($user['user_id']); ?>" readonly>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Username</span>
                                <input type="text" name="username" class="info-value" value="<?= htmlspecialchars($user['username']); ?>">
                            </div>
                            <div class="info-row">
                                <span class="info-label">Change Password</span>
                                <input type="password" name="password" class="info-value" placeholder="New Password">
                            </div>
                            <div class="info-row">
                                <span class="info-label">Birthday</span>
                                <input type="date" name="birthday" class="info-value" value="<?= htmlspecialchars($user['birthday']); ?>">
                            </div>
                            <div class="info-row">
                                <span class="info-label">Mobile</span>
                                <input type="text" name="mobile" class="info-value" value="<?= htmlspecialchars($user['mobile']); ?>" placeholder="Add Mobile Number">
                            </div>
                            <button type="submit" class="btn-save-changes">Save Changes</button>
                        </form>
                        <div class="policies-section">
                            <h4>Policies</h4>
                            <p><a href="#">Terms of Service</a></p>
                            <p><a href="#">Privacy Policy</a></p>
                        </div>
                    </div>
                </div>

                <div class="reservation-history-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Reservation History</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="reservationHistoryTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Guests</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($reservations)): ?>
                                        <?php foreach ($reservations as $res): ?>
                                            <tr class="reservation-row">
                                                <td><?= htmlspecialchars($res['res_date']); ?></td>
                                                <td><?= htmlspecialchars(date('g:i A', strtotime($res['res_time']))); ?></td>
                                                <td><?= htmlspecialchars($res['num_guests']); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?= strtolower(htmlspecialchars($res['status'])); ?>">
                                                        <?= htmlspecialchars($res['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $created_timestamp = strtotime($res['created_at']);
                                                    $current_timestamp = time();
                                                    $can_cancel = ($current_timestamp - $created_timestamp) < 1800;
                                                    $is_cancellable_status = in_array($res['status'], ['Pending', 'Confirmed']);

                                                    if ($is_cancellable_status && $can_cancel) {
                                                        echo '<button class="btn-cancel" data-id="' . $res['reservation_id'] . '">Cancel</button>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="no-reservations">You have no past or upcoming reservations.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="show-more-less-container">
                            <button id="showMoreBtn" class="btn-show-more">Show More</button>
                            <button id="showLessBtn" class="btn-show-less" style="display: none;">Show Less</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>
    <?php include 'partials/Signin-Signup.php'; ?>
    <script src="JS/main.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.btn-cancel').forEach(button => {
                button.addEventListener('click', function() {
                    const reservationId = this.dataset.id;
                    if (confirm('Are you sure you want to cancel this reservation? This action cannot be undone.')) {
                        handleCancelReservation(reservationId, this);
                    }
                });
            });

            const tableBody = document.getElementById('reservationHistoryTable').querySelector('tbody');
            const rows = tableBody.querySelectorAll('.reservation-row');
            const showMoreBtn = document.getElementById('showMoreBtn');
            const showLessBtn = document.getElementById('showLessBtn');
            const rowsToShow = 5;

            if (rows.length > rowsToShow) {
                for (let i = rowsToShow; i < rows.length; i++) {
                    rows[i].style.display = 'none';
                }
                showMoreBtn.style.display = 'block';
            } else {
                showMoreBtn.style.display = 'none';
            }

            showMoreBtn.addEventListener('click', () => {
                for (let i = rowsToShow; i < rows.length; i++) {
                    rows[i].style.display = 'table-row';
                }
                showMoreBtn.style.display = 'none';
                showLessBtn.style.display = 'block';
            });

            showLessBtn.addEventListener('click', () => {
                for (let i = rowsToShow; i < rows.length; i++) {
                    rows[i].style.display = 'none';
                }
                showLessBtn.style.display = 'none';
                showMoreBtn.style.display = 'block';
            });
        });

        async function handleCancelReservation(id, buttonElement) {
            const formData = new FormData();
            formData.append('reservation_id', id);

            try {
                const response = await fetch('cancel_reservation.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const row = buttonElement.closest('tr');
                    if(row) {
                        const statusBadge = row.querySelector('.status-badge');
                        statusBadge.textContent = 'Cancelled';
                        statusBadge.className = 'status-badge status-cancelled';
                        buttonElement.remove();
                    }
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                alert('An unexpected error occurred. Please try again.');
            }
        }
    </script>
</body>
</html>