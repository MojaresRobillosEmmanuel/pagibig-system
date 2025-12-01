<?php
// Start session first, before any output
session_start();
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>STL Management</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/style.css">
  <!-- Sidebar CSS -->
  <link rel="stylesheet" href="../asset/sidebar/sidebar.css">
  <!-- SheetJS -->
   <!-- In your header -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- SheetJS (XLSX) library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>
  /* Ensure proper layout visibility */
  * {
    box-sizing: border-box;
  }
  
  html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    width: 100%;
  }
  
  body {
    display: flex;
    min-height: 100vh;
    margin: 0;
    padding: 0;
  }
  
  .sidebar {
    position: relative !important;
    left: auto !important;
    top: auto !important;
    width: 270px !important;
    flex-shrink: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
  }
  
  .content {
    display: flex !important;
    flex-direction: column;
    visibility: visible !important;
    opacity: 1 !important;
    flex: 1;
    margin-left: 0 !important;
    padding: 20px;
    width: auto !important;
    height: 100vh;
    background-color: #f8f9fa;
    overflow: hidden;
    gap: 0;
  }
  
  /* Make the table container scrollable */
  .table-responsive {
    overflow-y: auto !important;
    overflow-x: auto !important;
  }
  
  /* Thin custom scrollbar for table only */
  .table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  
  .table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  
  .table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
  }
  
  .table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
</style>
</head>
<body style="display: flex !important; visibility: visible !important; opacity: 1 !important;">

  <!-- Include Sidebar OUTSIDE content area -->
  <?php include 'sidebar.php'; ?>

  <div class="content" style="display: flex !important; flex-direction: column; visibility: visible !important; opacity: 1 !important;">
    <!-- STL specific modals -->
    <?php include 'modals/register-employee-modal.php'; ?>

    <h3 style="margin: 0 0 15px 0; flex-shrink: 0;">Short Term Loan (STL)</h3>
    <div class="d-flex justify-content-end align-items-center" style="margin-bottom: 15px; flex-shrink: 0;">
      <!-- Right side controls -->
      <div class="d-flex align-items-center">
        <label for="rowsPerPage" class="me-3 mb-0 small">Show</label>
        <select id="rowsPerPage" class="form-select form-select-sm me-3" style="width: auto;">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="0">All</option>
        </select>
        <!-- Small search box aligned to the right upper side -->
        <div style="max-width: 240px; width:100%;">
          <input type="search" id="searchInput" class="form-control form-control-sm" placeholder="Search employee name">
        </div>
      </div>
    </div>

    <!-- Scrollable Table Container -->
    <div class="table-responsive" style="flex: 1; min-height: 0; display: flex; flex-direction: column; overflow-y: auto; overflow-x: auto; border: 1px solid #dee2e6; border-radius: 0.25rem;">
      <table class="table table-bordered mb-0" id="selectedEmployeesTable">
        <thead style="position: sticky; top: 0; z-index: 10; background-color: white; border-bottom: 2px solid #dee2e6;">
          <tr>
            <th>Pag-IBIG #</th>
            <th>ID #</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>EE</th>
            <th>ER</th>
            <th>TIN</th>
            <th>Birthdate</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>



  <!-- Edit Employee Modal for STL -->
  <div class="modal fade" id="editSTLModal" tabindex="-1" aria-labelledby="editSTLModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="editSTLModalLabel">
            <i class="fas fa-user-edit me-2"></i>Edit Employee Details
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editSTLForm">
            <input type="hidden" id="editSTL_employee_id" name="id">
            <input type="hidden" id="editSTL_pagibig_no" name="pagibig_no">
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editSTL_id_no" class="form-label">ID Number:</label>
                <input type="text" class="form-control" id="editSTL_id_no" name="id_no" readonly style="background-color: #e9ecef;">
              </div>
              <div class="col-md-6 mb-3">
                <label for="editSTL_pagibigNo" class="form-label">Pag-IBIG Number:</label>
                <input type="text" class="form-control" id="editSTL_pagibigNo" readonly style="background-color: #e9ecef;">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editSTL_last_name" class="form-label">Last Name: <small class="text-muted">(Letters only - Auto-uppercase)</small></label>
                <input type="text" class="form-control" id="editSTL_last_name" name="last_name" placeholder="Letters only">
              </div>
              <div class="col-md-6 mb-3">
                <label for="editSTL_first_name" class="form-label">First Name: <small class="text-muted">(Letters only - Auto-uppercase)</small></label>
                <input type="text" class="form-control" id="editSTL_first_name" name="first_name" placeholder="Letters only">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editSTL_middle_name" class="form-label">Middle Name: <small class="text-muted">(Letters only - Auto-uppercase)</small></label>
                <input type="text" class="form-control" id="editSTL_middle_name" name="middle_name" placeholder="Letters only">
              </div>
              <div class="col-md-6 mb-3">
                <label for="editSTL_tin" class="form-label">TIN: <small class="text-muted">(Format: XXX-XXX-XXX-0000)</small></label>
                <input type="text" class="form-control" id="editSTL_tin" name="tin" placeholder="Format: XXX-XXX-XXX-0000" maxlength="15">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="editSTL_birthdate" class="form-label">Birthdate: <small class="text-muted">(Format: MM/DD/YYYY)</small></label>
                <input type="text" class="form-control" id="editSTL_birthdate" name="birthdate" placeholder="MM/DD/YYYY" maxlength="10">
              </div>
            </div>

            <div id="editSTLError" style="display: none;" class="alert alert-danger mt-3"></div>

            <div class="alert alert-info mt-3">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Note:</strong> 
              <ul class="mb-0 mt-2">
                <li>ID Number and Pag-IBIG Number are read-only fields.</li>
                <li>Names automatically convert to uppercase - letters only.</li>
                <li>TIN will be formatted as XXX-XXX-XXX-0000 (requires 9 digits, last 4 are always 0000).</li>
                <li>Birthdate must be in MM/DD/YYYY format.</li>
                <li>All changes are automatically formatted when saved.</li>
              </ul>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-primary" onclick="saveSTLEmployeeChangesEnhanced()">
            <i class="fas fa-save me-2"></i>Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit ER Modal -->
  <div class="modal fade" id="editERModal" tabindex="-1" aria-labelledby="editERModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editERModalLabel">Edit ER Value</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editER_pagibig_no">
          <div class="mb-3">
            <label for="editER_value" class="form-label">ER Amount</label>
            <input type="number" class="form-control" id="editER_value" step="0.01" min="0">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveERValue()">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit EE Modal (STL Only) -->
  <div class="modal fade" id="editEEModal" tabindex="-1" aria-labelledby="editEEModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="editEEModalLabel">
            <i class="fas fa-edit me-2"></i>Edit EE Value
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editEE_pagibig_no">
          <div class="mb-3">
            <label for="editEE_value" class="form-label">EE Amount</label>
            <input type="number" class="form-control" id="editEE_value" step="0.01" min="0" placeholder="Enter EE amount">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-info text-white" onclick="saveEEValue()">
            <i class="fas fa-save me-2"></i>Save
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Load jQuery first -->
  </div>

  <!-- Select Employees Modal -->
  <!-- REMOVED - Buttons removed from UI -->

  <!-- Active Employees Modal -->
  <!-- REMOVED - Buttons removed from UI -->

  <!-- Inactive Employees Modal -->
  <!-- REMOVED - Buttons removed from UI -->

  <!-- Save Modal -->
  <div class="modal fade" id="saveModal" tabindex="-1" aria-labelledby="saveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="saveModalLabel">Generate Excel File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="month" class="form-label">Select Month</label>
            <select class="form-select" id="month" required>
              <option value="">Choose...</option>
              <option value="January">January</option>
              <option value="February">February</option>
              <option value="March">March</option>
              <option value="April">April</option>
              <option value="May">May</option>
              <option value="June">June</option>
              <option value="July">July</option>
              <option value="August">August</option>
              <option value="September">September</option>
              <option value="October">October</option>
              <option value="November">November</option>
              <option value="December">December</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="yearInput" class="form-label">Enter Year</label>
            <input type="text" class="form-control" id="yearInput" required placeholder="YYYY">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="btnGenerateExcel">Generate Excel</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Active Employees Modal (for selection when creating) -->
  <div class="modal fade" id="activeEmployeesModal" tabindex="-1" aria-labelledby="activeEmployeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="activeEmployeesModalLabel">
            <i class="fas fa-users me-2"></i>Select Active Employees
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 d-flex justify-content-between align-items-center">
            <input type="text" class="form-control" id="activeEmployeesSearch" placeholder="Search employees..." style="max-width: 400px;">
            <button class="btn btn-sm btn-primary" id="selectAllActiveBtn">Select All</button>
          </div>
          <div id="activeEmployeesLoadingSpinner" class="text-center">
            <div class="spinner-border text-success" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover" id="activeEmployeesTable">
              <thead style="position: sticky; top: 0; background-color: #f8f9fa;">
                <tr>
                  <th style="width: 50px;">SELECT</th>
                  <th>NAME</th>
                  <th>ID NUMBER</th>
                  <th>STATUS</th>
                </tr>
              </thead>
              <tbody id="activeEmployeesTableBody">
                <tr>
                  <td colspan="4" class="text-center text-muted">Loading employees...</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-3">
            <span id="activeEmployeesCount">0 employees selected</span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="activeEmployeesAddBtn">Add Selected</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Active Employees Management Modal (for viewing and deactivating) -->
  <div class="modal fade" id="activeEmployeesManagementModal" tabindex="-1" aria-labelledby="activeEmployeesManagementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="activeEmployeesManagementModalLabel">
            <i class="fas fa-users me-2"></i>Active Employees (STL System)
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="activeEmployeesManagementLoadingSpinner" class="text-center">
            <div class="spinner-border text-success" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div class="list-group" id="activeEmployeesManagementList">
            <!-- List will be populated dynamically -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Inactive Employees Modal -->
  <div class="modal fade" id="inactiveEmployeesModal" tabindex="-1" aria-labelledby="inactiveEmployeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="inactiveEmployeesModalLabel">
            <i class="fas fa-user-times me-2"></i>Inactive Employees (STL System)
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="inactiveLoadingSpinner" class="text-center">
            <div class="spinner-border text-danger" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div class="list-group" id="inactiveEmployeesList">
            <!-- List will be populated dynamically -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- STL Summary Report Modal -->
  <div class="modal fade" id="stlSummaryModal" tabindex="-1" aria-labelledby="stlSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="stlSummaryModalLabel">
            <i class="fas fa-chart-bar me-2"></i>Summary of Short-Term Loan (STL)
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="stlSummaryLoadingSpinner" class="text-center">
            <div class="spinner-border text-danger" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div class="table-responsive" id="stlSummaryContainer" style="display: none;">
            <table class="table table-striped table-hover">
              <thead class="table-danger">
                <tr>
                  <th>YEAR - MONTH</th>
                  <th class="text-center"># OF BORROWERS</th>
                  <th class="text-end">DEDUCTED AMOUNT</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="stlSummaryTableBody">
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-info" id="btnPopulateFromFiles" title="Scan and populate from existing Excel files">
            <i class="fas fa-sync-alt me-1"></i>Populate from Files
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Popper.js is required for Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- SheetJS (XLSX) library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

  <!-- Your custom scripts -->
  <script src="../assets/js/script.js?v=20251111"></script>
  <script src="./js/utilities.js?v=20251127-tin-no-global"></script> <!-- STL utilities -->
  
  <!-- Stub functions that will be fully defined later -->
  <script>
    // Stub declarations for functions that will be defined in inline scripts
    // This prevents ReferenceError when employee-management.js tries to use them
    window.openEditEEModal = window.openEditEEModal || function() {};
    window.openEditERModal = window.openEditERModal || function() {};
    window.saveEEValue = window.saveEEValue || function() {};
    window.saveERValue = window.saveERValue || function() {};
  </script>
  
  <script src="./js/employee-management.js?v=20251128d"></script> <!-- STL-specific logic -->
  <script src="./js/stl-employee-status.js?v=20251127-remove-fix-v2"></script> <!-- STL employee status management -->
  <script src="./js/register-validation.js?v=20251127-tin-format"></script> <!-- STL registration validation -->
  <script src="./js/modal-handlers.js?v=20251111"></script> <!-- STL modal handlers -->
  <script src="./js/edit-employee-modal.js?v=20251127"></script> <!-- Enhanced edit modal with formatting -->
  
  <!-- Verify scripts loaded successfully -->
  <script>
    console.log('=== STL Script Loading Verification ===');
    console.log('loadEmployees function:', typeof window.loadEmployees);
    console.log('displayEmployees function:', typeof window.displayEmployees);
    console.log('formatPagibigNumber function:', typeof window.formatPagibigNumber);
    console.log('saveTableState function:', typeof window.saveTableState);
    console.log('loadTableState function:', typeof window.loadTableState);
    
    // Make functions globally accessible if they exist
    if (typeof loadEmployees !== 'undefined') {
      window.loadEmployees = loadEmployees;
      console.log('✓ loadEmployees is accessible');
    } else {
      console.warn('✗ loadEmployees not yet available - scripts may still be loading');
    }
  </script>
  
  <!-- Initialize Bootstrap tooltips and popovers -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global modal instances store
        window.modalInstances = {};

        function initializeModal(modalEl) {
            if (!modalEl || !modalEl.id) return;

            try {
                // Create modal instance with consistent configuration
                const modalInstance = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false,
                    focus: true
                });

                // Store instance globally
                window.modalInstances[modalEl.id] = modalInstance;

                // Setup modal event handlers with error boundaries
                const setupModalEvents = (modal) => {
                    const events = ['show', 'shown', 'hide', 'hidden'];
                    events.forEach(event => {
                        modal.addEventListener(`${event}.bs.modal`, function(e) {
                            try {
                                // Handle each event type
                                switch(event) {
                                    case 'shown':
                                        const firstInput = this.querySelector('input:not([type="hidden"])');
                                        if (firstInput) firstInput.focus();
                                        break;
                                    case 'hidden':
                                        const form = this.querySelector('form');
                                        if (form) form.reset();
                                        break;
                                }
                            } catch (err) {
                                console.error(`Error in modal ${event} event:`, err);
                            }
                        });
                    });
                };

                setupModalEvents(modalEl);
                return modalInstance;
            } catch (error) {
                console.error(`Failed to initialize modal ${modalEl.id}:`, error);
                return null;
            }
        }

        // Initialize all modals in the document
        document.querySelectorAll('.modal').forEach(initializeModal);

        // Setup modal triggers with error handling
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                try {
                    const targetId = this.getAttribute('data-bs-target') || this.getAttribute('href');
                    if (!targetId) throw new Error('No target modal specified');

                    const modalEl = document.querySelector(targetId);
                    if (!modalEl) throw new Error(`Modal not found: ${targetId}`);

                    const modalInstance = window.modalInstances[modalEl.id] || initializeModal(modalEl);
                    if (modalInstance) modalInstance.show();
                } catch (error) {
                    console.error('Modal trigger error:', error);
                }
            });
        });
    });
  </script>

  <script>
    // Function to load active employees for management (deactivation)
    function loadSTLActiveEmployeesForManagement() {
      const activeEmployeesList = document.getElementById('activeEmployeesManagementList');
      const loadingSpinner = document.getElementById('activeEmployeesManagementLoadingSpinner');
      
      if (!activeEmployeesList) {
        console.error('activeEmployeesManagementList element not found');
        return;
      }
      
      loadingSpinner.style.display = 'block';
      activeEmployeesList.innerHTML = '';

      fetch('./includes/get_stl_active_employees.php')
        .then(res => res.json())
        .then(data => {
          loadingSpinner.style.display = 'none';
          
          if (data.status !== 'success' || !data.data || !data.data.employees || data.data.employees.length === 0) {
            activeEmployeesList.innerHTML = '<div class="list-group-item text-center text-muted">No active STL employees found</div>';
            return;
          }

          activeEmployeesList.innerHTML = '';

          data.data.employees.forEach(employee => {
            const listItem = document.createElement('div');
            listItem.className = 'list-group-item';
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'd-flex justify-content-between align-items-start';
            
            const infoDiv = document.createElement('div');
            infoDiv.className = 'flex-grow-1';
            
            const nameSpan = document.createElement('h6');
            nameSpan.className = 'mb-1 fw-bold';
            
            const nameLink = document.createElement('a');
            nameLink.href = '#';
            nameLink.textContent = `${employee.last_name}, ${employee.first_name}${employee.middle_name ? ' ' + employee.middle_name : ''}`;
            nameLink.style.color = 'black';
            nameLink.style.textDecoration = 'none';
            nameLink.style.cursor = 'pointer';
            nameLink.style.fontWeight = 'bold';
            nameLink.title = 'Click to edit employee details';
            
            // Click handler to open edit modal
            nameLink.addEventListener('click', (e) => {
              e.preventDefault();
              e.stopPropagation();
              console.log('✓ Employee name clicked in Active Employees modal');
              
              // Close active employees modal
              const activeModal = document.getElementById('activeEmployeesManagementModal');
              if (activeModal) {
                try {
                  const instance = bootstrap.Modal.getInstance(activeModal);
                  if (instance) {
                    instance.hide();
                    console.log('✓ Closed active employees management modal');
                  }
                } catch (e) {
                  console.warn('Could not close active modal:', e.message);
                }
              }
              
              // Populate and show edit modal
              setTimeout(() => {
                openSTLEmployeeEditModal(
                  employee.id,
                  employee.last_name,
                  employee.first_name,
                  employee.middle_name || '',
                  employee.id_number,
                  employee.pagibig_number,
                  employee.tin || '',
                  employee.birthdate || '',
                  employee.ee || '0.00',
                  employee.er || '0.00'
                );
              }, 500);
            });
            
            nameSpan.appendChild(nameLink);
            
            const detailsSpan = document.createElement('small');
            detailsSpan.className = 'text-muted d-block';
            detailsSpan.innerHTML = `
              <span class="badge bg-info me-2">Pag-IBIG: ${employee.pagibig_number || 'N/A'}</span>
              <span class="badge bg-secondary me-2">ID: ${employee.id_number || 'N/A'}</span>
              <span class="badge bg-warning">Birthdate: ${employee.birthdate || 'N/A'}</span>
            `;
            
            infoDiv.appendChild(nameSpan);
            infoDiv.appendChild(detailsSpan);
            
            // Create deactivate button
            const deactivateBtn = document.createElement('button');
            deactivateBtn.className = 'btn btn-danger btn-sm ms-3';
            deactivateBtn.innerHTML = '<i class="fas fa-user-times me-1"></i> Deactivate';
            deactivateBtn.title = 'Deactivate employee';
            deactivateBtn.onclick = (e) => {
              e.preventDefault();
              if (confirm(`Are you sure you want to deactivate ${employee.last_name}, ${employee.first_name}?`)) {
                deactivateSTLEmployee(employee.id, listItem, employee.last_name, employee.first_name);
              }
            };
            
            contentDiv.appendChild(infoDiv);
            contentDiv.appendChild(deactivateBtn);
            listItem.appendChild(contentDiv);
            activeEmployeesList.appendChild(listItem);
          });
        })
        .catch(error => {
          console.error('Error loading STL active employees for management:', error);
          loadingSpinner.style.display = 'none';
          activeEmployeesList.innerHTML = '<div class="list-group-item text-center text-danger">Error loading employees</div>';
        });
    }

    // Function to load STL inactive employees
    function loadSTLInactiveEmployees() {
      const inactiveEmployeesList = document.getElementById('inactiveEmployeesList');
      const inactiveLoadingSpinner = document.getElementById('inactiveLoadingSpinner');
      
      if (!inactiveEmployeesList) {
        console.error('inactiveEmployeesList element not found');
        return;
      }
      
      inactiveLoadingSpinner.style.display = 'block';
      inactiveEmployeesList.innerHTML = '';

      fetch('./includes/get_stl_inactive_employees.php')
        .then(res => res.json())
        .then(data => {
          inactiveLoadingSpinner.style.display = 'none';
          
          // Handle both response formats
          const employees = data.data && data.data.employees ? data.data.employees : (Array.isArray(data.data) ? data.data : []);
          
          if (!data.success || !employees || employees.length === 0) {
            inactiveEmployeesList.innerHTML = '<div class="list-group-item text-center text-muted">No inactive STL employees found</div>';
            return;
          }

          inactiveEmployeesList.innerHTML = '';

          employees.forEach(employee => {
            const listItem = document.createElement('div');
            listItem.className = 'list-group-item';
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'd-flex justify-content-between align-items-start';
            
            const infoDiv = document.createElement('div');
            infoDiv.className = 'flex-grow-1';
            
            const nameSpan = document.createElement('h6');
            nameSpan.className = 'mb-1 fw-bold';
            
            const nameLink = document.createElement('a');
            nameLink.href = '#';
            nameLink.textContent = `${employee.last_name}, ${employee.first_name}${employee.middle_name ? ' ' + employee.middle_name : ''}`;
            nameLink.style.color = 'black';
            nameLink.style.textDecoration = 'none';
            nameLink.style.cursor = 'pointer';
            nameSpan.appendChild(nameLink);
            
            const detailsSpan = document.createElement('small');
            detailsSpan.className = 'text-muted d-block';
            detailsSpan.innerHTML = `
              <span class="badge bg-info me-2">Pag-IBIG: ${employee.pagibig_number || 'N/A'}</span>
              <span class="badge bg-secondary me-2">ID: ${employee.id_number || 'N/A'}</span>
              <span class="badge bg-warning">Birthdate: ${employee.birthdate || 'N/A'}</span>
            `;
            
            infoDiv.appendChild(nameSpan);
            infoDiv.appendChild(detailsSpan);
            
            // Create reactivate button
            const reactivateBtn = document.createElement('button');
            reactivateBtn.className = 'btn btn-success btn-sm ms-3';
            reactivateBtn.innerHTML = '<i class="fas fa-user-check me-1"></i> Reactivate';
            reactivateBtn.title = 'Reactivate employee';
            reactivateBtn.onclick = (e) => {
              e.preventDefault();
              if (confirm(`Are you sure you want to reactivate ${employee.last_name}, ${employee.first_name}?`)) {
                reactivateSTLEmployee(employee.id, listItem, employee.last_name, employee.first_name);
              }
            };
            
            contentDiv.appendChild(infoDiv);
            contentDiv.appendChild(reactivateBtn);
            listItem.appendChild(contentDiv);
            inactiveEmployeesList.appendChild(listItem);
          });
        })
        .catch(error => {
          console.error('Error loading STL inactive employees:', error);
          inactiveLoadingSpinner.style.display = 'none';
          inactiveEmployeesList.innerHTML = '<div class="list-group-item text-center text-danger">Error loading employees</div>';
        });
    }

    // Function to deactivate an STL employee
    function deactivateSTLEmployee(employeeId, listItem, lastName, firstName) {
      const formData = new FormData();
      formData.append('employee_id', employeeId);

      fetch('./includes/deactivate_employee.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Remove the employee from the list with animation
          listItem.style.transition = 'opacity 0.3s ease-out';
          listItem.style.opacity = '0';
          setTimeout(() => {
            listItem.remove();
          }, 300);
          
          // Show success message
          const alertDiv = document.createElement('div');
          alertDiv.className = 'alert alert-success alert-dismissible fade show';
          alertDiv.role = 'alert';
          alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            <strong>${lastName}, ${firstName}</strong> has been deactivated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          `;
          
          // Insert alert at the top of management modal body
          const modalBody = document.querySelector('#activeEmployeesManagementModal .modal-body');
          const listGroup = document.getElementById('activeEmployeesManagementList');
          modalBody.insertBefore(alertDiv, listGroup);
          
          // Reload inactive employees list
          loadSTLInactiveEmployees();
          
          // Auto-dismiss alert after 3 seconds
          setTimeout(() => {
            alertDiv.remove();
          }, 3000);
        } else {
          throw new Error(data.message || 'Failed to deactivate employee');
        }
      })
      .catch(error => {
        console.error('Error deactivating employee:', error);
        alert('Error: ' + error.message);
      });
    }

    // Function to reactivate an STL employee
    function reactivateSTLEmployee(employeeId, listItem, lastName, firstName) {
      const formData = new FormData();
      formData.append('employee_id', employeeId);

      fetch('./includes/reactivate_employee.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Remove the employee from the list with animation
          listItem.style.transition = 'opacity 0.3s ease-out';
          listItem.style.opacity = '0';
          setTimeout(() => {
            listItem.remove();
          }, 300);
          
          // Show success message
          const alertDiv = document.createElement('div');
          alertDiv.className = 'alert alert-success alert-dismissible fade show';
          alertDiv.role = 'alert';
          alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            <strong>${lastName}, ${firstName}</strong> has been reactivated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          `;
          
          // Insert alert at the top of inactive modal body
          const modalBody = document.querySelector('#inactiveEmployeesModal .modal-body');
          const listGroup = document.getElementById('inactiveEmployeesList');
          modalBody.insertBefore(alertDiv, listGroup);
          
          // Reload active employees in both selection and management modals
          loadSTLActiveEmployees();
          loadSTLActiveEmployeesForManagement();
          
          // Auto-dismiss alert after 3 seconds
          setTimeout(() => {
            alertDiv.remove();
          }, 3000);
        } else {
          throw new Error(data.message || 'Failed to reactivate employee');
        }
      })
      .catch(error => {
        console.error('Error reactivating employee:', error);
        alert('Error: ' + error.message);
      });
    }

    // Function to open Edit ER Modal
    function openEditERModal(pagibigNo, erCell) {
      const currentER = erCell.textContent.trim();
      document.getElementById('editER_pagibig_no').value = pagibigNo;
      document.getElementById('editER_value').value = currentER;
      const modal = new bootstrap.Modal(document.getElementById('editERModal'));
      modal.show();
    }

    // Function to open Edit EE Modal (STL Only - when EE is 0.00)
    function openEditEEModal(pagibigNo, eeCell) {
      const currentEE = eeCell.textContent.trim();
      document.getElementById('editEE_pagibig_no').value = pagibigNo;
      document.getElementById('editEE_value').value = currentEE === '0.00' ? '' : currentEE;
      const modal = new bootstrap.Modal(document.getElementById('editEEModal'));
      modal.show();
      
      // Focus on the input field
      setTimeout(() => {
        document.getElementById('editEE_value').focus();
        document.getElementById('editEE_value').select();
      }, 300);
    }

    // Function to save ER value
    function saveERValue() {
      const pagibigNo = document.getElementById('editER_pagibig_no').value;
      const newER = document.getElementById('editER_value').value;
      
      // Validate input
      if (isNaN(newER) || newER === '') {
        alert('Please enter a valid number');
        return;
      }
      
      // Update database
      const formData = new FormData();
      formData.append('pagibig_no', pagibigNo);
      formData.append('er', newER);
      
      fetch('./includes/update_stl_er.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Find and update the ER cell in the table
          const table = document.getElementById('selectedEmployeesTable');
          const tbody = table.querySelector('tbody');
          const rows = tbody.querySelectorAll('tr');
          
          for (let row of rows) {
            const pagibigCell = row.querySelector('td:first-child');
            if (pagibigCell) {
              // Compare only digits to handle formatted vs unformatted numbers
              const cellDigits = pagibigCell.textContent.replace(/\D/g, '');
              const paramDigits = pagibigNo.replace(/\D/g, '');
              
              if (cellDigits === paramDigits) {
                const erCell = row.querySelector('td:nth-child(7)');
                // Format the new ER value to 2 decimal places
                const formattedER = parseFloat(newER).toFixed(2);
                erCell.textContent = formattedER;
                erCell.style.cursor = 'pointer';
                erCell.style.textAlign = 'right';
                erCell.style.width = '70px';
                erCell.onclick = function() { openEditERModal(pagibigNo, this); };
                break;
              }
            }
          }
          
          // Close modal and show success message
          const modal = bootstrap.Modal.getInstance(document.getElementById('editERModal'));
          modal.hide();
          
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'ER value updated successfully!',
            showConfirmButton: false,
            timer: 1500
          });
        } else {
          alert('Failed to save: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error updating ER:', error);
        alert('Error updating value: ' + error.message);
      });
    }

    // Function to save EE value (STL Only - clears ER when EE is set)
    function saveEEValue() {
      const pagibigNo = document.getElementById('editEE_pagibig_no').value;
      const newEE = document.getElementById('editEE_value').value;
      
      // Validate input
      if (newEE === '' || isNaN(newEE) || parseFloat(newEE) < 0) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Input',
          text: 'Please enter a valid positive number',
          confirmButtonText: 'OK'
        });
        return;
      }
      
      // Update database
      const formData = new FormData();
      formData.append('pagibig_no', pagibigNo);
      formData.append('ee', newEE);
      
      fetch('./includes/update_stl_ee.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Find and update both EE and ER cells in the table
          const table = document.getElementById('selectedEmployeesTable');
          const tbody = table.querySelector('tbody');
          const rows = tbody.querySelectorAll('tr');
          
          for (let row of rows) {
            const pagibigCell = row.querySelector('td:first-child');
            if (pagibigCell) {
              // Compare only digits to handle formatted vs unformatted numbers
              const cellDigits = pagibigCell.textContent.replace(/\D/g, '');
              const paramDigits = pagibigNo.replace(/\D/g, '');
              
              if (cellDigits === paramDigits) {
                // Update EE cell (6th column)
                const eeCell = row.querySelector('td:nth-child(6)');
                const formattedEE = parseFloat(newEE).toFixed(2);
                eeCell.textContent = formattedEE;
                eeCell.style.cursor = 'pointer';
                eeCell.style.textAlign = 'right';
                eeCell.style.width = '70px';
                eeCell.onclick = function() { openEditEEModal(pagibigNo, this); };
                break;
              }
            }
          }
          
          // Close modal and show success message
          const modal = bootstrap.Modal.getInstance(document.getElementById('editEEModal'));
          modal.hide();
          
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'EE value updated successfully!',
            showConfirmButton: false,
            timer: 2000
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to save: ' + (data.message || 'Unknown error'),
            confirmButtonText: 'OK'
          });
        }
      })
      .catch(error => {
        console.error('Error updating EE:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error updating value: ' + error.message,
          confirmButtonText: 'OK'
        });
      });
    }

    // Function to open STL employee edit modal and populate with employee data
    function openSTLEmployeeEditModal(id, lastName, firstName, middleName, idNumber, pagibigNo, tin, birthdate, ee, er) {
        console.log('openSTLEmployeeEditModal called with:', { id, lastName, firstName, middleName, idNumber, pagibigNo, tin, birthdate, ee, er });
        
        // Ensure modal exists first
        const editSTLModal = document.getElementById('editSTLModal');
        if (!editSTLModal) {
            console.error('editSTLModal element not found in DOM');
            alert('Error: Edit modal not found. Please refresh the page.');
            return;
        }
        console.log('✓ Edit modal element found');
        
        // Populate employee details in the edit modal
        const employeeIdField = document.getElementById('editSTL_employee_id');
        if (employeeIdField) {
            employeeIdField.value = id || '';
            console.log('✓ Set employee ID:', id);
        }
        
        const idNoField = document.getElementById('editSTL_id_no');
        if (idNoField) {
            idNoField.value = idNumber || '';
            console.log('✓ Set ID number:', idNumber);
        }
        
        const pagibigNoHiddenField = document.getElementById('editSTL_pagibig_no');
        if (pagibigNoHiddenField) {
            pagibigNoHiddenField.value = pagibigNo || '';
        }
        
        const pagibigNoDisplayField = document.getElementById('editSTL_pagibigNo');
        if (pagibigNoDisplayField) {
            const formatted = formatPagibigNumber(pagibigNo || '');
            pagibigNoDisplayField.value = formatted || '';
            console.log('✓ Set Pag-IBIG number (formatted):', formatted);
        }
        
        const lastNameField = document.getElementById('editSTL_last_name');
        if (lastNameField) {
            lastNameField.value = (lastName || '').toUpperCase();
            console.log('✓ Set last name:', lastName);
        }
        
        const firstNameField = document.getElementById('editSTL_first_name');
        if (firstNameField) {
            firstNameField.value = (firstName || '').toUpperCase();
            console.log('✓ Set first name:', firstName);
        }
        
        const middleNameField = document.getElementById('editSTL_middle_name');
        if (middleNameField) {
            middleNameField.value = (middleName || '').toUpperCase();
            console.log('✓ Set middle name:', middleName);
        }
        
        const tinField = document.getElementById('editSTL_tin');
        if (tinField) {
            const formattedTin = formatTIN(tin || '');
            tinField.value = formattedTin || '';
            console.log('✓ Set TIN (formatted):', formattedTin);
        }
        
        const birthdateField = document.getElementById('editSTL_birthdate');
        if (birthdateField) {
            birthdateField.value = birthdate || '';
            console.log('✓ Set birthdate:', birthdate);
        }
        
        console.log('✓ All modal fields populated');
        
        // Clear any previous errors
        const errorDiv = document.getElementById('editSTLError');
        if (errorDiv) {
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';
        }
        
        // Show the edit employee modal
        try {
            // Remove any existing modal instance
            let editModalInstance = bootstrap.Modal.getInstance(editSTLModal);
            if (editModalInstance) {
                editModalInstance.dispose();
                console.log('Disposed previous modal instance');
            }
            
            // Create new modal instance with proper options
            const editModal = new bootstrap.Modal(editSTLModal, {
                backdrop: 'static',
                keyboard: false,
                focus: true
            });
            
            // Show the modal
            editModal.show();
            console.log('✓ Edit modal shown successfully');
            
            // Setup formatting for all input fields
            setTimeout(function() {
                if (typeof setupEditModalFormatting === 'function') {
                    setupEditModalFormatting();
                    console.log('✓ Edit modal formatting setup complete');
                }
                
                // Force focus on first input
                const firstInput = editSTLModal.querySelector('input:not([type="hidden"]):not([readonly])');
                if (firstInput && firstInput.id !== 'editSTL_id_no') {
                    firstInput.focus();
                    console.log('✓ Focused on first editable input field');
                }
            }, 100);
            
        } catch (error) {
            console.error('✗ Error showing edit modal:', error);
            console.error('Error stack:', error.stack);
            alert('Error opening edit form: ' + error.message);
        }
    }

    // Function to save STL employee changes
    // This function is maintained for backward compatibility
    // It now calls the enhanced version with better validation and formatting
    function saveSTLEmployeeChanges() {
        if (typeof saveSTLEmployeeChangesEnhanced === 'function') {
            saveSTLEmployeeChangesEnhanced();
        } else {
            console.error('Enhanced save function not available');
            alert('Error: Save function not loaded properly. Please refresh the page.');
        }
    }

    // Function to delete employee from table and database
    function deleteSTLEmployeeRow(button, pagibigNo) {
      const row = button.closest('tr');
      
      // Delete from database
      const formData = new FormData();
      formData.append('pagibig_no', pagibigNo);
      
      fetch('./includes/delete_stl_selected_employee.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          row.remove();
        } else {
          console.error('Failed to delete:', data.message);
          alert('Failed to remove employee: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error deleting employee:', error);
        // Still remove from UI even if delete fails
        row.remove();
      });
    }

    // Function to add employee to the main table and database
    function addEmployeeToTable(employee) {
      const table = document.getElementById('selectedEmployeesTable');
      const tbody = table.querySelector('tbody');
      
      // Remove ALL placeholder/message rows first
      const allRows = tbody.querySelectorAll('tr');
      allRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        // If row only has one cell with colspan, it's a message row
        if (cells.length === 1 && cells[0].getAttribute('colspan')) {
          row.remove();
        }
      });
      
      // Check if employee already exists in table
      const existingRows = tbody.querySelectorAll('tr');
      for (let row of existingRows) {
        const pagibigCell = row.querySelector('td:first-child');
        if (pagibigCell && pagibigCell.textContent.trim() === employee.pagibig_number) {
          return false;
        }
      }
      
      // Create new row with ER empty
      const newRow = document.createElement('tr');
      newRow.innerHTML = `
        <td>${formatPagibigNumber(employee.pagibig_number || '')}</td>
        <td>${employee.id_number || ''}</td>
        <td>${employee.last_name || ''}</td>
        <td>${employee.first_name || ''}</td>
        <td>${employee.middle_name || ''}</td>
        <td style="width: 70px; text-align: right;"></td>
        <td>${formatTIN(employee.tin || '')}</td>
        <td>${employee.birthdate || ''}</td>
        <td>
        </td>
      `;
      
      tbody.appendChild(newRow);
      
      // Clean up any message rows
      setTimeout(() => {
        const allRows = tbody.querySelectorAll('tr');
        allRows.forEach(row => {
          const cells = row.querySelectorAll('td');
          if (cells.length === 1 && cells[0].getAttribute('colspan')) {
            row.remove();
          }
        });
      }, 100);
      
      // Save to database (fire and forget - no need to wait for response to update UI)
      fetch('./includes/save_stl_selected_employee.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          pagibig_number: employee.pagibig_number,
          id_number: employee.id_number
        })
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          console.error('Failed to save employee to database:', data.message);
        }
      })
      .catch(error => {
        console.error('Error saving employee to database:', error);
      });
      
      return true;
    }



    // Function to load STL active employees for selection
    function loadSTLActiveEmployees() {
      const tableBody = document.getElementById('activeEmployeesTableBody');
      const loadingSpinner = document.getElementById('activeEmployeesLoadingSpinner');
      
      if (!tableBody) {
        console.error('activeEmployeesTableBody element not found');
        return;
      }
      
      loadingSpinner.style.display = 'block';
      tableBody.innerHTML = '';

      fetch('./includes/get_stl_active_employees.php')
        .then(res => res.json())
        .then(data => {
          loadingSpinner.style.display = 'none';
          
          if (data.status !== 'success' || !data.data || !data.data.employees || data.data.employees.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No active STL employees found</td></tr>';
            return;
          }

          tableBody.innerHTML = '';

          data.data.employees.forEach((employee, index) => {
            // Use already_selected flag from database instead of checking HTML DOM
            const isAlreadyAdded = employee.already_selected === 1 || employee.already_selected === true;
            const row = document.createElement('tr');
            
            // Create checkbox cell
            const checkboxCell = document.createElement('td');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input active-employee-checkbox';
            checkbox.value = employee.id;
            checkbox.dataset.employee = JSON.stringify(employee);
            if (isAlreadyAdded) {
              checkbox.disabled = true;
            }
            checkboxCell.appendChild(checkbox);
            
            // Create name cell with clickable link
            const nameCell = document.createElement('td');
            const nameLink = document.createElement('a');
            nameLink.href = '#';
            nameLink.style.color = 'black';
            nameLink.style.textDecoration = 'none';
            nameLink.style.fontWeight = 'bold';
            nameLink.textContent = `${employee.last_name}, ${employee.first_name}${employee.middle_name ? ' ' + employee.middle_name : ''}`;
            nameLink.addEventListener('click', (e) => e.preventDefault());
            nameCell.appendChild(nameLink);
            
            // Create ID cell
            const idCell = document.createElement('td');
            idCell.textContent = employee.id_number || 'N/A';
            
            // Create status cell
            const statusCell = document.createElement('td');
            const statusBadge = document.createElement('span');
            statusBadge.className = isAlreadyAdded ? 'badge bg-warning text-dark' : 'badge bg-success';
            statusBadge.textContent = isAlreadyAdded ? 'ALREADY ADDED' : 'NOT ADDED';
            statusCell.appendChild(statusBadge);
            
            // Add all cells to row
            row.appendChild(checkboxCell);
            row.appendChild(nameCell);
            row.appendChild(idCell);
            row.appendChild(statusCell);
            
            // Append row to table
            tableBody.appendChild(row);
          });
          
          // Setup checkbox listeners
          setupActiveEmployeeCheckboxes();
        })
        .catch(error => {
          console.error('Error loading STL active employees:', error);
          loadingSpinner.style.display = 'none';
          tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading employees</td></tr>';
        });
    }

    // Function to setup checkbox listeners for active employees
    function setupActiveEmployeeCheckboxes() {
      const checkboxes = document.querySelectorAll('.active-employee-checkbox');
      const selectAllBtn = document.getElementById('selectAllActiveBtn');
      const addBtn = document.getElementById('activeEmployeesAddBtn');
      const countSpan = document.getElementById('activeEmployeesCount');
      const searchInput = document.getElementById('activeEmployeesSearch');

      // Select All functionality
      if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
          const allChecked = Array.from(checkboxes).every(cb => cb.checked);
          checkboxes.forEach(cb => {
            // Only check visible checkboxes
            if (cb.closest('tr').style.display !== 'none') {
              cb.checked = !allChecked;
            }
          });
          updateActiveEmployeeCount();
        });
      }

      // Individual checkbox change
      checkboxes.forEach(cb => {
        cb.addEventListener('change', updateActiveEmployeeCount);
      });

      // Search functionality
      if (searchInput) {
        searchInput.addEventListener('keyup', function() {
          const query = this.value.toLowerCase();
          const rows = document.querySelectorAll('#activeEmployeesTableBody tr');
          rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
          });
        });
      }

      // Add Selected button
      if (addBtn) {
        addBtn.addEventListener('click', function() {
          const selectedCheckboxes = document.querySelectorAll('.active-employee-checkbox:checked');
          if (selectedCheckboxes.length === 0) {
            return;
          }

          let addedCount = 0;
          selectedCheckboxes.forEach(cb => {
            const employee = JSON.parse(cb.dataset.employee);
            if (addEmployeeToTable(employee)) {
              addedCount++;
            }
          });

          if (addedCount > 0) {
            // Clear checkboxes and close modal
            selectedCheckboxes.forEach(cb => cb.checked = false);
            updateActiveEmployeeCount();
            const modal = bootstrap.Modal.getInstance(document.getElementById('activeEmployeesModal'));
            if (modal) modal.hide();
          }
        });
      }
    }

    function updateActiveEmployeeCount() {
      const selected = document.querySelectorAll('.active-employee-checkbox:checked').length;
      const countSpan = document.getElementById('activeEmployeesCount');
      if (countSpan) {
        countSpan.textContent = `${selected} employee${selected !== 1 ? 's' : ''} selected`;
      }
    }

    // Save STL Summary to Database
    function saveSummaryToDatabase(filename, month, year, numBorrowers, totalAmount) {
        return fetch('./includes/save_stl_summary.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                filename: filename,
                month: month,
                year: year,
                num_borrowers: numBorrowers,
                total_deducted_amount: totalAmount
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log('STL Summary saved:', data);
            return data;
        })
        .catch(error => {
            console.error('Error saving summary:', error);
            return { status: 'error', message: error.message };
        });
    }

    // Function to load STL Summary
    function loadSTLSummary() {
      const loadingSpinner = document.getElementById('stlSummaryLoadingSpinner');
      const container = document.getElementById('stlSummaryContainer');
      const tableBody = document.getElementById('stlSummaryTableBody');
      
      if (!tableBody) {
        console.error('stlSummaryTableBody element not found');
        return;
      }
      
      loadingSpinner.style.display = 'block';
      container.style.display = 'none';
      tableBody.innerHTML = '';

      fetch('./includes/get_stl_summary.php')
        .then(res => res.json())
        .then(data => {
          loadingSpinner.style.display = 'none';
          
          if (data.status !== 'success' || !data.data || data.data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No STL data available</td></tr>';
            container.style.display = 'block';
            return;
          }

          tableBody.innerHTML = '';

          data.data.forEach(summary => {
            const row = document.createElement('tr');
            const formattedAmount = parseFloat(summary.deducted_amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            
            row.innerHTML = `
              <td><strong>${summary.year_month || 'N/A'}</strong></td>
              <td class="text-center"><span class="badge bg-info">${summary.num_borrowers || 0}</span></td>
              <td class="text-end"><strong>₱${formattedAmount}</strong></td>
              <td class="text-center">
                <button class="btn btn-sm btn-success" title="Download Excel" onclick="downloadSTLSummaryFile('${summary.filename}', '${summary.month_name}', ${summary.year})">
                  <i class="fas fa-download"></i> Download
                </button>
                <button class="btn btn-sm btn-primary" title="View Details" onclick="viewSTLSummaryDetails('${summary.year_month}', ${summary.num_borrowers}, ${summary.deducted_amount})">
                  <i class="fas fa-eye"></i> Details
                </button>
              </td>
            `;
            tableBody.appendChild(row);
          });

          container.style.display = 'block';
        })
        .catch(error => {
          console.error('Error loading STL summary:', error);
          loadingSpinner.style.display = 'none';
          tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading summary data</td></tr>';
          container.style.display = 'block';
        });
    }

    // Function to populate STL Summary from existing files
    function populateSTLSummaryFromFiles() {
      const btnPopulate = document.getElementById('btnPopulateFromFiles');
      const originalText = btnPopulate.innerHTML;
      
      btnPopulate.disabled = true;
      btnPopulate.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Populating...';
      
      fetch('./includes/populate_stl_summary.php')
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            alert(`Populated ${data.data.populated} file(s) from existing Excel files.`);
            // Reload the summary table
            loadSTLSummary();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error populating summary:', error);
          alert('Error populating from files: ' + error.message);
        })
        .finally(() => {
          btnPopulate.disabled = false;
          btnPopulate.innerHTML = originalText;
        });
    }

    // Download STL Excel file for specific month from backup
    function downloadSTLSummaryFile(filename, month, year) {
      // Use the download endpoint that serves from backup folder
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = './includes/download_stl_excel.php';
      
      const monthInput = document.createElement('input');
      monthInput.type = 'hidden';
      monthInput.name = 'month';
      monthInput.value = month;
      
      const yearInput = document.createElement('input');
      yearInput.type = 'hidden';
      yearInput.name = 'year';
      yearInput.value = year;
      
      form.appendChild(monthInput);
      form.appendChild(yearInput);
      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);
    }

    // Regenerate STL Excel file from database and download
    function regenerateAndDownloadSTL(filename, month, year) {
      Swal.fire({
        title: 'Regenerating File',
        html: 'Regenerating from database records...',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      fetch('./includes/regenerate_stl_excel.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          month: month,
          year: year
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          Swal.close();
          
          // Download the regenerated file
          const link = document.createElement('a');
          link.href = '../generated excel files/' + data.filename;
          link.download = data.filename;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          
          Swal.fire({
            title: 'Success!',
            html: `File regenerated and downloaded successfully<br><small>${data.record_count} records, ₱${parseFloat(data.total_ee).toFixed(2)}</small>`,
            icon: 'success'
          });
        } else {
          Swal.fire({
            title: 'Error',
            text: data.message,
            icon: 'error'
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          title: 'Error',
          text: 'Failed to regenerate file',
          icon: 'error'
        });
      });
    }

    // View STL Summary Details for specific month
    function viewSTLSummaryDetails(yearMonth, borrowers, amount) {
      const formattedAmount = parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      
      Swal.fire({
        title: 'STL Summary - ' + yearMonth,
        html: `
          <div style="text-align: left; padding: 20px;">
            <p><strong>Month & Year:</strong> ${yearMonth}</p>
            <p><strong>Number of Borrowers:</strong> <span style="color: #007bff; font-weight: bold; font-size: 18px;">${borrowers}</span></p>
            <p><strong>Total Deducted Amount:</strong> <span style="color: #28a745; font-weight: bold; font-size: 18px;">₱${formattedAmount}</span></p>
          </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close',
        confirmButtonColor: '#dc3545',
        width: '500px'
      });
    }

    // Setup modal event listeners
    document.addEventListener('DOMContentLoaded', function() {
      const activeEmployeesModal = document.getElementById('activeEmployeesModal');
      const activeEmployeesManagementModal = document.getElementById('activeEmployeesManagementModal');
      const inactiveEmployeesModal = document.getElementById('inactiveEmployeesModal');
      const stlSummaryModal = document.getElementById('stlSummaryModal');
      
      if (activeEmployeesModal) {
        activeEmployeesModal.addEventListener('show.bs.modal', loadSTLActiveEmployees);
      }
      if (activeEmployeesManagementModal) {
        activeEmployeesManagementModal.addEventListener('show.bs.modal', loadSTLActiveEmployeesForManagement);
      }
      if (inactiveEmployeesModal) {
        inactiveEmployeesModal.addEventListener('show.bs.modal', loadSTLInactiveEmployees);
      }
      if (stlSummaryModal) {
        stlSummaryModal.addEventListener('show.bs.modal', loadSTLSummary);
      }
      
      // Add event listener for populate button
      const btnPopulateFromFiles = document.getElementById('btnPopulateFromFiles');
      if (btnPopulateFromFiles) {
        btnPopulateFromFiles.addEventListener('click', populateSTLSummaryFromFiles);
      }
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add input validation for yearInput - only allow digits
        const yearInput = document.getElementById('yearInput');
        if (yearInput) {
            yearInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        const generateExcelBtn = document.getElementById('btnGenerateExcel');
        if (generateExcelBtn) {
            generateExcelBtn.addEventListener('click', function() {
                const month = document.getElementById('month').value;
                const year = document.getElementById('yearInput').value;

                if (!month || !year) {
                    alert('Please select both month and year');
                    return;
                }

                // Validate year is a 4-digit number
                if (!/^\d{4}$/.test(year) || year < 1890) {
                    alert('Please enter a valid year (1890 or later)');
                    return;
                }

                // Get table data
                const table = document.getElementById('selectedEmployeesTable');
                const rows = table.querySelectorAll('tbody tr');

                if (rows.length === 0) {
                    alert('No data available in table');
                    return;
                }

                // Show loading state
                generateExcelBtn.disabled = true;
                generateExcelBtn.textContent = 'Generating...';

                try {
                    // Prepare data for Excel
                    const data = [];
                    
                    // Add headers
                    const headers = [
                        'PAG-IBIG MID NO.',
                        'EMPLOYEE NUMBER',
                        'LAST NAME',
                        'FIRST NAME',
                        'MIDDLE NAME',
                        'EE',
                        'ER',
                        'TIN',
                        'BIRTHDATE',                        
                    ];
                    data.push(headers);

          // Add data from table
          rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            
            // Skip if row doesn't have enough cells (e.g., empty placeholder rows)
            if (cells.length < 9) {
              return;
            }
            
            // Parse EE and ER but keep blank when zero or empty
            const eeText = cells[5].textContent.trim();
            const erText = cells[6].textContent.trim();

            const eeValue = (eeText !== '' && !isNaN(Number(eeText)) && Number(eeText) !== 0) ? Number(eeText) : '';
            const erValue = (erText !== '' && !isNaN(Number(erText)) && Number(erText) !== 0) ? Number(erText) : '';

            const rowData = [
              cells[0].textContent.trim(), // PAG-IBIG
              cells[1].textContent.trim(), // ID
              cells[2].textContent.trim(), // Last Name
              cells[3].textContent.trim(), // First Name
              cells[4].textContent.trim(), // Middle Name
              eeValue, // EE Share - blank if zero/empty
              erValue, // ER Share - blank if zero/empty
              cells[7].textContent.trim(), // TIN
              cells[8].textContent.trim(), // Birthdate
            ];
            data.push(rowData);
          });

                    // Create worksheet with default style
                    const ws = XLSX.utils.aoa_to_sheet(data);

                    // Set column widths
                    ws['!cols'] = [
                        {wch: 20}, // PAG-IBIG
                        {wch: 15}, // Employee Number
                        {wch: 20}, // Last Name
                        {wch: 20}, // First Name
                        {wch: 20}, // Middle Name
                        {wch: 15}, // EE Share
                        {wch: 15}, // ER Share
                        {wch: 15}, // TIN
                        {wch: 12}, // Birthdate
                    ];

                    // Define a center-aligned style
                    const centerStyle = {
                        alignment: {
                            horizontal: 'center',
                            vertical: 'center',
                            wrapText: true
                        }
                    };

                    // Apply center alignment to all cells
                    const range = XLSX.utils.decode_range(ws['!ref']);
                    for (let row = range.s.r; row <= range.e.r; row++) {
                        for (let col = range.s.c; col <= range.e.c; col++) {
                            const cell_address = XLSX.utils.encode_cell({ r: row, c: col });
                            if (!ws[cell_address]) {
                                ws[cell_address] = { v: '', t: 's' };
                            }
                            if (!ws[cell_address].s) {
                                ws[cell_address].s = {};
                            }
                            // Apply the center style
                            ws[cell_address].s = centerStyle;
                        }
                    }

          // Ensure numeric columns are properly formatted, but skip empty cells
          const numericColumns = [5, 6]; // EE and ER columns (0-based index)
          for (let row = range.s.r + 1; row <= range.e.r; row++) { // Skip header row
            numericColumns.forEach(col => {
              const cell_address = XLSX.utils.encode_cell({ r: row, c: col });
              if (ws[cell_address] && ws[cell_address].v !== '' && !isNaN(Number(ws[cell_address].v))) {
                ws[cell_address].t = 'n'; // Set type as number
                // Use integer format if the value is an integer, otherwise keep as general
                ws[cell_address].z = Number(ws[cell_address].v) % 1 === 0 ? '0' : '0.##';
              }
            });
          }

                    // Create workbook and add worksheet
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'PAG-IBIG Contributions');

                    // Generate and save file
                    // Clean year (remove any .00 or decimal parts) and add _Stl suffix
                    const yearClean = String(year).replace(/\.00$|\..*$/, '');
                    const filename = `${month}_${yearClean}_Stl.xls`;
                    
                    // Calculate totals for summary
                    let totalNumBorrowers = 0;
                    let totalAmount = 0;
                    
                    rows.forEach(row => {
                      const cells = row.querySelectorAll('td');
                      if (cells.length >= 6) {
                        totalNumBorrowers++;
                        const eeText = cells[5].textContent.trim();
                        if (eeText && !isNaN(Number(eeText))) {
                          totalAmount += Number(eeText);
                        }
                      }
                    });

                    // Generate Excel file in browser and send to server for backup
                    try {
                      // Create a temporary download to get file content, then send to server
                      const wbout = XLSX.write(wb, {bookType: 'xlsx', type: 'array'});
                      const blob = new Blob([wbout], {type: 'application/vnd.ms-excel'});
                      
                      // Create FormData with file upload for backup only
                      const formData = new FormData();
                      formData.append('month', month);
                      formData.append('year', yearClean);
                      formData.append('num_borrowers', totalNumBorrowers);
                      formData.append('total_deducted_amount', totalAmount);
                      formData.append('file', blob, filename);
                      
                      // Send to server for backup saving (user will choose primary location)
                      fetch('./includes/generate_and_save_stl_excel.php', {
                        method: 'POST',
                        body: formData
                      })
                      .then(res => {
                        if (!res.ok) {
                          throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.text().then(text => {
                          try {
                            return JSON.parse(text);
                          } catch (e) {
                            console.error('Invalid JSON response:', text);
                            throw new Error('Invalid server response');
                          }
                        });
                      })
                      .then(data => {
                        if (data.status === 'success') {
                          // Trigger browser download dialog - user chooses where to save
                          const link = document.createElement('a');
                          link.href = URL.createObjectURL(blob);
                          link.download = filename;
                          document.body.appendChild(link);
                          link.click();
                          document.body.removeChild(link);
                          URL.revokeObjectURL(link.href);
                          
                          // Close modal
                          const modal = bootstrap.Modal.getInstance(document.getElementById('saveModal'));
                          if (modal) {
                            modal.hide();
                          }
                          
                          alert('Excel file generated! Backup saved automatically.\nFile download started - choose where to save.');
                        } else {
                          alert('Error saving backup: ' + (data.message || 'Unknown error'));
                        }
                      })
                      .catch(error => {
                        console.error('Error saving backup:', error);
                        // Still allow download even if backup save fails
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(link.href);
                        
                        alert('File downloaded. Warning: Backup save failed - Error: ' + error.message);
                      })
                      .finally(() => {
                        generateExcelBtn.disabled = false;
                        generateExcelBtn.textContent = 'Generate Excel';
                      });
                      
                    } catch (error) {
                      console.error('Error generating Excel:', error);
                      alert('Error generating Excel file: ' + error.message);
                      generateExcelBtn.disabled = false;
                      generateExcelBtn.textContent = 'Generate Excel';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while generating the Excel file');
                } finally {
                    // Reset button state
                    generateExcelBtn.disabled = false;
                    generateExcelBtn.textContent = 'Generate Excel';
                }
            });
        }
    });
    


    // Filter table rows by employee name (first, last, middle) and respect rows-per-page
    const searchInput = document.getElementById('searchInput');
    const rowsPerPage = document.getElementById('rowsPerPage');
    const table = document.getElementById('selectedEmployeesTable');
    
    // Function to save table state to localStorage
    function saveTableState() {
      const state = {
        searchQuery: searchInput.value,
        rowsPerPage: rowsPerPage.value,
      };
      localStorage.setItem('contributionTableState', JSON.stringify(state));
    }

    // Function to load table state from localStorage
    function loadTableState() {
      const saved = localStorage.getItem('contributionTableState');
      if (saved) {
        const state = JSON.parse(saved);
        searchInput.value = state.searchQuery || '';
        rowsPerPage.value = state.rowsPerPage || '10';
      }
    }

    // Make applyFilter globally accessible
    window.applyFilter = function() {
      if (!searchInput || !table || !rowsPerPage) return;

      const tbody = table.querySelector('tbody');
      
      // First, remove ALL message rows
      const messageRows = tbody.querySelectorAll('tr');
      messageRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 1 && cells[0].getAttribute('colspan')) {
          row.remove();
        }
      });

      const q = searchInput.value.trim().toLowerCase();
      const limit = parseInt(rowsPerPage.value, 10);
      const rows = Array.from(tbody.querySelectorAll('tbody tr'));
      
      // Save state after filtering
      saveTableState();

      // Get only data rows (not message rows)
      const dataRows = rows.filter(row => {
        const cells = row.querySelectorAll('td');
        return cells.length > 1 || !cells[0].getAttribute('colspan');
      });

      // If no data rows, show message
      if (dataRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted"><em>No STL employees found. Please add active employees using "Register Employee" or the selection modal.</em></td></tr>';
        return;
      }

      // First, mark matches
      const matchedRows = dataRows.filter(row => {
        const cells = row.querySelectorAll('td');
        const last = (cells[2] && cells[2].textContent.toLowerCase()) || '';
        const first = (cells[3] && cells[3].textContent.toLowerCase()) || '';
        const middle = (cells[4] && cells[4].textContent.toLowerCase()) || '';
        const combined = `${first} ${middle} ${last}`.trim();

        return q === '' || last.indexOf(q) !== -1 || first.indexOf(q) !== -1 || middle.indexOf(q) !== -1 || combined.indexOf(q) !== -1;
      });

      // Hide all data rows first
      dataRows.forEach(r => r.style.display = 'none');

      // Show up to limit (0 means show all)
      let count = 0;
      matchedRows.forEach(r => {
        if (limit === 0 || count < limit) {
          r.style.display = '';
          count++;
        } else {
          r.style.display = 'none';
        }
      });
    };

    searchInput.addEventListener('input', window.applyFilter);
    rowsPerPage.addEventListener('change', window.applyFilter);

    // Initial apply
    window.applyFilter();

    
    // Function to cleanup duplicate entries in selected_stl table
    window.cleanupDuplicates = function() {
        if (!confirm('This will remove duplicate entries from the database. Continue?')) {
            return;
        }

        Swal.fire({
            title: 'Cleaning up duplicates...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('includes/cleanup_duplicates.php', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cleanup Complete',
                    html: `
                        <div class="text-start">
                            <p><strong>Duplicates Found:</strong> ${data.results.duplicates_found}</p>
                            <p><strong>Records Deleted:</strong> ${data.results.records_deleted}</p>
                            <p><strong>Violations Remaining:</strong> ${data.results.violations_remaining}</p>
                        </div>
                    `,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Reload the table after cleanup
                    window.loadEmployees();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Cleanup Failed',
                    text: data.message || 'An error occurred during cleanup'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to cleanup duplicates: ' + error.message
            });
        });
    };

    // Fetch data and handle page load
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for all scripts to be fully parsed and executed
        setTimeout(function() {
            console.log('=== Page Initialization Starting ===');
            
            // Verify functions are available
            if (typeof loadTableState !== 'function') {
                console.error('loadTableState not available');
            } else {
                loadTableState();
                console.log('✓ loadTableState executed');
            }
            
            // Add loading indicator
            const tbody = document.querySelector('#selectedEmployeesTable tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center">Loading...</td></tr>';
            }

            // Initial fetch of data with error handling
            if (typeof loadEmployees === 'function') {
                console.log('✓ loadEmployees available - calling now');
                loadEmployees();
            } else {
                console.error('✗ loadEmployees function not available');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="11" class="text-center text-danger">Error: Failed to load employees function</td></tr>';
                }
            }

            // Setup search input event listener
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('change', function() {
                    if (typeof saveTableState === 'function') {
                        saveTableState();
                    }
                });
                searchInput.addEventListener('keyup', function() {
                    if (typeof applyFilter === 'function') {
                        applyFilter();
                    }
                });
            }
            
            // Setup rows per page event listener
            const rowsPerPage = document.getElementById('rowsPerPage');
            if (rowsPerPage) {
                rowsPerPage.addEventListener('change', function() {
                    if (typeof saveTableState === 'function') {
                        saveTableState();
                    }
                });
            }

            // Save state before page unload
            window.addEventListener('beforeunload', function() {
                if (typeof saveTableState === 'function') {
                    saveTableState();
                }
            });

            console.log('=== Page Initialization Complete ===');
        }, 1000);
    });

    // Make functions globally accessible for use in onclick handlers and other contexts
    window.openSTLEmployeeEditModal = openSTLEmployeeEditModal;
    window.saveSTLEmployeeChanges = saveSTLEmployeeChanges;
    window.loadSTLActiveEmployeesForManagement = loadSTLActiveEmployeesForManagement;
    window.loadSTLInactiveEmployees = loadSTLInactiveEmployees;
    window.deactivateSTLEmployee = deactivateSTLEmployee;
    window.reactivateSTLEmployee = reactivateSTLEmployee;
    window.openEditERModal = openEditERModal;
    window.openEditEEModal = openEditEEModal;
    window.saveERValue = saveERValue;
    window.saveEEValue = saveEEValue;
    window.deleteSTLEmployeeRow = deleteSTLEmployeeRow;
    window.addEmployeeToTable = addEmployeeToTable;
    window.loadSTLActiveEmployees = loadSTLActiveEmployees;
    window.removeFromSTL = removeFromSTL;
    window.formatPagibigNumber = formatPagibigNumber;
    window.formatTIN = formatTIN;
    window.formatDateForDisplay = formatDateForDisplay;
    window.loadSTLSummary = loadSTLSummary;
    window.populateSTLSummaryFromFiles = populateSTLSummaryFromFiles;
    window.downloadSTLSummaryFile = downloadSTLSummaryFile;
    window.viewSTLSummaryDetails = viewSTLSummaryDetails;
    window.saveSummaryToDatabase = saveSummaryToDatabase;
    window.regenerateAndDownloadSTL = regenerateAndDownloadSTL;

  </script>
</body>
</html>
