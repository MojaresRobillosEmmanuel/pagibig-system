// Function to add employee to STL
window.addToSTL = async function(pagibigNumber) {
    try {
        const response = await fetch('../stl/includes/get_active_employees.php');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to get employee data');
        }

        const employee = result.data.find(emp => emp.pagibig_number === pagibigNumber);
        if (!employee) {
            throw new Error('Employee not found');
        }

        // Add employee to STL
        const addResponse = await fetch('../stl/includes/process_selected_stl.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                employee: {
                    ...employee,
                    payment_amount: 0 // Default payment amount
                }
            })
        });

        const addResult = await addResponse.json();
        if (!addResult.success) {
            throw new Error(addResult.message || 'Failed to add employee to STL');
        }

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Employee has been added to STL successfully.',
            showConfirmButton: false,
            timer: 1500
        });

        // Refresh both employee lists
        await window.fetchSTLEmployees();
        await window.fetchActiveEmployees();

    } catch (error) {
        console.error('Error adding to STL:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to add employee to STL'
        });
    }
};

// Function to remove employee from STL
window.removeFromSTL = async function(pagibigNumber) {
    try {
        // Ask for confirmation
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Confirm Removal',
            text: 'Are you sure you want to remove this employee from STL?',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove',
            cancelButtonText: 'No, cancel'
        });

        if (!result.isConfirmed) {
            return;
        }

        // Remove employee from STL
        const response = await fetch('../stl/includes/process_selected_stl.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                pagibig_number: pagibigNumber
            })
        });

        const responseData = await response.json();
        if (!responseData.success) {
            throw new Error(responseData.message || 'Failed to remove employee from STL');
        }

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Employee has been removed from STL successfully.',
            showConfirmButton: false,
            timer: 1500
        });

        // Refresh both employee lists
        await window.fetchSTLEmployees();
        await window.fetchActiveEmployees();

    } catch (error) {
        console.error('Error removing from STL:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to remove employee from STL'
        });
    }
};

// Function to fetch active employees
window.fetchActiveEmployees = async function() {
    try {
        const response = await fetch('../stl/includes/get_active_employees.php');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch active employees');
        }

        window.updateActiveEmployeesList(result.data);
    } catch (error) {
        console.error('Error fetching active employees:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to fetch active employees'
        });
    }
};
