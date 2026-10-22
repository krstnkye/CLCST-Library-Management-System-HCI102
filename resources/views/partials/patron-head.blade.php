<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.favicon')
<link href="https://fonts.googleapis.com" rel="preconnect">
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#00441d', 'primary-container': '#135d2e', 'primary-fixed': '#aaf4b5', 'on-primary': '#ffffff',
                    secondary: '#785a00', 'secondary-container': '#fdc425', 'on-secondary-container': '#6d5200', 'secondary-fixed': '#ffdf9a',
                    tertiary: '#002e97', 'tertiary-fixed': '#dce1ff',
                    background: '#faf8ff', surface: '#faf8ff', 'surface-container-lowest': '#ffffff', 'surface-container-low': '#f2f3ff',
                    'surface-container': '#eaedff', 'surface-container-high': '#e2e7ff', 'on-surface': '#131b2e', 'on-surface-variant': '#404940',
                    outline: '#707a6f', 'outline-variant': '#c0c9bd', error: '#ba1a1a', 'error-container': '#ffdad6',
                },
                fontFamily: {
                    headline: ['Plus Jakarta Sans', 'sans-serif'],
                    body: ['Inter', 'sans-serif'],
                },
            },
        },
    };
</script>
<style>
    body { font-family: Inter, sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; line-height: 1; }
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c0c9bd; border-radius: 9999px; }
</style>
@include('partials.ui-motion')
