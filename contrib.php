<?php
// Start session first, before any output
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Include necessary files
require_once 'database/db_connect.php';
require_once 'includes/Database.php';
require_once 'includes/Response.php';
require_once 'includes/Model.php';
require_once 'includes/ContributionModel.php';

// Get database connection
try {
    $conn = getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize ContributionModel
try {
    $contributionModel = new ContributionModel();
} catch (Exception $e) {
    die("Failed to initialize ContributionModel: " . $e->getMessage());
}

// Function to get selected contributions for current user
function getSelectedContributions($conn, $userId) {
    try {
        $query = "SELECT * FROM selected_contributions WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error in getSelectedContributions: " . $e->getMessage());
        return [];
    }
}

// Get selected contributions
$selectedContributions = getSelectedContributions($conn, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Contribution</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <!-- Sidebar CSS -->
  <link rel="stylesheet" href="asset/sidebar/sidebar.css">
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
</style>
</head>
<body style="display: flex !important; visibility: visible !important; opacity: 1 !important;">

  <!-- Include Sidebar OUTSIDE content area -->
  <?php include 'asset/sidebar/sidebar.php'; ?>

  <div class="content" style="display: flex !important; flex-direction: column; visibility: visible !important; opacity: 1 !important;">
    <h3 style="margin: 0 0 15px 0; flex-shrink: 0;">Contribution</h3>
    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 15px; flex-shrink: 0;">
      <h5 class="mb-0">Selected Contributions</h5>
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

    <div style="flex: 1; min-height: 0; display: flex; flex-direction: column; overflow-y: auto; overflow-x: auto; border: 1px solid #dee2e6; border-radius: 0.25rem;">
      <table class="table table-bordered mb-0" id="selectedEmployeesTable">
        <thead style="position: sticky; top: 0; background-color: white; z-index: 10; border-bottom: 2px solid #dee2e6;">
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
          <tr>
            <td colspan="10" class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="editModalLabel">
            <i class="fas fa-user-edit me-2"></i>Employee Details
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editForm">
            <input type="hidden" id="edit_employee_id" name="id">
            <input type="hidden" id="edit_pagibig_no" name="pagibig_no">
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit_id_no" class="form-label">ID Number:</label>
                <input type="text" class="form-control" id="edit_id_no" name="id_no" readonly>
              </div>
              <div class="col-md-6 mb-3">
                <label for="editPagibigNo" class="form-label">Pag-IBIG Number:</label>
                <input type="text" class="form-control" id="editPagibigNo" readonly>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit_last_name" class="form-label">Last Name:</label>
                <input type="text" class="form-control" id="edit_last_name" name="last_name"
                       placeholder="Letters only">
              </div>
              <div class="col-md-6 mb-3">
                <label for="edit_first_name" class="form-label">First Name:</label>
                <input type="text" class="form-control" id="edit_first_name" name="first_name"
                       placeholder="Letters only">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit_middle_name" class="form-label">Middle Name:</label>
                <input type="text" class="form-control" id="edit_middle_name" name="middle_name"
                       placeholder="Letters only">
              </div>
              <div class="col-md-6 mb-3">
                <label for="edit_tin" class="form-label">TIN:</label>
                <input type="text" class="form-control" id="edit_tin" name="tin"
                       placeholder="Format: XXX-XXX-XXX-XXXX">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit_birthdate" class="form-label">Birthdate:</label>
                <input type="text" class="form-control" id="edit_birthdate" name="birthdate" 
                       placeholder="MM/DD/YYYY">
              </div>
            </div>

            <div id="editError" style="display: none;" class="alert alert-danger mt-3"></div>

            <div class="alert alert-info mt-3">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Note:</strong> ID Number and Pag-IBIG Number are read-only fields. You can edit other employee information.
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-primary" onclick="saveEmployeeChanges()">
            <i class="fas fa-save me-2"></i>Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- SheetJS (XLSX) library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

  <!-- Your custom scripts -->
  <script src="assets/js/script.js"></script>
  <script src="assets/js/contribution.js"></script>
  <script src="assets/js/delete-employee.js"></script>
  <script src="assets/js/selected-employees.js"></script>
  <script src="assets/js/register-employee.js"></script>

  <script>
    // Save Employee Changes Function
    function saveEmployeeChanges() {
        const form = document.getElementById('editForm');
        const errorDiv = document.getElementById('editError');
        
        try {
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';

            // Get form values
            const employeeId = document.getElementById('edit_employee_id').value;
            const pagibigNo = document.getElementById('edit_pagibig_no').value;
            const lastName = document.getElementById('edit_last_name').value.trim();
            const firstName = document.getElementById('edit_first_name').value.trim();
            const middleName = document.getElementById('edit_middle_name').value.trim();
            const tin = document.getElementById('edit_tin').value.trim();
            const birthdate = document.getElementById('edit_birthdate').value.trim();

            // Validate required fields
            if (!lastName) throw new Error('Last name is required');
            if (!firstName) throw new Error('First name is required');

            // Validate birthdate format
            if (birthdate) {
                if (birthdate.includes('-')) {
                    throw new Error('Invalid birthdate format. Use forward slashes (/) not dashes (-). Example: 01/20/2001');
                }
                if (!birthdate.includes('/')) {
                    throw new Error('Please enter birthdate as MM/DD/YYYY');
                }
            }

            // Extract raw digits from formatted fields for storage
            const tinDigits = tin.replace(/\D/g, '');

            // Prepare data for submission
            const formData = {
                id: employeeId,
                pagibig_number: pagibigNo,
                last_name: lastName.toUpperCase(),
                first_name: firstName.toUpperCase(),
                middle_name: middleName.toUpperCase(),
                tin: tinDigits,
                birthdate: birthdate
            };

            // Submit to server
            fetch('includes/update_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Employee information updated successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    
                    // Close the edit modal
                    const editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                    editModal.hide();
                    
                    // Reload the active employees list
                    loadActiveEmployees();
                } else {
                    throw new Error(data.message || 'Update failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.textContent = error.message || 'Failed to update employee';
                errorDiv.style.display = 'block';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update employee'
                });
            });

        } catch (error) {
            console.error('Validation error:', error);
            errorDiv.textContent = error.message;
            errorDiv.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const generateExcelBtn = document.getElementById('btnGenerateExcel');
        if (generateExcelBtn) {
            generateExcelBtn.addEventListener('click', function() {
                const month = document.getElementById('month').value;
                // Get year and ensure it's a clean integer (no decimals)
                const yearInput = document.getElementById('yearInput').value;
                const year = parseInt(yearInput, 10);

                if (!month || !year) {
                    alert('Please select both month and year');
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
                        const eeValue = parseInt(cells[5].textContent) || 0; // Convert to integer
                        const erValue = parseInt(cells[6].textContent) || 0; // Convert to integer
                        const rowData = [
                            cells[0].textContent, // PAG-IBIG
                            cells[1].textContent, // ID
                            cells[2].textContent, // Last Name
                            cells[3].textContent, // First Name
                            cells[4].textContent, // Middle Name
                            eeValue, // EE Share as integer
                            erValue, // ER Share as integer
                            cells[7].textContent, // TIN
                            cells[8].textContent, // Birthdate
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

                    // Ensure numeric columns are properly formatted
                    const numericColumns = [5, 6]; // EE and ER columns (0-based index)
                    for (let row = range.s.r + 1; row <= range.e.r; row++) { // Skip header row
                        numericColumns.forEach(col => {
                            const cell_address = XLSX.utils.encode_cell({ r: row, c: col });
                            if (ws[cell_address]) {
                                ws[cell_address].t = 'n'; // Set type as number
                                ws[cell_address].z = '0'; // Format as whole number
                            }
                        });
                    }

                    // Create workbook and add worksheet
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'PAG-IBIG Contributions');

                    // Format year (remove .00 if present) and generate filename
                    const cleanYear = year.toString().replace('.00', '');
                    const filename = `${month}_${cleanYear}_contribution.xls`;
                    
                    // Generate and save file
                    XLSX.writeFile(wb, filename);

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('saveModal'));
                    if (modal) {
                        modal.hide();
                    }

                    alert('Excel file has been generated successfully!');
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

      const q = searchInput.value.trim().toLowerCase();
      const limit = parseInt(rowsPerPage.value, 10);
      const rows = Array.from(table.querySelectorAll('tbody tr'));
      
      // Save state after filtering
      saveTableState();

      // First, mark matches
      const matchedRows = rows.filter(row => {
        const cells = row.querySelectorAll('td');
        const last = (cells[2] && cells[2].textContent.toLowerCase()) || '';
        const first = (cells[3] && cells[3].textContent.toLowerCase()) || '';
        const middle = (cells[4] && cells[4].textContent.toLowerCase()) || '';
        const combined = `${first} ${middle} ${last}`.trim();

        return q === '' || last.indexOf(q) !== -1 || first.indexOf(q) !== -1 || middle.indexOf(q) !== -1 || combined.indexOf(q) !== -1;
      });

      // Hide all first
      rows.forEach(r => r.style.display = 'none');

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

    // Fetch data and handle page load
    document.addEventListener('DOMContentLoaded', function() {
        // Load saved state from localStorage
        loadTableState();
        
        // Add loading indicator
        const tbody = document.querySelector('#selectedEmployeesTable tbody');
        const loadingRow = document.createElement('tr');
        loadingRow.innerHTML = '<td colspan="10" class="text-center">Loading...</td>';
        tbody.appendChild(loadingRow);

        // Initial fetch of data with error handling
        fetchEmployees()
            .then(() => {
                console.log('Data loaded successfully');
            })
            .catch(error => {
                console.error('Error loading data:', error);
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data. Please refresh the page.</td></tr>';
            });

        // Save state when input changes
        searchInput.addEventListener('change', saveTableState);
        rowsPerPage.addEventListener('change', saveTableState);

        // Optional: Save state before page unload
        window.addEventListener('beforeunload', saveTableState);
    });

    // Function to cleanup/clear contribution data
    window.cleanupContributionData = function() {
        Swal.fire({
            title: 'Clear All Contribution Data?',
            text: 'This will delete ALL employees and selected contributions from the Contribution system. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e83e8c',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear all data',
            input: 'checkbox',
            inputValue: 0,
            inputPlaceholder: 'I understand this will delete all records'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetch('api/cleanup_contribution.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=clear_all_contribution'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Cleared!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Refresh the page
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', error.message, 'error');
                });
            }
        });
    };

  </script>

  <!-- Include all modal files at the end -->
  <?php
  include 'asset/modals/save_modal.php';  //SAVE BUTTON
  include 'asset/modals/employee_status_modals.php'; //ACTIVE/INACTIVE EMPLOYEES
  include 'asset/modals/select_employees.php'; //SELECT EMPLOYEES BUTTON
  include 'asset/modals/register_employee.php'; //REGISTER EMPLOYEE FORM
  ?>

</body>
</html>
