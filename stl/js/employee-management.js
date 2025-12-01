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

// Function to format TIN number with dashes (XXX-XXX-XXX-0000) - Last 4 digits always 0000
function formatTIN(tin) {
    if (!tin) return '';
    
    const tinStr = tin.toString().trim();
    
    // If it's empty after trimming, return empty
    if (tinStr === '') return '';
    
    // Remove all dashes and non-numeric characters to get clean digits
    const cleaned = tinStr.replace(/[^\d]/g, '');
    
    // If no digits, return original
    if (cleaned.length === 0) return '';
    
    // Always take the first 9 digits (ignore anything beyond)
    // This handles both old 12-digit format and new 9-digit format
    const first9Digits = cleaned.substring(0, 9);
    
    // Pad with leading zeros if less than 9 digits
    const paddedDigits = first9Digits.padStart(9, '0');
    
    // Format as XXX-XXX-XXX-0000
    return paddedDigits.substring(0, 3) + '-' + 
           paddedDigits.substring(3, 6) + '-' + 
           paddedDigits.substring(6, 9) + '-0000';
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
            employees.forEach((emp, index) => {
                const row = document.createElement('tr');
                
                // Ensure all fields exist, even if empty
                const pagibigNo = emp.pagibig_no || emp.pagibig_number || '';
                const idNo = emp.id_no || emp.id_number || '';
                const lastName = emp.last_name || '';
                const firstName = emp.first_name || '';
                const middleName = emp.middle_name || '';
                const eeValue = emp.ee && parseFloat(emp.ee) > 0 ? parseFloat(emp.ee).toFixed(2) : '0.00';
                const tinFormatted = formatTIN(emp.tin || '');
                const birthdateFormatted = formatDateForDisplay(emp.birthdate || '');
                const fullName = lastName + ', ' + firstName;
                
                row.innerHTML = `
                    <td style="width: 150px;">${formatPagibigNumber(pagibigNo)}</td>
                    <td style="width: 80px;">${idNo}</td>
                    <td style="width: 120px; text-transform: uppercase;">${lastName}</td>
                    <td style="width: 120px; text-transform: uppercase;">${firstName}</td>
                    <td style="width: 120px; text-transform: uppercase;">${middleName}</td>
                    <td style="width: 70px; text-align: right; cursor: pointer;" onclick="openEditEEModal('${pagibigNo}', this)">${eeValue}</td>
                    <td style="width: 70px; text-align: right;"></td>
                    <td style="width: 150px;" class="tin-cell">${tinFormatted}</td>
                    <td style="width: 100px;">${birthdateFormatted}</td>
                    <td style="width: auto; text-align: center;">
                        <button class="btn btn-sm btn-danger" onclick="removeFromSTL('${pagibigNo}', '${fullName}')" title="Remove from STL">
                            <i class="fas fa-trash"></i> Remove
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
    
    const formData = new FormData();
    formData.append('pagibig_no', pagibigNo);
    
    // Show loading state
    Swal.fire({
        title: 'Removing...',
        text: 'Please wait while the employee is being removed.',
        didOpen: () => {
            Swal.showLoading();
        },
        allowOutsideClick: false,
        allowEscapeKey: false
    });
    
    fetch('includes/remove_from_stl.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        // Check if response is JSON
        if (!res.ok && res.status !== 404) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        // Check content type
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('Response Content-Type:', contentType);
            throw new Error('Server returned non-JSON response. Please check the server logs.');
        }
        
        return res.json();
    })
    .then(data => {
        console.log('Remove response:', data);
        
        if (data.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Employee removed from STL',
                timer: 2000,
                showConfirmButton: false
            });
            
            // Reload the table after a brief delay
            setTimeout(() => {
                window.loadEmployees();
            }, 1000);
        } else {
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to remove employee from database'
            });
            
            // Reload to restore the row
            setTimeout(() => {
                window.loadEmployees();
            }, 500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'An error occurred while removing the employee. Check console for details.'
        });
        
        // Reload to restore the row
        setTimeout(() => {
            window.loadEmployees();
        }, 500);
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
