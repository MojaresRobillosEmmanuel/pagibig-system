<?php
session_start();
// Add any PHP logic here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="asset/login.css">
    <link rel="stylesheet" href="asset/modal.css">
    <link rel="stylesheet" href="asset/modals/modal.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <img src="asset/images/pagibig-logo.svg" alt="PAG-IBIG LOGO" class="logo-img" style="width: 120px; height: auto;">
                <h2 class="text-primary">PAG-IBIG</h2>
                <p class="text-muted">Remittances Generator</p>
            </div>
            <?php 
                // Generate CSRF token
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            ?>
            <form action="auth/process_login.php" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error']); ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success']); ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <div class="mb-3">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                        <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <div class="links mt-3 text-center">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Include modals -->
    <?php include 'asset/modals/forgotpass.php'; ?>

    <!-- jQuery (required for Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Initialize Bootstrap modals -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle login form submission
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    // Disable the submit button to prevent double submission
                    const submitButton = this.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
                    }
                });
            }

            // Check if modal is loaded
            const forgotPasswordModal = document.getElementById('forgotPasswordModal');
            if (!forgotPasswordModal) {
                console.error('Forgot password modal not found');
            }

            // Initialize Bootstrap modals
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                new bootstrap.Modal(modal);
            });
        });
    </script>
</body>
</html>
