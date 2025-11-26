<?php
// Save Modal for Month & Year Selection
?>
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
            <label for="year" class="form-label">Year</label>
            <div class="input-group">
              <input type="number" id="yearInput" class="form-control" list="yearList" placeholder="Type or select year" min="1900" max="2100" required>
              <datalist id="yearList">
              </datalist>
              <input type="hidden" id="year" name="year">
            </div>
          </div>
          <div class="text-center">
            <button type="button" id="btnGenerateExcel" class="btn btn-primary">Generate Excel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
