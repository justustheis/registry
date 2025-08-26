<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script>
            window.route = function(name, params) {
                const routes = {
                    'registry.index': '/registry',
                    'registry.store': '/registry',
                    'registry.update': '/registry/{entry}',
                    'registry.destroy': '/registry/{entry}',
                    'registry.rename': '/registry/{entry}/rename'
                };
                let url = routes[name] || '/registry';
                if (params) {
                    if (typeof params === 'object') {
                        Object.keys(params).forEach(key => {
                            const before = url;
                            url = url.replace('{' + key + '}', params[key]);
                        });
                    } else {
                        const before = url;
                        url = url.replace('{entry}', params);
                    }
                }
                return url;
            };
        </script>

        @php
            $manifest = json_decode(file_get_contents(public_path('vendor/justustheis/registry/manifest.json')), true);
            $jsFile = $manifest['resources/js/registry.js']['file'] ?? null;
            $cssFiles = [];
            if (isset($manifest['resources/js/registry.js']['css'])) {
                $cssFiles = $manifest['resources/js/registry.js']['css'];
            }
        @endphp

        @if($jsFile)
            @foreach($cssFiles as $cssFile)
                <link rel="stylesheet" href="{{ asset('vendor/justustheis/registry/' . $cssFile) }}">
            @endforeach
            <script type="module" src="{{ asset('vendor/justustheis/registry/' . $jsFile) }}"></script>
        @endif

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
