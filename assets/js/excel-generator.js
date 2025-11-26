document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('btnGenerateExcel');
    if (!generateBtn) {
        console.error('Generate Excel button not found');
        return;
    }

    generateBtn.addEventListener('click', async function() {
        try {
            // Get selected month and year
            const month = document.getElementById('month').value;
            const year = document.getElementById('yearInput').value;

            if (!month || !year) {
                alert('Please select both month and year');
                return;
            }

            console.log('Fetching data for:', { month, year });

            // Fetch data from the API
            const response = await fetch(`includes/get_contributions.php?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();

            if (!data || data.length === 0) {
                alert('No data found for the selected month and year.');
                return;
            }

            // Prepare worksheet data
            const headers = [
                'PAG-IBIG MID NO.',
                'EMPLOYEE NUMBER',
                'LAST NAME',
                'FIRST NAME',
                'MIDDLE NAME',
                'EMPLOYEE SHARE',
                'EMPLOYER SHARE',
                'TIN',
                'BIRTHDATE',
                'PERIOD MONTH',
                'PERIOD YEAR'
            ];

            const wsData = [headers];

            // Calculate totals while adding rows
            let totalEE = 0;
            let totalER = 0;

            // Add data rows
            data.forEach(row => {
                wsData.push([
                    row['pagibig_no'],
                    row['id_no'],
                    row['last_name'],
                    row['first_name'],
                    row['middle_name'],
                    row['ee'],
                    row['er'],
                    row['tin'],
                    row['birthdate'],
                    month,
                    year
                ]);

                totalEE += parseFloat(row['ee']) || 0;
                totalER += parseFloat(row['er']) || 0;
            });

            // Add totals row
            wsData.push(['', '', '', '', 'TOTAL:', totalEE.toFixed(2), totalER.toFixed(2), '', '', '', '']);

            // Generate Excel file
            const ws = XLSX.utils.aoa_to_sheet(wsData);

            // Add styling
            ws['!cols'] = [
                {wch: 20}, // PAG-IBIG
                {wch: 15}, // Employee Number
                {wch: 20}, // Last Name
                {wch: 20}, // First Name
                {wch: 20}, // Middle Name
                {wch: 15}, // EE Share
                {wch: 15}, // ER Share
                {wch: 15}, // TIN
                {wch: 12}, // Birthdate
                {wch: 12}, // Month
                {wch: 8}   // Year
            ];

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'PAG-IBIG Contributions');

            // Generate and save file
            const filename = `PAG-IBIG_Contributions_${month}_${year}.xls`;
            XLSX.writeFile(wb, filename);

            // Close modal
            const closeBtn = document.querySelector('.modal-header .btn-close');
            if (closeBtn) {
                closeBtn.click();
            }

            alert('Excel file has been generated successfully!');

        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while generating the Excel file. Please try again.');
        }
    });
});