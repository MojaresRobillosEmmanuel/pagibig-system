<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">
                    <i class="fas fa-lock-open me-2"></i>Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="forgotPasswordForm" action="auth/process_forgot_password.php" method="POST">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-key fa-3x text-primary mb-3"></i>
                        <p class="text-muted">Enter your email address below and we'll send you instructions to reset your password.</p>
                    </div>
                    <div class="mb-4">
                        <label for="resetEmail" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="resetEmail" name="email" 
                                placeholder="Enter your email" required>
                        </div>
                        <div class="form-text">
                            We'll send a password reset link to this email address.
                        </div>
                    </div>
                    <div id="resetMessage"></div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add JavaScript for form handling -->
<script>
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const messageDiv = document.getElementById('resetMessage');
    const email = document.getElementById('resetEmail').value;

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    submitBtn.disabled = true;

    // Send the request
    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.innerHTML = `
            <div class="alert alert-${data.status === 'success' ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        if (data.status === 'success') {
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                modal.hide();
            }, 3000);
        }
    })
    .catch(error => {
        messageDiv.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                An error occurred. Please try again later.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    });
});</script>
