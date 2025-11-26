<!-- Register Employee Modal -->
<div class="modal fade" id="registerEmployeeModal" tabindex="-1" aria-labelledby="registerEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="registerEmployeeModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Register Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registerEmployeeForm" method="POST" novalidate>
                    <div class="row g-3">
                        <!-- Pag-IBIG Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="pagibigNumber" 
                                    name="pagibig_number"
                                    maxlength="15" 
                                    placeholder="XXX-XXX-XXX-XXX"
                                    required>
                                <label for="pagibigNumber">
                                    <i class="fas fa-id-card me-2"></i>Pag-IBIG Number (XXX-XXX-XXX-XXX)
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a valid 12-digit Pag-IBIG number
                                </div>
                            </div>
                        </div>

                        <!-- ID Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="idNumber" 
                                    name="id_number"
                                    required>
                                <label for="idNumber">
                                    <i class="fas fa-id-badge me-2"></i>ID Number
                                </label>
                                <div class="invalid-feedback">
                                    Please enter an ID number
                                </div>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="lastName" 
                                    name="last_name"
                                    required>
                                <label for="lastName">
                                    <i class="fas fa-user me-2"></i>Last Name
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a last name (letters only)
                                </div>
                            </div>
                        </div>

                        <!-- First Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="firstName" 
                                    name="first_name"
                                    required>
                                <label for="firstName">
                                    <i class="fas fa-user me-2"></i>First Name
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a first name (letters only)
                                </div>
                            </div>
                        </div>

                        <!-- Middle Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="middleName" 
                                    name="middle_name">
                                <label for="middleName">
                                    <i class="fas fa-user me-2"></i>Middle Name
                                </label>
                            </div>
                        </div>

                        <!-- TIN Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="tin" 
                                    name="tin"
                                    maxlength="15" 
                                    placeholder="XXX-XXX-XXX-0000">
                                <label for="tin">
                                    <i class="fas fa-file-invoice me-2"></i>TIN (XXX-XXX-XXX-0000)
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a valid TIN number
                                </div>
                            </div>
                        </div>

                        <!-- Birthdate -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="birthdate" 
                                    name="birthdate"
                                    placeholder="MM/DD/YYYY"
                                    maxlength="10"
                                    required>
                                <label for="birthdate">
                                    <i class="fas fa-calendar me-2"></i>Birthdate (MM/DD/YYYY)
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a valid date (MM/DD/YYYY)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3" id="registerError" style="display: none;"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Register Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal for Registration -->
<div class="modal fade" id="confirmRegistrationModal" tabindex="-1" aria-labelledby="confirmRegistrationLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="confirmRegistrationLabel">
                    <i class="fas fa-check-circle me-2"></i>Confirm Employee Registration
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please review the following information before confirming registration:
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Pag-IBIG Number:</strong>
                        <p id="confirmPagibig">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>ID Number:</strong>
                        <p id="confirmIdNumber">-</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Last Name:</strong>
                        <p id="confirmLastName">-</p>
                    </div>
                    <div class="col-md-4">
                        <strong>First Name:</strong>
                        <p id="confirmFirstName">-</p>
                    </div>
                    <div class="col-md-4">
                        <strong>Middle Name:</strong>
                        <p id="confirmMiddleName">-</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>TIN:</strong>
                        <p id="confirmTin">-</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Birthdate:</strong>
                        <p id="confirmBirthdate">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmRegistrationBtn" onclick="submitRegistration()">
                    <i class="fas fa-check me-2"></i>Confirm & Register
                </button>
            </div>
        </div>
    </div>




<script>
// Store the data to be registered
let pendingRegistrationData = null;

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerEmployeeForm');
    const pagibigInput = document.getElementById('pagibigNumber');
    
    // Format Pag-IBIG number with proper cursor handling
    if (pagibigInput) {
        pagibigInput.addEventListener('input', function(e) {
            // Extract only digits from current input
            let digitsOnly = this.value.replace(/\D/g, '').slice(0, 12);
            
            // Format with dashes: XXXX-XXXX-XXXX
            let formatted = '';
            for (let i = 0; i < digitsOnly.length; i++) {
                if (i === 4 || i === 8) {
                    formatted += '-';
                }
                formatted += digitsOnly[i];
            }
            
            // Update the value
            this.value = formatted;
            
            // Always move cursor to end after formatting
            setTimeout(() => {
                this.setSelectionRange(formatted.length, formatted.length);
            }, 0);
            
            // Show validation
            if (digitsOnly.length === 12) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else if (digitsOnly.length > 0) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });

        // Prevent non-numeric input via keypress
        pagibigInput.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key)) {
                e.preventDefault();
            }
        });
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                // Get form data
                const formData = new FormData(registerForm);
                
                // Helper function to format TIN: remove all non-digits, then add dashes
                function formatTIN(tinValue) {
                    const digitsOnly = tinValue.replace(/\D/g, '');
                    if (digitsOnly.length !== 12) return digitsOnly; // Return as-is if not 12 digits
                    // Format as XXX-XXX-XXX-0000
                    return digitsOnly.substring(0, 3) + '-' + 
                           digitsOnly.substring(3, 6) + '-' + 
                           digitsOnly.substring(6, 9) + '-' + 
                           digitsOnly.substring(9);
                }
                
                const pagibigFormatted = formData.get('pagibig_number');
                const pagibigDigits = pagibigFormatted.replace(/\D/g, '');
                
                // Validate required fields
                if (!formData.get('last_name').trim()) throw new Error('Last name is required');
                if (!formData.get('first_name').trim()) throw new Error('First name is required');
                if (pagibigDigits.length !== 12) throw new Error('Pag-IBIG number must be 12 digits');
                if (!formData.get('id_number').trim()) throw new Error('ID Number is required');
                if (!formData.get('birthdate').trim()) throw new Error('Birthdate is required');
                
                // Validate birthdate format
                const birthdateValue = formData.get('birthdate').trim();
                if (birthdateValue.includes('-')) {
                    throw new Error('Invalid birthdate format. Use forward slashes (/) not dashes (-). Example: 01/20/2001');
                }
                if (!birthdateValue.includes('/')) {
                    throw new Error('Please enter birthdate as MM/DD/YYYY');
                }
                
                const data = {
                    pagibig_number: pagibigDigits,
                    id_number: formData.get('id_number'),
                    last_name: formData.get('last_name').toUpperCase(),
                    first_name: formData.get('first_name').toUpperCase(),
                    middle_name: formData.get('middle_name').toUpperCase() || '',
                    tin: formatTIN(formData.get('tin')),
                    birthdate: birthdateValue
                };

                // Store the data for later submission
                pendingRegistrationData = data;

                // Show confirmation modal with the data
                document.getElementById('confirmPagibig').textContent = pagibigFormatted;
                document.getElementById('confirmIdNumber').textContent = data.id_number;
                document.getElementById('confirmLastName').textContent = data.last_name;
                document.getElementById('confirmFirstName').textContent = data.first_name;
                document.getElementById('confirmMiddleName').textContent = data.middle_name || '(none)';
                document.getElementById('confirmTin').textContent = data.tin || '(none)';
                document.getElementById('confirmBirthdate').textContent = data.birthdate;

                // Close register modal
                const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerEmployeeModal'));
                if (registerModal) {
                    registerModal.hide();
                }

                // Show confirmation modal
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmRegistrationModal'));
                confirmModal.show();

            } catch (error) {
                console.error('Validation error:', error);
                const errorDiv = document.getElementById('registerError');
                if (errorDiv) {
                    errorDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
                    errorDiv.style.display = 'block';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: error.message
                });
            }
        });
    }
});

// Global function to submit the registration after confirmation
async function submitRegistration() {
    if (!pendingRegistrationData) {
        Swal.fire('Error', 'No data to register', 'error');
        return;
    }

    try {
        const confirmBtn = document.getElementById('confirmRegistrationBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registering...';

        console.log('Sending registration data:', pendingRegistrationData);

        const response = await fetch('includes/register_employee.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(pendingRegistrationData)
        });

        console.log('Response status:', response.status);

        const result = await response.json();
        console.log('Response data:', result);

        if (!result.success) {
            throw new Error(result.message || 'Failed to register employee');
        }

        // Close confirmation modal
        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmRegistrationModal'));
        if (confirmModal) {
            confirmModal.hide();
        }

        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Employee has been registered successfully!',
            confirmButtonText: 'OK'
        }).then(() => {
            // Reset form
            const registerForm = document.getElementById('registerEmployeeForm');
            if (registerForm) {
                registerForm.reset();
                const errorDiv = document.getElementById('registerError');
                if (errorDiv) {
                    errorDiv.innerHTML = '';
                    errorDiv.style.display = 'none';
                }
            }
            pendingRegistrationData = null;
        });

    } catch (error) {
        console.error('Error:', error);
        const confirmBtn = document.getElementById('confirmRegistrationBtn');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm & Register';

        // Check if it's a duplicate error
        if (error.message.includes('already exists')) {
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Employee',
                text: error.message,
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message
            });
        }
    }
}

// Reset confirmation modal when dismissed
document.addEventListener('DOMContentLoaded', function() {
    const confirmModal = document.getElementById('confirmRegistrationModal');
    if (confirmModal) {
        confirmModal.addEventListener('hidden.bs.modal', function() {
            const confirmBtn = document.getElementById('confirmRegistrationBtn');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm & Register';
            }
            // Show the register modal again
            const registerModal = new bootstrap.Modal(document.getElementById('registerEmployeeModal'));
            registerModal.show();
        });
    }
});
</script>
