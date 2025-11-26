<?php
// Edit Modals (Edit Employee & Edit Status)
?>
<!-- Modal: Edit Employee -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="editEmployeeForm" onsubmit="handleEditSubmit(event)">
    <script>
      function handleEditSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        fetch('includes/update_employee.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: data.message,
              showConfirmButton: false,
              timer: 1500
            });
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal'));
            editModal.hide();
            const activeEmployeesModal = new bootstrap.Modal(document.getElementById('activeEmployeesModal'));
            activeEmployeesModal.show();
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to update employee'
          });
        });
      }
    </script>
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Employee Information</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editId" name="id">
          <div class="row g-3">
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

