// Wrap all code in an IIFE to avoid global scope pollution
(function() {
    'use strict';

    // Helper function to format Pag-IBIG number with dashes (12 digits: XXXX-XXXX-XXXX)
    function formatPagibig(pagibig) {
        if (!pagibig) return '';
        const cleaned = pagibig.toString().replace(/\D/g, '');
        if (cleaned.length !== 12) return pagibig;
        return cleaned.substring(0, 4) + '-' + cleaned.substring(4, 8) + '-' + cleaned.substring(8, 12);
    }

    // Helper function to format TIN number with dashes (handles 12 or 13 digit TINs)
    function formatTIN(tin) {
        if (!tin) return '';
        const cleaned = tin.toString().replace(/\D/g, '');
        
        // Handle 12-digit TIN: XXX-XXX-XXX-XXX
        if (cleaned.length === 12) {
            return cleaned.substring(0, 3) + '-' + cleaned.substring(3, 6) + '-' + cleaned.substring(6, 9) + '-' + cleaned.substring(9, 12);
        }
        
        // Handle 13-digit TIN: XXXX-XXX-XXX-XXX (remove first digit, then format)
        if (cleaned.length === 13) {
            const truncated = cleaned.substring(1); // Remove first digit
            return truncated.substring(0, 3) + '-' + truncated.substring(3, 6) + '-' + truncated.substring(6, 9) + '-' + truncated.substring(9, 12);
        }
        
        return tin; // Return as-is if not the expected length
    }

    // Helper function to format date for display - Matches STL table format (MM/DD/YYYY)
    function formatDate(dateString) {
        // Handle null, undefined, 'null' string, empty string, and invalid dates
        if (!dateString || 
            dateString === '0000-00-00' || 
            dateString === '' || 
            dateString === 'null' || 
            dateString === null ||
            dateString === undefined) {
            return 'N/A';
        }
        
        try {
            // Check if it's already in MM/DD/YYYY format
            if (dateString.includes('/')) {
                // Already formatted as MM/DD/YYYY, just return it
                return dateString;
            }
            
            // Try to parse YYYY-MM-DD format
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

    // Function to update table with new data
    window.updateTable = function(employees) {
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

        // Log the incoming data for debugging
        console.log('updateTable called with employees:', employees);

        // Check if we have data
        if (!Array.isArray(employees)) {
            console.error('Expected employees to be an array, got:', employees);
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error: Invalid data format</td></tr>';
            return;
        }

        // Clear existing rows
        tbody.innerHTML = "";

        // If no employees, show message
        if (!employees || employees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">No employees selected</td></tr>';
            return;
        }

        employees.forEach((emp, index) => {
            console.log(`Processing employee ${index}:`, emp);
            const row = document.createElement("tr");
            // Normalize the employee data to handle different property names
            const employeeData = {
                pagibig: emp.pagibig_no || emp.pagibig || '',
                id: emp.id_no || emp.id_number || emp.id || '',
                lastname: emp.last_name || emp.lastname || '',
                firstname: emp.first_name || emp.firstname || '',
                middlename: emp.middle_name || emp.middlename || '',
                ee: emp.ee_share || emp.ee || '200',
                er: emp.er_share || emp.er || '200',
                tin: emp.tin || '',
                birthdate: emp.birthdate || ''
            };
            
            console.log(`Employee data prepared for row ${index}:`, employeeData);

            row.innerHTML = `
                <td style="width: 150px;">${formatPagibig(employeeData.pagibig)}</td>
                <td style="width: 80px;">${employeeData.id}</td>
                <td style="width: 120px; text-transform: uppercase;">${employeeData.lastname}</td>
                <td style="width: 120px; text-transform: uppercase;">${employeeData.firstname}</td>
                <td style="width: 120px; text-transform: uppercase;">${employeeData.middlename}</td>
                <td style="width: 70px; text-align: right;">${Number(employeeData.ee).toFixed(2)}</td>
                <td style="width: 70px; text-align: right;">${Number(employeeData.er).toFixed(2)}</td>
                <td style="width: 150px;">${formatTIN(employeeData.tin)}</td>
                <td style="width: 100px;">${formatDate(employeeData.birthdate)}</td>
                <td style="width: 100px;">
                    <button class="btn btn-sm btn-danger" onclick="window.removeSelectedEmployee('${emp.pagibig_no || emp.pagibig}')" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Save to localStorage
        localStorage.setItem('tableData', JSON.stringify(employees));

        // Re-apply current filters and pagination
        if (window.applyFilter) {
            window.applyFilter();
        }
    };

    // Function to fetch updated employee list
    window.fetchEmployees = function() {
        return new Promise((resolve, reject) => {
            const table = document.getElementById('selectedEmployeesTable');
            if (!table) {
                console.error('Table not found');
                reject(new Error('Table not found'));
                return;
            }
            let tbody = table.querySelector('tbody');
            if (!tbody) {
                tbody = document.createElement('tbody');
                table.appendChild(tbody);
            }
            let retryCount = 0;
            const maxRetries = 3;
            
            // Function to clear any existing warnings
            const clearWarnings = () => {
                if (table && table.parentNode) {
                    const warnings = table.parentNode.querySelectorAll('.alert');
                    warnings.forEach(warning => warning.remove());
                }
            };
            
            // Try to load cached data first
            const savedData = localStorage.getItem('tableData');
            if (savedData) {
                try {
                    const parsedData = JSON.parse(savedData);
                    window.updateTable(parsedData);
                } catch (e) {
                    console.error('Error parsing saved data:', e);
                }
            } else {
                // Only show loading if we don't have cached data
                tbody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...</td></tr>';
            }

            // Function to handle fetch errors
            const handleFetchError = (error) => {
            console.error('Error fetching employees:', error);
            clearWarnings();
            
            if (retryCount < maxRetries) {
                retryCount++;
                console.log(`Retrying... Attempt ${retryCount} of ${maxRetries}`);
                setTimeout(fetchData, 1000 * retryCount); // Exponential backoff
                return;
            }
            
            // Check if elements exist before trying to use them
            const table = document.getElementById('selectedEmployeesTable');
            const tbody = table ? table.querySelector('tbody') : null;
            if (!tbody) {
                console.error('Table body not found');
                reject(new Error('Table body not found'));
                return;
            }                const warningDiv = document.createElement('div');
                warningDiv.className = 'alert alert-warning alert-dismissible fade show';
                warningDiv.innerHTML = `
                    <strong>Warning:</strong> Unable to fetch latest data. ${savedData ? 'Showing cached data.' : ''} 
                    <button onclick="window.fetchEmployees()" class="btn btn-sm btn-link">Retry</button>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                if (table && table.parentNode) {
                    table.parentNode.insertBefore(warningDiv, table);
                    // Auto-dismiss after 5 seconds
                    setTimeout(() => warningDiv.remove(), 5000);
                }
                
                reject(error);
            };

            // Function to fetch data
            const fetchData = () => {
                fetch('get_selected_contributions.php')
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 401 || response.status === 403) {
                                window.location.href = 'login.php';
                                return;
                            }
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Invalid JSON:', text);
                                throw new Error('Server returned invalid JSON: ' + e.message);
                            }
                        });
                    })
                    .then(data => {
                        console.log('Fetched data:', data);
                        if (data.success) {
                            clearWarnings();
                            // Ensure employees is always an array
                            const employeesArray = Array.isArray(data.employees) ? data.employees : [];
                            window.updateTable(employeesArray);
                            resolve(employeesArray);
                        } else {
                            throw new Error(data.message || 'Unknown error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        const tbody = document.querySelector('#selectedEmployeesTable tbody');
                        if (tbody) {
                            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">
                                <i class="fas fa-exclamation-circle"></i> 
                                Error loading data: ${error.message}
                            </td></tr>`;
                        }
                        handleFetchError(error);
                    });
            };

            // Start the fetch process
            fetchData();
        });
    };

    // Function to add employee to selected list
    window.addToSelectedEmployees = function(employee) {
        // Convert empty values to appropriate defaults and handle multiple field name possibilities
        const employeeData = {
            pagibig_no: employee.pagibig_no || employee.pagibig_number || employee.pagibig || '',
            id_no: employee.id_no || employee.id_number || employee.id || '',
            last_name: employee.last_name || employee.lastname || '',
            first_name: employee.first_name || employee.firstname || '',
            middle_name: employee.middle_name || employee.middlename || '',
            ee: parseFloat(employee.ee_share || employee.ee || 200).toFixed(2),
            er: parseFloat(employee.er_share || employee.er || 200).toFixed(2),
            tin: employee.tin || '',
            birthdate: employee.birthdate || new Date().toISOString().split('T')[0]
        };

        fetch('process_selected_employees.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                employee: employeeData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.fetchEmployees();
                
                // Trigger modal refresh if it exists
                const selectEmployeesModal = document.getElementById('selectEmployeesModal');
                if (selectEmployeesModal && window.loadEmployeesInModal) {
                    window.loadEmployeesInModal();
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Employee added successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error adding employee: ' + data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while adding the employee'
            });
        });
    };

    // Function to delete/remove employee
    // Function to delete/remove employee
    window.deleteEmployee = window.removeSelectedEmployee = function(pagibigNo) {
        // Find the employee's name from the table
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Employee removed successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error removing employee: ' + data.message
                        });
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
            }
        });
    };

    // Function to edit employee
    window.editEmployee = function(pagibigNo) {
        // Find the employee in the table
        const row = document.querySelector(`button[onclick="window.editEmployee('${pagibigNo}')"]`).closest('tr');
        const cells = row.cells;

        // Populate the modal fields
        document.getElementById('edit_pagibig_no').value = pagibigNo;
        document.getElementById('edit_id_no').value = cells[1].textContent;
        document.getElementById('edit_last_name').value = cells[2].textContent;
        document.getElementById('edit_first_name').value = cells[3].textContent;
        document.getElementById('edit_middle_name').value = cells[4].textContent;
        document.getElementById('edit_ee').value = cells[5].textContent;
        document.getElementById('edit_er').value = cells[6].textContent;
        document.getElementById('edit_tin').value = cells[7].textContent;
        document.getElementById('edit_birthdate').value = formatDateForInput(cells[8].textContent);

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    };

    // Function to save employee changes
    window.saveEmployeeChanges = function() {
        const formData = {
            pagibig_no: document.getElementById('edit_pagibig_no').value,
            id_no: document.getElementById('edit_id_no').value,
            last_name: document.getElementById('edit_last_name').value,
            first_name: document.getElementById('edit_first_name').value,
            middle_name: document.getElementById('edit_middle_name').value,
            ee: document.getElementById('edit_ee').value,
            er: document.getElementById('edit_er').value,
            tin: document.getElementById('edit_tin').value,
            birthdate: document.getElementById('edit_birthdate').value
        };

        fetch('process_selected_employees.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'edit',
                employee: formData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                modal.hide();
                
                // Refresh table
                window.fetchEmployees();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Employee updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error updating employee: ' + data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the employee'
            });
        });
    };

    // Helper function to format date for input field (HTML date input requires YYYY-MM-DD)
    function formatDateForInput(dateString) {
        try {
            // If it's already in YYYY-MM-DD format, return it
            if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return dateString;
            }
            
            // If it's in MM/DD/YYYY format, convert to YYYY-MM-DD
            if (dateString.includes('/')) {
                const parts = dateString.split('/');
                if (parts.length === 3) {
                    const month = parts[0].padStart(2, '0');
                    const day = parts[1].padStart(2, '0');
                    const year = parts[2];
                    return `${year}-${month}-${day}`;
                }
            }
            
            // If it's in YYYY-MM-DD with dashes, replace - with - (no-op but handles edge cases)
            if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return dateString;
            }
            
            return dateString; // Return as is if not in expected format
        } catch (e) {
            console.error('Error formatting date for input:', e);
            return dateString;
        }
    }

    // Initialize when the DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing selected-employees.js'); // Debug log
        window.fetchEmployees().catch(error => {
            console.error('Initial fetch failed:', error);
        });
    });

})();
