<x-mail::message>
# Quotation {{ $quotation->number }}

Dear {{ $contactName }},

Thank you for your enquiry with **Al Waab Building Materials Trading EST**.

Please find attached our quotation **{{ $quotation->number }}** for your review.

@if($quotation->valid_until)
**Valid until:** {{ $quotation->valid_until->format('d M Y') }}
@endif

If you have any questions or would like to proceed, please reply to this email or contact us:

- Phone: +971 4 251 4228 / +971 54 769 6440
- Email: info@alwaab.ae

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
