$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: 'get_reservations.php',
        dayClick: function(date, jsEvent, view) {
            $('#block_date').val(date.format());
        }
    });

    $('#blockDateForm').on('submit', function(e) {
        e.preventDefault();
        var block_date = $('#block_date').val();

        $.ajax({
            url: 'blocked_dates.php',
            type: 'POST',
            data: {
                action: 'block',
                block_date: block_date
            },
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    $(document).on('click', '.unblock-date-btn', function() {
        var dateItem = $(this).closest('.blocked-date-item');
        var date_to_unblock = dateItem.find('span').text();

        $.ajax({
            url: 'blocked_dates.php',
            type: 'POST',
            data: {
                action: 'unblock',
                block_date: date_to_unblock
            },
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});