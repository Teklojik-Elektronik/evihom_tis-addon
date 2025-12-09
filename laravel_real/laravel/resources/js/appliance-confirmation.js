$(document).ready(function() {
    $(document).on('click', '.auto-create-appliances-btn', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');

        // First request to get the count
        fetch(url + '?check=true', {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                title: 'Warning!',
                html: `You are about to delete <strong>${data.count}</strong> existing appliance records.<br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Continue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url + '?confirmed=true';
                }
            });
        });
    });
});
