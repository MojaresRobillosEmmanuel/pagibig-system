// Function to load active employees
function loadActiveEmployees() {
    const activeEmployeesList = document.getElementById('activeEmployeesList');
    const loadingSpinner = document.getElementById('loadingSpinner');

    // Show loading spinner
    if (loadingSpinner) {
        activeEmployeesList.innerHTML = loadingSpinner.outerHTML;
    }

    // Fetch active employees
    fetch('includes/get_active_employees.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log
            
            if (data.success) {
                if (data.data.length === 0) {
                    activeEmployeesList.innerHTML = '<div class="list-group-item text-center">No active employees found</div>';
                    return;
                }

                // Clear loading spinner
                activeEmployeesList.innerHTML = '';

                // Sort employees by last name
                data.data.sort((a, b) => a.last_name.localeCompare(b.last_name));

                // Add each employee to the list
                data.data.forEach(employee => {
                    const listItem = document.createElement('div');
                    listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

                    // Create employee name display
                    const nameSpan = document.createElement('div');
                    nameSpan.innerHTML = `
                        <strong>${employee.last_name}, ${employee.first_name} ${employee.middle_name || ''}</strong><br>
                        <small class="text-muted">
                            ID: ${employee.id_number} | Pag-IBIG: ${employee.pagibig_number}
                        </small>
                    `;

                    // Create buttons container
                    const buttonsDiv = document.createElement('div');
                    buttonsDiv.className = 'btn-group';

                    // Deactivate button
                    const deactivateBtn = document.createElement('button');
                    deactivateBtn.className = 'btn btn-danger btn-sm';
                    deactivateBtn.innerHTML = '<i class="fas fa-user-times"></i>';
                    deactivateBtn.title = 'Deactivate';
                    deactivateBtn.onclick = () => deactivateEmployee(employee.id, employee.last_name + ', ' + employee.first_name);

                    // Add button to container
                    buttonsDiv.appendChild(deactivateBtn);

                    // Add elements to list item
                    listItem.appendChild(nameSpan);
                    listItem.appendChild(buttonsDiv);
                    activeEmployeesList.appendChild(listItem);
                });
            } else {
                throw new Error(data.message || 'Failed to load employees');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            activeEmployeesList.innerHTML = `
                <div class="list-group-item text-danger">
                    Failed to load employees. Error: ${error.message}
                </div>
            `;
        });
}

// Function to deactivate employee with improved error handling and UI feedback
function deactivateEmployee(employeeId, employeeName) {
    Swal.fire({
        title: 'Confirm Deactivation',
        text: `Are you sure you want to deactivate ${employeeName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, deactivate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Deactivating...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('includes/deactivate_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${employeeId}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Employee deactivated successfully',
                        timer: 1500
                    });
                    
                    // Reload both active and inactive lists if they exist
                    loadActiveEmployees();
                    if (typeof loadInactiveEmployees === 'function') {
                        loadInactiveEmployees();
                    }
                    
                    // Refresh main table if it exists
                    if (typeof fetchEmployees === 'function') {
                        fetchEmployees();
                    }
                } else {
                    throw new Error(data.message || 'Failed to deactivate employee');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to deactivate employee'
                });
            });
        }
    });
}

// Add event listeners when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load active employees when the modal is shown
    const modal = document.getElementById('activeEmployeesModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', loadActiveEmployees);
    }

    // Reload employees when a new employee is registered
    window.addEventListener('employeeRegistered', loadActiveEmployees);
});
