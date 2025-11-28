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

// Get employee statistics
$conn = getConnection();
$contribCount = 0;
$stlCount = 0;

// Count contribution employees
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM employees");
    if ($result) {
        $row = $result->fetch_assoc();
        $contribCount = $row['count'] ?? 0;
    }
} catch (Exception $e) {
    error_log("Error counting employees: " . $e->getMessage());
}

// Count STL employees
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM stl_employees");
    if ($result) {
        $row = $result->fetch_assoc();
        $stlCount = $row['count'] ?? 0;
    }
} catch (Exception $e) {
    error_log("Error counting STL employees: " . $e->getMessage());
}
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="asset/index.css">
</head>
<body>
    <div class="main-container">
        <div class="content-wrapper">
            <!-- Left Side - Buttons -->
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

            <!-- Right Side - Pie Chart -->
            <!-- REMOVED -->
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
    
    <!-- Chart Initialization -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Employee Statistics Data
        const contribCount = <?php echo $contribCount; ?>;
        const stlCount = <?php echo $stlCount; ?>;
        const totalEmployees = contribCount + stlCount;

        // Create Pie Chart
        const ctx = document.getElementById('employeeChart').getContext('2d');
        const employeeChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Contribution', 'Short Term Loan (STL)'],
                datasets: [{
                    data: [contribCount, stlCount],
                    backgroundColor: [
                        '#0d6efd', // Bootstrap primary (blue)
                        '#ffc107'  // Bootstrap warning (yellow)
                    ],
                    borderColor: [
                        '#fff',
                        '#fff'
                    ],
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const percentage = totalEmployees > 0 ? ((value / totalEmployees) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                }
            }
        });

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
