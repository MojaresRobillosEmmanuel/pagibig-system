<?php include 'edit_modals.php'; ?>



<!-- ✅ ACTIVE Employees Modal -->
<div class="modal fade" id="activeEmployeesModal" tabindex="-1" aria-labelledby="activeEmployeesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="fas fa-users me-2"></i>Active Employees
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="list-group" id="activeEmployeesList">
          <div class="text-center" id="loadingSpinner">
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

<!-- ✅ INACTIVE Employees Modal -->
<div class="modal fade" id="inactiveEmployeesModal" tabindex="-1" aria-labelledby="inactiveEmployeesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fas fa-user-times me-2"></i>Inactive Employees
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="recentlyDeactivatedAlert" class="alert alert-warning alert-dismissible fade d-none mb-3" role="alert">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <strong>Recently Deactivated:</strong> <span id="recentlyDeactivatedName"></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="list-group" id="inactiveEmployeeList">
          <div class="text-center" id="inactiveLoadingSpinner">
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
// ✅ Global function to load active employees
function loadActiveEmployees() {
  const activeEmployeesList = document.getElementById('activeEmployeesList');
  if (!activeEmployeesList) {
    console.error('activeEmployeesList element not found');
    return;
  }
  
  activeEmployeesList.innerHTML = `
      <div class="text-center">
        <div class="spinner-border text-success" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>`;

    fetch('includes/get_active_employees.php')
      .then(res => res.json())
      .then(data => {
        if (!data.success || data.data.length === 0) {
          activeEmployeesList.innerHTML = '<div class="list-group-item text-center">No active employees found</div>';
          return;
        }

        activeEmployeesList.innerHTML = '';

        data.data.forEach(employee => {
          // Check if employee is already in selected contributions
          const formData = new FormData();
          formData.append('pagibig_no', employee.pagibig_number);

          fetch('includes/check_employee_status.php', {
            method: 'POST',
            body: formData
          })
          .then(res => {
            if (!res.ok) {
              console.warn(`Status check failed with code ${res.status} for employee ${employee.pagibig_number}`);
              throw new Error(`HTTP ${res.status}`);
            }
            return res.json();
          })
          .then(statusData => {
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
            
            // Add click handler to open edit modal
            nameSpan.onclick = (e) => {
              e.stopPropagation();
              openEmployeeEditModalContribution(
                nameSpan.dataset.id,
                nameSpan.dataset.lastName,
                nameSpan.dataset.firstName,
                nameSpan.dataset.middleName,
                nameSpan.dataset.idNumber,
                nameSpan.dataset.pagibigNo,
                nameSpan.dataset.tin,
                nameSpan.dataset.birthdate,
                nameSpan.dataset.ee,
                nameSpan.dataset.er
              );
            };
            
            // Add hover effect
            nameSpan.onmouseover = () => nameSpan.style.color = '#333333';
            nameSpan.onmouseout = () => nameSpan.style.color = '#000000';
            
            info.appendChild(nameSpan);
            
            const smallText = document.createElement('small');
            smallText.className = 'text-muted ms-2';
            smallText.innerHTML = `
              Pag-IBIG: ${employee.pagibig_number || 'N/A'} | ID: ${employee.id_number || 'N/A'}
            `;
            info.appendChild(smallText);

            const btns = document.createElement('div');
            btns.className = 'd-flex gap-2 align-items-center';

            const deactivateBtn = document.createElement('button');
            deactivateBtn.className = 'btn btn-sm btn-danger';
            deactivateBtn.innerHTML = '<i class="fas fa-user-times"></i>';
            deactivateBtn.title = 'Deactivate';
            deactivateBtn.onclick = () => {
              if (confirm(`Are you sure you want to deactivate ${employee.last_name}, ${employee.first_name}?`)) {
                const formData = new FormData();
                formData.append('id', employee.id);

                fetch('includes/deactivate_employee.php', {
                  method: 'POST',
                  body: formData
                })
                .then(res => res.json())
                .then(result => {
                  if (result.success) {
                    localStorage.setItem('recentlyDeactivatedEmployee', `${employee.last_name}, ${employee.first_name}`);
                    loadActiveEmployees();
                    alert('Employee deactivated successfully');
                  } else {
                    alert('Error: ' + (result.message || 'Failed to deactivate.'));
                  }
                });
              }
            };

            btns.appendChild(deactivateBtn);

            listItem.appendChild(info);
            listItem.appendChild(btns);
            activeEmployeesList.appendChild(listItem);
          })
          .catch(error => {
            console.error('Error checking employee status:', error);
            // Still add the employee to the list even if status check fails
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
            
            nameSpan.onclick = (e) => {
              e.stopPropagation();
              openEmployeeEditModalContribution(
                nameSpan.dataset.id,
                nameSpan.dataset.lastName,
                nameSpan.dataset.firstName,
                nameSpan.dataset.middleName,
                nameSpan.dataset.idNumber,
                nameSpan.dataset.pagibigNo,
                nameSpan.dataset.tin,
                nameSpan.dataset.birthdate,
                employee.ee || '0.00',
                employee.er || '0.00'
              );
            };
            
            nameSpan.onmouseover = () => nameSpan.style.color = '#333333';
            nameSpan.onmouseout = () => nameSpan.style.color = '#000000';
            
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
                formData.append('id', employee.id);

                fetch('includes/deactivate_employee.php', {
                  method: 'POST',
                  body: formData
                })
                .then(res => res.json())
                .then(result => {
                  if (result.success) {
                    localStorage.setItem('recentlyDeactivatedEmployee', `${employee.last_name}, ${employee.first_name}`);
                    loadActiveEmployees();
                    alert('Employee deactivated successfully');
                  } else {
                    alert('Error: ' + (result.message || 'Failed to deactivate.'));
                  }
                });
              }
            };

            btns.appendChild(deactivateBtn);

            listItem.appendChild(info);
            listItem.appendChild(btns);
            activeEmployeesList.appendChild(listItem);
          });
        });
      });
  }

  // ✅ Load Inactive Employees
  function loadInactiveEmployees() {
    const inactiveEmployeesList = document.getElementById('inactiveEmployeeList');
    if (!inactiveEmployeesList) {
      console.error('inactiveEmployeeList element not found');
      return;
    }
    
    inactiveEmployeesList.innerHTML = `
      <div class="text-center">
        <div class="spinner-border text-danger" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>`;

    const alertBox = document.getElementById('recentlyDeactivatedAlert');
    const alertName = document.getElementById('recentlyDeactivatedName');
    const lastDeactivated = localStorage.getItem('recentlyDeactivatedEmployee');

    if (lastDeactivated && alertName && alertBox) {
      alertName.textContent = lastDeactivated;
      alertBox.classList.remove('d-none');
      alertBox.classList.add('show');
    } else if (alertBox) {
      alertBox.classList.add('d-none');
      alertBox.classList.remove('show');
    }

    fetch('includes/get_inactive_employees.php')
      .then(res => res.json())
      .then(data => {
        if (!data.success || data.data.length === 0) {
          inactiveEmployeesList.innerHTML = '<div class="list-group-item text-center">No inactive employees found</div>';
          return;
        }

        inactiveEmployeesList.innerHTML = '';

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
              formData.append('id', employee.id);

              fetch('includes/reactivate_employee.php', {
                method: 'POST',
                body: formData
              })
              .then(res => res.json())
              .then(result => {
                if (result.success) {
                  loadInactiveEmployees();
                  loadActiveEmployees();
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
          inactiveEmployeesList.appendChild(listItem);
        });
      });
  }

// ✅ Event Listeners - Setup when document is ready
// Function to open employee edit modal for contribution
function openEmployeeEditModalContribution(id, lastName, firstName, middleName, idNumber, pagibigNo, tin, birthdate, ee, er) {
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
    
    // Format Pag-IBIG number (XXXX-XXXX-XXXX)
    const formattedPagibig = formatPagibigNumber(pagibigNo);
    
    // Format TIN (XXX-XXX-XXX-XXXX)
    const formattedTin = formatTIN(tin);
    
    // Format birthdate (MM/DD/YYYY)
    const formattedBirthdate = formatBirthdateForDisplay(birthdate);
    
    // Populate employee details in the edit modal
    document.getElementById('edit_employee_id').value = id || '';
    document.getElementById('edit_pagibig_no').value = pagibigNo || '';
    document.getElementById('edit_id_no').value = idNumber || '';
    document.getElementById('editPagibigNo').value = formattedPagibig || '';
    document.getElementById('edit_last_name').value = lastName || '';
    document.getElementById('edit_first_name').value = firstName || '';
    document.getElementById('edit_middle_name').value = middleName || '';
    document.getElementById('edit_tin').value = formattedTin || '';
    document.getElementById('edit_birthdate').value = formattedBirthdate || '';
    // Removed EE and ER fields as they are no longer in the edit modal
    
    // Setup format validation for the form fields
    setupEditFormValidation();
    
    // Show the edit employee modal
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

// Format Pag-IBIG number to XXXX-XXXX-XXXX
function formatPagibigNumber(pagibigNo) {
    if (!pagibigNo) return '';
    const digits = pagibigNo.replace(/\D/g, '').slice(0, 12);
    if (digits.length === 12) {
        return digits.substring(0, 4) + '-' + digits.substring(4, 8) + '-' + digits.substring(8);
    }
    return digits;
}

// Format TIN to XXX-XXX-XXX-XXXX
function formatTIN(tin) {
    if (!tin) return '';
    const digits = tin.replace(/\D/g, '').slice(0, 12);
    if (digits.length === 12) {
        return digits.substring(0, 3) + '-' + digits.substring(3, 6) + '-' + digits.substring(6, 9) + '-' + digits.substring(9);
    }
    return digits;
}

// Format birthdate to MM/DD/YYYY
function formatBirthdateForDisplay(birthdate) {
    if (!birthdate) return '';
    
    // If it's already in MM/DD/YYYY format, return as-is
    if (birthdate.includes('/')) {
        return birthdate;
    }
    
    // If it's in YYYY-MM-DD format (database format), convert to MM/DD/YYYY
    if (birthdate.includes('-')) {
        const parts = birthdate.split('-');
        if (parts.length === 3) {
            return parts[1] + '/' + parts[2] + '/' + parts[0];
        }
    }
    
    return birthdate;
}

// Setup validation for edit form fields
function setupEditFormValidation() {
    const lastNameInput = document.getElementById('edit_last_name');
    const firstNameInput = document.getElementById('edit_first_name');
    const middleNameInput = document.getElementById('edit_middle_name');
    const tinInput = document.getElementById('edit_tin');
    const birthdateInput = document.getElementById('edit_birthdate');

    // Last Name - Allow only letters and spaces
    if (lastNameInput) {
        lastNameInput.addEventListener('input', function() {
            let value = this.value.replace(/[^a-zA-Z\s]/g, '').toUpperCase();
            value = value.replace(/\s+/g, ' ').trim();
            this.value = value;
        });
    }

    // First Name - Allow only letters and spaces
    if (firstNameInput) {
        firstNameInput.addEventListener('input', function() {
            let value = this.value.replace(/[^a-zA-Z\s]/g, '').toUpperCase();
            value = value.replace(/\s+/g, ' ').trim();
            this.value = value;
        });
    }

    // Middle Name - Allow only letters and spaces
    if (middleNameInput) {
        middleNameInput.addEventListener('input', function() {
            let value = this.value.replace(/[^a-zA-Z\s]/g, '').toUpperCase();
            value = value.replace(/\s+/g, ' ').trim();
            this.value = value;
        });
    }

    // TIN - Format with dashes (XXX-XXX-XXX-XXXX)
    if (tinInput) {
        tinInput.addEventListener('input', function(e) {
            const cursorPos = this.selectionStart;
            let value = this.value.replace(/\D/g, '').slice(0, 12);
            
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && (i === 3 || i === 6 || i === 9)) {
                    formatted += '-';
                }
                formatted += value[i];
            }
            
            this.value = formatted;
            
            // Calculate new cursor position
            let newPos = cursorPos;
            if (cursorPos > 3) newPos++;
            if (cursorPos > 6) newPos++;
            if (cursorPos > 9) newPos++;
            if (newPos > formatted.length) newPos = formatted.length;
            
            this.setSelectionRange(newPos, newPos);
        });
    }

    // Birthdate - Format as MM/DD/YYYY with FORWARD SLASHES ONLY
    if (birthdateInput) {
        birthdateInput.addEventListener('input', function(e) {
            // REJECT any input that contained dashes
            if (this.value.includes('-')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Format',
                    text: 'Please use forward slashes (/) not dashes (-). Example: 01/20/2001',
                    showConfirmButton: false,
                    timer: 2000
                });
                this.value = '';
                return;
            }

            let value = this.value.replace(/\D/g, '');
            
            if (value.length > 0) {
                let month = value.substring(0, 2);
                let day = value.substring(2, 4);
                let year = value.substring(4, 8);
                
                // Validate month
                if (month.length === 2 && (parseInt(month) < 1 || parseInt(month) > 12)) {
                    month = '12';
                }
                
                // Validate day
                if (day.length === 2 && (parseInt(day) < 1 || parseInt(day) > 31)) {
                    day = '31';
                }
                
                // Build formatted date with FORWARD SLASHES ONLY
                let formatted = month;
                if (value.length > 2) formatted += '/' + day;
                if (value.length > 4) formatted += '/' + year;
                
                this.value = formatted;
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
  const activeEmployeesModal = document.getElementById('activeEmployeesModal');
  const inactiveEmployeesModal = document.getElementById('inactiveEmployeesModal');

  if (activeEmployeesModal) {
    activeEmployeesModal.addEventListener('show.bs.modal', loadActiveEmployees);
  }
  if (inactiveEmployeesModal) {
    inactiveEmployeesModal.addEventListener('show.bs.modal', loadInactiveEmployees);
  }
});
</script>
