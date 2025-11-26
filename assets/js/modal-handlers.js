// Initialize modal instances storage
window.modalInstances = {};

// Safety wrapper: ensure any focused element inside a modal is blurred before
// Bootstrap toggles aria-hidden. This prevents the "Blocked aria-hidden on an
// element because its descendant retained focus" warning.
(function addSafeModalHide() {
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal && bootstrap.Modal.prototype) {
            const originalHide = bootstrap.Modal.prototype.hide;
            bootstrap.Modal.prototype.hide = function() {
                try {
                    const active = document.activeElement;
                    // If focus is inside the document and not on body, blur it to
                    // avoid focus being trapped when aria-hidden is applied.
                    if (active && typeof active.blur === 'function' && active !== document.body) {
                        active.blur();
                    }
                } catch (e) {
                    // ignore
                }

                // Call the original hide method
                return originalHide.apply(this, arguments);
            };
        }
    } catch (err) {
        console.error('Failed to apply safe modal hide wrapper:', err);
    }
})();

// Document Ready Handler with error boundary
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Register Employee Form Handler
        setupRegisterEmployeeForm();
        // Edit Employee Form Handler
        setupEditEmployeeForm();
        // Save Modal Handler
        setupSaveModal();
    } catch (error) {
        console.error('Error in modal initialization:', error);
    }
});

// Register Employee Form Setup
function setupRegisterEmployeeForm() {
    const form = document.getElementById('registerEmployeeForm');
    const errorDiv = document.getElementById('registerError');
    const pagibigInput = document.getElementById('pagibigNumber');

    if (!form || !errorDiv || !pagibigInput) return;

    // Name fields validation
    const nameFields = ['last_name', 'first_name', 'middle_name'];
    nameFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input) {
            input.addEventListener('input', function() {
                // Remove numbers and special characters, convert to uppercase
                let value = this.value.replace(/[^a-zA-Z\s]/g, '').toUpperCase();
                // Remove extra spaces
                value = value.replace(/\s+/g, ' ');
                this.value = value;

                // Validate field
                if (value.trim().length > 0 && /^[A-Z\s]+$/.test(value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        }
    });

    // PAG-IBIG Number validation
    pagibigInput.addEventListener('input', function() {
        const value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
        this.value = value;
        
        if (value.length === 12) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });

    // Form submission handler
    form.addEventListener('submit', handleRegisterSubmit);
}

// Edit Employee Form Setup with enhanced modal handling and error boundaries
function setupEditEmployeeForm() {
    try {
        const editModal = document.getElementById('editModal');
        if (!editModal) {
            console.warn('Edit modal element not found');
            return;
        }

        // Initialize the edit modal properly if not already initialized
        if (!window.modalInstances?.[editModal.id]) {
            const editModalInstance = new bootstrap.Modal(editModal, {
                backdrop: 'static',
                keyboard: false,
                focus: true
            });
            window.modalInstances[editModal.id] = editModalInstance;
        }

        const form = document.getElementById('editEmployeeForm');
        if (!form) {
            console.warn('Edit form element not found');
            return;
        }

        // Setup form validation and error handling
        const setupFormValidation = () => {
            const inputs = form.querySelectorAll('input:not([type="hidden"])');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    validateInput(this);
                });
            });
        };

        // Validate individual input
        const validateInput = (input) => {
            input.classList.remove('is-invalid', 'is-valid');
            let isValid = input.value.trim().length > 0;
            
            // Additional validation rules
            if (isValid && input.name === 'pagibig_no') {
                isValid = /^\d{12}$/.test(input.value);
            }
            
            input.classList.add(isValid ? 'is-valid' : 'is-invalid');
            return isValid;
        };

        // Handle modal events with error boundaries
        const setupModalEvents = () => {
            // Show event - reset validation states
            editModal.addEventListener('show.bs.modal', function(event) {
                try {
                    const inputs = form.querySelectorAll('input');
                    inputs.forEach(input => {
                        input.classList.remove('is-invalid', 'is-valid');
                    });
                } catch (error) {
                    console.error('Error in modal show event:', error);
                }
            });

            // Shown event - focus first input
            editModal.addEventListener('shown.bs.modal', function(event) {
                try {
                    const firstInput = form.querySelector('input:not([type="hidden"])');
                    if (firstInput) firstInput.focus();
                } catch (error) {
                    console.error('Error in modal shown event:', error);
                }
            });

            // Hidden event - reset form
            editModal.addEventListener('hidden.bs.modal', function(event) {
                try {
                    form.reset();
                } catch (error) {
                    console.error('Error in modal hidden event:', error);
                }
            });
        };

        // Initialize form validation and modal events
        setupFormValidation();
        setupModalEvents();

    } catch (error) {
        console.error('Error setting up edit employee form:', error);
    }
}

// Save Modal Setup
function setupSaveModal() {
    const generateExcelBtn = document.getElementById('btnGenerateExcel');
    if (!generateExcelBtn) return;

    generateExcelBtn.addEventListener('click', handleExcelGeneration);
}

// Handle Register Form Submit
async function handleRegisterSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const errorDiv = document.getElementById('registerError');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    try {
        // Validate PAG-IBIG number
        const pagibigInput = form.pagibig_number;
        if (pagibigInput.value.length !== 12) {
            throw new Error('Pag-IBIG Number must be exactly 12 digits');
        }

        // Validate required fields
        const requiredFields = ['pagibig_number', 'id_number', 'last_name', 'first_name', 'birthdate'];
        for (const field of requiredFields) {
            if (!form[field].value.trim()) {
                throw new Error(`${field.replace('_', ' ')} is required`);
            }
        }

        // Validate name fields
        const nameFields = ['last_name', 'first_name', 'middle_name'];
        for (const field of nameFields) {
            const value = form[field].value.trim();
            if (value && !/^[A-Z\s]+$/.test(value)) {
                throw new Error(`${field.replace('_', ' ')} must contain only letters`);
            }
        }

        // Collect form data
        const formData = {
            pagibig_number: form.pagibig_number.value.trim(),
            id_number: form.id_number.value.trim(),
            last_name: form.last_name.value.trim(),
            first_name: form.first_name.value.trim(),
            middle_name: form.middle_name.value.trim() || null,
            tin: form.tin.value.trim() || null,
            birthdate: form.birthdate.value.trim()
        };

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';

        // Register employee
        const registerResponse = await fetch('../includes/register_employee.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const registerData = await registerResponse.json();
        if (!registerData.success) {
            throw new Error(registerData.message || 'Registration failed');
        }

        // Add to selected contributions
        const contributionResponse = await fetch('../process_selected_employees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'add',
                employee: {
                    pagibig_no: formData.pagibig_number,
                    id_no: formData.id_number,
                    last_name: formData.last_name,
                    first_name: formData.first_name,
                    middle_name: formData.middle_name,
                    tin: formData.tin,
                    birthdate: formData.birthdate,
                    ee: 200.00,
                    er: 200.00
                }
            })
        });

        const contributionResult = await contributionResponse.json();
        if (!contributionResult.success) {
            throw new Error(contributionResult.message || 'Failed to add to contributions');
        }

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Employee registered and added to contributions successfully!',
            timer: 2000,
            showConfirmButton: false
        });

        // Reset form and close modal
        form.reset();
        const modal = bootstrap.Modal.getInstance(document.getElementById('registerEmployeeModal'));
        modal.hide();

        // Update table
        if (typeof window.fetchEmployees === 'function') {
            window.fetchEmployees();
        } else {
            window.location.reload();
        }

    } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = error.message || 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
}

// Handle Excel Generation
function handleExcelGeneration() {
    const month = document.getElementById('month').value;
    const year = document.getElementById('yearInput').value;

    if (!month || !year) {
        alert('Please select both month and year');
        return;
    }

    // Get table data
    const table = document.getElementById('selectedEmployeesTable');
    const rows = table.querySelectorAll('tbody tr');

    if (rows.length === 0) {
        alert('No data available in table');
        return;
    }

    // Show loading state
    this.disabled = true;
    this.textContent = 'Generating...';

    try {
        // Prepare data for Excel
        const data = [
            // Headers
            ['PAG-IBIG MID NO.', 'EMPLOYEE NUMBER', 'LAST NAME', 'FIRST NAME', 'MIDDLE NAME', 
             'EE', 'ER', 'TIN', 'BIRTHDATE']
        ];

        // Add row data
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            data.push([
                cells[0].textContent, // PAG-IBIG
                cells[1].textContent, // ID
                cells[2].textContent, // Last Name
                cells[3].textContent, // First Name
                cells[4].textContent, // Middle Name
                cells[5].textContent, // EE Share
                cells[6].textContent, // ER Share
                cells[7].textContent, // TIN
                cells[8].textContent, // Birthdate
            ]);
        });

        // Create workbook
        const ws = XLSX.utils.aoa_to_sheet(data);
        
        // Set column widths
        ws['!cols'] = [
            {wch: 20}, // PAG-IBIG
            {wch: 15}, // Employee Number
            {wch: 20}, // Last Name
            {wch: 20}, // First Name
            {wch: 20}, // Middle Name
            {wch: 15}, // EE Share
            {wch: 15}, // ER Share
            {wch: 15}, // TIN
            {wch: 12}, // Birthdate
        ];

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'PAG-IBIG Contributions');

        // Save file
        XLSX.writeFile(wb, `${month}_${year}.xlsx`);

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('saveModal'));
        if (modal) {
            modal.hide();
        }

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Excel file has been generated successfully!',
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while generating the Excel file'
        });
    } finally {
        // Reset button state
        this.disabled = false;
        this.textContent = 'Generate Excel';
    }
}
