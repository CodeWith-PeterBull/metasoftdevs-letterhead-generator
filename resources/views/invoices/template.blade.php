<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>

    {{-- Compute Document items Scaler factor --}}
    @php
        $itemCount = $invoice->items->count();

        // Define a simple rule for scaling
        // e.g. Start at 1 (100%) and reduce 0.02 for each extra item after 5
        $scale = 1;

        if ($itemCount > 2) {
            $scale = max(0.65, 1 - ($itemCount - 2) * 0.02);
            // never go below 65%
        }
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 3.7% 4% !important;
            size: letter;
            /* size: A4; */
            /* size: legal; */
            /* size: landscape; */
            transform: scale({{ $scale }});
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .invoice-container {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2pt solid #333;
            padding-bottom: 10pt;
            margin-bottom: 20pt;
            height: 2in;
            max-height: 2in;
        }

        .company-info {
            display: table-cell;
            /* width: 2.5in; */
            width: 50%;
            vertical-align: middle;
            padding-right: 20pt;
        }

        .company-logo {
            max-width: 2in;
            max-height: 1.5in;
            width: auto;
            height: auto;
            margin-bottom: 8pt;
        }

        .company-name-fallback {
            font-size: 16pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 8pt;
        }

        .company-info h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #333;
            margin: 0 0 8pt 0;
        }

        .company-info p {
            font-size: 11pt;
            color: #000;
            line-height: 1.3;
            margin: 0 0 4pt 0;
        }

        .invoice-details {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: auto;
        }

        .invoice-title {
            font-size: 24pt;
            font-weight: bold;
            color: #333;
            margin: 0 0 8pt 0;
        }

        .invoice-meta {
            font-size: 11pt;
            color: #000;
            line-height: 1.3;
        }

        .client-info {
            background-color: #f8f9fa;
            padding: 12pt;
            margin-bottom: 16pt;
            border-left: 3pt solid #007bff;
        }

        .client-info h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #333;
            margin: 0 0 6pt 0;
        }

        .client-info p {
            font-size: 11pt;
            color: #000;
            line-height: 1.3;
            margin: 0;
        }

        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16pt 0;
            border: 1pt solid #333;
        }

        .services-table th {
            background-color: #333;
            color: white;
            padding: 8pt;
            text-align: left;
            font-size: 11pt;
            font-weight: bold;
            border-right: 1pt solid #666;
            border-bottom: 1pt solid #666;
        }

        .services-table th:last-child {
            border-right: none;
        }

        .services-table td {
            padding: 10pt 8pt;
            border-bottom: 1pt solid #ddd;
            border-right: 1pt solid #ddd;
            font-size: 11pt;
            vertical-align: top;
        }

        .services-table td:last-child {
            border-right: none;
        }

        .service-number {
            font-weight: bold;
            color: #333;
        }

        .service-description {
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }

        .totals-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .totals-table {
            width: 300px;
            margin-left: auto;
        }

        .totals-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 12px;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
        }

        .totals-table .label {
            font-weight: bold;
            text-align: right;
        }

        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .grand-total {
            background-color: #333;
            color: white;
        }

        .payment-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .payment-info {
            flex: 1;
            margin-right: 20px;
        }

        .payment-info h3 {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .payment-info p {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 5px;
        }

        .bank-details {
            flex: 1;
        }

        .notes-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .notes-section h3 {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
        }

        .notes-section p {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .status-badge {
            display: inline-block;
            padding: 3pt 8pt;
            border-radius: 2pt;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 8pt;
        }

        .status-draft {
            background-color: #ffc107;
            color: #000;
        }

        .status-sent {
            background-color: #17a2b8;
            color: white;
        }

        .status-paid {
            background-color: #28a745;
            color: white;
        }

        .status-overdue {
            background-color: #dc3545;
            color: white;
        }

        .status-cancelled {
            background-color: #6c757d;
            color: white;
        }

        /* Print optimizations */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .header {
                border-bottom: 2pt solid #333 !important;
            }

            .services-table th {
                background-color: #333 !important;
                color: white !important;
            }

            .status-badge {
                border: 1pt solid #333 !important;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                @php
                    $logoHtml = '';
                    if ($invoice->company->hasMedia('logo')) {
                        $logoPath = $invoice->company->getFirstMedia('logo')->getPath();
                        if (file_exists($logoPath)) {
                            $logoBase64 = base64_encode(file_get_contents($logoPath));
                            $logoMime = mime_content_type($logoPath);
                            $logoHtml =
                                '<img src="data:' .
                                $logoMime .
                                ';base64,' .
                                $logoBase64 .
                                '" alt="' .
                                htmlspecialchars($invoice->company->name) .
                                '" class="company-logo">';
                        }
                    }
                @endphp


                @if ($logoHtml)
                    {!! $logoHtml !!}
                    <h1>{{ $invoice->company->name }}</h1>
                @else
                    <div class="company-name-fallback">{{ $invoice->company->name }}</div>
                @endif

                <p>
                    @if ($invoice->company->address)
                        {!! nl2br(e($invoice->company->address)) !!}<br>
                    @endif
                    @if ($invoice->company->phone_1)
                        Tel: {{ $invoice->company->phone_1 }}<br>
                    @endif
                    @if ($invoice->company->email_1)
                        Email: {{ $invoice->company->email_1 }}<br>
                    @endif
                    @if ($invoice->company->website)
                        Web: {{ $invoice->company->website }}
                    @endif
                </p>
            </div>
            <div class="invoice-details">
                <div class="invoice-title">INVOICE</div>
                <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                <div class="invoice-meta">
                    <strong>Number: {{ $invoice->invoice_number }}</strong><br>
                    <strong>Date: {{ $invoice->invoice_date->format('d/m/Y') }}</strong><br>
                    @if ($invoice->due_date)
                        <strong>Due Date: {{ $invoice->due_date->format('d/m/Y') }}</strong>
                    @endif
                </div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="client-info">
            <h3>TO: {{ strtoupper($invoice->invoiceTo->company_name) }}</h3>
            <p>
                @if ($invoice->invoiceTo->company_address)
                    {!! nl2br(e($invoice->invoiceTo->company_address)) !!}<br>
                @endif
                @if ($invoice->invoiceTo->primary_phone || $invoice->invoiceTo->secondary_phone || $invoice->invoiceTo->email)
                    Contact:<br>
                    @if ($invoice->invoiceTo->primary_phone)
                        {{ $invoice->invoiceTo->primary_phone }}
                    @endif
                    @if ($invoice->invoiceTo->primary_phone && $invoice->invoiceTo->secondary_phone)
                        |
                    @endif
                    @if ($invoice->invoiceTo->secondary_phone)
                        {{ $invoice->invoiceTo->secondary_phone }}
                    @endif
                    @if (($invoice->invoiceTo->primary_phone || $invoice->invoiceTo->secondary_phone) && $invoice->invoiceTo->email)
                        |
                    @endif
                    @if ($invoice->invoiceTo->email)
                        {{ $invoice->invoiceTo->email }}
                    @endif
                @endif
            </p>
        </div>

        <!-- Services Table -->
        <table class="services-table">
            <thead>
                <tr>
                    <th style="width: 60%;">DESCRIPTION</th>
                    <th style="width: 10%;">QTY</th>
                    <th style="width: 15%;">UNIT PRICE</th>
                    <th style="width: 15%;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $index => $item)
                    <tr>
                        <td>
                            <div class="service-number">{{ $index + 1 }}. {{ $item->service_name }}
                                @if ($item->period)
                                    ({{ $item->period }})
                                @endif
                            </div>
                            @if ($item->description)
                                <div class="service-description">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            {{ number_format($item->quantity, 2) }}
                            @if ($item->unit && $item->unit !== 'service')
                                {{ $item->unit }}
                            @endif
                        </td>
                        <td style="text-align: right;">
                            {{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: bold;">
                            {{ $invoice->currency }} {{ number_format($item->quantity * $item->unit_price, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <!-- Payment Information -->
            <div
                style=" position: absolute; flex: 1; margin-right: 20px;  transform: scale({{ $scale }}); transform-origin: top left;">
                @if ($invoice->invoiceTo->mpesa_account || $invoice->invoiceTo->bank_account)
                    <div class="payment-section">
                        <div class="payment-info">
                            <h3>PAYABLE TO</h3>
                            <p>
                                <strong>{{ $invoice->company->name }}</strong><br>
                                @if ($invoice->company->phone_1)
                                    {{ $invoice->company->phone_1 }}<br>
                                @endif
                                @if ($invoice->company->email_1)
                                    {{ $invoice->company->email_1 }}
                                @endif
                            </p>
                        </div>
                        <div class="bank-details">
                            <h3>PAYMENT DETAILS</h3>
                            @if ($invoice->invoiceTo->mpesa_account)
                                <p>
                                    <strong>MPESA Account:</strong> {{ $invoice->invoiceTo->mpesa_account }}<br>
                                    @if ($invoice->invoiceTo->mpesa_holder_name)
                                        <strong>Holder Name:</strong> {{ $invoice->invoiceTo->mpesa_holder_name }}
                                    @endif
                                </p>
                            @endif

                            @if ($invoice->invoiceTo->mpesa_account && $invoice->invoiceTo->bank_account)
                                <p style="margin: 10px 0;"><strong>or</strong></p>
                            @endif

                            @if ($invoice->invoiceTo->bank_account)
                                <p>
                                    @if ($invoice->invoiceTo->bank_name)
                                        <strong>{{ $invoice->invoiceTo->bank_name }} Account:</strong>
                                        {{ $invoice->invoiceTo->bank_account }}<br>
                                    @else
                                        <strong>Bank Account:</strong> {{ $invoice->invoiceTo->bank_account }}<br>
                                    @endif
                                    @if ($invoice->invoiceTo->bank_holder_name)
                                        <strong>Holder Name:</strong> {{ $invoice->invoiceTo->bank_holder_name }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
            <div class="totals-table">
                <table>
                    <tr>
                        <td class="label">SUB TOTAL</td>
                        <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->sub_total, 2) }}</td>
                    </tr>
                    @if ($invoice->discount_amount > 0)
                        <tr>
                            <td class="label">DISCOUNT</td>
                            <td class="amount">-{{ $invoice->currency }}
                                {{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->tax_amount > 0)
                        <tr>
                            <td class="label">TAX</td>
                            <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}
                            </td>
                        </tr>
                    @endif
                    @if ($invoice->status !== 'paid')
                        <tr>
                            <td class="label">BALANCE</td>
                            <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->balance, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="label">GRAND TOTAL</td>
                        <td class="amount">{{ $invoice->currency }} {{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>



        <!-- Notes Section -->
        @if ($invoice->notes)
            <div class="notes-section" style=" position: relative; margin-top: 15px;">
                <h3>NOTES:</h3>
                <p>{!! nl2br(e($invoice->notes)) !!}</p>
            </div>
        @endif

        <!-- Payment Status -->
        @if ($invoice->status === 'paid' && $invoice->paid_at)
            <div class="notes-section"
                style="margin-top: 15px; background-color: #d4edda; border-left: 4px solid #28a745;">
                <h3>PAYMENT RECEIVED</h3>
                <p>
                    This invoice was paid on {{ $invoice->paid_at->format('d/m/Y') }}
                    @if ($invoice->payment_method)
                        via {{ $invoice->payment_method }}
                    @endif
                    @if ($invoice->payment_reference)
                        (Reference: {{ $invoice->payment_reference }})
                    @endif
                </p>
            </div>
        @endif
    </div>
</body>

</html>
