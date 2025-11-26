const loadEmployees = async () => {
    try {
        const response = await fetch('includes/get_active_employees.php');
        const data = await response.json();
        
        if (data.success) {
            const employeeTable = document.getElementById('employeeTable');
            const tbody = employeeTable.querySelector('tbody');
            tbody.innerHTML = '';
            
            data.data.forEach(employee => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${employee.pagibig_number}</td>
                    <td>${employee.id_number}</td>
                    <td>${employee.last_name}</td>
                    <td>${employee.first_name}</td>
                    <td>${employee.middle_name || ''}</td>
                    <td>${employee.tin || ''}</td>
                    <td>${employee.ee}</td>
                    <td>${employee.er}</td>
                    <td>${new Date(employee.birthdate).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="addToSTL('${employee.pagibig_number}')">
                            ${employee.stl_status === 'Added' ? 'Added' : 'Add to STL'}
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        } else {
            console.error('Failed to load employees:', data.message);
            alert('Failed to load employees. Please try again.');
        }
    } catch (error) {
        console.error('Error loading employees:', error);
        alert('Error loading employees. Please check the console for details.');
    }
};
