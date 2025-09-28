// reservation.js

document.addEventListener('DOMContentLoaded', () => {
    const reservationModal = document.getElementById('reservationModal');
    const closeButtons = document.querySelectorAll('.modal .close-button');
    const editReservationForm = document.getElementById('editReservationForm');

    // Form fields in the modal
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

    // New Availability Modal Elements
    const availabilityModal = document.getElementById('availabilityModal');
    const checkOverallAvailabilityBtn = document.querySelector('.check-overall-availability-btn');
    const checkAvailabilityForm = document.getElementById('checkAvailabilityForm');
    const availabilityResultDiv = document.getElementById('availabilityResult');
    const checkDateInput = document.getElementById('checkDate');
    const checkTimeInput = document.getElementById('checkTime');
    const checkNumGuestsInput = document.getElementById('checkNumGuests');

    // Open Reservation Edit Modal and populate form fields
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

    // Close Modals (general function for both)
    if (closeButtons) {
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                reservationModal.style.display = 'none';
                availabilityModal.style.display = 'none'; // Close availability modal too
            });
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target === reservationModal || event.target === availabilityModal) {
            reservationModal.style.display = 'none';
            availabilityModal.style.display = 'none';
        }
    });

    // Handle View/Edit button click
    const reservationTableBody = document.querySelector('table tbody');
    if (reservationTableBody) {
        reservationTableBody.addEventListener('click', (event) => {
            const target = event.target;
            if (target.classList.contains('view-edit-btn')) {
                const row = target.closest('tr');
                if (row) {
                    const fullReservationJson = row.dataset.fullReservation;
                    try {
                        const reservationData = JSON.parse(fullReservationJson);
                        openReservationModal(reservationData);
                    } catch (e) {
                        console.error("Error parsing reservation data:", e);
                    }
                }
            } else if (target.classList.contains('delete-btn')) {
                // Direct delete from table row
                const row = target.closest('tr');
                if (row) {
                    const reservationId = row.dataset.reservationId;
                    if (confirm('Are you sure you want to delete this reservation permanently?')) {
                        deleteReservation(reservationId, row);
                    }
                }
            }
        });
    }

    // Handle Save Changes in Reservation Edit Modal
    if (editReservationForm) {
        editReservationForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // Prevent default form submission

            const formData = new FormData(editReservationForm);
            formData.append('action', 'update'); // Indicate an update action

            try {
                const response = await fetch('update_reservation.php', { // Target new update script
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    console.log('Reservation updated successfully!');
                    reservationModal.style.display = 'none';
                    // Optionally, update the table row directly without a full page reload
                    // For simplicity, let's just refresh the page for now
                    location.reload();
                } else {
                    console.error('Error updating reservation: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    // Handle Delete from Reservation Edit Modal
    if (modalDeleteBtn) {
        modalDeleteBtn.addEventListener('click', () => {
            const reservationId = modalReservationId.value;
            if (reservationId && confirm('Are you sure you want to delete this reservation permanently?')) {
                const row = document.querySelector(`tr[data-reservation-id="${reservationId}"]`);
                deleteReservation(reservationId, row);
                reservationModal.style.display = 'none'; // Close modal after delete attempt
            }
        });
    }

    // Function to send delete request
    async function deleteReservation(reservationId, rowElement) {
        const formData = new URLSearchParams();
        formData.append('reservation_id', reservationId);
        formData.append('action', 'delete'); // Indicate a delete action

        try {
            const response = await fetch('update_reservation.php', { // Target new update script
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                console.log('Reservation deleted successfully!');
                if (rowElement) {
                    rowElement.remove(); // Remove the row from the table
                }
            } else {
                console.error('Error deleting reservation: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // --- Search Functionality for All Reservations Page ---
    const searchInput = document.getElementById('reservationSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', () => {
            const filter = searchInput.value.toLowerCase();
            const rows = reservationTableBody.querySelectorAll('tr');

            rows.forEach(row => {
                // Search across all text content in the row
                const rowText = Array.from(row.children).map(td => td.textContent.toLowerCase()).join(' ');

                if (rowText.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // --- New: Overall Availability Check Logic ---
    if (checkOverallAvailabilityBtn) {
        checkOverallAvailabilityBtn.addEventListener('click', () => {
            availabilityModal.style.display = 'flex';
            availabilityResultDiv.style.display = 'none'; // Hide previous results
            availabilityResultDiv.innerHTML = ''; // Clear previous results

            // Optionally, pre-fill date/time with current date/next hour for convenience
            const now = new Date();
            const year = now.getFullYear();
            const month = (now.getMonth() + 1).toString().padStart(2, '0');
            const day = now.getDate().toString().padStart(2, '0');
            const hours = (now.getHours() + 1).toString().padStart(2, '0'); // Next hour
            const minutes = '00'; // Round to the hour

            checkDateInput.value = `${year}-${month}-${day}`;
            checkTimeInput.value = `${hours}:${minutes}`;
            checkNumGuestsInput.value = '2'; // Default to 2 guests
        });
    }

    if (checkAvailabilityForm) {
        checkAvailabilityForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            availabilityResultDiv.style.display = 'none'; // Hide results while checking
            availabilityResultDiv.innerHTML = 'Checking...';
            availabilityResultDiv.classList.remove('available', 'unavailable');

            const formData = new FormData(checkAvailabilityForm);

            try {
                const response = await fetch('check_table_availability.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                availabilityResultDiv.style.display = 'flex';
                if (result.success) {
                    if (result.available) {
                        availabilityResultDiv.classList.add('available');
                        availabilityResultDiv.innerHTML = `
                            <p>✅ ${result.message}</p>
                            <p>Total Capacity: ${result.details.total_capacity} guests</p>
                            <p>Booked Guests: ${result.details.booked_guests} guests</p>
                            <p>Remaining Capacity: ${result.details.remaining_capacity} guests</p>
                        `;
                    } else {
                        availabilityResultDiv.classList.add('unavailable');
                        availabilityResultDiv.innerHTML = `
                            <p>❌ ${result.message}</p>
                            <p>Total Capacity: ${result.details.total_capacity} guests</p>
                            <p>Booked Guests: ${result.details.booked_guests} guests</p>
                            <p>Remaining Capacity: ${result.details.remaining_capacity} guests</p>
                        `;
                    }
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