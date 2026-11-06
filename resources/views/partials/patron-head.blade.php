<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.favicon')
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ filemtime(public_path('css/fonts.css')) }}">
<link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ filemtime(public_path('css/tailwind.css')) }}">
<style>
    body { font-family: Inter, sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; line-height: 1; }
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c0c9bd; border-radius: 9999px; }
</style>
@include('partials.ui-motion')
