{{-- Shared page for HTTP errors (403, 404, 419, 429, 500, 503) in the library's house style. --}}
@php
    $user = auth()->user();
    $home = $user ? route($user->dashboardRouteName()) : route('login');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('code') · @yield('title') | CLCST Library</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#1f1d1a; --muted:#6b665c; --paper:#f6f3ec; --card:#fffdf9; --line:#e3ddd1; --green:#1f3c27; --green-2:#15231a; --gold:#a8843f; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.1fr); background:var(--paper); color:var(--ink); font:16px/1.6 Inter, system-ui, sans-serif; }
        .side { background:var(--green-2); color:#f6f3ec; padding:48px; display:flex; flex-direction:column; justify-content:space-between; }
        .brand { display:flex; align-items:center; gap:12px; font-family:'Cormorant Garamond', Georgia, serif; font-size:20px; }
        .brand img { width:44px; height:44px; border-radius:50%; background:#fff; }
        .code { font-family:'Cormorant Garamond', Georgia, serif; font-size:clamp(96px, 16vw, 180px); line-height:1; color:#d8bc7c; font-variant-numeric:lining-nums; }
        .side small { color:#b9b3a6; letter-spacing:.14em; text-transform:uppercase; font-size:11px; }
        main { display:flex; align-items:center; padding:48px; }
        .card { max-width:520px; }
        .eyebrow { display:flex; align-items:center; gap:10px; color:var(--gold); font-size:11px; font-weight:600; letter-spacing:.16em; text-transform:uppercase; }
        .eyebrow::before { content:''; width:28px; height:1px; background:var(--gold); }
        h1 { font-family:'Cormorant Garamond', Georgia, serif; font-weight:600; font-size:clamp(34px, 4vw, 48px); line-height:1.1; margin:14px 0 12px; }
        p { color:var(--muted); margin:0 0 10px; }
        .who { margin-top:18px; padding:12px 14px; border:1px solid var(--line); border-left:3px solid var(--gold); background:var(--card); border-radius:6px; font-size:14px; color:var(--ink); }
        .actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:26px; }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:11px 18px; border-radius:6px; font-weight:600; font-size:14px; text-decoration:none; border:1px solid var(--green); cursor:pointer; font-family:inherit; }
        .btn-primary { background:var(--green); color:#fff; }
        .btn-ghost { background:transparent; color:var(--green); }
        .btn:hover { filter:brightness(1.08); }
        form { margin:0; }
        @media (max-width:760px) { body { grid-template-columns:1fr; } .side { padding:28px 20px; gap:24px; } .code { font-size:96px; } main { padding:28px 20px 40px; } }
    </style>
</head>
<body>
    <aside class="side">
        <div class="brand"><img src="{{ asset('images/celtech-logo.png') }}" alt="">CLCST School Library</div>
        <div><div class="code">@yield('code')</div><small>Error code @yield('code')</small></div>
        <small>Central Luzon College of Science and Technology</small>
    </aside>
    <main>
        <div class="card">
            <span class="eyebrow">@yield('eyebrow')</span>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            @if ($user)
                <div class="who">You are signed in as <strong>{{ $user->name }}</strong> ({{ $user->roleLabel() }}). This page is outside what your account can open.</div>
            @endif
            <div class="actions">
                <a class="btn btn-primary" href="{{ $home }}">{{ $user ? 'Go to my dashboard' : 'Go to sign in' }}</a>
                @if ($user)
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-ghost" type="submit">Sign out and use another account</button></form>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
