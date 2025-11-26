<?php
// Registration Modal
?>
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="save_registration.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registrationModalLabel">Employee Registration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label>Pag-IBIG #</label>
          <input type="text" name="pagibig_no" id="pagibig_no" class="form-control" maxlength="19" required placeholder="XXXX-XXXX-XXXX-XXXX">
        </div>
        <div class="mb-2">
          <label>ID #</label>
          <input type="text" name="id_no" id="id_no" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Last Name</label>
          <input type="text" name="lname" id="lname" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>First Name</label>
          <input type="text" name="fname" id="fname" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Middle Name</label>
          <input type="text" name="mname" id="mname" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>TIN</label>
          <input type="text" name="tin" id="tin" class="form-control" maxlength="15" required placeholder="XXX-XXX-XXX-XXX">
        </div>
        <div class="mb-2">
          <label>Birthdate</label>
          <input type="date" name="birthdate" class="form-control" required>
        </div>
        <input type="hidden" name="ee" value="200">
        <input type="hidden" name="er" value="200">
        <input type="hidden" name="status" value="ACTIVE">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
