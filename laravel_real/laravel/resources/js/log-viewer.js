document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter-button');
    const refreshButton = document.getElementById('refreshButton');
    const lineCountElement = document.getElementById('lineCount');
    const logBody = document.querySelector('.log-body');

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterLogs();
    });

    // Filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            this.classList.add('active');

            // Filter logs
            filterLogs();
        });
    });

    const clearLogsButton = document.getElementById('clearLogsButton');
    if (clearLogsButton) {
        clearLogsButton.addEventListener('click', function() {
            swal({
                title: 'Warning',
                text: 'Are you sure you want to clear the logs? This action cannot be undone.',
                icon: 'warning',
                buttons: ['Cancel', 'Yes, clear logs'],
                dangerMode: true,
            }).then((value) => {
                if (value) {
                    // Proceed with the action
                    const clearLogsUrl = this.getAttribute('data-url');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    $.ajax({
                        url: clearLogsUrl,
                        type: 'GET',
                        data: {
                            _token: csrfToken
                        },
                        success: function(response) {
                            // Show success message
                            swal({
                                title: 'Success',
                                text: 'Logs cleared successfully.',
                                icon: 'success',
                                timer: 2000,
                                buttons: false,
                            });
                            // Optionally, refresh the page or update the logs display
                            location.reload();
                        },
                        error: function(xhr) {
                            // Show error message
                            swal({
                                title: 'Error',
                                text: 'Could not clear logs. Please try again later.',
                                icon: 'error',
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    });
                }
            });
        });
    }

    // Combined filtering function
    function filterLogs() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeFilter = document.querySelector('.filter-button.active').dataset.filter;
        const logLines = document.querySelectorAll('.log-line');
        let visibleCount = 0;

        logLines.forEach(line => {
            const content = line.querySelector('.line-content').textContent.toLowerCase();
            const lineType = line.dataset.type;

            // Check if line matches both search term and filter
            const matchesSearch = searchTerm === '' || content.includes(searchTerm);
            const matchesFilter = activeFilter === 'all' || lineType === activeFilter;

            if (matchesSearch && matchesFilter) {
                line.style.display = 'flex';
                visibleCount++;
            } else {
                line.style.display = 'none';
            }
        });

        // Update counter
        const filterName = document.querySelector('.filter-button.active').textContent.toLowerCase();
        const displayText = searchTerm ?
            `Showing ${visibleCount} matching ${filterName === 'all' ? 'logs' : filterName}` :
            `Showing ${visibleCount} ${filterName === 'all' ? 'logs' : filterName}`;

        lineCountElement.textContent = displayText;
    }

    // Refresh button functionality
    refreshButton.addEventListener('click', function(e) {
        e.preventDefault();

        // Show loading indicator
        this.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
            </svg>
            Refreshing...
        `;

        // Fetch new logs via AJAX
        fetch('/admin/logs/ajax', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update log content
            const logTable = document.querySelector('.log-table');
            logTable.innerHTML = data.html;

            // Update the log count
            lineCountElement.textContent = `Showing ${data.count} logs`;

            // Reset button text
            this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <polyline points="23 20 23 14 17 14"></polyline>
                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
                </svg>
                Refresh
            `;

            // Scroll to bottom
            logBody.scrollTop = logBody.scrollHeight;

            // Re-apply any active filters
            filterLogs();
        })
        .catch(error => {
            console.error('Error refreshing logs:', error);
            this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <polyline points="23 20 23 14 17 14"></polyline>
                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
                </svg>
                Refresh
            `;
        });
    });

    // Initialize on page load
    // Initial count of logs
    const logCount = document.querySelectorAll('.log-line').length;
    lineCountElement.textContent = `Showing ${logCount} logs`;

    // Scroll to bottom initially
    logBody.scrollTop = logBody.scrollHeight;
});
