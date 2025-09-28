<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !$_SESSION['is_admin']) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? 'block';
    $date_to_manage = $_POST['block_date'] ?? '';

    if (!empty($date_to_manage)) {
        if ($action === 'block') {
            $sql = "INSERT INTO blocked_dates (block_date) VALUES (?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $date_to_manage);
                if (mysqli_stmt_execute($stmt)) {
                    $response['success'] = true;
                    $response['message'] = 'Date blocked successfully.';
                } else {
                    $response['message'] = 'This date is already blocked.';
                }
                mysqli_stmt_close($stmt);
            }
        } elseif ($action === 'unblock') {
            $sql = "DELETE FROM blocked_dates WHERE block_date = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $date_to_manage);
                if (mysqli_stmt_execute($stmt)) {
                    $response['success'] = true;
                    $response['message'] = 'Date unblocked successfully.';
                } else {
                    $response['message'] = 'Error unblocking date.';
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $response['message'] = 'Invalid action.';
        }
    } else {
        $response['message'] = 'Invalid date.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

mysqli_close($link);
echo json_encode($response);
?>