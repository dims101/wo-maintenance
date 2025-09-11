<x-mail::message>
# Halo, {{ $name }}

Anda mendapatkan permintaan approval untuk **Maintenance Work Order**.  
Silakan klik tombol di bawah untuk memeriksa dan menyetujui:

<x-mail::button :url="$link">
Review & Approve
</x-mail::button>

Jika tombol tidak bekerja, buka link berikut di browser:  
{{ $link }}

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
