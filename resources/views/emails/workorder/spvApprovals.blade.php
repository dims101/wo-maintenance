<x-mail::message>
# Dear, {{ $name }}

Ada request yang butuh review dari Anda.  
Silakan klik tombol di bawah untuk melakukan approval:

<x-mail::button :url="$link">
Review Sekarang
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
