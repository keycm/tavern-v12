document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('historyTableBody');
    const searchInput = document.getElementById('historySearch');

    // --- Action Button Handlers (Restore / Purge) ---
    tableBody.addEventListener('click', async (event) => {
        const target = event.target;
        const row = target.closest('tr');
        if (!row) return;

        const logId = row.dataset.logId;

        if (target.classList.contains('restore-btn')) {
            if (confirm('Are you sure you want to restore this item?')) {
                await handleDeletionAction(logId, 'restore', row);
            }
        } else if (target.classList.contains('purge-btn')) {
            if (confirm('Are you sure you want to permanently delete this item? This action cannot be undone.')) {
                await handleDeletionAction(logId, 'purge', row);
            }
        }
    });

    // --- API Call to Manage Deletions ---
    async function handleDeletionAction(logId, action, rowElement) {
        const formData = new URLSearchParams();
        formData.append('log_id', logId);
        formData.append('action', action);

        try {
            const response = await fetch('manage_deletion.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                console.log(result.message);
                if (rowElement) {
                    rowElement.remove(); // Remove the row from the history table
                }
            } else {
                console.error('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // --- Search Functionality ---
    if (searchInput) {
        searchInput.addEventListener('keyup', () => {
            const filter = searchInput.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(filter) ? '' : 'none';
            });
        });
    }
});