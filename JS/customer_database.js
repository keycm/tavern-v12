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