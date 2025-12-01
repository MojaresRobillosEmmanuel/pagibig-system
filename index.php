<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['is_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Run auto-setup to ensure database schema is correct
require_once __DIR__ . '/database/auto_setup.php';
require_once __DIR__ . '/database/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PAG-IBIG REMITTANCES GENERATOR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="asset/index.css">
</head>
<body>
    <div class="main-container">
        <div class="content-wrapper">
            <!-- Left Side - Buttons and Logo -->
            <div class="content-box">
                <!-- Logo -->
                <img src="asset/images/pagibig-logo.svg" alt="PAG-IBIG LOGO" class="logo-img">
                
                <!-- Heading -->
                <h1 class="title">PAG-IBIG REMITTANCES<br>GENERATOR</h1>
                
                <!-- Buttons -->
                <div class="d-grid gap-3">
                    <a href="contrib.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-hand-holding-usd me-2"></i>Contribution
                    </a>
                    <a href="stl/stl.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Short Term Loan (STL)
                    </a>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Welcome Modal -->
    <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="welcomeModalLabel">Welcome</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <h3>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                    <p class="text-muted mb-0">You have successfully logged in to PAG-IBIG Remittances Generator.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show Welcome Modal
        <?php if(isset($_SESSION['show_welcome']) && $_SESSION['show_welcome']): ?>
            // Show the welcome modal
            const welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
            welcomeModal.show();
            
            <?php
            // Remove the welcome flag after showing
            unset($_SESSION['show_welcome']);
            ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>
