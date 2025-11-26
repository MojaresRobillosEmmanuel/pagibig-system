<!-- STL Register Employee Modal -->
<div class="modal fade" id="registerEmployeeModal" tabindex="-1" aria-labelledby="registerEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="registerEmployeeModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Register STL Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registerEmployeeForm" method="POST" novalidate>
                    <div class="row g-3">
                        <!-- Pag-IBIG Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="pagibigNumber" 
                                    name="pagibig_number"
                                    maxlength="15" 
                                    placeholder="XXX-XXX-XXX-XXX"
                                    required>
                                <label for="pagibigNumber">
                                    <i class="fas fa-id-card me-2"></i>Pag-IBIG Number (XXX-XXX-XXX-XXX)
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a valid 12-digit Pag-IBIG number
                                </div>
                            </div>
                        </div>

                        <!-- ID Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="idNumber" 
                                    name="id_number"
                                    required>
                                <label for="idNumber">
                                    <i class="fas fa-id-badge me-2"></i>ID Number
                                </label>
                                <div class="invalid-feedback">
                                    Please enter an ID number
                                </div>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="lastName" 
                                    name="last_name"
                                    required>
                                <label for="lastName">
                                    <i class="fas fa-user me-2"></i>Last Name
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a last name (letters only)
                                </div>
                            </div>
                        </div>

                        <!-- First Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="firstName" 
                                    name="first_name"
                                    required>
                                <label for="firstName">
                                    <i class="fas fa-user me-2"></i>First Name
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a first name (letters only)
                                </div>
                            </div>
                        </div>

                        <!-- Middle Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="middleName" 
                                    name="middle_name">
                                <label for="middleName">
                                    <i class="fas fa-user me-2"></i>Middle Name
                                </label>
                            </div>
                        </div>

                        <!-- TIN Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="tin" 
                                    name="tin"
                                    maxlength="15" 
                                    placeholder="XXX-XXX-XXX-0000">
                                <label for="tin">
                                    <i class="fas fa-file-invoice me-2"></i>TIN (XXX-XXX-XXX-0000)
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a valid TIN number
                                </div>
                            </div>
                        </div>

                        <!-- Birthdate -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" 
                                    class="form-control" 
                                    id="birthdate" 
                                    name="birthdate"
                                    placeholder="MM/DD/YYYY"
                                    maxlength="10"
                                    required>
                                <label for="birthdate">
                                    <i class="fas fa-calendar me-2"></i>Birthdate (MM/DD/YYYY)
                                </label>
                                <div class="invalid-feedback">
                                    Please enter a valid date (MM/DD/YYYY)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3" id="registerError" style="display: none;"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Register Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
