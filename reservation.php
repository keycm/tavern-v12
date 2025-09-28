<?php
session_start();
require_once 'db_connect.php'; // Include your database connection

// Check if the user is logged in AND is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit;
}

// Fetch all reservations from the database
$allReservations = [];
$sql = "SELECT reservation_id, user_id, res_date, res_time, num_guests, res_name, res_phone, res_email, status, created_at FROM reservations WHERE deleted_at IS NULL ORDER BY created_at DESC";

if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $allReservations[] = $row;
    }
    mysqli_free_result($result);
} else {
    error_log("Reservation page database error: " . mysqli_error($link));
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tavern Publico - All Reservations</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* Inlined Modal CSS to override cache */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fefefe;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            position: relative;
            animation-name: animatetop;
            animation-duration: 0.4s;
        }

        @keyframes animatetop {
            from {top: -300px; opacity: 0}
            to {top: 0; opacity: 1}
        }

        .modal-content h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 26px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-content p {
            margin-bottom: 12px;
            line-height: 1.8;
            color: #555;
            font-size: 15px;
        }

        .close-button {
            color: #888;
            font-size: 32px;
            font-weight: bold;
            position: absolute;
            top: 15px;
            right: 25px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-button:hover,
        .close-button:focus {
            color: #333;
            text-decoration: none;
        }

        .modal-actions {
            margin-top: 30px;
            text-align: right;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <div class="page-wrapper">

        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="Tavern.png" alt="Home Icon" class="home-icon">
            </div>
            <nav>
                <ul class="sidebar-menu">
                    <li class="menu-item">
                        <a href="admin.php"><i class="material-icons">dashboard</i> Dashboard</a>
                    </li>
                     <li class="menu-item"><a href="update.php"><i class="material-icons">file_upload</i> Upload Management</a></li>
                    <li class="menu-item active">
                        <a href="reservation.php"><i class="material-icons">event_note</i> Reservation</a>
                    </li>
                </ul>
                <div class="user-management-title">User Management</div>
                <ul class="sidebar-menu user-management-menu">
                    <li class="menu-item">
                        <a href="#"><i class="material-icons">people</i> Notification Control</a>
                    </li>
                    <li class="menu-item">
                        <a href="#"><i class="material-icons">security</i> Table Management</a>
                    </li>
                    <li class="menu-item">
                        <a href="customer_database.php"><i class="material-icons">settings</i> Customer Database</a>
                    </li>
                    <li class="menu-item">
                        <a href="reports.php"><i class="material-icons">settings</i>Reservation Reports</a>
                    </li>
                    <li class="menu-item"><a href="deletion_history.php"><i class="material-icons">history</i> Deletion History</a></li>
                    <li class="menu-item">
                        <a href="logout.php"><i class="material-icons">logout</i> Log out</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <div class="admin-content-area">
            <header class="main-header">
                <div class="header-content">
                    <div class="admin-header-right">
                        <img src="images/PEOPLE.jpg" alt="User Avatar" style="width: 40px; height: 40px; border-radius: 50%;">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <span class="admin-role">Admin</span>
                    </div>
                </div>
            </header>

            <main class="dashboard-main-content">
                <div class="reservation-page-header">
                    <h1>All Reservations</h1>
                    <input type="text" id="reservationSearch" class="search-input" placeholder="Search reservations...">
                    <button class="check-overall-availability-btn">Check Overall Availability</button>
                </div>

                <section class="all-reservations-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>CUSTOMER</th>
                                    <th>DATE</th>
                                    <th>TIME</th>
                                    <th>GUESTS</th>
                                    <th>PHONE</th>
                                    <th>STATUS</th>
                                    <th>BOOKED AT</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allReservations)): ?>
                                    <tr><td colspan="8" style="text-align: center;">No reservations found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($allReservations as $reservation): ?>
                                        <?php
                                            $statusClass = strtolower($reservation['status']);
                                            $fullReservationData = [
                                                'reservation_id' => $reservation['reservation_id'], 'user_id' => $reservation['user_id'] ?? 'N/A',
                                                'res_date' => $reservation['res_date'], 'res_time' => $reservation['res_time'],
                                                'num_guests' => $reservation['num_guests'], 'res_name' => $reservation['res_name'],
                                                'res_phone' => $reservation['res_phone'], 'res_email' => $reservation['res_email'],
                                                'status' => $reservation['status'], 'created_at' => $reservation['created_at']
                                            ];
                                            $fullReservationJson = htmlspecialchars(json_encode($fullReservationData), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr data-reservation-id="<?php echo $reservation['reservation_id']; ?>" data-full-reservation='<?php echo $fullReservationJson; ?>'>
                                            <td>
                                                <div class="customer-info">
                                                    <img src="images/default_avatar.png" alt="Customer Avatar" class="customer-avatar">
                                                    <div class="customer-name-email">
                                                        <strong><?php echo htmlspecialchars($reservation['res_name']); ?></strong><br>
                                                        <small><?php echo htmlspecialchars($reservation['res_email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($reservation['res_date']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['res_time']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['num_guests']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['res_phone']); ?></td>
                                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($reservation['status']); ?></span></td>
                                            <td><?php echo htmlspecialchars($reservation['created_at']); ?></td>
                                            <td class="actions">
                                                <button class="btn btn-small view-edit-btn">View/Edit</button>
                                                <button class="btn btn-small delete-btn">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

            <div id="reservationModal" class="modal">
                <div class="modal-content">
                    <span class="close-button">&times;</span>
                    <h2>Reservation Details & Edit</h2>
                    <form id="editReservationForm">
                        <input type="hidden" id="modalReservationId" name="reservation_id">
                        <div class="form-group"><label for="modalResName">Customer Name:</label><input type="text" id="modalResName" name="res_name" required></div>
                        <div class="form-group"><label for="modalResEmail">Email:</label><input type="email" id="modalResEmail" name="res_email" required></div>
                        <div class="form-group"><label for="modalResPhone">Phone:</label><input type="tel" id="modalResPhone" name="res_phone"></div>
                        <div class="form-group"><label for="modalResDate">Date:</label><input type="date" id="modalResDate" name="res_date" required></div>
                        <div class="form-group"><label for="modalResTime">Time:</label><input type="time" id="modalResTime" name="res_time" required></div>
                        <div class="form-group"><label for="modalNumGuests">Number of Guests:</label><input type="number" id="modalNumGuests" name="num_guests" min="1" required></div>
                        <div class="form-group"><label for="modalStatus">Status:</label><select id="modalStatus" name="status"><option value="Pending">Pending</option><option value="Confirmed">Confirmed</option><option value="Cancelled">Cancelled</option><option value="Declined">Declined</option></select></div>
                        <div class="form-group"><label for="modalCreatedAt">Booked At:</label><input type="text" id="modalCreatedAt" name="created_at" readonly></div>
                        <div class="modal-actions">
                            <button type="submit" class="btn modal-save-btn">Save Changes</button>
                            <button type="button" class="btn modal-delete-btn">Delete Reservation</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="availabilityModal" class="modal">
                <div class="modal-content">
                    <span class="close-button">&times;</span>
                    <h2>Check Table Availability</h2>
                    <form id="checkAvailabilityForm">
                        <div class="form-group"><label for="checkDate">Date:</label><input type="date" id="checkDate" name="check_date" required></div>
                        <div class="form-group"><label for="checkTime">Time:</label><input type="time" id="checkTime" name="check_time" required></div>
                        <div class="form-group"><label for="checkNumGuests">Number of Guests:</label><input type="number" id="checkNumGuests" name="check_num_guests" min="1" required></div>
                        <button type="submit" class="btn btn-primary">Check Availability</button>
                    </form>
                    <div id="availabilityResult" class="availability-result" style="display: none;"></div>
                </div>
            </div>

            <div id="confirmDeleteModal" class="modal">
                <div class="modal-content" style="max-width: 500px;">
                    <span class="close-button">&times;</span>
                    <h2>Confirm Deletion</h2>
                    <p>Are you sure you want to move this reservation to the deletion history? It will be permanently deleted after 30 days.</p>
                    <div class="modal-actions">
                        <button type="button" class="btn" id="cancelDeleteBtn" style="background-color: #6c757d; color: white;">Cancel</button>
                        <button type="button" class="btn delete-btn" id="confirmDeleteBtn">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inlined JavaScript to override cache
        document.addEventListener('DOMContentLoaded', () => {
            const reservationModal = document.getElementById('reservationModal');
            const editReservationForm = document.getElementById('editReservationForm');
            const modalReservationId = document.getElementById('modalReservationId');
            const modalResName = document.getElementById('modalResName');
            const modalResEmail = document.getElementById('modalResEmail');
            const modalResPhone = document.getElementById('modalResPhone');
            const modalResDate = document.getElementById('modalResDate');
            const modalResTime = document.getElementById('modalResTime');
            const modalNumGuests = document.getElementById('modalNumGuests');
            const modalStatus = document.getElementById('modalStatus');
            const modalCreatedAt = document.getElementById('modalCreatedAt');
            const modalDeleteBtn = document.querySelector('.modal-delete-btn');
            const availabilityModal = document.getElementById('availabilityModal');
            const checkOverallAvailabilityBtn = document.querySelector('.check-overall-availability-btn');
            const checkAvailabilityForm = document.getElementById('checkAvailabilityForm');
            const availabilityResultDiv = document.getElementById('availabilityResult');
            const checkDateInput = document.getElementById('checkDate');
            const checkTimeInput = document.getElementById('checkTime');
            const checkNumGuestsInput = document.getElementById('checkNumGuests');
            const confirmDeleteModal = document.getElementById('confirmDeleteModal');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            
            let itemToDelete = { id: null, element: null };

            const closeButtons = document.querySelectorAll('.modal .close-button');
            if (closeButtons) {
                closeButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        button.closest('.modal').style.display = 'none';
                    });
                });
            }

            window.addEventListener('click', (event) => {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            });

            function openConfirmDeleteModal(reservationId, rowElement) {
                itemToDelete.id = reservationId;
                itemToDelete.element = rowElement;
                confirmDeleteModal.style.display = 'flex';
            }

            function closeConfirmDeleteModal() {
                confirmDeleteModal.style.display = 'none';
                itemToDelete.id = null;
                itemToDelete.element = null;
            }

            if(confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', () => {
                    if (itemToDelete.id) {
                        deleteReservation(itemToDelete.id, itemToDelete.element);
                        closeConfirmDeleteModal();
                    }
                });
            }
            if(cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', closeConfirmDeleteModal);
            }

            function openReservationModal(reservationData) {
                modalReservationId.value = reservationData.reservation_id;
                modalResName.value = reservationData.res_name;
                modalResEmail.value = reservationData.res_email;
                modalResPhone.value = reservationData.res_phone;
                modalResDate.value = reservationData.res_date;
                modalResTime.value = reservationData.res_time;
                modalNumGuests.value = reservationData.num_guests;
                modalStatus.value = reservationData.status;
                modalCreatedAt.value = reservationData.created_at;
                reservationModal.style.display = 'flex';
            }

            const reservationTableBody = document.querySelector('table tbody');
            if (reservationTableBody) {
                reservationTableBody.addEventListener('click', (event) => {
                    const target = event.target;
                    const row = target.closest('tr');
                    if (!row) return;

                    if (target.classList.contains('view-edit-btn')) {
                        const fullReservationJson = row.dataset.fullReservation;
                        try {
                            const reservationData = JSON.parse(fullReservationJson);
                            openReservationModal(reservationData);
                        } catch (e) { console.error("Error parsing reservation data:", e); }
                    } else if (target.classList.contains('delete-btn')) {
                        const reservationId = row.dataset.reservationId;
                        openConfirmDeleteModal(reservationId, row);
                    }
                });
            }

            if (editReservationForm) {
                editReservationForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const formData = new FormData(editReservationForm);
                    formData.append('action', 'update');
                    try {
                        const response = await fetch('update_reservation.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        if (result.success) {
                            location.reload();
                        } else { console.error('Error updating reservation: ' + result.message); }
                    } catch (error) { console.error('Error:', error); }
                });
            }

            if (modalDeleteBtn) {
                modalDeleteBtn.addEventListener('click', () => {
                    const reservationId = modalReservationId.value;
                    if (reservationId) {
                        const row = document.querySelector(`tr[data-reservation-id="${reservationId}"]`);
                        reservationModal.style.display = 'none';
                        openConfirmDeleteModal(reservationId, row);
                    }
                });
            }

            async function deleteReservation(reservationId, rowElement) {
                const formData = new URLSearchParams();
                formData.append('reservation_id', reservationId);
                formData.append('action', 'delete');
                try {
                    const response = await fetch('update_reservation.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        if (rowElement) {
                            rowElement.remove();
                        }
                    } else {
                        console.error('Error deleting reservation: ' + result.message);
                    }
                } catch (error) { console.error('Error:', error); }
            }

            const searchInput = document.getElementById('reservationSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', () => {
                    const filter = searchInput.value.toLowerCase();
                    const rows = reservationTableBody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const rowText = Array.from(row.children).map(td => td.textContent.toLowerCase()).join(' ');
                        row.style.display = rowText.includes(filter) ? '' : 'none';
                    });
                });
            }

            if (checkOverallAvailabilityBtn) {
                checkOverallAvailabilityBtn.addEventListener('click', () => {
                    availabilityModal.style.display = 'flex';
                    availabilityResultDiv.style.display = 'none';
                    availabilityResultDiv.innerHTML = '';
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = (now.getMonth() + 1).toString().padStart(2, '0');
                    const day = now.getDate().toString().padStart(2, '0');
                    const hours = (now.getHours() + 1).toString().padStart(2, '0');
                    const minutes = '00';
                    checkDateInput.value = `${year}-${month}-${day}`;
                    checkTimeInput.value = `${hours}:${minutes}`;
                    checkNumGuestsInput.value = '2';
                });
            }

            if (checkAvailabilityForm) {
                checkAvailabilityForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    availabilityResultDiv.style.display = 'block';
                    availabilityResultDiv.innerHTML = 'Checking...';
                    availabilityResultDiv.className = 'availability-result';
                    const formData = new FormData(checkAvailabilityForm);
                    try {
                        const response = await fetch('check_table_availability.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        availabilityResultDiv.style.display = 'flex';
                        if (result.success) {
                            availabilityResultDiv.classList.add(result.available ? 'available' : 'unavailable');
                            availabilityResultDiv.innerHTML = `<p>${result.available ? '✅' : '❌'} ${result.message}</p>
                                <p>Total Capacity: ${result.details.total_capacity} | Booked: ${result.details.booked_guests} | Remaining: ${result.details.remaining_capacity}</p>`;
                        } else {
                            availabilityResultDiv.classList.add('unavailable');
                            availabilityResultDiv.innerHTML = `<p>Error: ${result.message}</p>`;
                        }
                    } catch (error) {
                        console.error('Error checking availability:', error);
                        availabilityResultDiv.style.display = 'flex';
                        availabilityResultDiv.classList.add('unavailable');
                        availabilityResultDiv.innerHTML = `<p>An error occurred while checking availability.</p>`;
                    }
                });
            }
        });
    </script>
</body>
</html>