<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Billing Details</title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #000;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .customer-info {
            margin-bottom: 20px;
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .bill-table th, .bill-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .totals {
            margin-left: auto;
            width: 400px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .denominations {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #000;
        }

        .denomination-title {
            text-align: right;
            margin-bottom: 10px;
        }

        .denomination-list {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
        }

        .denomination-item {
            display: flex;
            gap: 5px;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #000;
        }

        .download-btn, .print-btn {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid #000;
            background: white;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .download-btn {
            color: #2196F3;
            border-color: #2196F3;
        }

        .print-btn {
            color: #4CAF50;
            border-color: #4CAF50;
        }

        .download-btn:hover {
            background: #2196F3;
            color: white;
        }

        .print-btn:hover {
            background: #4CAF50;
            color: white;
        }

        /* Hide buttons when printing */
        @media print {
            .action-buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Billing Page</h2>

        <div class="customer-info">
            <strong>Customer Email:</strong> {{ $bill->customer_email }}
        </div>

        <table class="bill-table">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Purchase Price</th>
                    <th>Tax % for item</th>
                    <th>Tax payable for item</th>
                    <th>Total price of line item</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->items as $item)
                    <tr>
                        <td>{{ $item->product_id }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                        <td>{{ $item->tax_percentage }}%</td>
                        <td>{{ number_format(($item->unit_price * $item->quantity * $item->tax_percentage) / 100, 2) }}</td>
                        <td>{{ number_format(($item->unit_price * $item->quantity) + (($item->unit_price * $item->quantity * $item->tax_percentage) / 100), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Total price without tax:</span>
                <span>{{ number_format($totalPriceWithoutTax, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Total tax payable:</span>
                <span>{{ number_format($totalTaxPayable, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Net price of the purchased item:</span>
                <span>{{ number_format($netPrice, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Rounded down value of the purchased items net price:</span>
                <span>{{ number_format($roundedNetPrice, 2) }}</span>
            </div>
            
            <div class="totals-row">
                <span>Balance payable to the customer:</span>
                <span>{{ number_format($balancePayable, 2) }}</span>
            </div>
        </div>

        <div class="denominations">
            <div class="denomination-list">
                <span>Paid Amount:</span>
                <span>{{ number_format($amount_paid, 2) }}</span>
            </div>
            <div class="denomination-title">Paid Denomination:</div>
            <div class="denomination-list">
                @foreach($bill->denominations as $denomination)
                    <div class="denomination-item">
                        <span>{{ $denomination->denomination }}:</span>
                        <span>{{ $denomination->count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="action-buttons">
            <a href="{{ route('billing.download', $bill->id) }}" class="download-btn">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Download PDF
            </a>
        </div>
    </div>
</body>
</html>