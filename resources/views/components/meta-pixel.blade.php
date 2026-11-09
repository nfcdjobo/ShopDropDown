{{-- resources/views/components/meta-pixel.blade.php
     Initialise tous les pixels Meta configurés dans la bannière.
     fbq('track', ...) appelé ailleurs (AddToCart, Purchase…) part vers TOUS les pixels.
     Pour cibler un seul pixel : fbq('trackSingle', 'ID', 'Purchase', {...}) --}}
@php
    $metaPixelIds = collect($banniere?->meta_pixel_ids ?? [])
        ->map(fn ($id) => trim((string) $id))
        ->filter(fn ($id) => preg_match('/^\d{15,16}$/', $id))
        ->unique()
        ->values();
@endphp

@if ($metaPixelIds->isNotEmpty())
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');

        @foreach ($metaPixelIds as $pixelId)
        fbq('init', @json($pixelId));
        @endforeach
        fbq('track', 'PageView');
    </script>
    <noscript>
        @foreach ($metaPixelIds as $pixelId)
            <img height="1" width="1" style="display:none" alt=""
                 src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1">
        @endforeach
    </noscript>
@endif
