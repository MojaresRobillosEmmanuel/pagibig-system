<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="registerModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registrationForm" action="auth/process_register.php" method="POST">
                    <div class="row g-3">
                        <!-- ID Number -->
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="idNumber" name="idNumber" placeholder="Enter ID Number" required>
                                <label for="idNumber"><i class="fas fa-id-card me-2"></i>ID Number</label>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter Last Name">
                                <label for="lastName"><i class="fas fa-user me-2"></i>Last Name</label>
                            </div>
                        </div>

                        <!-- First Name -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter First Name">
                                <label for="firstName"><i class="fas fa-user me-2"></i>First Name</label>
                            </div>
                        </div>

                        <!-- Middle Name -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Enter Middle Name">
                                <label for="middleName"><i class="fas fa-user me-2"></i>Middle Name</label>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="regUsername" name="username" placeholder="Choose Username">
                                <label for="regUsername"><i class="fas fa-user-circle me-2"></i>Username</label>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="regPassword" name="password" placeholder="Enter Password">
                                <label for="regPassword"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password">
                                <label for="confirmPassword"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3" id="registerError" style="display: none;"></div>
                    
                    <div class="modal-footer border-0 pt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const errorDiv = document.getElementById('registerError');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // Submit form data
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and close modal
                alert('Registration successful! You can now login.');
                const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                modal.hide();
                form.reset();
            } else {
                // Show error message
                errorDiv.textContent = data.message || 'Registration failed. Please try again.';
                errorDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.style.display = 'block';
        });
    });

    // Clear form when modal is closed
    document.getElementById('registerModal').addEventListener('hidden.bs.modal', function () {
        form.reset();
        form.classList.remove('was-validated');
        errorDiv.style.display = 'none';
    });
});
</script>
