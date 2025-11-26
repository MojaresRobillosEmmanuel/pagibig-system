<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" style="background-color: #2c3e50; color: white; padding: 20px; height: 100vh; width: 270px;">
    <div class="sidebar-header text-center mb-4">
        <h2 style="font-size: 1.8rem; font-weight: bold; margin-bottom: 2rem;">PAG-IBIG<br>REMITTANCES<br>GENERATOR</h2>
    </div>
    <div class="sidebar-menu d-flex flex-column" style="gap: 10px;">
        <!-- Create Button -->
        <a href="#" class="menu-item btn w-100 d-flex align-items-center" 
           style="background-color: #007bff; color: white; padding: 12px 20px; border-radius: 8px;"
           data-bs-toggle="modal" data-bs-target="#activeEmployeesModal">
            <i class="fas fa-plus-circle me-2"></i> Create
        </a>

        <!-- Save Button -->
        <a href="#" class="menu-item btn w-100 d-flex align-items-center" 
           style="background-color: #28a745; color: white; padding: 12px 20px; border-radius: 8px;"
           data-bs-toggle="modal" data-bs-target="#saveModal">
            <i class="fas fa-save me-2"></i> Save
        </a>

        <!-- Register Employee Button -->
        <a href="#" class="menu-item btn w-100 d-flex align-items-center" 
           style="background-color: #ffc107; color: #000; padding: 12px 20px; border-radius: 8px;"
           data-bs-toggle="modal" data-bs-target="#registerEmployeeModal">
            <i class="fas fa-user-plus me-2"></i> Register Employee
        </a>

        <!-- Active Employees Button -->
        <a href="#" class="menu-item btn w-100 d-flex align-items-center" 
           style="background-color: #17a2b8; color: white; padding: 12px 20px; border-radius: 8px;"
           data-bs-toggle="modal" data-bs-target="#activeEmployeesManagementModal">
            <i class="fas fa-users me-2"></i> Active Employees
        </a>

        <!-- Inactive Employees Button -->
        <a href="#" class="menu-item btn w-100 d-flex align-items-center" 
           style="background-color: #6c757d; color: white; padding: 12px 20px; border-radius: 8px;"
           data-bs-toggle="modal" data-bs-target="#inactiveEmployeesModal">
            <i class="fas fa-user-times me-2"></i> Inactive Employees
        </a>

        <!-- Spacer -->
        <div class="flex-grow-1"></div>

        <!-- Logout Button -->
        <a href="../logout.php" class="menu-item btn w-100 d-flex align-items-center mt-auto" 
           style="background-color: #dc3545; color: white; padding: 12px 20px; border-radius: 8px;">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </div>
</div>

<style>
.sidebar {
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.menu-item {
    transition: all 0.3s ease;
    text-decoration: none;
    border: none;
}

.menu-item:hover {
    transform: translateX(5px);
    filter: brightness(110%);
}

.sidebar-menu {
    height: calc(100vh - 200px);
}
</style>
