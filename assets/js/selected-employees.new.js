// Wrap all code in an IIFE to avoid global scope pollution
(function() {
    'use strict';

    // Function to update table with new data
    window.updateTable = function(employees) {
        const tbody = document.querySelector('#selectedEmployeesTable tbody');
        
        // Check if we have data
        if (!Array.isArray(employees)) {
            console.error('Expected employees to be an array, got:', employees);
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error: Invalid data format</td></tr>';
            return;
        }

        // Clear existing rows
        tbody.innerHTML = '';
        
        // If no employees, show message
        if (employees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">No employees selected</td></tr>';
            return;
        }
        
        employees.forEach(emp => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${emp.pagibig_no || ''}</td>
                <td>${emp.id_no || ''}</td>
                <td>${emp.last_name || ''}</td>
                <td>${emp.first_name || ''}</td>
                <td>${emp.middle_name || ''}</td>
                <td>${emp.ee_share || emp.ee || ''}</td>
                <td>${emp.er_share || emp.er || ''}</td>
                <td>${emp.tin || ''}</td>
                <td>${emp.birthdate || ''}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="window.removeSelectedEmployee('${emp.pagibig_no}')">
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
            const tbody = document.querySelector('#selectedEmployeesTable tbody');
            
            // Show loading indicator
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...</td></tr>';

            // First try to load from localStorage while we fetch
            const savedData = localStorage.getItem('tableData');
            if (savedData) {
                try {
                    const parsedData = JSON.parse(savedData);
                    window.updateTable(parsedData);
                } catch (e) {
                    console.error('Error parsing saved data:', e);
                }
            }

            // Then fetch fresh data from server
            fetch('get_selected_contributions.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Fetched data:', data); // Debug log
                    if (data.success) {
                        window.updateTable(data.employees);
                        resolve(data.employees);
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error fetching employees:', error);
                    // If we have saved data, don't show error
                    if (!savedData) {
                        tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">
                            Error loading data: ${error.message}. 
                            <button onclick="window.fetchEmployees()" class="btn btn-sm btn-link">Retry</button>
                        </td></tr>`;
                    }
                    reject(error);
                });
        });
    };

    // Function to add employee to selected list
    window.addToSelectedEmployees = function(employee) {
        fetch('process_selected_employees.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                employee: {
                    pagibig_no: employee.pagibig_number,
                    id_no: employee.id_number,
                    last_name: employee.last_name,
                    first_name: employee.first_name,
                    middle_name: employee.middle_name,
                    ee: employee.ee || 200,
                    er: employee.er || 200,
                    tin: employee.tin,
                    birthdate: employee.birthdate
                }
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.fetchEmployees();
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

    // Function to remove employee from selected list
    window.removeSelectedEmployee = function(pagibigNo) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to remove this employee from the selected list?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
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

    // Initialize when the DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing selected-employees.js'); // Debug log
        window.fetchEmployees().catch(error => {
            console.error('Initial fetch failed:', error);
        });
    });

})();
