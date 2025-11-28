/**
 * STL Employee Status Management
 * Handles Active and Inactive employee modals for STL system
 */

console.log('✓ stl-employee-status.js loading...');

// Define formatting functions upfront
window.formatPagibigNumberDisplay = function(number) {
  if (!number) return '';
  const cleaned = number.replace(/-/g, '');
  return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, '$1-$2-$3-$4');
};

window.formatTINDisplay = function(tin) {
  if (!tin) return '';
  const cleaned = tin.replace(/-/g, '');
  return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{4})/, '$1-$2-$3-$4');
};

// Alias functions for backward compatibility
window.formatPagibigNumber = window.formatPagibigNumberDisplay;
window.formatTIN = window.formatTINDisplay;

// Function to remove from STL
window.removeFromSTL = function(pagibigNo, employeeName) {
  if (!confirm(`Are you sure you want to remove ${employeeName} from STL?`)) {
    return;
  }
  
  const formData = new FormData();
  formData.append('pagibig_no', pagibigNo);
  
  // Show loading state
  Swal.fire({
    title: 'Removing...',
    text: 'Please wait while the employee is being removed.',
    didOpen: () => {
      Swal.showLoading();
    },
    allowOutsideClick: false,
    allowEscapeKey: false
  });
  
  fetch('includes/remove_from_stl.php', {
    method: 'POST',
    body: formData
  })
  .then(res => {
    // Check if response is JSON
    if (!res.ok && res.status !== 404) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    
    // Check content type
    const contentType = res.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      console.error('Response Content-Type:', contentType);
      throw new Error('Server returned non-JSON response. Please check the server logs.');
    }
    
    return res.json();
  })
  .then(data => {
    console.log('Remove response:', data);
    
    if (data.success) {
      // Show success message
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Employee removed from STL',
        timer: 2000,
        showConfirmButton: false
      });
      
      // Reload the table after a brief delay
      setTimeout(() => {
        if (typeof window.loadEmployees === 'function') {
          window.loadEmployees();
        }
      }, 1000);
    } else {
      // Show error message
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'Failed to remove employee from database'
      });
      
      // Reload to restore the row
      setTimeout(() => {
        if (typeof window.loadEmployees === 'function') {
          window.loadEmployees();
        }
      }, 500);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'An error occurred while removing the employee. Check console for details.'
    });
    
    // Reload to restore the row
    setTimeout(() => {
      if (typeof window.loadEmployees === 'function') {
        window.loadEmployees();
      }
    }, 500);
  });
};

console.log('✓ Formatting functions defined');

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
        nameSpan.style.color = '#0066cc';
        nameSpan.style.textDecoration = 'underline';
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
        nameSpan.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          console.log('Employee name clicked:', this.dataset.lastName, this.dataset.firstName);
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
  console.log('openSTLEmployeeEditModal called for:', lastName, firstName);
  console.log('All parameters:', { id, lastName, firstName, middleName, idNumber, pagibigNo, tin, birthdate, ee, er });
  
  // Ensure modal exists first
  const editSTLModal = document.getElementById('editSTLModal');
  if (!editSTLModal) {
    console.error('editSTLModal element not found in DOM');
    alert('Error: Edit modal not found. Please refresh the page.');
    return;
  }
  console.log('Edit modal element found');
  
  // Populate employee details in the edit modal
  const employeeIdField = document.getElementById('editSTL_employee_id');
  if (employeeIdField) {
    employeeIdField.value = id || '';
    console.log('✓ Set employee ID:', id);
  } else {
    console.warn('✗ editSTL_employee_id not found');
  }
  
  const idNoField = document.getElementById('editSTL_id_no');
  if (idNoField) {
    idNoField.value = idNumber || '';
    console.log('✓ Set ID number:', idNumber);
  } else {
    console.warn('✗ editSTL_id_no not found');
  }
  
  const pagibigNoHiddenField = document.getElementById('editSTL_pagibig_no');
  if (pagibigNoHiddenField) {
    pagibigNoHiddenField.value = pagibigNo || '';
  }
  
  const pagibigNoDisplayField = document.getElementById('editSTL_pagibigNo');
  if (pagibigNoDisplayField) {
    const formatted = window.formatPagibigNumberDisplay ? window.formatPagibigNumberDisplay(pagibigNo) : pagibigNo;
    pagibigNoDisplayField.value = formatted || '';
    console.log('✓ Set Pag-IBIG number:', formatted);
  } else {
    console.warn('✗ editSTL_pagibigNo not found');
  }
  
  const lastNameField = document.getElementById('editSTL_last_name');
  if (lastNameField) {
    lastNameField.value = lastName || '';
    console.log('✓ Set last name:', lastName);
  }
  
  const firstNameField = document.getElementById('editSTL_first_name');
  if (firstNameField) {
    firstNameField.value = firstName || '';
    console.log('✓ Set first name:', firstName);
  }
  
  const middleNameField = document.getElementById('editSTL_middle_name');
  if (middleNameField) {
    middleNameField.value = middleName || '';
  }
  
  const tinField = document.getElementById('editSTL_tin');
  if (tinField) {
    const formattedTin = window.formatTINDisplay ? window.formatTINDisplay(tin) : tin;
    tinField.value = formattedTin || '';
  }
  
  const birthdateField = document.getElementById('editSTL_birthdate');
  if (birthdateField) {
    birthdateField.value = birthdate || '';
    console.log('✓ Set birthdate:', birthdate);
  }
  
  console.log('Modal fields populated');
  
  // Clear any previous errors
  const errorDiv = document.getElementById('editSTLError');
  if (errorDiv) {
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
  }
  
  // Close the active employees modal if it's open
  const activeModal = document.getElementById('activeEmployeesModal');
  if (activeModal) {
    try {
      const instance = bootstrap.Modal.getInstance(activeModal);
      if (instance) {
        instance.hide();
        console.log('✓ Closed active employees modal');
      }
    } catch (e) {
      console.warn('Could not close active modal - may not be open:', e.message);
    }
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
    
    // Force focus on first input
    setTimeout(function() {
      const firstInput = editSTLModal.querySelector('input:not([type="hidden"])');
      if (firstInput && firstInput.id !== 'editSTL_id_no') {
        firstInput.focus();
        console.log('✓ Focused on first input field');
      }
    }, 100);
    
  } catch (error) {
    console.error('✗ Error showing edit modal:', error);
    console.error('Error stack:', error.stack);
    alert('Error opening edit form: ' + error.message);
  }
}

// Setup event listeners when document is ready
document.addEventListener('DOMContentLoaded', function () {
  console.log('✓ STL Employee Status Management initialized');
  console.log('✓ openSTLEmployeeEditModal function available:', typeof window.openSTLEmployeeEditModal === 'function');
  
  const activeEmployeesModal = document.getElementById('activeEmployeesModal');
  const activeEmployeesManagementModal = document.getElementById('activeEmployeesManagementModal');
  const inactiveEmployeesModal = document.getElementById('inactiveEmployeesModal');

  if (activeEmployeesModal) {
    activeEmployeesModal.addEventListener('show.bs.modal', loadSTLActiveEmployees);
  }
  if (activeEmployeesManagementModal) {
    activeEmployeesManagementModal.addEventListener('show.bs.modal', loadSTLActiveEmployeesForManagement);
    console.log('✓ Active Employees Management modal listener attached');
  }
  if (inactiveEmployeesModal) {
    inactiveEmployeesModal.addEventListener('show.bs.modal', loadSTLInactiveEmployees);
  }
});

// Load STL active employees for management (deactivation)
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
        nameLink.style.color = '#0066cc';
        nameLink.style.textDecoration = 'underline';
        nameLink.style.cursor = 'pointer';
        nameLink.style.fontWeight = 'bold';
        nameLink.title = 'Click to edit employee details';
        nameLink.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          console.log('✓ Employee name clicked in Active Employees modal');
          console.log('Employee data:', { 
            id: employee.id,
            last_name: employee.last_name,
            first_name: employee.first_name,
            pagibig_number: employee.pagibig_number
          });
          
          if (typeof window.openSTLEmployeeEditModal === 'function') {
            console.log('✓ Calling openSTLEmployeeEditModal...');
            window.openSTLEmployeeEditModal(
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
          } else {
            console.error('✗ openSTLEmployeeEditModal function not found');
            alert('Error: Edit function not available. Please refresh the page.');
          }
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

// Make functions globally accessible
window.loadSTLActiveEmployees = loadSTLActiveEmployees;
window.loadSTLInactiveEmployees = loadSTLInactiveEmployees;
window.openSTLEmployeeEditModal = openSTLEmployeeEditModal;
window.loadSTLActiveEmployeesForManagement = loadSTLActiveEmployeesForManagement;
