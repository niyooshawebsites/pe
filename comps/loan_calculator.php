<div class="modal fade" id="loanCalculatorModal" tabindex="-1" aria-labelledby="loanCalculatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="loanCalculatorModalLabel">Loan EMI Calculator</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Loan Amount (₹)</label>
                        <input type="text" id="loanAmount" class="form-control" placeholder="e.g. 5000000" oninput="formatLoanAmount(this)" onblur="formatLoanAmount(this)">
                    </div>
                    <div class="col-md-4">
                        <label>Interest Rate (%)</label>
                        <input type="number" id="interestRate" class="form-control" placeholder="e.g. 8.5">
                    </div>
                    <div class="col-md-4">
                        <label>Loan Tenure (Years)</label>
                        <input type="number" id="loanTenure" class="form-control" placeholder="e.g. 20">
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button onclick="calculateEMI()" class="btn btn-success px-4">Calculate</button>
                </div>

                <div id="result" class="alert alert-info mt-4 d-none"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to format the loan amount in Indian style numbering (e.g. 50,00,000)
    function formatLoanAmount(input) {
        let value = input.value.replace(/[^\d]/g, ''); // Remove non-numeric characters

        // Indian number system formatting logic
        if (value.length <= 3) {
            input.value = value;
            return;
        }

        // If the number length is greater than 3, format it as per Indian numbering system
        let lastThree = value.slice(-3);
        let otherNumbers = value.slice(0, value.length - 3);
        if (otherNumbers != '') lastThree = ',' + lastThree;
        input.value = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
    }

    function calculateEMI() {
        const P = parseFloat(document.getElementById("loanAmount").value.replace(/,/g, '')); // Remove commas for calculation
        const annualRate = parseFloat(document.getElementById("interestRate").value);
        const years = parseInt(document.getElementById("loanTenure").value);

        if (isNaN(P) || isNaN(annualRate) || isNaN(years) || P <= 0 || annualRate <= 0 || years <= 0) {
            alert("Please enter valid values in all fields.");
            return;
        }

        const R = annualRate / 12 / 100; // monthly interest
        const N = years * 12; // number of months

        const EMI = (P * R * Math.pow(1 + R, N)) / (Math.pow(1 + R, N) - 1);
        const totalPayment = EMI * N;
        const totalInterest = totalPayment - P;

        const formatINR = (amount) => {
            // Format number in Indian style
            return amount.toLocaleString('en-IN', {
                style: 'currency',
                currency: 'INR'
            }).replace(/^(\D+)/, '$1'); // Remove currency symbol
        };

        const resultDiv = document.getElementById("result");
        resultDiv.classList.remove("d-none");
        resultDiv.innerHTML = `
        <h6 class="mb-2">Results:</h6>
        <strong>Monthly EMI:</strong> ${formatINR(EMI)}<br>
        <strong>Total Payment:</strong> ${formatINR(totalPayment)}<br>
        <strong>Total Interest:</strong> ${formatINR(totalInterest)}
    `;
    }
</script>