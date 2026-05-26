<x-mail::message>
# Purchase Approval Status Updated

The status of your purchase order has been updated.

**Purchase Number:** {{ $purchase->purchase_number }}
**Status:** {{ $purchase->status }}
**Total Amount:** {{ number_format($purchase->total_amount, 2) }}

<x-mail::button :url="config('app.url') . '/purchases/' . $purchase->id">
View Purchase
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
