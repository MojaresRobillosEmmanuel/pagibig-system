// Function to validate and format name input
function setupNameValidation(inputElement) {
    if (!inputElement) return;

    inputElement.addEventListener('input', function(e) {
        // Remove numbers, special characters, and convert to uppercase
        let value = this.value.replace(/[^a-zA-Z\s.]/g, '').toUpperCase();
        
        // Remove extra spaces
        value = value.replace(/\s+/g, ' ');
        
        // Update input value
        this.value = value;
        
        // Validate field
        if (value.trim().length > 0 && /^[A-Z\s.]+$/.test(value)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else if (value.trim().length > 0) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });

    // Add invalid feedback div if it doesn't exist
    if (!inputElement.nextElementSibling?.classList.contains('invalid-feedback')) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = 'Please enter valid name (letters only)';
        inputElement.parentNode.appendChild(feedback);
    }
}

// Function to setup all name fields
function setupAllNameFields() {
    const nameFields = ['lastName', 'firstName', 'middleName'];
    nameFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input) {
            setupNameValidation(input);
        }
    });
}

// Setup validation when document is ready
document.addEventListener('DOMContentLoaded', setupAllNameFields);

// Setup validation when modal is shown (in case the modal is loaded dynamically)
document.addEventListener('shown.bs.modal', function(event) {
    if (event.target.id === 'registerEmployeeModal') {
        setupAllNameFields();
    }
});
