// Common utility functions and event handlers

// Function to format dates to MM/DD/YYYY
function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return `${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getDate().toString().padStart(2, '0')}/${d.getFullYear()}`;
}

// Function to format PAG-IBIG numbers with dashes
function formatPagibigNumber(number) {
    if (!number) return '';
    const cleaned = number.replace(/\D/g, '');
    if (cleaned.length !== 12) return number;
    return cleaned.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
}

// Function to format TIN numbers
function formatTIN(tin) {
    if (!tin) return '';
    const cleaned = tin.replace(/\D/g, '');
    if (cleaned.length < 9) return tin;
    let formatted = cleaned.substring(0, 9);
    return formatted.replace(/(\d{3})(\d{3})(\d{3})/, '$1-$2-$3') + '-0000';
}

// Function to show loading spinner
function showSpinner(container) {
    container.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
}

// Function to show error message
function showError(message, container) {
    container.innerHTML = `
        <div class="alert alert-danger" role="alert">
            ${message}
        </div>
    `;
}

// Function to validate birthdate format
function isValidBirthdate(date) {
    if (!date) return false;
    const pattern = /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/\d{4}$/;
    if (!pattern.test(date)) return false;
    
    const [month, day, year] = date.split('/').map(Number);
    const d = new Date(year, month - 1, day);
    return d.getMonth() === month - 1 && d.getDate() === day && d.getFullYear() === year;
}

// Function to format currency values
function formatCurrency(amount) {
    if (!amount) return '';
    return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Attach common event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format PAG-IBIG number inputs
    document.querySelectorAll('input[name*="pagibig"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 12) value = value.substr(0, 12);
            if (value.length >= 4) {
                value = value.replace(/(\d{4})(\d{4})?(\d{4})?/, function(match, p1, p2, p3) {
                    let result = p1;
                    if (p2) result += '-' + p2;
                    if (p3) result += '-' + p3;
                    return result;
                });
            }
            e.target.value = value;
        });
    });

    // Auto-format TIN inputs
    document.querySelectorAll('input[name*="tin"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 12) value = value.substr(0, 12);
            if (value.length >= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1-$2-$3');
                if (value.length === 11) value += '-0000';
            }
            e.target.value = value;
        });
    });

    // Auto-format date inputs with MM/DD/YYYY format
    document.querySelectorAll('input[type="text"][name*="date"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) value = value.substr(0, 8);
            
            if (value.length >= 4) {
                const month = parseInt(value.substr(0, 2));
                const day = parseInt(value.substr(2, 2));
                
                if (month > 0 && month <= 12 && day > 0 && day <= 31) {
                    value = value.replace(/(\d{2})(\d{2})(\d{4})/, '$1/$2/$3');
                }
            }
            
            e.target.value = value;
        });
    });

    // Convert name inputs to uppercase
    document.querySelectorAll('input[name*="name"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    // Format currency inputs
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    });
});

// Export utility functions
window.utils = {
    formatDate,
    formatPagibigNumber,
    formatTIN,
    showSpinner,
    showError,
    isValidBirthdate,
    formatCurrency
};
