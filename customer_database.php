<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in AND is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch all non-admin users from the database
$users = [];
$sql = "SELECT user_id, username, email, created_at, is_verified FROM users WHERE is_admin = 0 ORDER BY created_at DESC";

if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    mysqli_free_result($result);
} else {
    error_log("Customer Database page error: " . mysqli_error($link));
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tavern Publico - Customer Database</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                    <li class="menu-item">
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
                    <li class="menu-item active">
                        <a href="customer_database.php"><i class="material-icons">settings</i> Customer Database</a>
                    </li>
                    <li class="menu-item">
                        <a href="reports.php"><i class="material-icons">analytics</i>Reservation Reports</a>
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
                        <span><?= $_SESSION['username']; ?></span>
                        <span class="admin-role">Admin</span>
                    </div>
                </div>
            </header>

            <main class="dashboard-main-content">
                <div class="reservation-page-header">
                    <h1>Customer Database</h1>
                    <input type="text" id="userSearch" class="search-input" placeholder="Search customers...">
                    <button id="addNewUserBtn" class="btn btn-primary" style="background-color: #28a745;">Add New Customer</button>
                </div>

                <section class="all-reservations-section">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>USER ID</th>
                                    <th>USERNAME</th>
                                    <th>EMAIL</th>
                                    <th>DATE JOINED</th>
                                    <th>STATUS</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <?php if (empty($users)): ?>
                                    <tr><td colspan="6" style="text-align: center;">No customers found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr data-user-id="<?= $user['user_id']; ?>"
                                            data-username="<?= htmlspecialchars($user['username'], ENT_QUOTES); ?>"
                                            data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES); ?>">
                                            <td><?= sprintf('%04d', $user['user_id']); ?></td>
                                            <td><?= htmlspecialchars($user['username']); ?></td>
                                            <td><?= htmlspecialchars($user['email']); ?></td>
                                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?></td>
                                            <td>
                                                <?php if ($user['is_verified']): ?>
                                                    <span class="status-badge confirmed">Verified</span>
                                                <?php else: ?>
                                                    <span class="status-badge pending">Not Verified</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="actions">
                                                <?php if (!$user['is_verified']): ?>
                                                    <button class="btn btn-small verify-btn" style="background-color: #17a2b8;">Verify</button>
                                                <?php endif; ?>
                                                <button class="btn btn-small view-edit-btn">Edit</button>
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

            <div id="userModal" class="modal">
                <div class="modal-content">
                    <span class="close-button">&times;</span>
                    <h2 id="modalTitle">Add New Customer</h2>
                    <form id="userForm">
                        <input type="hidden" id="userId" name="user_id">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password">
                            <small id="passwordHelp">Leave blank to keep the current password.</small>
                        </div>
                        <div class="modal-actions">
                            <button type="submit" class="btn modal-save-btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const userModal = document.getElementById('userModal');
        const closeModalButton = userModal.querySelector('.close-button');
        const addNewUserBtn = document.getElementById('addNewUserBtn');
        const userForm = document.getElementById('userForm');
        const usersTableBody = document.getElementById('usersTableBody');
        const userSearchInput = document.getElementById('userSearch');

        const modalTitle = document.getElementById('modalTitle');
        const userIdInput = document.getElementById('userId');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordHelp = document.getElementById('passwordHelp');

        // --- Modal Handling ---

        const openModalForEdit = (user) => {
            userForm.reset();
            modalTitle.textContent = 'Edit Customer';
            userIdInput.value = user.id;
            usernameInput.value = user.username;
            emailInput.value = user.email;
            passwordInput.placeholder = "New password (optional)";
            passwordHelp.style.display = 'block';
            passwordInput.required = false;
            userModal.style.display = 'flex';
        };

        const openModalForAdd = () => {
            userForm.reset();
            modalTitle.textContent = 'Add New Customer';
            userIdInput.value = '';
            passwordInput.placeholder = "Create a password";
            passwordHelp.style.display = 'none';
            passwordInput.required = true;
            userModal.style.display = 'flex';
        };

        const closeModal = () => {
            userModal.style.display = 'none';
        };

        addNewUserBtn.addEventListener('click', openModalForAdd);
        closeModalButton.addEventListener('click', closeModal);
        window.addEventListener('click', (event) => {
            if (event.target === userModal) {
                closeModal();
            }
        });


        // --- CRUD and Verification Operations via AJAX ---

        // Edit, Delete, and Verify button clicks
        usersTableBody.addEventListener('click', (event) => {
            const target = event.target;
            const row = target.closest('tr');
            if (!row) return;

            const userId = row.dataset.userId;

            if (target.classList.contains('view-edit-btn')) {
                const userData = {
                    id: userId,
                    username: row.dataset.username,
                    email: row.dataset.email
                };
                openModalForEdit(userData);
            }

            if (target.classList.contains('delete-btn')) {
                if (confirm(`Are you sure you want to delete this user (${row.dataset.username})? This action cannot be undone.`)) {
                    deleteUser(userId);
                }
            }
            
            // This is the specific logic for the verify button
            if (target.classList.contains('verify-btn')) {
                if (confirm(`Are you sure you want to verify this user (${row.dataset.username})?`)) {
                    verifyUser(userId, target);
                }
            }
        });

        // Form submission for Add/Edit
        userForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(userForm);
            formData.append('action', 'saveUser');

            try {
                const response = await fetch('manage_user.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                console.log(result.message);
                if (result.success) {
                    closeModal();
                    location.reload(); // Easiest way to show changes
                }
            } catch (error) {
                console.error('Error submitting form:', error);
            }
        });

        // Delete user function
        async function deleteUser(id) {
            const formData = new FormData();
            formData.append('action', 'deleteUser');
            formData.append('user_id', id);

            try {
                const response = await fetch('manage_user.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                console.log(result.message);
                if (result.success) {
                    location.reload(); // Easiest way to show changes
                }
            } catch (error) {
                console.error('Error deleting user:', error);
            }
        }
        
        // Verify user function
        async function verifyUser(id, buttonElement) {
            const formData = new FormData();
            formData.append('user_id', id);

            try {
                const response = await fetch('verify_user.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                console.log(result.message);
                if (result.success) {
                    // Update the UI without reloading
                    const statusCell = buttonElement.closest('tr').querySelector('.status-badge');
                    statusCell.classList.remove('pending');
                    statusCell.classList.add('confirmed');
                    statusCell.textContent = 'Verified';
                    buttonElement.remove(); // Remove the verify button
                }
            } catch (error) {
                console.error('Error verifying user:', error);
            }
        }

        // --- Search Functionality ---
        userSearchInput.addEventListener('keyup', () => {
            const filter = userSearchInput.value.toLowerCase();
            const rows = usersTableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const username = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                if (username.includes(filter) || email.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    </script>
</body>
</html>