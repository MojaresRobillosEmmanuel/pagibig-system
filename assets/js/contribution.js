document.addEventListener('DOMContentLoaded', function() {
    // Get the form element
    const createForm = document.getElementById('createContributionForm');
    if (createForm) {
        // Add submit event listener to the form
        createForm.addEventListener('submit', function(event) {
            event.preventDefault();

            // Create FormData object
            const formData = new FormData(createForm);

            // Send POST request to the server
            fetch('process_contribution.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Contribution added successfully!');
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createContributionModal'));
                    modal.hide();
                    // Reload the page to show the new data
                    window.location.reload();
                } else {
                    // Show error message
                    alert(data.message || 'Failed to add contribution. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        // Reset form when modal is closed
        document.getElementById('createContributionModal').addEventListener('hidden.bs.modal', function () {
            createForm.reset();
        });
    }

    // Handle removing employee from selected list
    document.querySelectorAll('.remove-employee').forEach(button => {
        button.addEventListener('click', function() {
            const pagibigNo = this.getAttribute('data-pagibig');
            removeSelectedEmployee(pagibigNo, this.closest('tr'));
        });
    });

    // Function to remove selected employee
    function removeSelectedEmployee(pagibigNo, row) {
        fetch('process_contribution.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                pagibig_no: pagibigNo
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
            } else {
                alert('Error removing employee: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the employee');
        });
    }

    // Function to save selected employee
    window.saveSelectedEmployee = function(employeeData) {
        fetch('process_contribution.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save',
                employee: employeeData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show updated list
                location.reload();
            } else {
                alert('Error saving employee: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the employee');
        });
    };
});
