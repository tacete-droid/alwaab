<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice->number }}</title>
    <style>
        /* ── A4 page — generous white border around content ── */
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

        /* Brand palette — matches AL WAAB logo blue */
        .c-blue      { color: #0b5ed7; }
        .bg-blue     { background-color: #0b5ed7; color: #ffffff; }
        .bg-blue-lt  { background-color: #e8f2fc; }
        .bg-stripe   { background-color: #f4f7fb; }
        .border-blue { border-color: #0b5ed7; }
        .border-gray { border: 1px solid #c8d3e0; }

        /* ── Page wrapper — stays inside printable area ── */
        .page { width: 100%; max-width: 100%; }

        /* ── Top accent strip ── */
        .accent-bar { height: 3px; background: #0b5ed7; margin-bottom: 8px; }

        /* ── Header ── */
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
        .doc-badge .number { font-family: DejaVu Sans Mono, monospace; font-size: 9pt; font-weight: bold; color: #1e293b; margin-top: 2px; }
        .doc-badge .date   { font-size: 7.5pt; color: #5c6b7a; margin-top: 2px; }

        /* ── Section title ── */
        .sec-title {
            background: #0b5ed7;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            padding: 4px 8px;
            margin-top: 8px;
        }

        /* ── Client info card ── */
        .info-card { border: 1px solid #c8d3e0; margin-top: 0; }
        .info-card td { padding: 3px 8px; border-bottom: 1px solid #e8edf2; font-size: 8pt; }
        .info-card tr:last-child td { border-bottom: none; }
        .info-card .lbl { width: 28%; font-weight: bold; color: #0b5ed7; background: #f4f7fb; white-space: nowrap; }
        .info-card .val { color: #1e293b; }
        .info-card .lbl-r { width: 22%; font-weight: bold; color: #0b5ed7; background: #f4f7fb; }
        .client-banner td {
            background: #0b5ed7;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 4px 8px;
            letter-spacing: 0.4px;
        }

        /* ── Items table ── */
        .items { margin-top: 0; }
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
            font-size: 8pt;
            border-bottom: 1px solid #e8edf2;
            border-right: 1px solid #e8edf2;
        }
        .items tbody td:last-child { border-right: none; }
        .items tbody tr:nth-child(even) td { background: #f4f7fb; }
        .items .c-sn    { width: 5%;  text-align: center; color: #5c6b7a; }
        .items .c-desc  { width: 36%; text-align: left; }
        .items .c-enq   { width: 22%; text-align: left; color: #5c6b7a; }
        .items .c-qty   { width: 7%;  text-align: center; }
        .items .c-unit  { width: 8%;  text-align: center; }
        .items .c-price { width: 11%; text-align: right; font-family: DejaVu Sans Mono, monospace; }
        .items .c-total { width: 11%; text-align: right; font-family: DejaVu Sans Mono, monospace; font-weight: bold; color: #0b5ed7; }

        /* ── Summary block ── */
        .summary-wrap { margin-top: 8px; }
        .summary-wrap td { vertical-align: top; }
        .notes-box {
            border: 1px solid #c8d3e0;
            background: #f4f7fb;
            padding: 6px 8px;
            min-height: 50px;
            font-size: 7.5pt;
            color: #475569;
        }
        .notes-box .nt { font-weight: bold; color: #0b5ed7; font-size: 8pt; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.4px; }
        .totals-box { border: 1.5px solid #0b5ed7; }
        .totals-box td { padding: 4px 8px; font-size: 8pt; border-bottom: 1px solid #e8edf2; }
        .totals-box tr:last-child td { border-bottom: none; }
        .totals-box .tl { color: #5c6b7a; font-weight: bold; width: 55%; }
        .totals-box .tv { text-align: right; font-family: DejaVu Sans Mono, monospace; font-weight: bold; color: #1e293b; }
        .totals-box .grand td { background: #0b5ed7; color: #ffffff; font-size: 9pt; font-weight: bold; padding: 5px 8px; }
        .totals-box .grand .tv { color: #ffffff; font-size: 10pt; }

        /* ── Terms ── */
        .terms-box { border: 1px solid #c8d3e0; margin-top: 8px; }
        .terms-box td { padding: 5px 8px; font-size: 7pt; color: #475569; vertical-align: top; border-right: 1px solid #e8edf2; }
        .terms-box td:last-child { border-right: none; }
        .terms-box .th { font-weight: bold; color: #0b5ed7; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .words-box { background: #e8f2fc; font-style: italic; }

        /* ── Bank details ── */
        .bank-wrap { margin-top: 8px; border: 1px solid #c8d3e0; }
        .bank-hdr td { background: #0b5ed7; color: #fff; font-size: 7.5pt; font-weight: bold; padding: 4px 8px; letter-spacing: 0.4px; text-transform: uppercase; }
        .bank-wrap td { padding: 3px 8px; font-size: 7pt; border-bottom: 1px solid #e8edf2; border-right: 1px solid #e8edf2; }
        .bank-wrap tr:last-child td { border-bottom: none; }
        .bank-wrap .bk { font-weight: bold; color: #0b5ed7; background: #f4f7fb; width: 22%; }

        /* ── Footer ── */
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

        .spacer-6 { height: 6px; }
    </style>
</head>
<body>
@php
    use App\Support\NumberToWords;

    $typeLabel = match($invoice->type->value) {
        'quotation' => 'Quotation',
        'sales'     => 'Sales Invoice',
        'proforma'  => 'Proforma Invoice',
        default     => 'Quotation',
    };
    $clientName  = $invoice->client_name ?: ($invoice->contact?->name_en ?? $invoice->contact?->name_ar ?? '');
    $projectName = $invoice->project_name ?: ($invoice->project?->name_en ?? $invoice->project?->name_ar ?? '');
    $afterDiscount = max(0, $invoice->subtotal_aed - $invoice->discount_aed);
    $amountWords = NumberToWords::aed((float) $invoice->total_aed);
@endphp

<div class="page">

    <div class="accent-bar"></div>

    {{-- ══ HEADER ══ --}}
    <table class="hdr-table">
        <tr>
            <td class="hdr-logo">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="AL WAAB" />
                @endif
            </td>
            <td class="hdr-company">
                <div class="hdr-name">{{ $settings['company_name_en'] ?? 'AL WAAB BUILDING MATERIALS TRADING EST' }}</div>
                <div class="hdr-tag">{{ $settings['company_tagline'] ?? 'FlowGuard CPVC Pipes & Fittings — DIN EN ISO15877' }}</div>
                <div class="hdr-cert">FM Approved &nbsp;·&nbsp; NSF Certified &nbsp;·&nbsp; UL Listed</div>
            </td>
            <td style="width:34%; padding-left:12px;">
                <div class="doc-badge">
                    <div class="type">{{ $typeLabel }}</div>
                    <div class="number">{{ $invoice->number }}</div>
                    <div class="date">Date: {{ $invoice->document_date->format('d F Y') }}</div>
                    @if($invoice->valid_until)
                    <div class="date">Valid Until: {{ $invoice->valid_until->format('d F Y') }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="spacer-6"></div>

    {{-- ══ CLIENT BANNER ══ --}}
    <table class="client-banner">
        <tr>
            <td>CLIENT: {{ strtoupper($clientName) }}</td>
        </tr>
    </table>

    {{-- ══ CLIENT DETAILS (2-column) ══ --}}
    <table class="info-card">
        <tr>
            <td class="lbl">Quot Ref No.</td>
            <td class="val" style="font-weight:bold; font-family:monospace;">{{ $invoice->number }}</td>
            <td class="lbl-r">Date</td>
            <td class="val" style="font-weight:bold;">{{ $invoice->document_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Client Name</td>
            <td class="val" colspan="3">{{ $clientName ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Attention To</td>
            <td class="val">{{ $invoice->attention_to ?: '—' }}</td>
            <td class="lbl-r">Contact No.</td>
            <td class="val">{{ $invoice->phone ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Email Address</td>
            <td class="val">{{ $invoice->email ?: '—' }}</td>
            <td class="lbl-r">Order / LPO No.</td>
            <td class="val">{{ $invoice->lpo_number ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Project</td>
            <td class="val" colspan="3">{{ $projectName ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Consultant</td>
            <td class="val">{{ $invoice->consultant ?: '—' }}</td>
            <td class="lbl-r">Main Contractor</td>
            <td class="val">{{ $invoice->main_contractor ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">MEP Contractor</td>
            <td class="val">{{ $invoice->mep_contractor ?: '—' }}</td>
            <td class="lbl-r">Address</td>
            <td class="val">{{ $invoice->address ?: ($invoice->contact?->company ?? '—') }}</td>
        </tr>
    </table>

    {{-- ══ ITEMS ══ --}}
    <div class="sec-title">Items &amp; Description</div>
    <table class="items border-gray">
        <thead>
            <tr>
                <th class="c-sn">#</th>
                <th class="c-desc" style="text-align:left;">FlowGuard CPVC Materials</th>
                <th class="c-enq" style="text-align:left;">Client Enquiry</th>
                <th class="c-qty">Qty</th>
                <th class="c-unit">Unit</th>
                <th class="c-price">Unit Price<br><span style="font-weight:normal;font-size:7pt;">(AED)</span></th>
                <th class="c-total">Total<br><span style="font-weight:normal;font-size:7pt;">(AED)</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="c-sn">{{ $i + 1 }}</td>
                <td class="c-desc">{{ $item->description }}</td>
                <td class="c-enq">{{ $item->product?->sku ?? '—' }}</td>
                <td class="c-qty">{{ (int) $item->quantity }}</td>
                <td class="c-unit">{{ $item->unit }}</td>
                <td class="c-price">{{ number_format($item->unit_price_aed, 2) }}</td>
                <td class="c-total">{{ number_format($item->total_aed, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ══ NOTES + TOTALS ══ --}}
    <table class="summary-wrap">
        <tr>
            <td style="width:57%; padding-right:10px;">
                <div class="notes-box">
                    <div class="nt">Special Notes &amp; Instructions</div>
                    @if($invoice->notes){{ $invoice->notes }}<br><br>@endif
                    {{ $settings['invoice_delivery_terms'] ?? 'Delivery: 7–8 days from confirmed LPO date.' }}<br>
                    {{ $settings['invoice_validity_text'] ?? 'Validity: 10 Days' }}
                </div>
            </td>
            <td style="width:43%;">
                <table class="totals-box">
                    <tr>
                        <td class="tl">Subtotal</td>
                        <td class="tv">{{ number_format($invoice->subtotal_aed, 2) }}</td>
                    </tr>
                    @if($invoice->discount_aed > 0)
                    <tr>
                        <td class="tl">Discount</td>
                        <td class="tv" style="color:#dc2626;">-{{ number_format($invoice->discount_aed, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="tl">After Discount</td>
                        <td class="tv">{{ number_format($afterDiscount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="tl">VAT (5%)</td>
                        <td class="tv">{{ number_format($invoice->vat_aed, 2) }}</td>
                    </tr>
                    <tr class="grand">
                        <td class="tl" style="color:#fff;">GRAND TOTAL (AED)</td>
                        <td class="tv">{{ number_format($invoice->total_aed, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══ TERMS + AMOUNT IN WORDS ══ --}}
    <table class="terms-box">
        <tr>
            <td style="width:52%;">
                <div class="th">Terms &amp; Conditions</div>
                {{ $settings['invoice_payment_terms'] ?? 'Full payment required upon order confirmation.' }}<br>
                {{ $settings['invoice_delivery_terms'] ?? '' }}
            </td>
            <td class="words-box" style="width:48%;">
                <div class="th">Amount in Words (AED)</div>
                {{ $amountWords }}
            </td>
        </tr>
    </table>

    {{-- ══ BANK DETAILS ══ --}}
    <table class="bank-wrap">
        <tr class="bank-hdr">
            <td colspan="4">Bank Details &amp; Contact Information</td>
        </tr>
        <tr>
            <td class="bk">Bank Name</td>
            <td style="width:28%;">{{ $settings['bank_name'] ?? '—' }}</td>
            <td class="bk">Contact No.</td>
            <td>{{ $settings['company_phone'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="bk">Account Name</td>
            <td>{{ $settings['bank_account_name'] ?? $settings['company_name_en'] }}</td>
            <td class="bk">Email</td>
            <td>{{ $settings['company_email'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="bk">Account No.</td>
            <td style="font-family:monospace;">{{ $settings['bank_account_no'] ?? '—' }}</td>
            <td class="bk">Sales Person</td>
            <td>{{ $invoice->creator?->name_en ?? '—' }}</td>
        </tr>
        <tr>
            <td class="bk">IBAN</td>
            <td style="font-family:monospace; font-size:7pt;">{{ $settings['bank_iban'] ?? '—' }}</td>
            <td class="bk">TRN No.</td>
            <td>{{ $settings['company_trn'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="bk">Branch / City</td>
            <td>{{ ($settings['bank_branch'] ?? '') . ($settings['bank_city'] ? ', '.$settings['bank_city'] : '') ?: '—' }}</td>
            <td class="bk">P.O. Box</td>
            <td>{{ $settings['bank_po_box'] ?? '—' }}</td>
        </tr>
    </table>

    {{-- ══ FOOTER ══ --}}
    <div class="footer">
        <strong>{{ $settings['company_name_en'] ?? 'AL WAAB BUILDING MATERIALS TRADING EST' }}</strong><br>
        {{ $settings['invoice_footer_note'] ?? 'Thank you for your business!' }}<br>
        Tel: {{ $settings['company_phone'] ?? '' }} &nbsp;|&nbsp;
        Landline: {{ $settings['company_landline'] ?? '' }} &nbsp;|&nbsp;
        {{ $settings['company_email'] ?? '' }} &nbsp;|&nbsp;
        {{ $settings['company_website'] ?? '' }}<br>
        {{ $settings['company_address'] ?? '' }}
    </div>

</div>
</body>
</html>
