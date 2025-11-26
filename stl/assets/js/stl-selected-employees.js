// Function to update table with new data
(function() {
    'use strict';

    window.updateSTLTable = function(employees) {
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

        if (!Array.isArray(employees)) {
            console.error('Expected employees to be an array, got:', employees);
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error: Invalid data format</td></tr>';
            return;
        }

        tbody.innerHTML = "";

        if (!employees || employees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center">No employees selected</td></tr>';
            return;
        }

        employees.forEach(emp => {
            const row = document.createElement("tr");
            const employeeData = {
                pagibig: emp.pagibig_number || '',
                id: emp.id_number || '',
                name: `${(emp.last_name || '').toUpperCase()}, ${(emp.first_name || '').toUpperCase()} ${(emp.middle_name || '').toUpperCase()}`,
                ee: parseFloat(emp.ee || 0).toFixed(2),
                er: parseFloat(emp.er || 0).toFixed(2),
                stl: parseFloat(emp.payment_amount || 0).toFixed(2),
                tin: emp.tin || '',
                birthdate: emp.birthdate || ''
            };

            row.innerHTML = `
                <td style="width: 150px;">${employeeData.pagibig}</td>
                <td style="width: 80px;">${employeeData.id}</td>
                <td style="width: 250px;">${employeeData.name}</td>
                <td style="width: 100px; text-align: right;">₱${employeeData.ee}</td>
                <td style="width: 100px; text-align: right;">₱${employeeData.er}</td>
                <td style="width: 100px; text-align: right;">₱${employeeData.stl}</td>
                <td style="width: 150px;">${employeeData.tin}</td>
                <td style="width: 100px;">${formatDate(employeeData.birthdate)}</td>
                <td style="width: 100px;">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary me-1" onclick="window.editSTLEmployee('${emp.pagibig_number}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="window.removeSTLEmployee('${emp.pagibig_number}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        localStorage.setItem('stlTableData', JSON.stringify(employees));

        if (window.applyFilter) {
            window.applyFilter();
        }
    };

    // Function to fetch STL employees
    window.fetchSTLEmployees = function() {
        return new Promise((resolve, reject) => {
            const table = document.getElementById('selectedEmployeesTable');
            if (!table) {
                console.error('Table not found');
                reject(new Error('Table not found'));
                return;
            }

            const savedData = localStorage.getItem('stlTableData');
            if (savedData) {
                try {
                    const parsedData = JSON.parse(savedData);
                    window.updateSTLTable(parsedData);
                } catch (e) {
                    console.error('Error parsing saved data:', e);
                }
            }

            fetch('../stl/includes/get_selected_stl.php')
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 401 || response.status === 403) {
                            window.location.href = '../login.php';
                            return;
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const employeesArray = Array.isArray(data.employees) ? data.employees : [];
                        window.updateSTLTable(employeesArray);
                        resolve(employeesArray);
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    reject(error);
                });
        });
    };

    // Function to format date
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

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        window.fetchSTLEmployees().catch(error => {
            console.error('Initial fetch failed:', error);
        });
    });
})();
