/**
 * STL Employee Management
 * Handles loading and displaying employees in the main STL table
 */

// Function to format Pag-IBIG number with dashes (12 digits: XXXX-XXXX-XXXX)
function formatPagibigNumber(pagibig) {
    if (!pagibig) return '';
    const cleaned = pagibig.toString().replace(/\D/g, '');
    if (cleaned.length !== 12) return pagibig;
    return cleaned.substring(0, 4) + '-' + cleaned.substring(4, 8) + '-' + cleaned.substring(8, 12);
}

// Function to format TIN number with dashes (preserve existing dashes)
function formatTIN(tin) {
    if (!tin) return '';
    const tinStr = tin.toString().trim();
    
    // If already formatted with dashes, return as is
    if (tinStr.includes('-')) {
        return tinStr;
    }
    
    // Otherwise, clean and format
    const cleaned = tinStr.replace(/\D/g, '');
    
    if (cleaned.length === 12) {
        return cleaned.substring(0, 3) + '-' + cleaned.substring(3, 6) + '-' + cleaned.substring(6, 9) + '-' + cleaned.substring(9, 12);
    }
    
    if (cleaned.length === 13) {
        const truncated = cleaned.substring(1);
        return truncated.substring(0, 3) + '-' + truncated.substring(3, 6) + '-' + truncated.substring(6, 9) + '-' + truncated.substring(9, 12);
    }
    
    return tin;
}

// Function to format date for display - MM/DD/YYYY
function formatDateForDisplay(dateString) {
    if (!dateString || dateString === '0000-00-00' || dateString === '' || dateString === 'null') {
        return 'N/A';
    }
    
    try {
        if (dateString.includes('/')) {
            return dateString;
        }
        
        const date = new Date(dateString + 'T00:00:00');
        if (isNaN(date.getTime())) {
            return 'N/A';
        }
        
        return date.toLocaleDateString('en-US', {
            month: '2-digit',
            day: '2-digit',
            year: 'numeric'
        });
    } catch (e) {
        console.error('Error formatting date:', e);
        return 'N/A';
    }
}

// Function to load STL employees and display in table
window.loadEmployees = function() {
    const table = document.getElementById('selectedEmployeesTable');
    if (!table) {
        console.error('Table not found');
        return;
    }
    
    let tbody = table.querySelector('tbody');
    if (!tbody) {
        tbody = document.createElement('tbody');
        table.appendChild(tbody);
    }
    
    // Show loading state
    tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted"><small><em>Loading employees...</em></small></td></tr>';
    
    // Fetch STL employees
    fetch('includes/get_stl_employees.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Fetched data:', data);
            
            if (data.status !== 'success' && !data.success) {
                throw new Error(data.message || 'Failed to load employees');
            }
            
            // Handle both response formats
            let employees = [];
            if (data.data && data.data.employees) {
                employees = Array.isArray(data.data.employees) ? data.data.employees : [];
            } else if (Array.isArray(data.data)) {
                employees = data.data;
            }
            
            // Clear table
            tbody.innerHTML = '';
            
            if (!employees || employees.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted"><em>No STL employees found. Please add active employees using "Register Employee" or the selection modal.</em></td></tr>';
                return;
            }
            
            // Populate table with employees
            employees.forEach(emp => {
                const row = document.createElement('tr');
                
                // Format values - show 0.00 format for EE and ER
                const eeValue = emp.ee ? parseFloat(emp.ee).toFixed(2) : '0.00';
                const erValue = emp.er ? parseFloat(emp.er).toFixed(2) : '200.00';
                
                row.innerHTML = `
                    <td style="width: 150px;">${formatPagibigNumber(emp.pagibig_no || emp.pagibig_number || '')}</td>
                    <td style="width: 80px;">${emp.id_no || emp.id_number || ''}</td>
                    <td style="width: 120px; text-transform: uppercase;">${emp.last_name || ''}</td>
                    <td style="width: 120px; text-transform: uppercase;">${emp.first_name || ''}</td>
                    <td style="width: 120px; text-transform: uppercase;">${emp.middle_name || ''}</td>
                    <td style="width: 70px; text-align: right;">${eeValue}</td>
                    <td style="width: 70px; text-align: right; cursor: pointer;" onclick="openEditERModal('${emp.pagibig_no || emp.pagibig_number}', this)">${erValue}</td>
                    <td style="width: 150px;">${formatTIN(emp.tin || '')}</td>
                    <td style="width: 100px;">${formatDateForDisplay(emp.birthdate || '')}</td>
                    <td style="width: 100px; text-align: center;">
                        <button class="btn btn-sm btn-danger" onclick="removeFromSTL('${emp.pagibig_no || emp.pagibig_number}', '${emp.last_name}, ${emp.first_name}')" title="Remove">
                            <i class="fas fa-trash me-1"></i>Remove
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
            
            // Re-apply filter if it exists
            if (window.applyFilter) {
                window.applyFilter();
            }
        })
        .catch(error => {
            console.error('Error loading employees:', error);
            tbody.innerHTML = `<tr><td colspan="11" class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Error loading data: ${error.message}</td></tr>`;
        });
};

// Function to display employees (alternative name)
window.displayEmployees = function() {
    window.loadEmployees();
};

// Function to remove employee from STL
window.removeFromSTL = function(pagibigNo, employeeName) {
    if (!confirm(`Are you sure you want to remove ${employeeName} from STL?`)) {
        return;
    }
    
    // Find and remove the row from the table immediately
    const table = document.getElementById('selectedEmployeesTable');
    if (table) {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const pagibigCell = row.querySelector('td:first-child');
            if (pagibigCell) {
                // Extract just the digits from the Pag-IBIG number for comparison
                const cellValue = pagibigCell.textContent.replace(/\D/g, '');
                const paramValue = pagibigNo.replace(/\D/g, '');
                if (cellValue === paramValue) {
                    // Remove the row with animation
                    row.style.transition = 'opacity 0.3s ease-out';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        
                        // Check if table is now empty
                        const remainingRows = tbody.querySelectorAll('tr');
                        if (remainingRows.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted"><em>No STL employees found. Please add active employees using "Register Employee" or the selection modal.</em></td></tr>';
                        }
                    }, 300);
                }
            }
        });
    }
    
    const formData = new FormData();
    formData.append('pagibig_no', pagibigNo);
    
    fetch('includes/remove_from_stl.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Employee removed from STL',
                timer: 2000,
                showConfirmButton: false
            });
            
            // Reload the table to sync with database
            setTimeout(() => {
                window.loadEmployees();
            }, 1000);
        } else {
            // If removal failed, reload to restore the row
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to remove employee'
            });
            window.loadEmployees();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while removing the employee'
        });
    });
};

// Function to save employee changes
window.saveEmployeeChanges = function() {
    const form = document.getElementById('editForm');
    const errorDiv = document.getElementById('editError');
    
    if (!form || !errorDiv) {
        console.error('Form or error div not found');
        return;
    }
    
    try {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
        
        // Get form values
        const pagibigNo = document.getElementById('edit_pagibig_no').value;
        const idNo = document.getElementById('edit_id_no').value;
        const lastName = document.getElementById('edit_last_name').value.trim();
        const firstName = document.getElementById('edit_first_name').value.trim();
        const middleName = document.getElementById('edit_middle_name').value.trim();
        const tin = document.getElementById('edit_tin').value.trim();
        const birthdate = document.getElementById('edit_birthdate').value.trim();
        
        // Validate required fields
        if (!lastName) throw new Error('Last name is required');
        if (!firstName) throw new Error('First name is required');
        
        // Prepare data for submission
        const formData = {
            pagibig_no: pagibigNo,
            id_no: idNo,
            last_name: lastName.toUpperCase(),
            first_name: firstName.toUpperCase(),
            middle_name: middleName.toUpperCase(),
            tin: tin,
            birthdate: birthdate
        };
        
        // Submit to server
        fetch('includes/update_stl_employee.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Employee information updated successfully!',
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Close the edit modal
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                if (editModal) {
                    editModal.hide();
                }
                
                // Reload the employees list
                window.loadEmployees();
            } else {
                throw new Error(data.message || 'Update failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorDiv.textContent = error.message || 'Failed to update employee';
            errorDiv.style.display = 'block';
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to update employee'
            });
        });
        
    } catch (error) {
        console.error('Validation error:', error);
        errorDiv.textContent = error.message;
        errorDiv.style.display = 'block';
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('employee-management.js loaded');
    window.loadEmployees();
});
