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
        
        // Format with dashes after every 3 digits
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 3 === 0) formatted += '-';
            formatted += value[i];
        }
        
        // Update value
        this.value = formatted;
        
        // Calculate new cursor position
        let newPos = cursorPos;
        if (cursorPos > 3) newPos++;  // After first dash
        if (cursorPos > 7) newPos++;  // After second dash
        if (cursorPos > 11) newPos++; // After third dash
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

    // ID Number validation - numeric only
    idNumberInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
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

    // TIN validation and formatting (XXX-XXX-XXX-0000) - Last 4 digits always 0000
    tinInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '').slice(0, 9);
        
        // Format with dashes every 3 digits, then add -0000
        let formatted = '';
        if (value.length > 0) {
            formatted = value.slice(0, 3);
            if (value.length > 3) formatted += '-' + value.slice(3, 6);
            if (value.length > 6) formatted += '-' + value.slice(6, 9);
            if (value.length === 9) formatted += '-0000';
        }
        
        // Update value
        this.value = formatted;
        
        // Set cursor to end
        this.setSelectionRange(formatted.length, formatted.length);
        
        // Validate
        if (value.length === 9) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
        } else if (value.length > 0) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });

    // Birthdate formatting and validation
    birthdateInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        
        // Limit to 8 digits max (MMDDYYYY)
        value = value.slice(0, 8);
        
        // Format as MM/DD/YYYY or MM/DD/YY
        if (value.length > 0) {
            let month = value.substring(0, 2);
            let day = value.substring(2, 4);
            let year = value.substring(4);  // Can be 2 or 4 digits
            
            // Build formatted date with proper slashes
            let formatted = month;
            if (value.length > 2) formatted += '/' + day;
            if (value.length > 4) formatted += '/' + year;
            
            this.value = formatted;
            
            // Set cursor to end
            this.setSelectionRange(formatted.length, formatted.length);
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
            
            // Validate birthdate
            const birthdateValue = form.birthdate.value.trim();
            if (!birthdateValue) {
                throw new Error('Birthdate is required');
            }
            if (!/^\d{1,2}\/\d{1,2}\/\d{2,4}$/.test(birthdateValue)) {
                throw new Error('Birthdate must be in MM/DD/YYYY or MM/DD/YY format');
            }

            // Get TIN value and format it: remove all non-digits, then add dashes
            const tinRaw = tinInput.value.replace(/\D/g, '');
            let tinFormatted = tinRaw;
            if (tinRaw.length >= 9) {
                // Format as XXX-XXX-XXX-0000 (first 9 digits + fixed 0000)
                const tinFirst9 = tinRaw.substring(0, 9);
                tinFormatted = tinFirst9.substring(0, 3) + '-' + 
                               tinFirst9.substring(3, 6) + '-' + 
                               tinFirst9.substring(6, 9) + '-0000';
            }

            // Prepare form data
            const formData = {
                pagibig_number: pagibigValue,
                id_number: form.id_number.value.trim(),
                last_name: form.last_name.value.trim().toUpperCase(),
                first_name: form.first_name.value.trim().toUpperCase(),
                middle_name: form.middle_name.value.trim().toUpperCase() || null,
                tin: tinFormatted || null,
                birthdate: form.birthdate.value.trim()
            };

            // Submit to server
            const response = await fetch('includes/register_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('registerEmployeeModal'));
                modal.hide();

                // Show success message
                await Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Employee registered successfully in STL!',
                    showConfirmButton: false,
                    timer: 1500
                });

                // Reset form
                form.reset();
                form.querySelectorAll('.is-valid, .is-invalid').forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                });

                // DO NOT refresh employee list - user must manually select and add to STL
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