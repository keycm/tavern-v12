<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['loggedin']) || !$_SESSION['is_admin']) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $message_id = filter_input(INPUT_POST, 'message_id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($message_id)) {
        $response['message'] = 'Invalid message ID.';
        echo json_encode($response);
        exit;
    }

    if ($action === 'reply') {
        $reply_text = trim($_POST['reply_text'] ?? '');
        $customer_email = filter_var(trim($_POST['customer_email'] ?? ''), FILTER_SANITIZE_EMAIL);

        if (empty($reply_text) || empty($customer_email)) {
            $response['message'] = 'Reply text and customer email are required.';
            echo json_encode($response);
            exit;
        }

        // Check if the email belongs to a registered user
        $user_id = null;
        $sql_find_user = "SELECT user_id FROM users WHERE email = ?";
        if($stmt_find_user = mysqli_prepare($link, $sql_find_user)){
            mysqli_stmt_bind_param($stmt_find_user, "s", $customer_email);
            mysqli_stmt_execute($stmt_find_user);
            mysqli_stmt_bind_result($stmt_find_user, $found_user_id);
            if(mysqli_stmt_fetch($stmt_find_user)){
                $user_id = $found_user_id;
            }
            mysqli_stmt_close($stmt_find_user);
        }

        if ($user_id) {
            // User found, insert into notifications table
            $sql_insert_notification = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            if($stmt_insert = mysqli_prepare($link, $sql_insert_notification)){
                mysqli_stmt_bind_param($stmt_insert, "is", $user_id, $reply_text);
                mysqli_stmt_execute($stmt_insert);
                mysqli_stmt_close($stmt_insert);

                // Update the original message to mark as replied
                $sql_update_message = "UPDATE contact_messages SET admin_reply = ?, replied_at = NOW(), is_read = 1 WHERE id = ?";
                if ($stmt_update = mysqli_prepare($link, $sql_update_message)) {
                    mysqli_stmt_bind_param($stmt_update, "si", $reply_text, $message_id);
                    mysqli_stmt_execute($stmt_update);
                    mysqli_stmt_close($stmt_update);
                }
                $response['success'] = true;
                $response['message'] = 'Reply sent as an in-app notification.';
            }
        } else {
            $response['message'] = 'Could not send notification. The message is from a guest who is not a registered user.';
        }

    } elseif ($action === 'delete') {
        $sql = "DELETE FROM contact_messages WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $message_id);
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Message deleted successfully.';
            } else {
                $response['message'] = 'Error deleting message.';
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $response['message'] = 'Invalid action.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

mysqli_close($link);
echo json_encode($response);