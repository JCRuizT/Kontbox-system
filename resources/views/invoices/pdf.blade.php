<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>{{ __('ui.invoice_pdf.title', ['number' => $invoice->invoice_number]) }}</title>
<style>
    body { font-family: sans-serif; font-size: 12px; padding: 40px; }
    h1 { font-size: 24px; margin-bottom: 5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f3f4f6; }
    .total { font-size: 18px; font-weight: bold; margin-top: 20px; text-align: right; }
</style>
</head>
<body>
    <h1>Kontbox</h1>
    <p>{{ __('ui.invoice_pdf.subtitle') }}</p>
    <hr>
    <h2>{{ __('ui.invoice_pdf.title', ['number' => $invoice->invoice_number]) }}</h2>
    <p><strong>{{ __('ui.invoice_pdf.emission_date') }}</strong> {{ $invoice->issued_date->format('d/m/Y') }}</p>
    <p><strong>{{ __('ui.invoice_pdf.client') }}</strong> {{ $invoice->contract->quotation->prospect->company_name ?? __('ui.common.na') }}</p>
    <p><strong>{{ __('ui.invoice_pdf.contract') }}</strong> {{ $invoice->contract->contract_number ?? __('ui.common.na') }}</p>
    <p><strong>{{ __('ui.invoice_pdf.status') }}</strong> {{ ucfirst($invoice->status) }}</p>
    @if($invoice->notes)
    <p><strong>{{ __('ui.invoice_pdf.notes') }}</strong> {{ $invoice->notes }}</p>
    @endif
    <div class="total">{{ __('ui.invoice_pdf.total') }} ${{ number_format($invoice->amount, 2) }}</div>
    <hr>
    <p style="text-align:center;color:#999;font-size:10px;margin-top:40px;">
        {!! __('ui.invoice_pdf.fiscal_disclaimer') !!}
    </p>
</body>
</html>
