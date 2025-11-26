// Function to handle API requests
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'API request failed');
        }

        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Contribution related functions
const ContributionService = {
    async getContributions(month, year) {
        return apiRequest(`api/get_contributions.php?month=${month}&year=${year}`);
    },

    async saveContribution(data) {
        return apiRequest('api/get_contributions.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    // Function to display contributions in table
    displayContributions(contributions) {
        const tbody = document.querySelector('#selectedEmployeesTable tbody');
        tbody.innerHTML = '';

        contributions.forEach(contribution => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${contribution.pagibig_no}</td>
                <td>${contribution.id_no}</td>
                <td>${contribution.last_name}</td>
                <td>${contribution.first_name}</td>
                <td>${contribution.middle_name}</td>
                <td>${contribution.ee}</td>
                <td>${contribution.er}</td>
                <td>${contribution.tin}</td>
                <td>${contribution.birthdate}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="ContributionService.deleteContribution('${contribution.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    },

    // Function to handle errors
    handleError(error) {
        console.error('Error:', error);
        alert(error.message || 'An error occurred');
    }
};
