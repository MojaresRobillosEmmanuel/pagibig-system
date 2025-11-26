// Function to load inactive employees from the Contribution system
function loadInactiveEmployees() {
    const inactiveEmployeesList = document.getElementById('inactiveEmployeesList');
    
    if (!inactiveEmployeesList) {
        console.error('Inactive employees list element not found');
        return;
    }

    // Show loading state
    inactiveEmployeesList.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>';

    fetch('includes/get_inactive_employees.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Inactive employees data:', data);
            
            if (data.success) {
                if (data.data.length === 0) {
                    inactiveEmployeesList.innerHTML = '<div class="list-group-item text-center">No inactive employees found</div>';
                    return;
                }

                // Clear loading state
                inactiveEmployeesList.innerHTML = '';

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

                    // Reactivate button
                    const reactivateBtn = document.createElement('button');
                    reactivateBtn.className = 'btn btn-success btn-sm';
                    reactivateBtn.innerHTML = '<i class="fas fa-redo"></i>';
                    reactivateBtn.title = 'Reactivate';
                    reactivateBtn.onclick = () => reactivateEmployee(employee.id, employee.last_name + ', ' + employee.first_name);

                    // Add button to container
                    buttonsDiv.appendChild(reactivateBtn);

                    // Add elements to list item
                    listItem.appendChild(nameSpan);
                    listItem.appendChild(buttonsDiv);
                    inactiveEmployeesList.appendChild(listItem);
                });
            } else {
                throw new Error(data.message || 'Failed to load inactive employees');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            inactiveEmployeesList.innerHTML = `
                <div class="list-group-item text-danger">
                    Failed to load inactive employees. Error: ${error.message}
                </div>
            `;
        });
}

// Function to reactivate employee
function reactivateEmployee(employeeId, employeeName) {
    Swal.fire({
        title: 'Confirm Reactivation',
        text: `Are you sure you want to reactivate ${employeeName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, reactivate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Reactivating...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('includes/reactivate_employee.php', {
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
                        text: data.message || 'Employee reactivated successfully',
                        timer: 1500
                    });
                    
                    // Reload both lists
                    loadInactiveEmployees();
                    if (typeof loadActiveEmployees === 'function') {
                        loadActiveEmployees();
                    }
                } else {
                    throw new Error(data.message || 'Failed to reactivate employee');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to reactivate employee'
                });
            });
        }
    });
}

// Add event listeners when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load inactive employees when the modal is shown
    const modal = document.getElementById('inactiveEmployeesModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', loadInactiveEmployees);
    }
});
