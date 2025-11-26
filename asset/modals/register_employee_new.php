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
                <form id="registerEmployeeForm" action="../includes/register_employee.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="pagibigNumber" name="pagibig_number" 
                                    pattern="\d{12}" maxlength="12" required
                                    title="Pag-IBIG Number must be exactly 12 digits"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)">
                                <label for="pagibigNumber"><i class="fas fa-id-card me-2"></i>Pag-IBIG Number</label>
                                <div class="invalid-feedback">
                                    Pag-IBIG Number must be exactly 12 digits
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="idNumber" name="id_number" required>
                                <label for="idNumber"><i class="fas fa-id-badge me-2"></i>ID Number</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                                <label for="lastName"><i class="fas fa-user me-2"></i>Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                                <label for="firstName"><i class="fas fa-user me-2"></i>First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="middleName" name="middle_name">
                                <label for="middleName"><i class="fas fa-user me-2"></i>Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="tin" name="tin">
                                <label for="tin"><i class="fas fa-file-invoice me-2"></i>TIN</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                                <label for="birthdate"><i class="fas fa-calendar me-2"></i>Birthdate</label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3" id="registerError" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" form="registerEmployeeForm" class="btn btn-warning">
                    <i class="fas fa-save me-2"></i>Register Employee
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerEmployeeForm');
    const errorDiv = document.getElementById('registerError');
    const pagibigInput = document.getElementById('pagibigNumber');

    // Add validation for Pag-IBIG number
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

    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        // Validate Pag-IBIG number length
        if (pagibigInput.value.length !== 12) {
            errorDiv.textContent = 'Pag-IBIG Number must be exactly 12 digits';
            errorDiv.style.display = 'block';
            pagibigInput.focus();
            return;
        }

        // Collect form data
        const formData = {
            pagibig_number: form.pagibig_number.value,
            id_number: form.id_number.value,
            last_name: form.last_name.value,
            first_name: form.first_name.value,
            middle_name: form.middle_name.value || null,
            tin: form.tin.value || null,
            birthdate: form.birthdate.value
        };

        try {
            // First register the employee
            const response = await fetch('includes/register_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                // Then add to selected employees
                const added = await window.handleSuccessfulRegistration(formData);
                
                if (added) {
                    // Show success message and close modal
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Employee registered and added to selected list!',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Close modal and reset form
                    const modal = bootstrap.Modal.getInstance(document.getElementById('registerEmployeeModal'));
                    modal.hide();
                    form.reset();
                    
                    // Dispatch event to update active employees list
                    window.dispatchEvent(new Event('employeeRegistered'));
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Partial Success',
                        text: 'Employee registered but not added to selected list. Please try adding manually.',
                        timer: 3000
                    });
                }
            } else {
                errorDiv.textContent = data.message || 'Registration failed. Please try again.';
                errorDiv.style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.style.display = 'block';
        }
    });

    // Reset form when modal is closed
    document.getElementById('registerEmployeeModal').addEventListener('hidden.bs.modal', function () {
        form.reset();
        errorDiv.style.display = 'none';
        // Clear validation classes
        pagibigInput.classList.remove('is-valid', 'is-invalid');
    });
});
</script>
