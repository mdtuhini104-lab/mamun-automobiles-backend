<x-mail::message>
# Low Stock Alert

The following part is low on stock:

**Name:** {{ $part->name }}
**SKU:** {{ $part->sku }}
**Current Stock:** {{ $part->stock_quantity }}
**Threshold:** {{ $part->low_stock_threshold }}

Please reorder this part as soon as possible.

<x-mail::button :url="config('app.url') . '/parts/' . $part->id">
View Part
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
