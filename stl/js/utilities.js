// Utility functions for STL system

// Format PAG-IBIG number with dashes (XXX-XXX-XXX-XXX)
window.formatPagibigNumber = function(number) {
    // Remove any existing dashes
    const cleaned = number.replace(/-/g, '');
    // Add dashes after every 3 digits
    return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, '$1-$2-$3-$4');
};

// Remove formatting from PAG-IBIG number
window.unformatPagibigNumber = function(number) {
    return number.replace(/-/g, '');
};

// Filter employees by search term
window.applyFilter = function() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('selectedEmployeesTable');
    
    if (!searchInput || !table) return;
    
    const filter = searchInput.value.toUpperCase();
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let row of rows) {
        const text = row.textContent || row.innerText;
        row.style.display = text.toUpperCase().includes(filter) ? '' : 'none';
    }
};

// Format TIN for display (XXX-XXX-XXX-0000) - Last 4 digits are always 0000
// NOTE: This function is overridden by the one in employee-management.js for table display
// This version is kept here for reference and used by edit modal
// DO NOT assign to window.formatTIN to avoid conflicts with employee-management.js version
function formatTINUtility(number) {
    // Remove any existing dashes and non-numeric characters
    let cleaned = number.replace(/-/g, '').replace(/\D/g, '');
    
    // If input is empty, return empty
    if (cleaned.length === 0) return '';
    
    // Take only first 9 digits and pad if needed
    if (cleaned.length < 9) {
        cleaned = cleaned.padStart(9, '0');
    }
    cleaned = cleaned.slice(0, 9);
    
    // Format as XXX-XXX-XXX-0000 (first 9 digits + fixed 0000)
    return cleaned.slice(0, 3) + '-' + cleaned.slice(3, 6) + '-' + cleaned.slice(6, 9) + '-0000';
};

// Format TIN input with dashes (XXX-XXX-XXX-0000) - Last 4 digits are always 0000
window.formatTINInput = function(value) {
    const cleaned = value.replace(/\D/g, '').slice(0, 9);
    
    if (cleaned.length === 0) return '';
    if (cleaned.length <= 3) return cleaned;
    if (cleaned.length <= 6) return cleaned.slice(0, 3) + '-' + cleaned.slice(3);
    
    // Always format with last 4 digits as 0000 (XXX-XXX-XXX-0000)
    return cleaned.slice(0, 3) + '-' + cleaned.slice(3, 6) + '-' + cleaned.slice(6, 9) + '-0000';
};

// Format Pag-IBIG input with dashes (XXX-XXX-XXX-XXX)
window.formatPagibigInput = function(value) {
    const cleaned = value.replace(/\D/g, '');
    if (cleaned.length <= 3) {
        return cleaned;
    } else if (cleaned.length <= 6) {
        return cleaned.slice(0, 3) + '-' + cleaned.slice(3);
    } else if (cleaned.length <= 9) {
        return cleaned.slice(0, 3) + '-' + cleaned.slice(3, 6) + '-' + cleaned.slice(6);
    } else {
        return cleaned.slice(0, 3) + '-' + cleaned.slice(3, 6) + '-' + cleaned.slice(6, 9) + '-' + cleaned.slice(9, 12);
    }
};

// Format Birthdate input (MM/DD/YYYY)
window.formatBirthdateInput = function(value) {
    const cleaned = value.replace(/\D/g, '');
    if (cleaned.length <= 2) {
        return cleaned;
    } else if (cleaned.length <= 4) {
        return cleaned.slice(0, 2) + '/' + cleaned.slice(2);
    } else {
        return cleaned.slice(0, 2) + '/' + cleaned.slice(2, 4) + '/' + cleaned.slice(4, 8);
    }
};

// Setup real-time formatting for edit modal
window.setupSTLEditModalFormatting = function() {
    const tinInput = document.getElementById('editSTL_tin');
    const birthdateInput = document.getElementById('editSTL_birthdate');
    
    if (tinInput) {
        tinInput.addEventListener('input', function(e) {
            const formatted = window.formatTINInput(this.value);
            if (formatted !== this.value) {
                this.value = formatted;
            }
        });
    }
    
    if (birthdateInput) {
        birthdateInput.addEventListener('input', function(e) {
            const formatted = window.formatBirthdateInput(this.value);
            if (formatted !== this.value) {
                this.value = formatted;
            }
        });
    }
};