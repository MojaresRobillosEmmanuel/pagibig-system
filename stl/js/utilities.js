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