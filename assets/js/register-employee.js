document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerEmployeeForm');
    if (!form) return;

    const errorDiv = document.getElementById('registerError');
    const pagibigInput = document.getElementById('pagibigNumber');
    const idNumberInput = document.getElementById('idNumber');
    const tinInput = document.getElementById('tin');
    const birthdateInput = document.getElementById('birthdate');
    const nameInputs = ['lastName', 'firstName', 'middleName'].map(id => document.getElementById(id));

    // Pag-IBIG number validation and formatting
    pagibigInput.addEventListener('input', function(e) {
        const cursorPos = this.selectionStart;
        let value = this.value.replace(/\D/g, '').slice(0, 12);
        
        // Format with dashes
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += '-';
            formatted += value[i];
        }
        
        // Update value
        this.value = formatted;
        
        // Calculate new cursor position
        let newPos = cursorPos;
        if (cursorPos > 4) newPos++;  // After first dash
        if (cursorPos > 9) newPos++;  // After second dash
        if (newPos > formatted.length) newPos = formatted.length;
        
        // Set cursor position
        this.setSelectionRange(newPos, newPos);
        
        // Validate
        if (value.length === 12) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
        } else if (value.length > 0) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });

    // Prevent non-numeric input for Pag-IBIG
    pagibigInput.addEventListener('keypress', function(e) {
        if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) {
            e.preventDefault();
        }
    });

    // Name fields validation
    nameInputs.forEach(input => {
        if (!input) return;
        
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^a-zA-Z\s]/g, '').toUpperCase();
            value = value.replace(/\s+/g, ' ').trim();
            this.value = value;
            
            if (value.length > 0) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else if (this.required) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    });

    // TIN validation and formatting
    tinInput.addEventListener('input', function(e) {
        const cursorPos = this.selectionStart;
        let value = this.value.replace(/\D/g, '').slice(0, 9);
        
        // Format with dashes
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 3 === 0) formatted += '-';
            formatted += value[i];
        }
        
        // Add -0000 if 9 digits entered
        if (value.length === 9) {
            formatted += '-0000';
        }
        
        // Update value
        this.value = formatted;
        
        // Calculate new cursor position
        let newPos = cursorPos;
        if (cursorPos > 3) newPos++;  // After first dash
        if (cursorPos > 7) newPos++;  // After second dash
        if (formatted.endsWith('-0000') && !this.value.endsWith('-0000')) {
            newPos = formatted.length - 5;  // Place cursor before -0000
        }
        if (newPos > formatted.length) newPos = formatted.length;
        
        // Set cursor position
        this.setSelectionRange(newPos, newPos);
    });

    // Birthdate formatting and validation - STRICT: ONLY forward slashes
    birthdateInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        
        // REJECT any input that contained dashes
        if (this.value.includes('-')) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Format',
                text: 'Please use forward slashes (/) not dashes (-). Example: 01/20/2001',
                showConfirmButton: false,
                timer: 2000
            });
            this.value = ''; // Clear the field
            return;
        }
        
        // Format as MM/DD/YYYY
        if (value.length > 0) {
            let month = value.substring(0, 2);
            let day = value.substring(2, 4);
            let year = value.substring(4, 8);
            
            // Validate month
            if (month.length === 2 && (parseInt(month) < 1 || parseInt(month) > 12)) {
                month = '12';
            }
            
            // Validate day
            if (day.length === 2 && (parseInt(day) < 1 || parseInt(day) > 31)) {
                day = '31';
            }
            
            // Build formatted date with FORWARD SLASHES ONLY
            let formatted = month;
            if (value.length > 2) formatted += '/' + day;
            if (value.length > 4) formatted += '/' + year;
            
            this.value = formatted;
        }
    });

    // Form submission
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        try {
            // Validate Pag-IBIG number
            const pagibigValue = pagibigInput.value.replace(/\D/g, '');
            if (pagibigValue.length !== 12) {
                throw new Error('Please enter a valid 12-digit Pag-IBIG number');
            }

            // Validate names
            if (!form.last_name.value.trim()) throw new Error('Last name is required');
            if (!form.first_name.value.trim()) throw new Error('First name is required');

            // Validate birthdate format - MUST have forward slashes, NOT dashes
            const birthdateValue = form.birthdate.value.trim();
            if (birthdateValue) {
                if (birthdateValue.includes('-')) {
                    throw new Error('Invalid birthdate format. Use forward slashes (/) not dashes (-). Example: 01/20/2001');
                }
                if (!birthdateValue.includes('/')) {
                    throw new Error('Please enter birthdate as MM/DD/YYYY');
                }
            }

            // Get TIN value without validation
            const tinValue = tinInput.value.replace(/\D/g, '');

            // Prepare form data
            const formData = {
                pagibig_number: pagibigValue,
                id_number: form.id_number.value.trim(),
                last_name: form.last_name.value.trim().toUpperCase(),
                first_name: form.first_name.value.trim().toUpperCase(),
                middle_name: form.middle_name.value.trim().toUpperCase() || '',
                tin: tinValue || '',
                birthdate: birthdateValue
            };

            // Submit to server
            const response = await fetch('includes/register_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('registerEmployeeModal'));
                modal.hide();

                // Show success message
                await Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: `Employee ${data.employee_name || 'registered'} registered successfully!`,
                    showConfirmButton: false,
                    timer: 1500
                });

                // Reset form
                form.reset();
                form.querySelectorAll('.is-valid, .is-invalid').forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                });

                // Store the newly registered employee name in sessionStorage
                const employeeName = `${formData.first_name} ${formData.last_name}`.trim();
                sessionStorage.setItem('newlyRegisteredEmployee', employeeName);

                // Don't call fetchEmployees() here - the employee was registered but not added to selected contributions
                // User needs to manually add the employee to the contribution list via "Active Employees" section
            } else {
                throw new Error(data.message || 'Registration failed');
            }
        } catch (error) {
            console.error('Error:', error);
            errorDiv.textContent = error.message;
            errorDiv.style.display = 'block';
        }
    });

    // Reset form when modal is closed
    const modal = document.getElementById('registerEmployeeModal');
    modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        form.querySelectorAll('.is-valid, .is-invalid').forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
        errorDiv.style.display = 'none';
    });
});
