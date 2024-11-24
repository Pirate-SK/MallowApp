<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Billing Page</title>
    <style>
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #000;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        /* Email section */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group label {
            min-width: 120px;
        }

        .form-group input {
            flex: 1;
            max-width: 200px;
            padding: 5px;
            border: 1px solid #000;
        }

        /* Bill section */
        .bill-section {
            margin-bottom: 30px;
        }

        .bill-section label {
            display: block;
            margin-bottom: 10px;
        }

        .bill-row {
            display: grid;
            grid-template-columns: 200px 200px;
            gap: 20px;
            margin-bottom: 10px;
        }

        .bill-row input {
            width: 100%;
            padding: 5px;
            border: 1px solid #000;
        }

        /* Add New button */
        .add-new {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 15px;
            cursor: pointer;
            margin: 10px 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        /* Denominations section */
        .denominations {
            margin: 30px 0;
            border-top: 1px solid #000;
            padding-top: 20px;
        }

        .denomination-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .denomination-row span {
            min-width: 30px;
        }

        .denomination-row input {
            width: 150px;
            padding: 5px;
            border: 1px solid #000;
        }

        /* Button section */
        .button-section {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }

        .button-section button {
            padding: 5px 15px;
            cursor: pointer;
        }

        .button-section button.generate {
            background: white;
            color: green;
            border: 1px solid #000;
        }

        .button-section button.cancel {
            background: white;
            color: black;
            border: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Billing Page</h2>

        <form id="billingForm" method="POST" action="{{ route('billing.store') }}">
            @csrf
            
            <div class="form-group">
                <label>Customer Email</label>
                <input 
                    type="email" 
                    name="customer_email" 
                    placeholder="Email ID" 
                    required
                >
            </div>

            <div class="bill-section">
                <label>Bill section</label>
                <div id="bill-items">
                    <div class="bill-row">
                        <input 
                            type="text" 
                            name="product_id[]" 
                            placeholder="Product ID"
                            required
                        >
                        <input 
                            type="number" 
                            name="quantity[]" 
                            placeholder="Quantity"
                            min="1"
                            required
                        >
                    </div>
                </div>
                <button type="button" class="add-new" id="add-item">Add New</button>
            </div>

            <div class="denominations">
                <label>Denominations</label>
                @foreach([500, 50, 20, 10, 5, 2, 1] as $denomination)
                    <div class="denomination-row">
                        <span>{{ $denomination }}</span>
                        <input 
                            type="number" 
                            name="denomination[{{ $denomination }}]" 
                            placeholder="Count"
                            min="0"
                            oninput="validity.valid||(value='');"
                        >
                    </div>
                @endforeach
            </div>

            <div class="form-group">
                <label>Cash paid by customer</label>
                <input 
                    type="number" 
                    name="amount_paid" 
                    placeholder="Amount"
                    min="0"
                    step="0.01"
                    required
                >
            </div>

            <div class="button-section">
                <button type="button" class="cancel">Cancel</button>
                <button type="submit" class="generate">Generate Bill</button>
            </div>
        </form>

        <div id="errorMessages" style="color: red; margin-top: 20px;"></div>
    </div>

    <script>
        document.getElementById('add-item').addEventListener('click', function() {
            const billItems = document.getElementById('bill-items');
            const newRow = `
                <div class="bill-row">
                    <input 
                        type="text" 
                        name="product_id[]" 
                        placeholder="Product ID"
                        required
                    >
                    <input 
                        type="number" 
                        name="quantity[]" 
                        placeholder="Quantity"
                        min="1"
                        required
                    >
                </div>
            `;
            billItems.insertAdjacentHTML('beforeend', newRow);
        });

        // Prevent negative values for all number inputs
        document.addEventListener('input', function(e) {
            if (e.target.type === 'number') {
                if (e.target.value < 0) {
                    e.target.value = '';
                }
                // Ensure whole numbers for denomination counts
                if (e.target.name.startsWith('denomination')) {
                    e.target.value = Math.floor(e.target.value);
                }
            }
        });

        // Form submission handling
        document.getElementById('billingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear previous error messages
            const errorDiv = document.getElementById('errorMessages');
            errorDiv.innerHTML = '';
            
            try {
                // Validate form data
                let hasError = false;
                const errorMessages = [];

                // Validate denominations
                document.querySelectorAll('input[name^="denomination"]').forEach(input => {
                    if (input.value && (!Number.isInteger(Number(input.value)) || Number(input.value) < 0)) {
                        hasError = true;
                        errorMessages.push('Denomination counts must be positive whole numbers');
                    }
                });

                // Validate amount paid
                const amountPaid = document.querySelector('input[name="amount_paid"]').value;
                if (Number(amountPaid) < 0) {
                    hasError = true;
                    errorMessages.push('Amount paid must be positive');
                }

                // Display validation errors if any
                if (hasError) {
                    errorMessages.forEach(message => {
                        errorDiv.innerHTML += `<p>${message}</p>`;
                    });
                    return;
                }

                // Prepare form data
                const formData = new FormData(this);

                // Make API call
                const response = await fetch('{{ route('billing.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong');
                }

                if (data.success) {
                    // Redirect on success
                    window.location.href = data.redirect_url;
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        Object.values(data.errors).forEach(error => {
                            errorDiv.innerHTML += `<p>${error}</p>`;
                        });
                    } else if (data.message) {
                        errorDiv.innerHTML = `<p>${data.message}</p>`;
                    }
                }

            } catch (error) {
                console.error('Error:', error);
                errorDiv.innerHTML = `<p>An error occurred: ${error.message}</p>`;
            }
        });

        // Optional: Add cancel button functionality
        document.querySelector('.cancel').addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
                window.location.href = '{{ route('billing.create') }}';
            }
        });

        // Optional: Calculate total amount as denominations are entered
        function updateTotalAmount() {
            let total = 0;
            document.querySelectorAll('input[name^="denomination"]').forEach(input => {
                const value = parseInt(input.name.match(/\[(\d+)\]/)[1]);
                const count = parseInt(input.value) || 0;
                total += value * count;
            });
            const amountInput = document.querySelector('input[name="amount_paid"]');
            amountInput.value = total.toFixed(2);
        }

        // Add event listeners for denomination inputs
        document.querySelectorAll('input[name^="denomination"]').forEach(input => {
            input.addEventListener('input', updateTotalAmount);
        });
    </script>
</body>
</html>
