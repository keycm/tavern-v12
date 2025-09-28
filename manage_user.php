<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Admin authentication check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['is_admin'] !== true) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // --- Action: Delete User ---
    if ($action === 'deleteUser') {
        $userId = filter_var($_POST['user_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        if ($userId > 0) {
            $sql = "DELETE FROM users WHERE user_id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $userId);
                if (mysqli_stmt_execute($stmt)) {
                    $response['success'] = true;
                    $response['message'] = 'User deleted successfully.';
                } else {
                    $response['message'] = 'Error deleting user.';
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $response['message'] = 'Invalid user ID.';
        }
    }
    // --- Action: Add or Update User ---
    elseif ($action === 'saveUser') {
        $userId = filter_var($_POST['user_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (empty($username) || empty($email)) {
            $response['message'] = 'Username and Email are required.';
            echo json_encode($response);
            exit;
        }

        // --- UPDATE existing user ---
        if ($userId > 0) {
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, email = ?, password_hash = ? WHERE user_id = ?";
                $stmt = mysqli_prepare($link, $sql);
                mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $password_hash, $userId);
            } else {
                $sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
                $stmt = mysqli_prepare($link, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $userId);
            }

            if ($stmt && mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'User updated successfully.';
            } else {
                $response['message'] = 'Failed to update user. Username or email might already be taken.';
            }
            if ($stmt) mysqli_stmt_close($stmt);
        }
        // --- ADD new user ---
        else {
            if (empty($password)) {
                $response['message'] = 'Password is required for new users.';
                echo json_encode($response);
                exit;
            }
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password_hash, is_admin) VALUES (?, ?, ?, 0)";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password_hash);
            
            if ($stmt && mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'User added successfully.';
            } else {
                $response['message'] = 'Failed to add user. Username or email might already exist.';
            }
            if ($stmt) mysqli_stmt_close($stmt);
        }
    } else {
        $response['message'] = 'Invalid action.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

mysqli_close($link);
echo json_encode($response);
?>
