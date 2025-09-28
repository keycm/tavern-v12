<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $resDate = htmlspecialchars(trim($_POST['resDate'] ?? ''));
    $resTime = htmlspecialchars(trim($_POST['resTime'] ?? ''));
    $numGuests = filter_var(trim($_POST['numGuests'] ?? ''), FILTER_SANITIZE_NUMBER_INT);
    $resName = htmlspecialchars(trim($_POST['resName'] ?? ''));
    $resPhone = htmlspecialchars(trim($_POST['resPhone'] ?? ''));
    $resEmail = filter_var(trim($_POST['resEmail'] ?? ''), FILTER_SANITIZE_EMAIL);
    $status = "Pending";

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // --- FEATURE: Server-Side Validation ---
    if (empty($resDate) || empty($resTime) || empty($numGuests) || empty($resName) || empty($resPhone) || empty($resEmail)) {
        header('Location: reserve.php?status=error&message=' . urlencode('Please fill in all required fields.'));
        exit;
    }
    
    // Validate Philippine phone number (must be 11 digits and start with 09)
    if (!preg_match('/^09\d{9}$/', $resPhone)) {
        header('Location: reserve.php?status=error&message=' . urlencode('Invalid phone number. Please enter a valid 11-digit Philippine mobile number starting with 09.'));
        exit;
    }

    // Validate email format
    if (!filter_var($resEmail, FILTER_VALIDATE_EMAIL)) {
         header('Location: reserve.php?status=error&message=' . urlencode('Invalid email address format.'));
         exit;
    }

    $sql = "INSERT INTO reservations (user_id, res_date, res_time, num_guests, res_name, res_phone, res_email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ississss", $user_id, $resDate, $resTime, $numGuests, $resName, $resPhone, $resEmail, $status);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: reserve.php?status=success');
            exit;
        } else {
            error_log("Reservation insert error: " . mysqli_stmt_error($stmt));
            header('Location: reserve.php?status=error&message=' . urlencode('Database insert failed.'));
            exit;
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Reservation prepare error: " . mysqli_error($link));
        header('Location: reserve.php?status=error&message=' . urlencode('Database preparation failed.'));
        exit;
    }

    mysqli_close($link);

} else {
    header('Location: reserve.php');
    exit;
}
?>