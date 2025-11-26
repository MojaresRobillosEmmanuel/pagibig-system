<!-- STL Application Modal -->
<div class="modal fade" id="stlModal" tabindex="-1" aria-labelledby="stlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="stlModalLabel">
                    <i class="fas fa-money-bill-wave me-2"></i>Short Term Loan Application
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="stlForm">
                    <div class="row g-3">
                        <!-- Pag-IBIG Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="stlPagibigNumber" name="pagibig_number" 
                                    pattern="\d{12}" maxlength="12" required>
                                <label for="stlPagibigNumber"><i class="fas fa-id-card me-2"></i>Pag-IBIG Number</label>
                                <div class="invalid-feedback">
                                    Please enter a valid 12-digit Pag-IBIG number
                                </div>
                            </div>
                        </div>

                        <!-- ID Number -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="stlIdNumber" name="id_number" required>
                                <label for="stlIdNumber"><i class="fas fa-id-badge me-2"></i>ID Number</label>
                                <div class="invalid-feedback">
                                    Please enter an ID number
                                </div>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="stlLastName" name="last_name" required>
                                <label for="stlLastName"><i class="fas fa-user me-2"></i>Last Name</label>
                                <div class="invalid-feedback">
                                    Please enter your last name
                                </div>
                            </div>
                        </div>

                        <!-- First Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="stlFirstName" name="first_name" required>
                                <label for="stlFirstName"><i class="fas fa-user me-2"></i>First Name</label>
                                <div class="invalid-feedback">
                                    Please enter your first name
                                </div>
                            </div>
                        </div>

                        <!-- Middle Name -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="stlMiddleName" name="middle_name">
                                <label for="stlMiddleName"><i class="fas fa-user me-2"></i>Middle Name</label>
                            </div>
                        </div>

                        <!-- Loan Amount -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="stlLoanAmount" name="loan_amount" 
                                    min="5000" step="1000" required>
                                <label for="stlLoanAmount"><i class="fas fa-peso-sign me-2"></i>Loan Amount</label>
                                <div class="invalid-feedback">
                                    Minimum loan amount is ₱5,000
                                </div>
                            </div>
                        </div>

                        <!-- Loan Term -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="stlLoanTerm" name="loan_term" required>
                                    <option value="">Select loan term</option>
                                    <option value="6">6 months</option>
                                    <option value="12">12 months</option>
                                    <option value="24">24 months</option>
                                </select>
                                <label for="stlLoanTerm"><i class="fas fa-calendar me-2"></i>Loan Term</label>
                                <div class="invalid-feedback">
                                    Please select a loan term
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Amortization Preview -->
                        <div class="col-12">
                            <div class="alert alert-info" id="monthlyAmortizationPreview" style="display: none;">
                                <i class="fas fa-calculator me-2"></i>
                                Monthly Amortization: <strong>₱<span id="monthlyAmortizationAmount">0.00</span></strong>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3" id="stlError" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" form="stlForm" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('stlForm');
    const errorDiv = document.getElementById('stlError');
    const pagibigInput = document.getElementById('stlPagibigNumber');
    const loanAmountInput = document.getElementById('stlLoanAmount');
    const loanTermSelect = document.getElementById('stlLoanTerm');
    const monthlyPreview = document.getElementById('monthlyAmortizationPreview');
    const monthlyAmount = document.getElementById('monthlyAmortizationAmount');

    // Format Pag-IBIG number while typing
    pagibigInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length > 12) {
            value = value.substr(0, 12);
        }
        e.target.value = value;
        
        if (value.length === 12) {
            e.target.classList.remove('is-invalid');
            e.target.classList.add('is-valid');
        } else {
            e.target.classList.remove('is-valid');
            e.target.classList.add('is-invalid');
        }
    });

    // Calculate monthly amortization
    function calculateMonthlyAmortization() {
        const loanAmount = parseFloat(loanAmountInput.value) || 0;
        const loanTerm = parseInt(loanTermSelect.value) || 0;
        
        if (loanAmount >= 5000 && loanTerm > 0) {
            const monthlyInterest = 0.105 / 12; // 10.5% annual interest
            const monthlyPayment = (loanAmount * monthlyInterest * Math.pow(1 + monthlyInterest, loanTerm)) / 
                                 (Math.pow(1 + monthlyInterest, loanTerm) - 1);
            
            monthlyAmount.textContent = monthlyPayment.toFixed(2);
            monthlyPreview.style.display = 'block';
        } else {
            monthlyPreview.style.display = 'none';
        }
    }

    // Update monthly amortization on input changes
    loanAmountInput.addEventListener('input', calculateMonthlyAmortization);
    loanTermSelect.addEventListener('change', calculateMonthlyAmortization);

    // Name fields validation
    ['stlLastName', 'stlFirstName', 'stlMiddleName'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function() {
                let value = this.value.replace(/[^a-zA-Z\s.]/g, '').toUpperCase();
                value = value.replace(/\s+/g, ' ').trim();
                this.value = value;
                
                if (value && this.required) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else if (this.required) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        }
    });

    // Form submission
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        errorDiv.style.display = 'none';
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

        try {
            const formData = {
                pagibig_number: pagibigInput.value.replace(/[^0-9]/g, ''),
                id_number: form.id_number.value.trim(),
                last_name: form.last_name.value.trim(),
                first_name: form.first_name.value.trim(),
                middle_name: form.middle_name.value.trim() || null,
                loan_amount: parseFloat(form.loan_amount.value),
                loan_term: parseInt(form.loan_term.value)
            };

            const response = await fetch('/Pagibig/includes/register_stl.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            let data;
            try {
                data = await response.json();
            } catch (e) {
                throw new Error('Invalid response from server');
            }

            if (!data.success) {
                throw new Error(data.message || 'Failed to submit loan application');
            }

            // Success handling
            const modal = bootstrap.Modal.getInstance(document.getElementById('stlModal'));
            modal.hide();

            await Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your loan application has been submitted successfully!',
                showConfirmButton: true
            });

            // Reset form
            form.reset();
            monthlyPreview.style.display = 'none';
            form.querySelectorAll('.is-valid, .is-invalid').forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
            });

        } catch (error) {
            console.error('Error:', error);
            errorDiv.textContent = error.message || 'An error occurred. Please try again.';
            errorDiv.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Application';
        }
    });

    // Reset form when modal is closed
    document.getElementById('stlModal').addEventListener('hidden.bs.modal', function () {
        form.reset();
        errorDiv.style.display = 'none';
        monthlyPreview.style.display = 'none';
        form.querySelectorAll('.is-valid, .is-invalid').forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
    });
});
</script>
