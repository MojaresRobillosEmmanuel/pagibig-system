<!-- Select Active Employees Modal -->
<div class="modal fade" id="selectEmployeesModal" tabindex="-1" aria-labelledby="selectEmployeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="selectEmployeesModalLabel">
                    <i class="fas fa-user-check me-2"></i>Select Active Employees
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search and Select All Row -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="employeeSearch" placeholder="Search employees...">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="form-check d-inline-block">
                            <input type="checkbox" class="form-check-input" id="selectAllEmployees">
                            <label class="form-check-label" for="selectAllEmployees">Select All</label>
                        </div>
                    </div>
                </div>

                <!-- Employees List -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">Select</th>
                                <th>Name</th>
                                <th>ID Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="employeeList">
                            <!-- Loading placeholder -->
                            <tr id="loadingRow">
                                <td colspan="4" class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span class="me-auto" id="selectedCount">0 employees selected</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="addSelectedBtn">
                    <i class="fas fa-plus me-2"></i>Add Selected
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const employeeList = document.getElementById('employeeList');
    const searchInput = document.getElementById('employeeSearch');
    const selectAllCheckbox = document.getElementById('selectAllEmployees');
    const selectedCountSpan = document.getElementById('selectedCount');
    let allEmployees = [];
    
    // Make loadEmployees globally accessible for refresh from other scripts
    window.loadEmployeesInModal = function() {
        loadEmployees();
    };

    // Function to update selected count
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('#employeeList input[type="checkbox"]:checked').length;
        selectedCountSpan.textContent = `${selectedCount} employee${selectedCount !== 1 ? 's' : ''} selected`;
    }

    // Function to filter employees
    function filterEmployees(searchTerm) {
        const tbody = document.getElementById('employeeList');
        const rows = tbody.getElementsByTagName('tr');
        
        for (const row of rows) {
            if (row.id === 'loadingRow') continue;
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
        }
    }

    // Function to check if employee is in the contributions table
    function isEmployeeInContributions(pagibigNo) {
        const contributionsTable = document.getElementById('selectedEmployeesTable');
        if (!contributionsTable) return false;
        
        const tbody = contributionsTable.querySelector('tbody');
        if (!tbody) return false;
        
        const rows = tbody.getElementsByTagName('tr');
        for (const row of rows) {
            const pagibigCell = row.cells[0];
            if (pagibigCell && pagibigCell.textContent.trim().replace(/[^\d]/g, '') === pagibigNo.replace(/[^\d]/g, '')) {
                return true;
            }
        }
        return false;
    }

    // Function to load employees from the database
    function loadEmployees() {
        employeeList.innerHTML = `
            <tr id="loadingRow">
                <td colspan="4" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `;

        fetch('includes/get_active_employees.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load employees');
                }

                allEmployees = data.data;
                employeeList.innerHTML = ''; // Clear loading state

                if (allEmployees.length === 0) {
                    employeeList.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center">No active employees found</td>
                        </tr>
                    `;
                    return;
                }

                // Sort employees by last name
                allEmployees.sort((a, b) => a.last_name.localeCompare(b.last_name));

                allEmployees.forEach(employee => {
                    // Check if employee is already in contributions table
                    const isAdded = isEmployeeInContributions(employee.pagibig_number);
                    
                    const row = document.createElement('tr');
                    row.className = isAdded ? 'table-success' : '';
                    row.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" 
                                value="${employee.id}" 
                                data-pagibig="${employee.pagibig_number}"
                                data-employee='${JSON.stringify(employee)}'
                                ${isAdded ? 'disabled' : ''}>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                ${employee.last_name}, ${employee.first_name} ${employee.middle_name || ''}
                                ${isAdded ? '<span class="badge bg-success ms-2">Already added</span>' : ''}
                            </div>
                        </td>
                        <td>${employee.id_number}</td>
                        <td>
                            ${isAdded ? 
                                '<span class="text-success"><i class="fas fa-check-circle"></i> Already added</span>' : 
                                '<span class="text-warning"><i class="fas fa-clock"></i> Available</span>'
                            }
                        </td>
                    `;
                    employeeList.appendChild(row);
                });

                // Add event listeners to checkboxes
                const checkboxes = document.querySelectorAll('#employeeList input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectedCount);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                employeeList.innerHTML = `
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
        const checkboxes = document.querySelectorAll('#employeeList input[type="checkbox"]:not([disabled])');
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
        loadEmployees();
    });

    const addSelectedBtn = document.getElementById('addSelectedBtn');
    
    addSelectedBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('#employeeList input[type="checkbox"]:checked');
        
        if (selectedCheckboxes.length === 0) {
            alert('Please select at least one employee');
            return;
        }

        const contributionsTable = document.getElementById('selectedEmployeesTable').getElementsByTagName('tbody')[0];
        
        selectedCheckboxes.forEach(checkbox => {
            // Get employee data
            const employeeData = JSON.parse(checkbox.dataset.employee);
            
            // Check if employee is already in the contributions table
            const existingRows = contributionsTable.getElementsByTagName('tr');
            let isDuplicate = false;
            
            for (const row of existingRows) {
                if (row.querySelector(`[data-employee-id="${employeeData.id}"]`)) {
                    isDuplicate = true;
                    break;
                }
            }
            
            if (!isDuplicate) {
                // Add the employee to the selected list using the existing addToSelectedEmployees function
                const employee = {
                    pagibig_number: employeeData.pagibig_number,
                    id_number: employeeData.id_number,
                    last_name: employeeData.last_name,
                    first_name: employeeData.first_name,
                    middle_name: employeeData.middle_name || '',
                    ee: parseFloat(employeeData.ee || 200).toFixed(2),
                    er: parseFloat(employeeData.er || 200).toFixed(2),
                    tin: employeeData.tin || '',
                    birthdate: employeeData.birthdate
                };

                // Call the global addToSelectedEmployees function
                window.addToSelectedEmployees(employee);
            }
        });

        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('selectEmployeesModal'));
        modal.hide();

        // Refresh the employee list to update status indicators
        loadEmployees();
        
        // Show success message
        alert(`Successfully added ${selectedCheckboxes.length} employee(s) to the contributions list.`);
    });
});

// Function to remove employee from the contributions table
function removeEmployee(button) {
    const row = button.closest('tr');
    const employeeData = {
        pagibig: row.cells[0].textContent,
        id: row.cells[1].textContent,
        lastname: row.cells[2].textContent,
        firstname: row.cells[3].textContent,
        middlename: row.cells[4].textContent,
        ee: row.cells[5].textContent,
        er: row.cells[6].textContent,
        tin: row.cells[7].textContent,
        birthdate: row.cells[8].textContent
    };

    // Add the employee back to the selection modal
    const employeeList = document.getElementById('employeeList');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td><input type="checkbox" class="form-check-input" value="${employeeData.id}" data-employee='${JSON.stringify(employeeData)}'></td>
        <td>${employeeData.lastname}, ${employeeData.firstname} ${employeeData.middlename}</td>
    `;
    employeeList.appendChild(newRow);

    // Remove from contributions table
    row.remove();
}
</script>

