<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $quotation->number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 18mm 22mm 18mm;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.4;
        }

        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: top; }

        .page { width: 100%; max-width: 100%; }
        .accent-bar { height: 3px; background: #0b5ed7; margin-bottom: 8px; }

        /* Header */
        .hdr-table td { padding: 0; vertical-align: middle; }
        .hdr-logo  { width: 68px; }
        .hdr-logo img { height: 50px; width: auto; }
        .hdr-company { padding-left: 10px; }
        .hdr-name  { font-size: 11pt; font-weight: bold; color: #0b5ed7; letter-spacing: 0.2px; }
        .hdr-tag   { font-size: 7.5pt; color: #5c6b7a; margin-top: 2px; }
        .hdr-cert  { font-size: 7pt; color: #94a3b8; margin-top: 1px; }
        .doc-badge {
            border: 1.5px solid #0b5ed7;
            border-radius: 3px;
            padding: 6px 10px;
            text-align: right;
            background: #e8f2fc;
        }
        .doc-badge .type  { font-size: 11pt; font-weight: bold; color: #0b5ed7; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-badge .ref   { font-family: DejaVu Sans Mono, monospace; font-size: 9pt; font-weight: bold; color: #1e293b; margin-top: 2px; }
        .doc-badge .date  { font-size: 7.5pt; color: #5c6b7a; margin-top: 2px; }

        /* Bill To / Project */
        .bill-project { margin-top: 8px; border: 1px solid #c8d3e0; }
        .bill-project th {
            background: #0b5ed7;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            padding: 4px 8px;
            text-align: left;
            width: 50%;
        }
        .bill-project td {
            padding: 5px 8px;
            font-size: 8pt;
            border-top: 1px solid #e8edf2;
            vertical-align: top;
        }
        .bill-project .name { font-weight: bold; font-size: 9pt; color: #1e293b; }
        .bill-project .sub  { color: #5c6b7a; font-size: 7.5pt; margin-top: 2px; }

        /* Items */
        .items { margin-top: 8px; }
        .items thead th {
            background: #0b5ed7;
            color: #ffffff;
            font-size: 7pt;
            font-weight: bold;
            padding: 5px 4px;
            text-align: center;
            letter-spacing: 0.3px;
            border-right: 1px solid #084298;
        }
        .items thead th:last-child { border-right: none; }
        .items tbody td {
            padding: 4px 5px;
            font-size: 7.5pt;
            border-bottom: 1px solid #e8edf2;
            border-right: 1px solid #e8edf2;
        }
        .items tbody td:last-child { border-right: none; }
        .items tbody tr:nth-child(even) td { background: #f4f7fb; }
        .items .c-sn    { width: 4%;  text-align: center; color: #5c6b7a; }
        .items .c-code  { width: 18%; text-align: left; font-family: DejaVu Sans Mono, monospace; font-size: 6.5pt; color: #475569; word-break: break-all; }
        .items .c-desc  { width: 30%; text-align: left; }
        .items .c-qty   { width: 6%;  text-align: center; }
        .items .c-unit  { width: 8%;  text-align: center; }
        .items .c-price { width: 12%; text-align: right; font-family: DejaVu Sans Mono, monospace; }
        .items .c-total { width: 12%; text-align: right; font-family: DejaVu Sans Mono, monospace; font-weight: bold; color: #0b5ed7; }

        /* Contact strip */
        .contact-strip {
            margin-top: 8px;
            background: #0b5ed7;
            color: #ffffff;
            text-align: center;
            font-size: 7pt;
            padding: 4px 8px;
            letter-spacing: 0.2px;
        }

        /* Summary */
        .summary-wrap { margin-top: 8px; }
        .summary-wrap td { vertical-align: top; }
        .totals-box { border: 1.5px solid #0b5ed7; }
        .totals-box td { padding: 4px 8px; font-size: 8pt; border-bottom: 1px solid #e8edf2; }
        .totals-box tr:last-child td { border-bottom: none; }
        .totals-box .tl { color: #5c6b7a; font-weight: bold; width: 55%; text-transform: uppercase; font-size: 7.5pt; letter-spacing: 0.3px; }
        .totals-box .tv { text-align: right; font-family: DejaVu Sans Mono, monospace; font-weight: bold; color: #1e293b; }
        .totals-box .grand td { background: #0b5ed7; color: #ffffff; font-size: 9pt; font-weight: bold; padding: 5px 8px; }
        .totals-box .grand .tv { color: #ffffff; font-size: 10pt; }

        /* Words */
        .words-box {
            border: 1px solid #c8d3e0;
            background: #e8f2fc;
            padding: 6px 8px;
            margin-top: 8px;
            font-size: 7.5pt;
            font-style: italic;
            color: #475569;
        }
        .words-box .wh { font-weight: bold; color: #0b5ed7; font-style: normal; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 3px; }

        /* Terms */
        .terms-box { border: 1px solid #c8d3e0; margin-top: 8px; padding: 6px 8px; }
        .terms-box .th { font-weight: bold; color: #0b5ed7; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .terms-box ul { margin: 0; padding-left: 14px; font-size: 7pt; color: #475569; line-height: 1.55; }
        .terms-box li { margin-bottom: 2px; }

        /* Bank */
        .bank-wrap { margin-top: 8px; border: 1px solid #c8d3e0; }
        .bank-hdr td { background: #0b5ed7; color: #fff; font-size: 7.5pt; font-weight: bold; padding: 4px 8px; letter-spacing: 0.4px; text-transform: uppercase; }
        .bank-wrap td { padding: 3px 8px; font-size: 7pt; border-bottom: 1px solid #e8edf2; border-right: 1px solid #e8edf2; }
        .bank-wrap tr:last-child td { border-bottom: none; }
        .bank-wrap .bk { font-weight: bold; color: #0b5ed7; background: #f4f7fb; width: 22%; }

        /* Footer */
        .footer {
            margin-top: 8px;
            border-top: 1.5px solid #0b5ed7;
            padding-top: 5px;
            text-align: center;
            font-size: 6.5pt;
            color: #94a3b8;
            line-height: 1.5;
        }
        .footer strong { color: #0b5ed7; }
    </style>
</head>
<body>
@php
    use App\Support\NumberToWords;

    $clientName  = $quotation->contact?->name_en ?: $quotation->contact?->name_ar ?: '';
    $clientCo    = $quotation->contact?->company ?? '';
    $clientEmail = $quotation->contact?->email ?? '';
    $clientPhone = $quotation->contact?->phone ?? '';
    $projectName = $quotation->project?->name_en ?: $quotation->project?->name_ar ?: '';

    $subtotal      = (float) $quotation->subtotal_aed;
    $discount      = (float) $quotation->discount_aed;
    $afterDiscount = max(0, $subtotal - $discount);
    $vat           = round($afterDiscount * 0.05, 2);
    $grandTotal    = round($afterDiscount + $vat, 2);
    $amountWords   = NumberToWords::aed($grandTotal);

    $companyName = $settings['company_name_en'] ?? 'AL WAAB BUILDING MATERIALS TRADING EST';
    $validityDays = $settings['quotation_validity_days'] ?? '10';
@endphp

<div class="page">

    <div class="accent-bar"></div>

    {{-- Header --}}
    <table class="hdr-table">
        <tr>
            <td class="hdr-logo">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="AL WAAB" />
                @endif
            </td>
            <td class="hdr-company">
                <div class="hdr-name">{{ $companyName }}</div>
                <div class="hdr-tag">{{ $settings['company_tagline'] ?? 'FlowGuard® CPVC Pipes & Fittings — DIN EN ISO 15877' }}</div>
                <div class="hdr-cert">Licensed Manufacturers under M/s Lubrizol Corporation, USA</div>
            </td>
            <td style="width:34%; padding-left:12px;">
                <div class="doc-badge">
                    <div class="type">Quotation</div>
                    <div class="ref">Ref: {{ $quotation->number }}</div>
                    <div class="date">Date: {{ $quotation->created_at->format('d/m/Y') }}</div>
                    @if($quotation->valid_until)
                    <div class="date">Valid Until: {{ $quotation->valid_until->format('d/m/Y') }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Bill To / Project --}}
    <table class="bill-project">
        <tr>
            <th>Bill To</th>
            <th>Project</th>
        </tr>
        <tr>
            <td>
                <div class="name">{{ $clientName ?: '—' }}</div>
                @if($clientCo)<div class="sub">{{ $clientCo }}</div>@endif
                @if($clientEmail || $clientPhone)
                <div class="sub">{{ $clientEmail }}@if($clientEmail && $clientPhone) · @endif{{ $clientPhone }}</div>
                @endif
            </td>
            <td>
                <div class="name">{{ $projectName ?: '—' }}</div>
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <table class="items">
        <thead>
            <tr>
                <th class="c-sn">S.No</th>
                <th class="c-code" style="text-align:left;">Part Code</th>
                <th class="c-desc" style="text-align:left;">Description</th>
                <th class="c-qty">Qty</th>
                <th class="c-unit">Unit</th>
                <th class="c-price">Unit Price<br><span style="font-weight:normal;font-size:6.5pt;">(AED)</span></th>
                <th class="c-total">Amount<br><span style="font-weight:normal;font-size:6.5pt;">(AED)</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $i => $item)
            <tr>
                <td class="c-sn">{{ $i + 1 }}</td>
                <td class="c-code">{{ $item->product?->sku ?? '—' }}</td>
                <td class="c-desc">{{ $item->description ?? $item->product?->name_en ?? '—' }}</td>
                <td class="c-qty">{{ (int) $item->quantity }}</td>
                <td class="c-unit">{{ $item->unit }}</td>
                <td class="c-price">{{ number_format($item->unit_price_aed, 2) }}</td>
                <td class="c-total">{{ number_format($item->total_aed, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Contact strip --}}
    <div class="contact-strip">
        {{ $companyName }}
        · {{ $settings['company_phone'] ?? '' }}
        · {{ $settings['company_email'] ?? '' }}
        · {{ $settings['company_website'] ?? '' }}
    </div>

    {{-- Totals --}}
    <table class="summary-wrap">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%;">
                <table class="totals-box">
                    <tr>
                        <td class="tl">Subtotal</td>
                        <td class="tv">AED {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($discount > 0)
                    <tr>
                        <td class="tl">Discount</td>
                        <td class="tv" style="color:#dc2626;">-AED {{ number_format($discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="tl">VAT (5%)</td>
                        <td class="tv">AED {{ number_format($vat, 2) }}</td>
                    </tr>
                    <tr class="grand">
                        <td class="tl" style="color:#fff;">Grand Total</td>
                        <td class="tv">AED {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Amount in words --}}
    <div class="words-box">
        <div class="wh">Amount Chargeable (in words) AED</div>
        {{ $amountWords }}
    </div>

    {{-- Terms --}}
    <div class="terms-box">
        <div class="th">Terms &amp; Conditions</div>
        <ul>
            <li>Payment Terms: {{ $settings['invoice_payment_terms'] ?? 'Full payment required upon order confirmation.' }}</li>
            <li>Make all cheques payable to: <strong>{{ $companyName }}</strong>.</li>
            <li>Delivery: 7–8 days from confirmed LPO date. 225mm pipes/fittings within 50 days.</li>
            <li>Returns/wrong orders will not be taken back. Transport charges apply for orders below AED 2,000.</li>
            <li>Warranty certificate is issued only after full payment for the project.</li>
            <li>Validity: {{ $validityDays }} Days from quotation date.</li>
        </ul>
    </div>

    {{-- Bank Details --}}
    <table class="bank-wrap">
        <tr class="bank-hdr">
            <td colspan="4">Bank Details</td>
        </tr>
        <tr>
            <td class="bk">Bank</td>
            <td style="width:28%;">{{ $settings['bank_name'] ?? '—' }}</td>
            <td class="bk">Contact No.</td>
            <td>{{ $settings['company_phone'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="bk">Account</td>
            <td>{{ $settings['bank_account_name'] ?? $companyName }}</td>
            <td class="bk">Email</td>
            <td>{{ $settings['company_email'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="bk">A/C No</td>
            <td style="font-family:monospace;">{{ $settings['bank_account_no'] ?? '—' }}</td>
            <td class="bk">Sales Person</td>
            <td>{{ $quotation->creator?->name_en ?? '—' }}</td>
        </tr>
        <tr>
            <td class="bk">IBAN</td>
            <td style="font-family:monospace; font-size:6.5pt;">{{ $settings['bank_iban'] ?? '—' }}</td>
            <td class="bk">TRN No.</td>
            <td>{{ $settings['company_trn'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="bk">Branch</td>
            <td>{{ ($settings['bank_branch'] ?? '') . ($settings['bank_city'] ? ', '.$settings['bank_city'] : '') ?: '—' }}</td>
            <td class="bk">P.O. Box</td>
            <td>{{ $settings['bank_po_box'] ?? '—' }}</td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <strong>Thank you for your business.</strong><br>
        {{ $companyName }}<br>
        Tel: {{ $settings['company_phone'] ?? '' }} &nbsp;|&nbsp;
        Landline: {{ $settings['company_landline'] ?? '' }} &nbsp;|&nbsp;
        {{ $settings['company_email'] ?? '' }} &nbsp;|&nbsp;
        {{ $settings['company_website'] ?? '' }}<br>
        {{ $settings['company_address'] ?? '' }}
    </div>

</div>
</body>
</html>
