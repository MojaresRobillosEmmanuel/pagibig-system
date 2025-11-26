<!-- Employee Edit Modal -->
<div class="modal fade" id="employeeEditModal" tabindex="-1" aria-labelledby="employeeEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%); border: none;">
                <h5 class="modal-title text-white" id="employeeEditModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Employee Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background-color: #f8f9fa; padding: 30px;">
                <form id="employeeEditForm">
                    <input type="hidden" id="editEmployeeId">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editEmployeeIdNumber" class="form-label fw-bold">ID Number:</label>
                            <input type="text" class="form-control" id="editEmployeeIdNumber" readonly style="background-color: #e9ecef;">
                        </div>
                        <div class="col-md-6">
                            <label for="editEmployeePagibigNo" class="form-label fw-bold">Pag-IBIG Number:</label>
                            <input type="text" class="form-control" id="editEmployeePagibigNo" readonly style="background-color: #e9ecef;">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editEmployeeLastName" class="form-label fw-bold">Last Name:</label>
                            <input type="text" class="form-control" id="editEmployeeLastName" 
                                   oninput="this.value = this.value.replace(/[0-9]/g, '').toUpperCase();" 
                                   placeholder="Letters only">
                        </div>
                        <div class="col-md-6">
                            <label for="editEmployeeFirstName" class="form-label fw-bold">First Name:</label>
                            <input type="text" class="form-control" id="editEmployeeFirstName" 
                                   oninput="this.value = this.value.replace(/[0-9]/g, '').toUpperCase();" 
                                   placeholder="Letters only">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editEmployeeMiddleName" class="form-label fw-bold">Middle Name:</label>
                            <input type="text" class="form-control" id="editEmployeeMiddleName" 
                                   oninput="this.value = this.value.replace(/[0-9]/g, '').toUpperCase();" 
                                   placeholder="Letters only">
                        </div>
                        <div class="col-md-6">
                            <label for="editEmployeeTin" class="form-label fw-bold">TIN:</label>
                            <input type="text" class="form-control" id="editEmployeeTin" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '');" 
                                   placeholder="Numbers only">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="editEmployeeBirthdate" class="form-label fw-bold">Birthdate:</label>
                            <input type="date" class="form-control" id="editEmployeeBirthdate">
                        </div>
                    </div>

                    <div class="alert alert-info" style="border-left: 4px solid #0066cc; border-radius: 4px;">
                        <i class="fas fa-info-circle me-2" style="color: #0066cc;"></i>
                        <strong>Note:</strong> ID Number and Pag-IBIG Number are read-only fields. You can edit other employee information.
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="background-color: #f8f9fa; border-top: 1px solid #dee2e6; padding: 20px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="padding: 8px 20px;">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveEmployeeChanges()" style="padding: 8px 20px; background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%); border: none;">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function saveEmployeeChanges() {
    const employeeId = document.getElementById('editEmployeeId').value;
    const pagibigNo = document.getElementById('editEmployeePagibigNo').value;
    
    if (!employeeId || !pagibigNo) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Employee information is missing'
        });
        return;
    }

    const updateData = {
        id: employeeId,
        pagibig_number: pagibigNo,
        last_name: document.getElementById('editEmployeeLastName').value.trim().replace(/[0-9]/g, '').toUpperCase(),
        first_name: document.getElementById('editEmployeeFirstName').value.trim().replace(/[0-9]/g, '').toUpperCase(),
        middle_name: document.getElementById('editEmployeeMiddleName').value.trim().replace(/[0-9]/g, '').toUpperCase(),
        tin: formatTINWithDashes(document.getElementById('editEmployeeTin').value.trim()),
        birthdate: document.getElementById('editEmployeeBirthdate').value
    };

    // Helper function to format TIN with dashes
    function formatTINWithDashes(tinValue) {
        const digitsOnly = tinValue.replace(/\D/g, '');
        if (digitsOnly.length !== 12) return digitsOnly;
        // Format as XXX-XXX-XXX-0000
        return digitsOnly.substring(0, 3) + '-' + 
               digitsOnly.substring(3, 6) + '-' + 
               digitsOnly.substring(6, 9) + '-' + 
               digitsOnly.substring(9);
    }

    // Validate required fields
    if (!updateData.last_name || !updateData.first_name) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Last Name and First Name are required (letters only)'
        });
        return;
    }

    // Validate that names contain only letters and spaces
    const nameRegex = /^[A-Z\s.-]*$/;
    if (!nameRegex.test(updateData.last_name)) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Last Name can only contain letters, spaces, periods, and hyphens'
        });
        return;
    }

    if (!nameRegex.test(updateData.first_name)) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'First Name can only contain letters, spaces, periods, and hyphens'
        });
        return;
    }

    if (updateData.middle_name && !nameRegex.test(updateData.middle_name)) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Middle Name can only contain letters, spaces, periods, and hyphens'
        });
        return;
    }

    // Validate TIN (should be numbers only)
    if (updateData.tin && !/^[0-9]*$/.test(updateData.tin)) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'TIN can only contain numbers'
        });
        return;
    }

    Swal.fire({
        title: 'Confirm Update',
        text: 'Are you sure you want to save these changes?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, save',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../includes/update_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(updateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Employee information has been updated successfully'
                    }).then(() => {
                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('employeeEditModal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Reload the employees list if available
                        if (typeof loadEmployees === 'function') {
                            loadEmployees();
                        }
                    });
                } else {
                    throw new Error(data.message || 'Failed to update employee');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update employee information. Please try again.'
                });
            });
        }
    });
}
</script>
