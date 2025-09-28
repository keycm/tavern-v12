<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$events = [];

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['is_admin'] === true) {
    // Fetch reservations
    $sql_reservations = "SELECT res_date, COUNT(*) as count FROM reservations GROUP BY res_date";
    if ($result = mysqli_query($link, $sql_reservations)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = [
                'title' => $row['count'] . ' Reservations',
                'start' => $row['res_date'],
                'backgroundColor' => '#28a745', // Green for reservations
                'borderColor' => '#28a745'
            ];
        }
    }

    // Fetch blocked dates
    $sql_blocked = "SELECT block_date FROM blocked_dates";
    if ($result = mysqli_query($link, $sql_blocked)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $events[] = [
                'title' => 'Blocked',
                'start' => $row['block_date'],
                'backgroundColor' => '#dc3545', // Red for blocked dates
                'borderColor' => '#dc3545'
            ];
        }
    }
}

echo json_encode($events);
?>