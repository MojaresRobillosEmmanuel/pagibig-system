<?php
// Prevent direct access
if (!defined('ALLOW_ACCESS')) {
    header("HTTP/1.1 403 Forbidden");
    exit('Direct access forbidden');
}
?>

<!-- Save Modal -->
<div class="modal fade" id="saveModal" tabindex="-1" aria-labelledby="saveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveModalLabel">Select Month and Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="saveForm">
                    <div class="mb-3">
                        <label for="month" class="form-label">Month</label>
                        <select class="form-select" id="month" name="month" required>
                            <option value="">Select Month</option>
                            <?php
                            $months = array('January', 'February', 'March', 'April', 'May', 'June', 
                                          'July', 'August', 'September', 'October', 'November', 'December');
                            foreach ($months as $month) {
                                echo "<option value=\"$month\">$month</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="yearInput" class="form-label">Year</label>
                        <div class="input-group">
                            <input type="number" id="yearInput" class="form-control" 
                                   list="yearList" placeholder="Type or select year" 
                                   min="1900" max="2100" required>
                            <datalist id="yearList">
                                <?php
                                $currentYear = date('Y');
                                for ($year = $currentYear; $year >= $currentYear - 10; $year--) {
                                    echo "<option value=\"$year\">";
                                }
                                ?>
                            </datalist>
                            <input type="hidden" id="year" name="year">
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="button" id="btnGenerateExcel" class="btn btn-primary">
                            <i class="fas fa-file-excel me-2"></i>Generate Excel
                        </button>
                    </div>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
                </form>
            </div>
        </div>
    </div>
</div>

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
                <form id="registerEmployeeForm" action="includes/register_employee.php" method="POST">
                    <div class="row g-3">
                        <!-- PAG-IBIG Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="pagibigNumber" name="pagibig_number" 
                                       pattern="\d{12}" maxlength="12" required
                                       title="Pag-IBIG Number must be exactly 12 digits"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)">
                                <label for="pagibigNumber">
                                    <i class="fas fa-id-card me-2"></i>Pag-IBIG Number
                                </label>
                                <div class="invalid-feedback">
                                    Pag-IBIG Number must be exactly 12 digits
                                </div>
                            </div>
                        </div>
                        <!-- ID Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="idNumber" name="id_number" required>
                                <label for="idNumber">
                                    <i class="fas fa-id-badge me-2"></i>ID Number
                                </label>
                            </div>
                        </div>
                        <!-- Name Fields -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                                <label for="lastName">
                                    <i class="fas fa-user me-2"></i>Last Name
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                                <label for="firstName">
                                    <i class="fas fa-user me-2"></i>First Name
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="middleName" name="middle_name">
                                <label for="middleName">
                                    <i class="fas fa-user me-2"></i>Middle Name
                                </label>
                            </div>
                        </div>
                        <!-- Additional Fields -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="tin" name="tin">
                                <label for="tin">
                                    <i class="fas fa-file-invoice me-2"></i>TIN
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                                <label for="birthdate">
                                    <i class="fas fa-calendar me-2"></i>Birthdate
                                </label>
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

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editEmployeeForm" onsubmit="handleEditSubmit(event)">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>Edit Employee Information
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">
                    <div class="row g-3">
                        <!-- Edit Form Fields -->
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="editPagibig" name="pagibig" placeholder="Pag-IBIG #">
                                <label for="editPagibig">Pag-IBIG #</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="editIDNo" name="id_no" placeholder="ID #">
                                <label for="editIDNo">ID #</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control text-uppercase" id="editLName" name="lname" placeholder="Last Name">
                                <label for="editLName">Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control text-uppercase" id="editFName" name="fname" placeholder="First Name">
                                <label for="editFName">First Name</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control text-uppercase" id="editMName" name="mname" placeholder="Middle Name">
                                <label for="editMName">Middle Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="editTIN" name="tin" placeholder="TIN">
                                <label for="editTIN">TIN (optional)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="editBirthdate" name="birthdate" placeholder="Birthdate">
                                <label for="editBirthdate">Birthdate</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Include your JavaScript here -->
<script src="assets/js/modal-handlers.js"></script>
