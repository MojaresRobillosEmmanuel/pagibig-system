document.addEventListener('DOMContentLoaded', () => {
    // STL modals use different IDs
    const editModal = document.getElementById('editSTLModal');
    const registerModal = document.getElementById('registerEmployeeModal');
    
    function setupEditEmployeeForm() {
        if (!editModal) {
            console.warn('Edit modal element not found - STL edit form may not be initialized via modal-handlers');
            return;
        }
        
        // Initialize modal properties
        editModal.style.display = 'none'; // Hide by default
        
        // Add event listeners for opening/closing modal
        document.querySelectorAll('.edit-employee-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const pagibigNo = btn.getAttribute('data-pagibig');
                if (!pagibigNo) {
                    console.error('No Pag-IBIG number provided');
                    return;
                }

                try {
                    // Fetch employee data
                    const response = await fetch(`../includes/get_stl_employee.php?pagibig_no=${pagibigNo}`);
                    const data = await response.json();

                    if (data.success && data.employee) {
                        // Populate form fields
                        document.getElementById('edit_pagibig_no').value = data.employee.pagibig_no;
                        document.getElementById('edit_id_no').value = data.employee.id_number;
                        document.getElementById('edit_last_name').value = data.employee.last_name;
                        document.getElementById('edit_first_name').value = data.employee.first_name;
                        document.getElementById('edit_middle_name').value = data.employee.middle_name || '';
                        document.getElementById('edit_tin').value = data.employee.tin || '';
                        document.getElementById('edit_birthdate').value = data.employee.birthdate;
                        document.getElementById('edit_stl_amount').value = data.employee.stl_amount || '';

                        // Show modal
                        editModal.style.display = 'block';
                    } else {
                        throw new Error(data.message || 'Failed to fetch employee data');
                    }
                } catch (error) {
                    console.error('Error fetching employee data:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load employee data'
                    });
                }
            });
        });
        
        // Close button functionality
        const closeBtn = editModal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                editModal.style.display = 'none';
            });
        }
        
        // Close when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        });

        // Handle form submission
        const editForm = editModal.querySelector('#editForm');
        if (editForm) {
            editForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = editForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                try {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                    const formData = {
                        pagibig_no: document.getElementById('edit_pagibig_no').value,
                        id_number: document.getElementById('edit_id_no').value,
                        last_name: document.getElementById('edit_last_name').value,
                        first_name: document.getElementById('edit_first_name').value,
                        middle_name: document.getElementById('edit_middle_name').value,
                        tin: document.getElementById('edit_tin').value,
                        birthdate: document.getElementById('edit_birthdate').value,
                        stl_amount: document.getElementById('edit_stl_amount').value
                    };

                    const response = await fetch('../includes/update_stl_employee.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();
                    if (!result.success) {
                        throw new Error(result.message || 'Failed to update employee');
                    }

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Employee updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Close modal and refresh table
                    editModal.style.display = 'none';
                    if (typeof loadEmployees === 'function') {
                        loadEmployees();
                    }

                } catch (error) {
                    console.error('Error updating employee:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to update employee'
                    });
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        }
    }

    function setupRegisterEmployeeForm() {
        if (!registerModal) return;

        // Initialize register modal properties
        registerModal.style.display = 'none';

        // Add event listeners for opening/closing modal
        document.querySelectorAll('.register-employee-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                registerModal.style.display = 'block';
            });
        });

        // Close button functionality
        const closeBtn = registerModal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                registerModal.style.display = 'none';
            });
        }

        // Close when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === registerModal) {
                registerModal.style.display = 'none';
            }
        });
    }
    
    // Initialize the form setups
    setupEditEmployeeForm();
    setupRegisterEmployeeForm();
});