/**
 * STL Employee Status Management
 * Handles Active and Inactive employee modals for STL system
 */

// Load active STL employees
function loadSTLActiveEmployees() {
  const activeEmployeesListContainer = document.getElementById('activeEmployeesListContainer');
  if (!activeEmployeesListContainer) {
    console.error('activeEmployeesListContainer element not found');
    return;
  }
  
  activeEmployeesListContainer.innerHTML = `
    <div class="text-center">
      <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>`;

  fetch('includes/get_stl_active_employees.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success || data.data.length === 0) {
        activeEmployeesListContainer.innerHTML = '<div class="list-group-item text-center">No active employees found</div>';
        return;
      }

      activeEmployeesListContainer.innerHTML = '';

      data.data.forEach(employee => {
        const listItem = document.createElement('div');
        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

        const info = document.createElement('div');
        const nameSpan = document.createElement('span');
        nameSpan.textContent = `${employee.last_name}, ${employee.first_name} ${employee.middle_name || ''}`;
        nameSpan.style.cursor = 'pointer';
        nameSpan.style.color = '#000000';
        nameSpan.style.textDecoration = 'none';
        nameSpan.style.fontWeight = 'bold';
        nameSpan.dataset.id = employee.id;
        nameSpan.dataset.lastName = employee.last_name;
        nameSpan.dataset.firstName = employee.first_name;
        nameSpan.dataset.middleName = employee.middle_name || '';
        nameSpan.dataset.idNumber = employee.id_number;
        nameSpan.dataset.pagibigNo = employee.pagibig_number;
        nameSpan.dataset.tin = employee.tin || '';
        nameSpan.dataset.birthdate = employee.birthdate || '';
        nameSpan.dataset.ee = employee.ee || '0.00';
        nameSpan.dataset.er = employee.er || '0.00';
        
        // Open edit modal when name is clicked
        nameSpan.addEventListener('click', function() {
          openSTLEmployeeEditModal(
            this.dataset.id,
            this.dataset.lastName,
            this.dataset.firstName,
            this.dataset.middleName,
            this.dataset.idNumber,
            this.dataset.pagibigNo,
            this.dataset.tin,
            this.dataset.birthdate,
            this.dataset.ee,
            this.dataset.er
          );
        });
        
        info.appendChild(nameSpan);
        
        const smallText = document.createElement('small');
        smallText.className = 'text-muted ms-2';
        smallText.innerHTML = `
          Pag-IBIG: ${employee.pagibig_number || 'N/A'} | ID: ${employee.id_number || 'N/A'}
        `;
        info.appendChild(smallText);

        const btns = document.createElement('div');
        btns.className = 'btn-group btn-group-sm';

        const deactivateBtn = document.createElement('button');
        deactivateBtn.className = 'btn btn-danger';
        deactivateBtn.innerHTML = '<i class="fas fa-user-times"></i>';
        deactivateBtn.title = 'Deactivate';
        deactivateBtn.onclick = () => {
          if (confirm(`Are you sure you want to deactivate ${employee.last_name}, ${employee.first_name}?`)) {
            const formData = new FormData();
            formData.append('employee_id', employee.id);

            fetch('includes/deactivate_stl_employee.php', {
              method: 'POST',
              body: formData
            })
            .then(res => res.json())
            .then(result => {
              if (result.success) {
                localStorage.setItem('recentlyDeactivatedSTL', `${employee.last_name}, ${employee.first_name}`);
                loadSTLActiveEmployees();
                loadSTLInactiveEmployees();
              } else {
                alert('Error: ' + (result.message || 'Failed to deactivate.'));
              }
            });
          }
        };

        btns.appendChild(deactivateBtn);

        listItem.appendChild(info);
        listItem.appendChild(btns);
        activeEmployeesListContainer.appendChild(listItem);
      });
    })
    .catch(error => {
      console.error('Error loading active employees:', error);
      activeEmployeesListContainer.innerHTML = '<div class="list-group-item text-center text-danger">Error loading employees</div>';
    });
}

// Load inactive STL employees
function loadSTLInactiveEmployees() {
  const inactiveEmployeesListContainer = document.getElementById('inactiveEmployeesListContainer');
  if (!inactiveEmployeesListContainer) {
    console.error('inactiveEmployeesListContainer element not found');
    return;
  }
  
  inactiveEmployeesListContainer.innerHTML = `
    <div class="text-center">
      <div class="spinner-border text-danger" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>`;

  const alertBox = document.getElementById('recentlyDeactivatedAlert');
  const alertName = document.getElementById('recentlyDeactivatedName');
  const lastDeactivated = localStorage.getItem('recentlyDeactivatedSTL');

  if (lastDeactivated && alertName && alertBox) {
    alertName.textContent = lastDeactivated;
    alertBox.classList.remove('d-none');
    alertBox.classList.add('show');
  } else if (alertBox) {
    alertBox.classList.add('d-none');
    alertBox.classList.remove('show');
  }

  fetch('includes/get_stl_inactive_employees.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success || data.data.length === 0) {
        inactiveEmployeesListContainer.innerHTML = '<div class="list-group-item text-center">No inactive employees found</div>';
        return;
      }

      inactiveEmployeesListContainer.innerHTML = '';

      data.data.forEach(employee => {
        const listItem = document.createElement('div');
        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

        const info = document.createElement('div');
        info.innerHTML = `
          <i class="fas fa-user-slash text-danger me-2"></i>
          ${employee.last_name}, ${employee.first_name} ${employee.middle_name || ''}`;

        const reactivateBtn = document.createElement('button');
        reactivateBtn.className = 'btn btn-success btn-sm';
        reactivateBtn.innerHTML = '<i class="fas fa-user-check"></i>';
        reactivateBtn.title = 'Reactivate';
        reactivateBtn.onclick = () => {
          if (confirm(`Reactivate ${employee.last_name}, ${employee.first_name}?`)) {
            const formData = new FormData();
            formData.append('employee_id', employee.id);

            fetch('includes/reactivate_stl_employee.php', {
              method: 'POST',
              body: formData
            })
            .then(res => res.json())
            .then(result => {
              if (result.success) {
                loadSTLInactiveEmployees();
                loadSTLActiveEmployees();
              } else {
                alert('Error: ' + (result.message || 'Could not reactivate.'));
              }
            });
          }
        };

        const btnWrap = document.createElement('div');
        btnWrap.appendChild(reactivateBtn);

        listItem.appendChild(info);
        listItem.appendChild(btnWrap);
        inactiveEmployeesListContainer.appendChild(listItem);
      });
    })
    .catch(error => {
      console.error('Error loading inactive employees:', error);
      inactiveEmployeesListContainer.innerHTML = '<div class="list-group-item text-center text-danger">Error loading employees</div>';
    });
}

// Open employee edit modal for STL
function openSTLEmployeeEditModal(id, lastName, firstName, middleName, idNumber, pagibigNo, tin, birthdate, ee, er) {
  // Close the active employees modal
  const activeModal = document.getElementById('activeEmployeesModal');
  if (activeModal) {
    const modal = bootstrap.Modal.getInstance(activeModal);
    if (modal) {
      modal.hide();
    }
  }
  
  // Check if edit modal elements exist
  const editEmployeeId = document.getElementById('edit_pagibig_no');
  if (!editEmployeeId) {
    console.error('Edit modal elements not found');
    return;
  }
  
  // Populate employee details in the edit modal
  document.getElementById('edit_pagibig_no').value = pagibigNo || '';
  document.getElementById('edit_id_no').value = idNumber || '';
  document.getElementById('editPagibigNo').value = pagibigNo || '';
  document.getElementById('edit_last_name').value = lastName || '';
  document.getElementById('edit_first_name').value = firstName || '';
  document.getElementById('edit_middle_name').value = middleName || '';
  document.getElementById('edit_tin').value = tin || '';
  document.getElementById('edit_birthdate').value = birthdate || '';
  
  // Show the edit employee modal
  const editModal = new bootstrap.Modal(document.getElementById('editModal'));
  editModal.show();
}

// Setup event listeners when document is ready
document.addEventListener('DOMContentLoaded', function () {
  const activeEmployeesModal = document.getElementById('activeEmployeesModal');
  const inactiveEmployeesModal = document.getElementById('inactiveEmployeesModal');

  if (activeEmployeesModal) {
    activeEmployeesModal.addEventListener('show.bs.modal', loadSTLActiveEmployees);
  }
  if (inactiveEmployeesModal) {
    inactiveEmployeesModal.addEventListener('show.bs.modal', loadSTLInactiveEmployees);
  }
});
