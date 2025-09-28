<?php
// Include the database connection file
require_once 'db_connect.php';

header('Content-Type: application/json'); // Set header to return JSON response

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- Validation Checks (same as before) ---
    if (empty($username) || empty($email) || empty($password)) {
        $response['message'] = 'Please fill in all fields.';
        echo json_encode($response);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }
    if (substr($email, -10) !== '@gmail.com') {
        $response['message'] = 'Registration is limited to @gmail.com addresses only.';
        echo json_encode($response);
        exit;
    }
    if (strlen($password) < 6 || !preg_match('/[A-Z]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $response['message'] = 'Password does not meet the requirements.';
        echo json_encode($response);
        exit;
    }

    // Check if username or email already exists
    $sql_check = "SELECT user_id FROM users WHERE username = ? OR email = ?";
    if ($stmt_check = mysqli_prepare($link, $sql_check)) {
        mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
        if (mysqli_stmt_execute($stmt_check)) {
            mysqli_stmt_store_result($stmt_check);
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $response['message'] = 'Username or Email already taken.';
                echo json_encode($response);
                mysqli_stmt_close($stmt_check);
                mysqli_close($link);
                exit;
            }
        }
        mysqli_stmt_close($stmt_check);
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $sql_insert = "INSERT INTO users (username, email, password_hash, is_verified) VALUES (?, ?, ?, 0)";
    if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
        mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $password_hash);

        if (mysqli_stmt_execute($stmt_insert)) {
            $user_id = mysqli_insert_id($link); // Get the new user's ID

            // --- NEW: Automatic Avatar Generation ---
            $first_letter = strtoupper(substr($username, 0, 1));
            $avatar_path = 'uploads/avatars/' . uniqid() . '.png';

            $image = imagecreate(100, 100);
            $bg_color = imagecolorallocate($image, 22, 160, 133); // A nice teal color
            $text_color = imagecolorallocate($image, 255, 255, 255);
            $font = 5; // Use a built-in GD font
            
            // Center the letter
            $text_width = imagefontwidth($font) * strlen($first_letter);
            $text_height = imagefontheight($font);
            $x = (100 - $text_width) / 2;
            $y = (100 - $text_height) / 2;

            imagestring($image, $font, $x, $y, $first_letter, $text_color);
            imagepng($image, $avatar_path);
            imagedestroy($image);

            // Update the user's record with the avatar path
            $sql_update_avatar = "UPDATE users SET avatar = ? WHERE user_id = ?";
            if ($stmt_update = mysqli_prepare($link, $sql_update_avatar)) {
                mysqli_stmt_bind_param($stmt_update, "si", $avatar_path, $user_id);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
            }
            // --- END of NEW code ---

            $response['success'] = true;
            $response['message'] = 'Registration successful! Your account is pending admin approval.';
        } else {
            $response['message'] = 'Registration failed. Please try again.';
        }
        mysqli_stmt_close($stmt_insert);
    } else {
        $response['message'] = 'Database error: Could not prepare statement.';
    }

    mysqli_close($link);
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>