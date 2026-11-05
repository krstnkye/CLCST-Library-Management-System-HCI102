<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
<link rel="stylesheet" href="{{ asset('css/modern.css') }}?v={{ filemtime(public_path('css/modern.css')) }}">
{{-- Apply the remembered sidebar state before the page paints, so it does not jump. --}}
<script>try { if (localStorage.getItem('clcst-sidebar') === 'collapsed') document.documentElement.classList.add('sidebar-collapsed'); } catch (_) {}</script>
<script src="{{ asset('js/sidebar.js') }}?v={{ filemtime(public_path('js/sidebar.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ filemtime(public_path('css/fonts.css')) }}">
<script src="{{ asset('js/ui-motion.js') }}?v={{ filemtime(public_path('js/ui-motion.js')) }}" defer></script>
<script src="{{ asset('js/ui-interactions.js') }}?v={{ filemtime(public_path('js/ui-interactions.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset('css/ui-actions.css') }}?v={{ filemtime(public_path('css/ui-actions.css')) }}">
<script src="{{ asset('js/ui-actions.js') }}?v={{ filemtime(public_path('js/ui-actions.js')) }}" defer></script>
