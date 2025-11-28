/**
 * Edit Employee Modal - Enhanced Formatting and Validation
 * Handles real-time formatting for TIN, Pag-IBIG, birthdate, and name fields
 */

// Setup formatting when edit modal is shown
document.addEventListener('DOMContentLoaded', function() {
    const editSTLModal = document.getElementById('editSTLModal');
    
    if (editSTLModal) {
        editSTLModal.addEventListener('show.bs.modal', function() {
            setupEditModalFormatting();
        });
    }
});

/**
 * Setup real-time formatting for all editable fields in the edit modal
 */
window.setupEditModalFormatting = function() {
    // Setup name fields - auto uppercase
    setupNameFieldFormatting('editSTL_last_name');
    setupNameFieldFormatting('editSTL_first_name');
    setupNameFieldFormatting('editSTL_middle_name');
    
    // Setup TIN field - auto format and uppercase letters
    setupTINFieldFormatting();
    
    // Setup birthdate field - auto format
    setupBirthdateFieldFormatting();
};

/**
 * Setup name field to auto-uppercase and allow only letters
 */
function setupNameFieldFormatting(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Remove old listeners by cloning
    const newField = field.cloneNode(true);
    field.parentNode.replaceChild(newField, field);
    const updatedField = document.getElementById(fieldId);
    
    updatedField.addEventListener('input', function(e) {
        // Remove non-letter characters
        let value = this.value.replace(/[^a-zA-Z\s]/g, '');
        // Convert to uppercase
        value = value.toUpperCase();
        // Remove extra spaces
        value = value.replace(/\s+/g, ' ').trim();
        
        if (value !== this.value) {
            this.value = value;
        }
    });
    
    // Add focus event to show format hint
    updatedField.addEventListener('focus', function() {
        this.placeholder = 'Letters only - Auto-uppercase';
    });
    
    // Add blur event to clear hint
    updatedField.addEventListener('blur', function() {
        this.placeholder = 'Letters only';
    });
};

/**
 * Setup TIN field formatting (XXX-XXX-XXX-0000)
 * Last 4 digits are always 0000, only first 9 digits are variable
 */
function setupTINFieldFormatting() {
    const tinField = document.getElementById('editSTL_tin');
    if (!tinField) return;
    
    // Remove old listeners by cloning
    const newField = tinField.cloneNode(true);
    tinField.parentNode.replaceChild(newField, tinField);
    const updatedField = document.getElementById('editSTL_tin');
    
    // Format the initial value if present
    if (updatedField.value) {
        updatedField.value = window.formatTIN(updatedField.value);
    }
    
    updatedField.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '').slice(0, 9);
        
        // Format with dashes - last 4 digits always 0000
        let formatted = '';
        if (value.length > 0) {
            formatted = value.slice(0, 3);
            if (value.length > 3) formatted += '-' + value.slice(3, 6);
            if (value.length > 6) formatted += '-' + value.slice(6, 9);
            if (value.length === 9) formatted += '-0000';
        }
        
        if (formatted !== this.value) {
            this.value = formatted;
            
            // Set cursor to end
            this.setSelectionRange(formatted.length, formatted.length);
        }
    });
    
    // Add visual feedback
    updatedField.addEventListener('focus', function() {
        this.placeholder = 'Format: XXX-XXX-XXX-0000';
    });
    
    updatedField.addEventListener('blur', function() {
        this.placeholder = 'Format: XXX-XXX-XXX-0000';
        // Validate format if not empty
        if (this.value.trim()) {
            const digits = this.value.replace(/\D/g, '');
            if (digits.length < 9) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                // Format to ensure dashes are in place with 0000 at end
                this.value = window.formatTIN(this.value);
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        }
    });
    
    // Prevent non-numeric input
    updatedField.addEventListener('keypress', function(e) {
        if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) {
            e.preventDefault();
        }
    });
}

/**
 * Setup birthdate field formatting (MM/DD/YYYY)
 */
function setupBirthdateFieldFormatting() {
    const birthdateField = document.getElementById('editSTL_birthdate');
    if (!birthdateField) return;
    
    // Remove old listeners by cloning
    const newField = birthdateField.cloneNode(true);
    birthdateField.parentNode.replaceChild(newField, birthdateField);
    const updatedField = document.getElementById('editSTL_birthdate');
    
    updatedField.addEventListener('input', function(e) {
        const cursorPos = this.selectionStart;
        let value = this.value.replace(/\D/g, '').slice(0, 8);
        
        // Format as MM/DD/YYYY
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i === 2 || i === 4) formatted += '/';
            formatted += value[i];
        }
        
        if (formatted !== this.value) {
            this.value = formatted;
            
            // Adjust cursor position accounting for slashes
            let newPos = cursorPos;
            if (value.length >= 2 && cursorPos > 2) newPos++;
            if (value.length >= 4 && cursorPos > 4) newPos++;
            
            if (newPos > formatted.length) newPos = formatted.length;
            this.setSelectionRange(newPos, newPos);
        }
    });
    
    // Add visual feedback
    updatedField.addEventListener('focus', function() {
        this.placeholder = 'Format: MM/DD/YYYY';
    });
    
    updatedField.addEventListener('blur', function() {
        this.placeholder = 'Format: MM/DD/YYYY';
        // Validate format if not empty
        if (this.value.trim()) {
            if (!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(this.value)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                const [month, day, year] = this.value.split('/').map(Number);
                if (month < 1 || month > 12 || day < 1 || day > 31 || year < 1900 || year > 2100) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                }
            }
        }
    });
    
    // Prevent non-numeric input (except slashes)
    updatedField.addEventListener('keypress', function(e) {
        if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) {
            e.preventDefault();
        }
    });
}

/**
 * Enhanced save function with validation
 */
window.saveSTLEmployeeChangesEnhanced = function() {
    const form = document.getElementById('editSTLForm');
    const errorDiv = document.getElementById('editSTLError');
    
    if (!form) {
        console.error('Form not found');
        return;
    }
    
    try {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';

        // Get form values
        const employeeId = document.getElementById('editSTL_employee_id').value;
        const pagibigNo = document.getElementById('editSTL_pagibig_no').value;
        const lastName = document.getElementById('editSTL_last_name').value.trim();
        const firstName = document.getElementById('editSTL_first_name').value.trim();
        const middleName = document.getElementById('editSTL_middle_name').value.trim();
        const tin = document.getElementById('editSTL_tin').value.trim();
        const birthdate = document.getElementById('editSTL_birthdate').value.trim();

        // Validate required fields
        if (!lastName) throw new Error('Last name is required');
        if (!firstName) throw new Error('First name is required');

        // Validate names contain only letters
        if (!/^[a-zA-Z\s]+$/.test(lastName)) throw new Error('Last name can only contain letters');
        if (!/^[a-zA-Z\s]+$/.test(firstName)) throw new Error('First name can only contain letters');
        if (middleName && !/^[a-zA-Z\s]+$/.test(middleName)) throw new Error('Middle name can only contain letters');

        // Validate TIN format if provided
        if (tin) {
            const tinDigits = tin.replace(/\D/g, '');
            if (tinDigits.length < 9) {
                throw new Error('TIN must be at least 9 digits (Format: XXX-XXX-XXX-0000)');
            }
        }

        // Validate birthdate format if provided
        if (birthdate) {
            if (!birthdate.includes('/')) {
                throw new Error('Please enter birthdate as MM/DD/YYYY');
            }
            if (!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(birthdate)) {
                throw new Error('Invalid birthdate format. Use MM/DD/YYYY');
            }
            const [month, day, year] = birthdate.split('/').map(Number);
            if (month < 1 || month > 12) throw new Error('Invalid month. Must be between 01-12');
            if (day < 1 || day > 31) throw new Error('Invalid day. Must be between 01-31');
            if (year < 1900 || year > 2100) throw new Error('Invalid year. Must be between 1900-2100');
        }

        // Extract raw digits from formatted fields
        const tinDigits = tin.replace(/\D/g, '');

        // Prepare data with proper formatting
        const formData = {
            id: employeeId,
            pagibig_number: pagibigNo,
            last_name: lastName.toUpperCase(),
            first_name: firstName.toUpperCase(),
            middle_name: middleName.toUpperCase(),
            tin: tinDigits,
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
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editSTLModal'));
                if (editModal) {
                    editModal.hide();
                }
                
                // Reload the employee lists
                if (typeof loadSTLActiveEmployeesForManagement === 'function') {
                    setTimeout(loadSTLActiveEmployeesForManagement, 500);
                }
                if (typeof loadEmployees === 'function') {
                    setTimeout(loadEmployees, 500);
                }
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

// Make function globally accessible
window.saveSTLEmployeeChangesEnhanced = saveSTLEmployeeChangesEnhanced;
