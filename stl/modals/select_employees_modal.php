<!-- Select Active Employees Modal -->
<div class="modal fade" id="selectEmployeesModal" tabindex="-1" aria-labelledby="selectEmployeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="selectEmployeesModalLabel">
                    <i class="fas fa-user-check me-2"></i>Select Active Employees for STL
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
                                <th>PAG-IBIG #</th>
                                <th>ID #</th>
                                <th>Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="employeeList">
                            <!-- Loading placeholder -->
                            <tr id="loadingRow">
                                <td colspan="5" class="text-center py-3">
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
// Define updateSelectedCount globally so it's accessible from event listeners
function updateSelectedCount() {
    const selectedCountSpan = document.getElementById('selectedCount');
    if (selectedCountSpan) {
        const selectedCount = document.querySelectorAll('#employeeList input[type="checkbox"]:checked').length;
        selectedCountSpan.textContent = `${selectedCount} employee${selectedCount !== 1 ? 's' : ''} selected`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const employeeList = document.getElementById('employeeList');
    const searchInput = document.getElementById('employeeSearch');
    const selectAllCheckbox = document.getElementById('selectAllEmployees');
    const selectedCountSpan = document.getElementById('selectedCount');
    let allEmployees = [];

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

    // Function to load employees
    function loadEmployees() {
        employeeList.innerHTML = `
            <tr id="loadingRow">
                <td colspan="5" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `;

        // Fetch both active employees and STL employees in parallel
        Promise.all([
            fetch('../includes/get_active_employees.php').then(r => r.json()),
            fetch('../includes/get_stl_registered_employees.php?limit=1000').then(r => r.json())
        ])
            .then(([activeData, stlData]) => {
                if (!activeData.success) {
                    throw new Error(activeData.message || 'Failed to load employees');
                }

                allEmployees = activeData.data;
                
                // Get list of Pag-IBIG numbers already in STL
                const stlPagibigNumbers = new Set();
                if (stlData.success && stlData.data && stlData.data.employees) {
                    stlData.data.employees.forEach(emp => {
                        stlPagibigNumbers.add(emp.pagibig_number || emp.pagibig_no);
                    });
                }

                employeeList.innerHTML = '';

                if (allEmployees.length === 0) {
                    employeeList.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center">No active employees found</td>
                        </tr>
                    `;
                    return;
                }

                // Sort employees by last name
                allEmployees.sort((a, b) => a.last_name.localeCompare(b.last_name));

                allEmployees.forEach(employee => {
                    // Check if employee is already in STL
                    const isAlreadyInSTL = stlPagibigNumbers.has(employee.pagibig_number);

                    const row = document.createElement('tr');
                    row.className = isAlreadyInSTL ? 'table-light' : '';
                    row.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input" 
                                value="${employee.pagibig_number}" 
                                data-employee='${JSON.stringify(employee)}'
                                ${isAlreadyInSTL ? 'disabled' : ''}>
                        </td>
                        <td>${employee.pagibig_number}</td>
                        <td>${employee.id_number}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                ${employee.last_name}, ${employee.first_name} ${employee.middle_name || ''}
                            </div>
                        </td>
                        <td>
                            ${isAlreadyInSTL ? 
                                '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Already Added</span>' : 
                                '<span class="badge bg-warning text-dark"><i class="fas fa-circle me-1"></i>Not Added</span>'
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
                        <td colspan="5" class="text-center text-danger">
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

    // Add selected employees button click handler
    const addSelectedBtn = document.getElementById('addSelectedBtn');
    
    addSelectedBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('#employeeList input[type="checkbox"]:checked:not([disabled])');
        
        if (selectedCheckboxes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one employee'
            });
            return;
        }

        const promises = Array.from(selectedCheckboxes).map(checkbox => {
            const employeeData = JSON.parse(checkbox.dataset.employee);
            return fetch('../includes/add_to_stl.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `pagibig_no=${employeeData.pagibig_number}`
            }).then(response => response.json());
        });

        Promise.all(promises)
            .then(results => {
                const successCount = results.filter(result => result.success).length;
                const failCount = results.length - successCount;

                if (successCount > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: `Successfully added ${successCount} employee(s) to STL${failCount > 0 ? `. ${failCount} failed.` : ''}`
                    }).then(() => {
                        // Refresh the STL table
                        if (typeof loadEmployees === 'function') {
                            loadEmployees();
                        } else if (typeof window.loadEmployees === 'function') {
                            window.loadEmployees();
                        }
                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('selectEmployeesModal'));
                        modal.hide();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to add employees to STL'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while adding employees to STL'
                });
            });
    });
});
</script>
