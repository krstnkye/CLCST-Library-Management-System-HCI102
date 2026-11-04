<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
<link rel="stylesheet" href="{{ asset('css/modern.css') }}?v={{ filemtime(public_path('css/modern.css')) }}">
@include('partials.luxe-theme')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="{{ asset('js/ui-motion.js') }}?v={{ filemtime(public_path('js/ui-motion.js')) }}" defer></script>
<script src="{{ asset('js/ui-interactions.js') }}?v={{ filemtime(public_path('js/ui-interactions.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset('css/ui-actions.css') }}?v={{ filemtime(public_path('css/ui-actions.css')) }}">
<script src="{{ asset('js/ui-actions.js') }}?v={{ filemtime(public_path('js/ui-actions.js')) }}" defer></script>
