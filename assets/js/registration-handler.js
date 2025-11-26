// Function to be called after successful registration
function handleSuccessfulRegistration(employeeData) {
    // Add to selected employees list
    return fetch('process_selected_employees.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            employee: {
                pagibig_no: employeeData.pagibig_number,
                id_no: employeeData.id_number,
                last_name: employeeData.last_name,
                first_name: employeeData.first_name,
                middle_name: employeeData.middle_name,
                tin: employeeData.tin,
                birthdate: employeeData.birthdate,
                ee: 200,
                er: 200
            }
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the table
            if (typeof window.fetchEmployees === 'function') {
                window.fetchEmployees();
            }
            return true;
        } else {
            console.error('Error adding to selected employees:', data.message);
            return false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        return false;
    });
}

// Export the function to window object
window.handleSuccessfulRegistration = handleSuccessfulRegistration;
