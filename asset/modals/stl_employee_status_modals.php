<?php include 'edit_modals.php'; ?>

<!-- STL Active Employees Modal -->
<div class="modal fade" id="stlActiveEmployeesModal" tabindex="-1" aria-labelledby="stlActiveEmployeesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="fas fa-users me-2"></i>STL Active Employees
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="list-group" id="stlActiveEmployeesList">
          <div class="text-center" id="stlLoadingSpinner">
            <div class="spinner-border text-success" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- STL Inactive Employees Modal -->
<div class="modal fade" id="stlInactiveEmployeesModal" tabindex="-1" aria-labelledby="stlInactiveEmployeesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fas fa-user-times me-2"></i>STL Inactive Employees
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="stlRecentlyDeactivatedAlert" class="alert alert-warning alert-dismissible fade d-none mb-3" role="alert">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <strong>Recently Deactivated from STL:</strong> <span id="stlRecentlyDeactivatedName"></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="list-group" id="stlInactiveEmployeeList">
          <div class="text-center" id="stlInactiveLoadingSpinner">
            <div class="spinner-border text-danger" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const stlActiveEmployeesList = document.getElementById('stlActiveEmployeesList');
  const stlInactiveEmployeesList = document.getElementById('stlInactiveEmployeeList');

  // Load STL Active Employees
  function loadStlActiveEmployees() {
    stlActiveEmployeesList.innerHTML = `
      <div class="text-center">
        <div class="spinner-border text-success" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>`;

    fetch('includes/get_stl_employees_list.php')
      .then(res => res.json())
      .then(data => {
        if (!data.success || data.data.length === 0) {
          stlActiveEmployeesList.innerHTML = '<div class="list-group-item text-center">No active STL employees found</div>';
          return;
        }

        stlActiveEmployeesList.innerHTML = '';

        // Filter only active employees
        const activeEmployees = data.data.filter(emp => emp.stl_status === 'Added');
        
        activeEmployees.forEach(employee => {
          const listItem = document.createElement('div');
          listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

          const info = document.createElement('div');
          info.innerHTML = `
            <span>${employee.last_name}, ${employee.first_name} ${employee.middle_name || ''}</span>
            <small class="text-muted ms-2">
              Pag-IBIG: ${employee.pagibig_number || 'N/A'} | ID: ${employee.id_number || 'N/A'}
            </small>`;

          const btns = document.createElement('div');
          btns.className = 'btn-group btn-group-sm';

          const deactivateBtn = document.createElement('button');
          deactivateBtn.className = 'btn btn-danger';
          deactivateBtn.innerHTML = '<i class="fas fa-user-times"></i>';
          deactivateBtn.title = 'Remove from STL';
          deactivateBtn.onclick = () => {
            if (confirm(`Are you sure you want to remove ${employee.last_name}, ${employee.first_name} from STL?`)) {
              const formData = new FormData();
              formData.append('employee_id', employee.id);

              fetch('includes/deactivate_stl_employee.php', {
                method: 'POST',
                body: formData
              })
              .then(res => res.json())
              .then(result => {
                if (result.success) {
                  localStorage.setItem('recentlyDeactivatedStlEmployee', `${employee.last_name}, ${employee.first_name}`);
                  loadStlActiveEmployees();
                  loadStlInactiveEmployees();
                } else {
                  alert('Error: ' + (result.message || 'Failed to remove from STL.'));
                }
              });
            }
          };

          btns.appendChild(deactivateBtn);

          listItem.appendChild(info);
          listItem.appendChild(btns);
          stlActiveEmployeesList.appendChild(listItem);
        });
      });
  }

  // Load STL Inactive Employees
  function loadStlInactiveEmployees() {
    stlInactiveEmployeesList.innerHTML = `
      <div class="text-center">
        <div class="spinner-border text-danger" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>`;

    const alertBox = document.getElementById('stlRecentlyDeactivatedAlert');
    const alertName = document.getElementById('stlRecentlyDeactivatedName');
    const lastDeactivated = localStorage.getItem('recentlyDeactivatedStlEmployee');

    if (lastDeactivated) {
      alertName.textContent = lastDeactivated;
      alertBox.classList.remove('d-none');
      alertBox.classList.add('show');
    } else {
      alertBox.classList.add('d-none');
      alertBox.classList.remove('show');
    }

    fetch('includes/get_stl_employees_list.php')
      .then(res => res.json())
      .then(data => {
        if (!data.success || data.data.length === 0) {
          stlInactiveEmployeesList.innerHTML = '<div class="list-group-item text-center">No inactive STL employees found</div>';
          return;
        }

        stlInactiveEmployeesList.innerHTML = '';

        // Filter only inactive employees (Not Added to STL)
        const inactiveEmployees = data.data.filter(emp => emp.stl_status === 'Not Added');
        
        inactiveEmployees.forEach(employee => {
          const listItem = document.createElement('div');
          listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

          const info = document.createElement('div');
          info.innerHTML = `
            <i class="fas fa-user-slash text-danger me-2"></i>
            ${employee.last_name}, ${employee.first_name} ${employee.middle_name || ''}`;

          const reactivateBtn = document.createElement('button');
          reactivateBtn.className = 'btn btn-success btn-sm';
          reactivateBtn.innerHTML = '<i class="fas fa-user-check"></i>';
          reactivateBtn.title = 'Add to STL';
          reactivateBtn.onclick = () => {
            if (confirm(`Add ${employee.last_name}, ${employee.first_name} to STL?`)) {
              const formData = new FormData();
              formData.append('employee_id', employee.id);

              fetch('includes/register_stl_employee.php', {
                method: 'POST',
                body: formData
              })
              .then(res => res.json())
              .then(result => {
                if (result.success) {
                  loadStlInactiveEmployees();
                  loadStlActiveEmployees();
                } else {
                  alert('Error: ' + (result.message || 'Could not add to STL.'));
                }
              });
            }
          };

          const btnWrap = document.createElement('div');
          btnWrap.appendChild(reactivateBtn);

          listItem.appendChild(info);
          listItem.appendChild(btnWrap);
          stlInactiveEmployeesList.appendChild(listItem);
        });
      });
  }

  // Event Listeners
  const stlActiveEmployeesModal = document.getElementById('stlActiveEmployeesModal');
  const stlInactiveEmployeesModal = document.getElementById('stlInactiveEmployeesModal');

  stlActiveEmployeesModal.addEventListener('show.bs.modal', loadStlActiveEmployees);
  stlInactiveEmployeesModal.addEventListener('show.bs.modal', loadStlInactiveEmployees);
});
</script>
