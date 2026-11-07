<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'ShipFast'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script>
        const preferences = @json(auth()->user()?->preferences ?? []);
        const applyTheme = () => { document.documentElement.dataset.theme = preferences.theme==='dark'||((preferences.theme??'system')==='system'&&matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light'; };
        applyTheme(); matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);
        document.documentElement.dataset.density=preferences.density??'comfortable';
        document.documentElement.dataset.reduceMotion=preferences.reduce_motion?'true':'false';
    </script>
    @vite(['resources/css/app.css', 'resources/css/identity.css', 'resources/js/app.js'])
</head>
<body class="min-h-full">
    @yield('body')
    @stack('scripts')
</body>
</html>
