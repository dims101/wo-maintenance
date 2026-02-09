@component('mail::message')
    # {{ $sapaan }} {{ $nama }},

    {{ $isi }}
    {{ $reason }}
    @if (!empty($link))
        @component('mail::button', ['url' => $link])
            Lihat Tautan
        @endcomponent
    @endif

    {{ $penutup }}
    **{{ config('app.name') }}**
@endcomponent
