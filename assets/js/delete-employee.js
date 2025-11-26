// Function to delete/remove employee
window.deleteEmployee = window.removeSelectedEmployee = function(pagibigNo) {
    // First find the employee's name from the table
    const row = document.querySelector(`button[onclick*="${pagibigNo}"]`).closest('tr');
    const lastName = row.cells[2].textContent.trim();

    // First confirmation
    if (!confirm(`Are you sure you want to remove employee: ${lastName}?`)) {
        return;
    }

    // Second confirmation
    Swal.fire({
        title: 'Final Confirmation',
        text: `You are about to remove ${lastName}. Do you want to continue?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, remove it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Proceed with deletion
            fetch('process_selected_employees.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    pagibig_no: pagibigNo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.fetchEmployees();
                } else {
                    console.error('Error removing employee:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
};
