<style>
    @media (max-width: 992px) {
        /* This rule is the key: it pushes the logo to the far left,
           which forces all other items in the header to group together
           on the far right. */
        header.main-header .header-content .logo {
            margin-right: auto !important;
        }

        /* This ensures the user profile/login buttons are visible and aligned. */
        header.main-header .header-content .header-right {
            display: flex !important;
            align-items: center;
        }

        /* This ensures the burger menu button is visible. */
        header.main-header .header-content .mobile-nav-toggle {
            display: block !important;
        }
    }

    /* Original styles from this file */
    .header-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ddd;
    }
    .header-content { max-width: 85%; padding-left: 20px; padding-right: 25px; }

    .user-profile-menu {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    #profileBtn {
        background-color: #fff;
        color: #333;
        font-size: 1em;
        border: 1px solid #ddd;
        cursor: pointer;
        border-radius: 50px;
        font-family: 'Mada', sans-serif;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        height: 42px;
        padding: 0 15px 0 5px;
    }
    .notification-button {
        background-color: transparent;
        border: 1px solid #ddd;
        color: #333;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: pointer;
        transition: background-color 0.3s ease, border-color 0.3s ease;
        top: 0 !important;
    }

    .notification-button .fa-bell { font-size: 1.1em; }
    .notification-button:hover { background-color: #f5f5f5; border-color: #ccc; }
    .notification-badge { position: absolute; top: 0px; right: 0px; background-color: #e74c3c; color: white; font-size: 0.7rem; border-radius: 50%; padding: 3px 6px; display: flex; justify-content: center; align-items: center; min-width: 18px; height: 18px; font-weight: bold; }
    #profileBtn:hover { background-color: #f5f5f5; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    #profileBtn .fa-user-circle { font-size: 1.2em; }
    .profile-dropdown { position: relative; display: inline-block; }
    #profileDropdownContent { display: none; position: absolute; background-color: #ffffff; min-width: 180px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.15); z-index: 2000; right: 0; border: 1px solid #eee; border-radius: 8px; margin-top: 8px; overflow: hidden; }
    #profileDropdownContent.show-dropdown { display: block; }
    #profileDropdownContent a { color: black; padding: 12px 16px; text-decoration: none; display: block; font-size: 1em; }
    #profileDropdownContent a:hover { background-color: #f1f1f1; }

    /* Notification Dropdown Styles */
    .notification-dropdown-content { display: none; position: absolute; background-color: #ffffff; min-width: 320px; max-width: 350px; box-shadow: 0px 8px 20px 0px rgba(0,0,0,0.15); border: 1px solid #eee; border-radius: 8px; margin-top: 8px; padding: 0; overflow: hidden; right: 0; z-index: 2000; }
    .notification-dropdown-content.show { display: block; }
    .notification-header { padding: 12px 16px; font-weight: bold; font-size: 1.1em; color: #333; border-bottom: 1px solid #eee; }
    .notification-body { max-height: 300px; overflow-y: auto; }
    .notification-item { display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid #f0f0f0; transition: background-color 0.2s ease; text-decoration: none; color: inherit; }
    .notification-item:last-child { border-bottom: none; }
    .notification-item:hover { background-color: #f8f9fa; }
    .notification-item .icon { margin-right: 15px; font-size: 1.2em; color: #007bff; }
    .notification-item .message { flex-grow: 1; font-size: 0.9em; line-height: 1.4; color: #555; }
    .notification-dismiss-btn { background: none; border: none; color: #aaa; font-size: 1.2em; cursor: pointer; padding: 5px; margin-left: 10px; line-height: 1; transition: color 0.2s ease; }
    .notification-dismiss-btn:hover { color: #333; }
    .no-notifications { text-align: center; color: #777; padding: 20px; font-size: 0.9em; }
</style>

<header class="main-header">
    <div class="header-content">
        <div class="logo">
            <div class="logo-main-line">
                <span>Tavern Publico</span>
            </div>
            <span class="est-year">EST ★ 2024</span>
        </div>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>

        <div class="header-right">
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                require_once 'db_connect.php';
                $db_link_for_header = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                if ($db_link_for_header) {
                    $current_user_id = $_SESSION['user_id'];
                    $sql_user_sync = "SELECT username, avatar FROM users WHERE user_id = ?";
                    if($stmt_sync = mysqli_prepare($db_link_for_header, $sql_user_sync)){
                        mysqli_stmt_bind_param($stmt_sync, "i", $current_user_id);
                        if(mysqli_stmt_execute($stmt_sync)){
                            $sync_result = mysqli_stmt_get_result($stmt_sync);
                            if($current_user_data = mysqli_fetch_assoc($sync_result)){
                                $_SESSION['username'] = $current_user_data['username'];
                                $_SESSION['avatar'] = $current_user_data['avatar'];
                            }
                        }
                        mysqli_stmt_close($stmt_sync);
                    }
                    mysqli_close($db_link_for_header);
                }

                $avatar_path = isset($_SESSION['avatar']) && file_exists($_SESSION['avatar']) ? $_SESSION['avatar'] : 'images/default_avatar.png';

                echo '<div class="user-profile-menu">';
                echo '  <div class="profile-dropdown">';
                echo '    <button id="profileBtn" class="profile-button">';
                echo '      <img src="' . htmlspecialchars($avatar_path) . '" alt="My Avatar" class="header-avatar">';
                echo '      ' . htmlspecialchars($_SESSION['username']);
                echo '      <i class="fas fa-caret-down" style="font-size: 0.8em;"></i>';
                echo '    </button>';
                echo '    <div id="profileDropdownContent" class="profile-dropdown-content">';
                echo '      <a href="profile.php">My Profile</a>';
                echo '      <a href="logout.php">Logout</a>';
                echo '    </div>';
                echo '  </div>';
                echo '  <div class="notification-dropdown">';
                echo '      <button class="notification-button" id="notificationBtn">';
                echo '          <i class="fas fa-bell"></i>';
                echo '          <span class="notification-badge" id="notificationCount" style="display: none;">0</span>';
                echo '      </button>';
                echo '      <div class="notification-dropdown-content" id="notificationDropdownContent"></div>';
                echo '  </div>';
                echo '</div>';
            } else {
                echo '<a href="#" class="btn header-button signin-button" id="openModalBtn">Sign In/Sign Up</a>';
            }
            ?>
        </div>

        <button class="mobile-nav-toggle" aria-label="Open navigation menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Elements ---
    const profileButton = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdownContent');
    const notificationButton = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdownContent');
    const notificationCountBadge = document.getElementById('notificationCount');
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const mainNav = document.querySelector('.main-nav');

    // --- NEW: ACTIVE NAV LINK LOGIC ---
    const navLinks = document.querySelectorAll('.main-nav a');
    const currentPageFile = window.location.pathname.split('/').pop();

    navLinks.forEach(link => {
        const linkFile = link.getAttribute('href').split('/').pop();
        // Set Home as active for index.php or an empty path
        if (linkFile === currentPageFile || (currentPageFile === '' && linkFile === 'index.php')) {
            link.classList.add('active-nav-link');
        }
    });
    // --- END NEW LOGIC ---

    // --- Mobile Nav Logic ---
    if (mobileNavToggle) {
        mobileNavToggle.addEventListener('click', function() {
            mainNav.classList.toggle('nav-open');
            this.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
        });
    }

    // --- Profile Dropdown Logic ---
    if (profileButton) {
        profileButton.addEventListener('click', function(event) {
            event.stopPropagation();
            notificationDropdown.classList.remove('show');
            profileDropdown.classList.toggle('show-dropdown');
        });
    }

    // --- Notification Bell Logic ---
    if (notificationButton) {
        notificationButton.addEventListener('click', function(event) {
            event.stopPropagation();
            profileDropdown.classList.remove('show-dropdown');
            notificationDropdown.classList.toggle('show');
        });
    }

    // --- Close Dropdowns When Clicking Elsewhere ---
    window.addEventListener('click', function() {
        if (profileDropdown && profileDropdown.classList.contains('show-dropdown')) {
            profileDropdown.classList.remove('show-dropdown');
        }
        if (notificationDropdown && notificationDropdown.classList.contains('show')) {
            notificationDropdown.classList.remove('show');
        }
    });

    // Stop clicks inside dropdowns from closing them
    if(profileDropdown) profileDropdown.addEventListener('click', e => e.stopPropagation());
    if(notificationDropdown) notificationDropdown.addEventListener('click', e => e.stopPropagation());


    // --- Notification Fetching Logic ---
    async function fetchNotifications() {
        try {
            const response = await fetch('get_notifications.php');
            const data = await response.json();
            notificationDropdown.innerHTML = '';
            const header = document.createElement('div');
            header.className = 'notification-header';
            header.textContent = 'Notifications';
            notificationDropdown.appendChild(header);
            const body = document.createElement('div');
            body.className = 'notification-body';
            if (data.success && data.notifications.length > 0) {
                notificationCountBadge.textContent = data.notifications.length;
                notificationCountBadge.style.display = 'flex';
                data.notifications.forEach(notif => {
                    const itemLink = document.createElement('a');
                    itemLink.href = notif.link || '#';
                    itemLink.className = 'notification-item';
                    itemLink.dataset.id = notif.id;
                    itemLink.dataset.type = notif.type;
                    const icon = document.createElement('i');
                    icon.className = 'icon fas ' + (notif.type === 'reservation' ? 'fa-calendar-alt' : 'fa-reply');
                    const message = document.createElement('span');
                    message.className = 'message';
                    message.textContent = notif.message;
                    const dismissBtn = document.createElement('button');
                    dismissBtn.className = 'notification-dismiss-btn';
                    dismissBtn.innerHTML = '&times;';
                    dismissBtn.dataset.id = notif.id;
                    dismissBtn.dataset.type = notif.type;
                    itemLink.appendChild(icon);
                    itemLink.appendChild(message);
                    itemLink.appendChild(dismissBtn);
                    body.appendChild(itemLink);
                });
            } else {
                notificationCountBadge.style.display = 'none';
                const noNotif = document.createElement('p');
                noNotif.className = 'no-notifications';
                noNotif.textContent = 'No new notifications.';
                body.appendChild(noNotif);
            }
            notificationDropdown.appendChild(body);
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }
    async function clearNotification(id, type, element) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('type', type);
        try {
            const response = await fetch('clear_notifications.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                element.remove();
                const remaining = notificationDropdown.querySelectorAll('.notification-item').length;
                notificationCountBadge.textContent = remaining;
                if (remaining === 0) {
                    notificationCountBadge.style.display = 'none';
                    const body = notificationDropdown.querySelector('.notification-body');
                    body.innerHTML = '<p class="no-notifications">No new notifications.</p>';
                }
            }
        } catch (error) {
            console.error('Error clearing notification:', error);
        }
    }

    if (notificationDropdown) {
        notificationDropdown.addEventListener('click', function(event) {
            const target = event.target;
            if (target.classList.contains('notification-dismiss-btn')) {
                event.preventDefault();
                event.stopPropagation();
                const notifItem = target.closest('.notification-item');
                clearNotification(target.dataset.id, target.dataset.type, notifItem);
            }
            else if (target.closest('.notification-item')) {
                event.preventDefault();
                const notifItem = target.closest('.notification-item');
                clearNotification(notifItem.dataset.id, notifItem.dataset.type, notifItem).then(() => {
                    if (notifItem.href && notifItem.href.slice(-1) !== '#') {
                        window.location.href = notifItem.href;
                    }
                });
            }
        });
    }

    if (notificationButton) {
        fetchNotifications();
        setInterval(fetchNotifications, 60000);
    }
});
</script>