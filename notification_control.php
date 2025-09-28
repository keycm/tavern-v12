<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in AND is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

// Fetch all contact messages
$messages = [];
$sql_messages = "SELECT * FROM contact_messages ORDER BY created_at DESC";
if ($result = mysqli_query($link, $sql_messages)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
}

// Fetch all testimonials
$testimonials = [];
$sql_testimonials = "SELECT t.*, u.username FROM testimonials t JOIN users u ON t.user_id = u.user_id ORDER BY t.created_at DESC";
if ($result = mysqli_query($link, $sql_testimonials)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $testimonials[] = $row;
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tavern Publico - Notification Control</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* --- STYLES FOR TABS --- */
        .tabs { overflow: hidden; border-bottom: 1px solid #dee2e6; margin-bottom: 25px; }
        .tab-link { background-color: #f8f9fa; border: 1px solid transparent; border-bottom: none; border-radius: 8px 8px 0 0; cursor: pointer; float: left; font-size: 16px; font-weight: 600; outline: none; padding: 12px 20px; margin-right: 5px; transition: background-color 0.3s ease, color 0.3s ease; color: #495057; }
        .tab-link:hover { background-color: #e9ecef; color: #007bff; }
        .tab-link.active { background-color: #ffffff; color: #007bff; border-color: #dee2e6 #dee2e6 #fff; position: relative; top: 1px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.5s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
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
                    <li class="menu-item"><a href="admin.php"><i class="material-icons">dashboard</i> Dashboard</a></li>
                    <li class="menu-item"><a href="update.php"><i class="material-icons">file_upload</i> Upload Management</a></li>
                    <li class="menu-item"><a href="reservation.php"><i class="material-icons">event_note</i> Reservation</a></li>
                </ul>
                <div class="user-management-title">User Management</div>
                <ul class="sidebar-menu user-management-menu">
                    <li class="menu-item active"><a href="notification_control.php"><i class="material-icons">notifications</i> Notification Control</a></li>
                    <li class="menu-item"><a href="table_management.php"><i class="material-icons">table_restaurant</i> Table Management</a></li>
                    <li class="menu-item"><a href="customer_database.php"><i class="material-icons">people</i> Customer Database</a></li>
                    <li class="menu-item"><a href="reports.php"><i class="material-icons">analytics</i>Reservation Reports</a></li>
                    <li class="menu-item"><a href="deletion_history.php"><i class="material-icons">history</i> Deletion History</a></li>
                    <li class="menu-item"><a href="logout.php"><i class="material-icons">logout</i> Log out</a></li>
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
                <div class="tabs">
                    <button class="tab-link active" onclick="openTab(event, 'messages')">Contact Messages</button>
                    <button class="tab-link" onclick="openTab(event, 'testimonials')">Guest Testimonials</button>
                </div>

                <div id="messages" class="tab-content">
                    <div class="reservation-page-header">
                        <h1>Contact Form Messages</h1>
                        <input type="text" id="messageSearch" class="search-input" placeholder="Search messages...">
                    </div>
                    <section class="all-reservations-section">
                        <div class="table-responsive">
                            <table id="messagesTable">
                                <thead>
                                    <tr>
                                        <th>CUSTOMER</th>
                                        <th>SUBJECT</th>
                                        <th>MESSAGE</th>
                                        <th>RECEIVED</th>
                                        <th>STATUS</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($messages)): ?>
                                        <tr><td colspan="6" style="text-align: center;">No messages found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($messages as $message): ?>
                                            <tr data-id="<?php echo $message['id']; ?>" 
                                                data-email="<?php echo htmlspecialchars($message['email']); ?>"
                                                data-subject="<?php echo htmlspecialchars($message['subject']); ?>"
                                                data-messagebody="<?php echo htmlspecialchars($message['message']); ?>">
                                                <td>
                                                    <strong><?php echo htmlspecialchars($message['name']); ?></strong><br>
                                                    <small><?php echo htmlspecialchars($message['email']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                                <td class="truncate-text" title="<?php echo htmlspecialchars($message['message']); ?>"><?php echo htmlspecialchars($message['message']); ?></td>
                                                <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo !empty($message['replied_at']) ? 'confirmed' : 'pending'; ?>">
                                                        <?php echo !empty($message['replied_at']) ? 'Replied' : 'New'; ?>
                                                    </span>
                                                </td>
                                                <td class="actions">
                                                    <button class="btn btn-small reply-message-btn">Reply</button>
                                                    <button class="btn btn-small delete-message-btn">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <div id="testimonials" class="tab-content">
                    <div class="reservation-page-header">
                        <h1>Guest Testimonials</h1>
                        <input type="text" id="testimonialSearch" class="search-input" placeholder="Search testimonials...">
                    </div>
                    <section class="all-reservations-section">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>USERNAME</th>
                                        <th>RATING</th>
                                        <th>COMMENT</th>
                                        <th>FEATURED</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody id="testimonialsTableBody">
                                    <?php if (empty($testimonials)): ?>
                                        <tr><td colspan="5" style="text-align: center;">No testimonials found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($testimonials as $testimonial): ?>
                                            <tr data-id="<?php echo $testimonial['id']; ?>">
                                                <td><?php echo htmlspecialchars($testimonial['username']); ?></td>
                                                <td><?php echo str_repeat('★', $testimonial['rating']) . str_repeat('☆', 5 - $testimonial['rating']); ?></td>
                                                <td class="truncate-text" title="<?php echo htmlspecialchars($testimonial['comment']); ?>"><?php echo htmlspecialchars($testimonial['comment']); ?></td>
                                                <td>
                                                    <button class="btn btn-small feature-btn" data-featured="<?php echo $testimonial['is_featured']; ?>">
                                                        <?php echo $testimonial['is_featured'] ? 'Yes' : 'No'; ?>
                                                    </button>
                                                </td>
                                                <td class="actions">
                                                    <button class="btn btn-small delete-testimonial-btn">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <div id="replyModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Reply to Message</h2>
            <form id="replyMessageForm">
                <input type="hidden" id="replyMessageId" name="message_id">
                <input type="hidden" id="replyCustomerEmail" name="customer_email">
                <input type="hidden" id="replyOriginalSubject" name="original_subject">
                <div class="form-group">
                    <label>Original Message:</label>
                    <div id="originalMessage" style="background-color: #f0f0f0; padding: 10px; border-radius: 5px; min-height: 80px;"></div>
                </div>
                <div class="form-group">
                    <label for="replyText">Your Reply:</label>
                    <textarea id="replyText" name="reply_text" rows="6" required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn modal-save-btn">Send Reply</button>
                </div>
            </form>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.className += " active";
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('.tab-link.active').click();

        // Search functionality
        $('#messageSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#messagesTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        $('#testimonialSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#testimonialsTableBody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Testimonial actions
        $('#testimonialsTableBody').on('click', '.feature-btn', function() { /* ... existing code ... */ });
        $('#testimonialsTableBody').on('click', '.delete-testimonial-btn', function() { /* ... existing code ... */ });
        
        // Message Actions
        const replyModal = document.getElementById('replyModal');
        const closeBtn = replyModal.querySelector('.close-button');

        $('#messagesTable').on('click', '.reply-message-btn', function() {
            var row = $(this).closest('tr');
            $('#replyMessageId').val(row.data('id'));
            $('#replyCustomerEmail').val(row.data('email'));
            $('#replyOriginalSubject').val(row.data('subject'));
            $('#originalMessage').text(row.data('messagebody'));
            replyModal.style.display = 'flex';
        });

        $('#messagesTable').on('click', '.delete-message-btn', function() {
            var row = $(this).closest('tr');
            var messageId = row.data('id');
            if (confirm('Are you sure you want to delete this message?')) {
                $.ajax({
                    url: 'manage_message.php',
                    type: 'POST',
                    data: { action: 'delete', message_id: messageId },
                    success: function(response) {
                        alert(response.message);
                        if (response.success) {
                            row.remove();
                        }
                    }
                });
            }
        });

        closeBtn.onclick = function() {
            replyModal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == replyModal) {
                replyModal.style.display = 'none';
            }
        }

        $('#replyMessageForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize() + '&action=reply';
            $.ajax({
                url: 'manage_message.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    if (response.success) {
                        replyModal.style.display = 'none';
                        location.reload(); // Easiest way to update status
                    }
                }
            });
        });
    });
</script>
</body>
</html>