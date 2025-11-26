<!-- Active Employees Modal -->
<div class="modal fade" id="activeEmployeesModal" tabindex="-1" aria-labelledby="activeEmployeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activeEmployeesModalLabel">Active Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Pag-IBIG #</th>
                                <th>ID #</th>
                                <th>Name</th>
                                <th>EE</th>
                                <th>ER</th>
                                <th>TIN</th>
                                <th>Birthdate</th>
                            </tr>
                        </thead>
                        <tbody id="activeEmployeesTableBody">
                            <!-- Table content will be dynamically populated -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to load active employees
    async function loadActiveEmployees() {
        const tableBody = document.getElementById('activeEmployeesTableBody');
        try {
            // Show loading indicator
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </td>
                </tr>
            `;

            const response = await fetch('includes/get_active_employees.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            
            tableBody.innerHTML = '';
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load employees');
            }
            
            if (data.data && data.data.length > 0) {
                data.data.forEach(employee => {
                    // Format the values with defaults
                    const ee = parseFloat(employee.ee || 200).toFixed(2);
                    const er = parseFloat(employee.er || 200).toFixed(2);
                    const fullName = [
                        employee.last_name,
                        employee.first_name,
                        employee.middle_name || ''
                    ].filter(Boolean).join(', ');
                    
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        </td>
                        <td>${employee.pagibig_number || ''}</td>
                        <td>${employee.id_number || ''}</td>
                        <td>${fullName}</td>
                        <td>${ee}</td>
                        <td>${er}</td>
                        <td>${employee.tin || 'N/A'}</td>
                        <td>${employee.birthdate || 'N/A'}</td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center">
                            No active employees found
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error loading active employees:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-danger">
                        <i class="fas fa-exclamation-circle"></i> 
                        Error loading employees: ${error.message}
                    </td>
                </tr>
            `;
        }
    }

    // Load active employees when the modal is shown
    document.getElementById('activeEmployeesModal').addEventListener('show.bs.modal', function () {
        loadActiveEmployees();
    });
});
</script>
