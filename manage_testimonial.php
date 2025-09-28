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
    $testimonial_id = filter_input(INPUT_POST, 'testimonial_id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($testimonial_id)) {
        $response['message'] = 'Invalid testimonial ID.';
        echo json_encode($response);
        exit;
    }

    if ($action === 'feature') {
        $sql = "UPDATE testimonials SET is_featured = !is_featured WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $testimonial_id);
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Testimonial feature status updated.';
            } else {
                $response['message'] = 'Error updating testimonial.';
            }
            mysqli_stmt_close($stmt);
        }
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM testimonials WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $testimonial_id);
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Testimonial deleted successfully.';
            } else {
                $response['message'] = 'Error deleting testimonial.';
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
?>