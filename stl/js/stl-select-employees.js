/**
 * STL Select Active Employees Modal
 * Handles selection and adding active employees to STL main table
 */

document.addEventListener('DOMContentLoaded', function() {
    const employeeTableBody = document.getElementById('selectEmployeesTableBody');
    const searchInput = document.getElementById('employeeSearchInput');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const selectedCountSpan = document.getElementById('selectedCountSpan');
    let allEmployees = [];

    // Function to update selected count
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('#selectEmployeesTableBody input[type="checkbox"]:checked').length;
        selectedCountSpan.textContent = `${selectedCount} employee${selectedCount !== 1 ? 's' : ''} selected`;
    }

    // Function to filter employees by search
    function filterEmployees(searchTerm) {
        const tbody = document.getElementById('selectEmployeesTableBody');
        const rows = tbody.getElementsByTagName('tr');
        
        for (const row of rows) {
            if (row.id === 'loadingRow') continue;
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
        }
    }

    // Function to load active employees for selection
    function loadSTLSelectEmployees() {
        employeeTableBody.innerHTML = `
            <tr id="loadingRow">
                <td colspan="4" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch('includes/get_stl_select_active_employees.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load employees');
                }

                allEmployees = data.data;
                employeeTableBody.innerHTML = ''; // Clear loading state

                if (allEmployees.length === 0) {
                    employeeTableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center">No active employees found</td>
                        </tr>
                    `;
                    return;
                }

                // Sort employees by last name
                allEmployees.sort((a, b) => a.last_name.localeCompare(b.last_name));

                allEmployees.forEach(employee => {
                    const isAdded = employee.stl_status === 'already added';
                    const row = document.createElement('tr');
                    row.className = isAdded ? 'table-success' : '';
                    row.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" 
                                value="${employee.id}" 
                                data-employee='${JSON.stringify(employee)}'
                                ${isAdded ? 'disabled' : ''}>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                ${employee.last_name}, ${employee.first_name} ${employee.middle_name || ''}
                                ${isAdded ? '<span class="badge bg-success ms-2">Added</span>' : ''}
                            </div>
                        </td>
                        <td>${employee.id_number}</td>
                        <td>
                            ${isAdded ? 
                                '<span class="text-success"><i class="fas fa-check-circle"></i> Already Added</span>' : 
                                '<span class="text-warning"><i class="fas fa-clock"></i> Not Added</span>'
                            }
                        </td>
                    `;
                    employeeTableBody.appendChild(row);
                });

                // Add event listeners to checkboxes
                const checkboxes = document.querySelectorAll('#selectEmployeesTableBody input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectedCount);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                employeeTableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading employees: ${error.message}
                        </td>
                    </tr>
                `;
            });
    }

    // Event listeners
    searchInput.addEventListener('input', () => filterEmployees(searchInput.value));

    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#selectEmployeesTableBody input[type="checkbox"]:not([disabled])');
        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            if (row.style.display !== 'none') { // Only select visible rows
                checkbox.checked = this.checked;
            }
        });
        updateSelectedCount();
    });

    // Load employees when modal is shown
    document.getElementById('selectEmployeesModal').addEventListener('show.bs.modal', function () {
        searchInput.value = '';
        selectAllCheckbox.checked = false;
        loadSTLSelectEmployees();
    });

    // Confirm selection button
    const confirmSelectEmployeesBtn = document.getElementById('confirmSelectEmployees');
    
    confirmSelectEmployeesBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('#selectEmployeesTableBody input[type="checkbox"]:checked');
        
        if (selectedCheckboxes.length === 0) {
            alert('Please select at least one employee');
            return;
        }

        // Add selected employees to STL
        let successCount = 0;
        let errorCount = 0;

        const addPromises = Array.from(selectedCheckboxes).map(checkbox => {
            const employeeData = JSON.parse(checkbox.dataset.employee);
            
            return fetch('includes/add_to_stl.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `pagibig_no=${encodeURIComponent(employeeData.pagibig_number)}`
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    successCount++;
                } else {
                    errorCount++;
                }
            })
            .catch(err => {
                console.error('Error adding employee:', err);
                errorCount++;
            });
        });

        Promise.all(addPromises).then(() => {
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('selectEmployeesModal'));
            modal.hide();

            // Reload the main table
            if (typeof loadEmployees === 'function') {
                loadEmployees();
            }

            // Refresh the select modal to update status
            loadSTLSelectEmployees();

            // Show success message
            let message = `Successfully added ${successCount} employee(s) to STL`;
            if (errorCount > 0) {
                message += ` (${errorCount} failed)`;
            }
            alert(message);
        });
    });
});
