document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('editModal');
    
    function setupEditEmployeeForm() {
        if (!editModal) {
            console.error('Edit modal element not found');
            return;
        }
        
        // Initialize modal properties
        editModal.style.display = 'none'; // Hide by default
        
        // Add event listeners for opening/closing modal
        document.querySelectorAll('.edit-employee-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                editModal.style.display = 'block';
            });
        });
        
        // Close button functionality
        const closeBtn = editModal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                editModal.style.display = 'none';
            });
        }
        
        // Close when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        });
    }
    
    // Initialize the form setup
    setupEditEmployeeForm();
});