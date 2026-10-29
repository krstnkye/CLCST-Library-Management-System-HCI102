<!DOCTYPE html>
<html class="h-full bg-[#f7f9f7]" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account - CLCST Library Management System</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { clcst: { 50:'#eef8f1',100:'#d9efe0',600:'#146b37',700:'#0d572c',800:'#084522',900:'#043719' }, gold:'#f5bd22' },
            fontFamily: { sans: ['Inter','sans-serif'] }
        } } };
    </script>
    <style>
        .material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24}
        input[type=radio]:focus-visible+.role-card{outline:3px solid rgba(20,107,55,.25);outline-offset:2px}
        input[type=radio]:checked+.role-card{border-color:#146b37;background:#eef8f1;box-shadow:0 0 0 1px #146b37}
        input[type=radio]:checked+.role-card .role-icon{background:#146b37;color:#fff}
        input[type=radio]:checked+.role-card .role-check{display:inline-flex}
    </style>
    @include('partials.ui-motion')
</head>
<body class="clcst-portal clcst-auth min-h-full bg-[#f7f9f7] font-sans text-slate-900 antialiased">
<header class="clcst-topbar sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8">
        <a class="flex min-w-0 items-center gap-3" href="{{ route('login') }}" aria-label="CLCST Library sign in">
            @include('partials.celtech-logo', ['class'=>'h-12 w-12 flex-none rounded-full object-contain'])
            <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><span class="text-base font-extrabold text-clcst-800">CLCST Library Portal</span><span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900">EST. 1959</span></div><p class="truncate text-xs font-medium text-slate-500">Central Luzon College of Science and Technology</p></div>
        </a>
        <div class="flex items-center gap-3">
            <div class="hidden items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 md:flex"><span class="material-symbols-outlined text-base text-clcst-700">verified_user</span>Data Privacy Protected</div>
            <a class="inline-flex h-10 items-center gap-2 whitespace-nowrap rounded-md bg-clcst-800 px-4 text-sm font-bold text-white hover:bg-clcst-700 focus:outline-none focus:ring-2 focus:ring-clcst-600 focus:ring-offset-2" href="{{ route('login') }}">Sign In<span class="material-symbols-outlined text-lg">login</span></a>
        </div>
    </div>
</header>

<main class="mx-auto w-full max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
    <section class="registration-intro">
        <div class="relative z-10 max-w-3xl">
            <div class="registration-label"><span class="material-symbols-outlined mr-1 text-sm">person_add</span>Library Account Registration</div>
            <h1 class="text-2xl font-extrabold sm:text-3xl">Create your CLCST library account</h1>
            <p>Enter your official school information and upload one supporting document. Authorized library personnel will review your application before access is activated.</p>
        </div>
    </section>

    @if($errors->any())
        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            <div class="flex items-start gap-3"><span class="material-symbols-outlined mt-0.5 text-lg">error</span><div><p class="font-bold">Please review the highlighted information.</p><ul class="mt-1 list-disc space-y-0.5 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
        </div>
    @endif

    <form class="mt-6 grid items-start gap-6 lg:grid-cols-12" method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="space-y-6 lg:col-span-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col justify-between gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center">
                    <div><div class="flex items-center gap-2"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-clcst-700 text-sm font-bold text-white">1</span><h2 class="text-lg font-extrabold text-clcst-900">Choose your account type</h2></div><p class="ml-9 mt-1 text-xs text-slate-500">Select the role shown in your official school record.</p></div>
                    <span class="ml-9 text-xs font-semibold text-slate-500 sm:ml-0">Approval is required</span>
                </div>
                <fieldset class="mt-5">
                    <legend class="sr-only">Account type</legend>
                    @php
                        $selectedRole=old('patron_role','student');
                        $roles=[
                            'student'=>['Student','school','Student ID'],
                            'faculty'=>['Faculty / Staff','co_present','Employee ID'],
                            'librarian'=>['Librarian','local_library','Library staff ID'],
                        ];
                    @endphp
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach($roles as $roleValue=>[$roleLabel,$roleIcon,$roleHint])
                            <label class="cursor-pointer">
                                <input class="sr-only" type="radio" name="patron_role" value="{{ $roleValue }}" @checked($selectedRole===$roleValue) required>
                                <span class="role-card flex min-h-32 flex-col rounded-lg border border-slate-300 bg-white p-3 transition hover:border-clcst-600">
                                    <span class="flex items-start justify-between gap-2"><span class="role-icon flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-clcst-800"><span class="material-symbols-outlined text-xl">{{ $roleIcon }}</span></span><span class="role-check hidden h-5 w-5 items-center justify-center rounded-full bg-clcst-700 text-white"><span class="material-symbols-outlined text-sm">check</span></span></span>
                                    <span class="mt-3 text-sm font-bold">{{ $roleLabel }}</span><span class="mt-1 text-[11px] leading-4 text-slate-500">{{ $roleHint }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('patron_role')<p class="mt-2 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                    <p class="mt-3 flex items-start gap-2 rounded-md bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-900"><span class="material-symbols-outlined mt-0.5 text-base">info</span>Librarian applications are reviewed by a library administrator. Administrator accounts are created by the system administrator and cannot be requested here.</p>
                </fieldset>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-2 border-b border-slate-200 pb-4"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-clcst-700 text-sm font-bold text-white">2</span><div><h2 class="text-lg font-extrabold text-clcst-900">Personal and school information</h2><p class="mt-1 text-xs text-slate-500">Use the same details shown on your school ID or employment record.</p></div></div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1.5 block text-sm font-bold" for="first_name">First name <span class="text-red-600">*</span></label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required maxlength="100" autocomplete="given-name" class="w-full rounded-md border-slate-300 text-sm focus:border-clcst-600 focus:ring-clcst-600 @error('first_name') border-red-500 @enderror">@error('first_name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-1.5 block text-sm font-bold" for="last_name">Last name <span class="text-red-600">*</span></label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required maxlength="100" autocomplete="family-name" class="w-full rounded-md border-slate-300 text-sm focus:border-clcst-600 focus:ring-clcst-600 @error('last_name') border-red-500 @enderror">@error('last_name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-1.5 block text-sm font-bold" for="middle_initial">Middle initial <span class="font-normal text-slate-500">(optional)</span></label><input id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="5" class="w-full rounded-md border-slate-300 text-sm focus:border-clcst-600 focus:ring-clcst-600 @error('middle_initial') border-red-500 @enderror">@error('middle_initial')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-1.5 block text-sm font-bold" for="patron_id">School or employee ID <span class="text-red-600">*</span></label><div class="relative"><span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><span class="material-symbols-outlined text-lg">badge</span></span><input id="patron_id" name="patron_id" value="{{ old('patron_id') }}" required maxlength="100" placeholder="Example: 2024-10492-OL" class="w-full rounded-md border-slate-300 pl-10 text-sm focus:border-clcst-600 focus:ring-clcst-600 @error('patron_id') border-red-500 @enderror"></div>@error('patron_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-1.5 block text-sm font-bold" for="program_or_department">Program or department <span class="text-red-600">*</span></label><input id="program_or_department" name="program_or_department" value="{{ old('program_or_department') }}" required maxlength="150" placeholder="Example: BS Computer Science" class="w-full rounded-md border-slate-300 text-sm focus:border-clcst-600 focus:ring-clcst-600 @error('program_or_department') border-red-500 @enderror">@error('program_or_department')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-1.5 block text-sm font-bold" for="mobile_number">Mobile number <span class="text-red-600">*</span></label><input id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}" required maxlength="13" inputmode="tel" autocomplete="tel" pattern="^(09|\+639)\d{9}$" title="Philippine mobile number, for example 09171234567" placeholder="Example: 09171234567" class="w-full rounded-md border-slate-300 text-sm focus:border-clcst-600 focus:ring-clcst-600 @error('mobile_number') border-red-500 @enderror">@error('mobile_number')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-bold" for="email">School email address <span class="text-red-600">*</span></label><div class="relative"><span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><span class="material-symbols-outlined text-lg">mail</span></span><input id="email" name="email" value="{{ old('email') }}" required type="email" maxlength="150" autocomplete="email" pattern=".+@clcst\.edu\.ph$" title="Use your school email ending in @clcst.edu.ph" placeholder="juan.delacruz@clcst.edu.ph" aria-describedby="email-help" class="w-full rounded-md border-slate-300 pl-10 text-sm focus:border-clcst-600 focus:ring-clcst-600 @error('email') border-red-500 @enderror"></div><p id="email-help" class="mt-1 text-xs text-slate-500">Only school-registered emails ending in <strong>@clcst.edu.ph</strong> are accepted.</p>@error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-2 border-b border-slate-200 pb-4"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-clcst-700 text-sm font-bold text-white">3</span><div><h2 class="text-lg font-extrabold text-clcst-900">Security and verification</h2><p class="mt-1 text-xs text-slate-500">Create a strong password and attach proof of your CLCST affiliation.</p></div></div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1.5 block text-sm font-bold" for="password">Password <span class="text-red-600">*</span></label><div class="relative"><input id="password" name="password" required type="password" autocomplete="new-password" class="w-full rounded-md border-slate-300 pr-11 text-sm focus:border-clcst-600 focus:ring-clcst-600 @error('password') border-red-500 @enderror"><button class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-500 hover:text-clcst-700" type="button" data-password-toggle="password" aria-label="Show password"><span class="material-symbols-outlined text-lg">visibility</span></button></div><p class="mt-1 text-xs leading-5 text-slate-500">At least 10 characters with uppercase, lowercase, and a number.</p>@error('password')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div>
                        <label class="mb-1.5 block text-sm font-bold" for="password_confirmation">Confirm password <span class="text-red-600">*</span></label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" required type="password" autocomplete="new-password" aria-describedby="password-match-message" class="w-full rounded-md border-slate-300 pr-11 text-sm focus:border-clcst-600 focus:ring-clcst-600">
                            <button class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-500 hover:text-clcst-700" type="button" data-password-toggle="password_confirmation" aria-label="Show password confirmation"><span class="material-symbols-outlined text-lg">visibility</span></button>
                        </div>
                        <p id="password-match-message" class="mt-1 hidden items-center gap-1 text-xs font-semibold" aria-live="polite">
                            <span class="material-symbols-outlined text-sm" aria-hidden="true"></span>
                            <span data-password-match-text></span>
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-bold" for="credential_file">Supporting school document <span class="text-red-600">*</span></label>
                        <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center transition hover:border-clcst-600 hover:bg-clcst-50" for="credential_file"><span class="flex h-11 w-11 items-center justify-center rounded-full bg-clcst-100 text-clcst-800"><span class="material-symbols-outlined">upload_file</span></span><span class="mt-3 text-sm font-bold text-slate-800">Choose your COR, school ID, or employee ID</span><span class="mt-1 text-xs text-slate-500">PDF, JPG, or PNG. Maximum file size: 10 MB.</span><span id="file-name" class="mt-2 hidden rounded bg-white px-3 py-1 text-xs font-semibold text-clcst-800"></span></label>
                        <input id="credential_file" name="credential_file" required type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                        @error('credential_file')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <label class="flex cursor-pointer items-start gap-3 text-sm leading-6"><input class="mt-1 rounded border-slate-300 text-clcst-700 focus:ring-clcst-600" name="privacy_consent" required type="checkbox" value="1" @checked(old('privacy_consent'))><span>I confirm that the information is accurate and consent to its use for account verification and library services under the Data Privacy Act. <span class="text-red-600">*</span></span></label>
                @error('privacy_consent')<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
                <div class="mt-5 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end"><a class="inline-flex h-11 items-center justify-center rounded-md border border-slate-300 px-5 text-sm font-bold text-slate-700 hover:bg-slate-50" href="{{ route('login') }}">Cancel</a><button class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-clcst-800 px-5 text-sm font-bold text-white shadow-sm hover:bg-clcst-700 focus:outline-none focus:ring-2 focus:ring-clcst-600 focus:ring-offset-2" type="submit"><span class="material-symbols-outlined text-lg">how_to_reg</span>Submit Registration</button></div>
            </section>
        </div>

        <aside class="space-y-5 lg:sticky lg:top-24 lg:col-span-4">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-200 pb-3"><span class="material-symbols-outlined text-clcst-700">fact_check</span><h2 class="font-extrabold text-clcst-900">What happens next?</h2></div>
                <ol class="mt-4 space-y-4">
                    <li class="flex gap-3"><span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-clcst-700 text-xs font-bold text-white">1</span><div><p class="text-sm font-bold">Submit your application</p><p class="mt-0.5 text-xs leading-5 text-slate-500">Your information is stored securely for review.</p></div></li>
                    <li class="flex gap-3"><span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-clcst-700 text-xs font-bold text-white">2</span><div><p class="text-sm font-bold">Library staff verify your record</p><p class="mt-0.5 text-xs leading-5 text-slate-500">Your ID and document are checked against school records.</p></div></li>
                    <li class="flex gap-3"><span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-gold text-xs font-bold text-amber-950">3</span><div><p class="text-sm font-bold">Sign in after approval</p><p class="mt-0.5 text-xs leading-5 text-slate-500">Pending accounts cannot access a dashboard until approved.</p></div></li>
                </ol>
            </section>
            <section class="rounded-lg border border-emerald-200 bg-clcst-50 p-5"><div class="flex items-start gap-3"><span class="flex h-9 w-9 flex-none items-center justify-center rounded-md bg-white text-clcst-700"><span class="material-symbols-outlined">lock</span></span><div><h2 class="text-sm font-extrabold text-clcst-900">Your account is protected</h2><p class="mt-1 text-xs leading-5 text-slate-600">Passwords are securely hashed. Verification files are stored outside the public website directory.</p></div></div></section>
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-clcst-700">support_agent</span><h2 class="font-extrabold text-clcst-900">Need help?</h2></div><p class="mt-2 text-xs leading-5 text-slate-600">Contact the CLCST Library if your school ID is not recognized or you are unsure which account type to choose.</p><div class="mt-4 space-y-2 text-xs font-semibold text-slate-700"><p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-clcst-700">mail</span>library@clcst.edu.ph</p><p class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-clcst-700">location_on</span>Main Library, CLCST Campus</p></div></section>
            <p class="text-center text-xs text-slate-500">Already registered? <a class="font-bold text-clcst-700 hover:underline" href="{{ route('login') }}">Sign in to your account</a></p>
        </aside>
    </form>
</main>

<footer class="mt-4 border-t border-slate-200 bg-white"><div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8"><p>CLCST Library Management System - HCI102 Project</p><p>Account verification and access control portal</p></div></footer>
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button)=>{
        button.addEventListener('click',()=>{
            const input=document.getElementById(button.dataset.passwordToggle);
            const hidden=input.type==='password';
            input.type=hidden?'text':'password';
            button.querySelector('.material-symbols-outlined').textContent=hidden?'visibility_off':'visibility';
            button.setAttribute('aria-label',hidden?'Hide password':'Show password');
        });
    });

    const passwordInput=document.getElementById('password');
    const passwordConfirmation=document.getElementById('password_confirmation');
    const passwordMatchMessage=document.getElementById('password-match-message');
    const passwordMatchIcon=passwordMatchMessage.querySelector('.material-symbols-outlined');
    const passwordMatchText=passwordMatchMessage.querySelector('[data-password-match-text]');

    const updatePasswordMatch=()=>{
        const confirmationStarted=passwordConfirmation.value.length>0;
        const passwordsMatch=passwordInput.value===passwordConfirmation.value;

        passwordMatchMessage.classList.toggle('hidden',!confirmationStarted);
        passwordMatchMessage.classList.toggle('flex',confirmationStarted);
        passwordConfirmation.setCustomValidity(confirmationStarted&&!passwordsMatch?'Passwords do not match.':'');
        passwordConfirmation.setAttribute('aria-invalid',confirmationStarted&&!passwordsMatch?'true':'false');
        passwordConfirmation.classList.toggle('border-red-500',confirmationStarted&&!passwordsMatch);
        passwordConfirmation.classList.toggle('focus:border-red-500',confirmationStarted&&!passwordsMatch);
        passwordConfirmation.classList.toggle('focus:ring-red-500',confirmationStarted&&!passwordsMatch);
        passwordConfirmation.classList.toggle('border-emerald-600',confirmationStarted&&passwordsMatch);
        passwordMatchMessage.classList.toggle('text-red-700',confirmationStarted&&!passwordsMatch);
        passwordMatchMessage.classList.toggle('text-emerald-700',confirmationStarted&&passwordsMatch);

        if(!confirmationStarted){
            passwordMatchIcon.textContent='';
            passwordMatchText.textContent='';
        }else if(passwordsMatch){
            passwordMatchIcon.textContent='check_circle';
            passwordMatchText.textContent='Passwords match.';
        }else{
            passwordMatchIcon.textContent='error';
            passwordMatchText.textContent='Passwords do not match.';
        }
    };

    passwordInput.addEventListener('input',updatePasswordMatch);
    passwordConfirmation.addEventListener('input',updatePasswordMatch);

    const credentialInput=document.getElementById('credential_file');
    const fileName=document.getElementById('file-name');
    credentialInput.addEventListener('change',()=>{
        const selectedFile=credentialInput.files[0];
        fileName.textContent=selectedFile?selectedFile.name:'';
        fileName.classList.toggle('hidden',!selectedFile);
    });
</script>
</body>
</html>
