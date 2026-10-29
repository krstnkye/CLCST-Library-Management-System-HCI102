<!DOCTYPE html>
<html class="h-full" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create your CLCST library account</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config={theme:{extend:{colors:{clcst:{50:'#eef8f1',100:'#d9efe0',600:'#146b37',700:'#0d572c',800:'#084522',900:'#043719'}},fontFamily:{sans:['Inter','sans-serif']}}}};
    </script>
    <style>
        .material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 450,'GRAD' 0,'opsz' 24}
        input[type=radio]:focus-visible+.role-card{outline:3px solid rgba(20,107,55,.25);outline-offset:2px}
        input[type=radio]:checked+.role-card{border-color:#315e3f;background:#e5eedf;box-shadow:inset 0 -2px #315e3f}
        input[type=radio]:checked+.role-card .role-icon{background:#165740;color:#fff}
        input[type=radio]:checked+.role-card .role-check{display:inline-flex}
    </style>
    @include('partials.ui-motion')
</head>
<body class="clcst-portal library-login registration-page font-sans text-slate-900 antialiased">
<div class="login-shell registration-shell">
    <aside class="login-story registration-story" aria-label="CLCST School Library">
        <div class="login-aurora" aria-hidden="true"><span></span><span></span><span></span></div>
        <a class="login-brand" href="{{ route('login') }}">
            @include('partials.celtech-logo', ['class'=>'rounded-full'])
            <div><strong>CLCST School Library</strong><span>Central Luzon College of Science and Technology</span></div>
        </a>
        <div class="login-story-copy">
            <p class="login-eyebrow">Your place in the library</p>
            <h2>Begin your<br>next <em>chapter.</em></h2>
            <p>Create one account for the catalog, borrowing, reservations, and the library services available to your role.</p>
            <div class="login-stats"><span><span class="material-symbols-outlined" aria-hidden="true">verified_user</span>School email only</span><span><span class="material-symbols-outlined" aria-hidden="true">how_to_reg</span>Staff-verified accounts</span><span><span class="material-symbols-outlined" aria-hidden="true">devices</span>Works on any device</span></div>
        </div>
        <div class="library-shelf" aria-hidden="true">
            <span>Literature</span><span>Science &amp; Discovery</span><span>The Arts</span><span>Technology</span><span>History</span><span>Ideas</span>
        </div>
        <div class="login-story-footer"><span>A space for curious minds.</span><span>CLCST · Main Library</span></div>
    </aside>

    <main class="registration-content">
        <div class="login-content-top">Already have an account?<a href="{{ route('login') }}">Sign in <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_outward</span></a></div>
        <div class="registration-panel">
            <header class="registration-heading">
                <p class="login-eyebrow">Library account application</p>
                <h1>Create your CLCST library account</h1>
                <p>Use the details on your school or employee record. A library staff member will review your application before you can sign in.</p>
            </header>

            @if($errors->any())
                <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <div class="flex items-start gap-3"><span class="material-symbols-outlined mt-0.5 text-lg">error</span><div><p class="font-bold">Please review the highlighted information.</p><ul class="mt-1 list-disc space-y-0.5 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
                </div>
            @endif

            <form class="registration-form" method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data">
                @csrf
                <section class="registration-form-section" aria-labelledby="account-type-heading">
                    <div class="registration-step">
                        <span>01</span>
                        <div><h2 id="account-type-heading">Choose your account type</h2><p>Select the role shown in your official school record.</p></div>
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
                        <div class="registration-role-grid">
                            @foreach($roles as $roleValue=>[$roleLabel,$roleIcon,$roleHint])
                                <label class="cursor-pointer">
                                    <input class="sr-only" type="radio" name="patron_role" value="{{ $roleValue }}" @checked($selectedRole===$roleValue) required>
                                    <span class="role-card">
                                        <span class="role-icon"><span class="material-symbols-outlined text-lg">{{ $roleIcon }}</span></span>
                                        <span class="min-w-0"><strong>{{ $roleLabel }}</strong><small>{{ $roleHint }}</small></span>
                                        <span class="role-check hidden"><span class="material-symbols-outlined text-sm">check</span></span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('patron_role')<p class="mt-2 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                        <p class="registration-note"><span class="material-symbols-outlined">info</span><span>Librarian applications need administrator approval. Administrator accounts are created internally.</span></p>
                    </fieldset>
                </section>

                <section class="registration-form-section" aria-labelledby="personal-heading">
                    <div class="registration-step">
                        <span>02</span>
                        <div><h2 id="personal-heading">Personal and school information</h2><p>Enter the same details shown on your school ID or employment record.</p></div>
                    </div>
                    <div class="registration-fields">
                        <div><label for="first_name">First name <b>*</b></label><input id="first_name" name="first_name" value="{{ old('first_name') }}" required maxlength="100" autocomplete="given-name" class="@error('first_name') border-red-500 @enderror">@error('first_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div><label for="last_name">Last name <b>*</b></label><input id="last_name" name="last_name" value="{{ old('last_name') }}" required maxlength="100" autocomplete="family-name" class="@error('last_name') border-red-500 @enderror">@error('last_name')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div><label for="middle_initial">Middle initial <small>(optional)</small></label><input id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="5" class="@error('middle_initial') border-red-500 @enderror">@error('middle_initial')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div><label for="patron_id">School or employee ID <b>*</b></label><input id="patron_id" name="patron_id" value="{{ old('patron_id') }}" required maxlength="100" placeholder="Example: 2024-10492-OL" class="@error('patron_id') border-red-500 @enderror">@error('patron_id')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div><label for="program_or_department">Program or department <b>*</b></label><input id="program_or_department" name="program_or_department" value="{{ old('program_or_department') }}" required maxlength="150" placeholder="Example: BS Computer Science" class="@error('program_or_department') border-red-500 @enderror">@error('program_or_department')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div><label for="mobile_number">Mobile number <b>*</b></label><input id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}" required maxlength="13" inputmode="tel" autocomplete="tel" pattern="^(09|\+639)\d{9}$" title="Philippine mobile number, for example 09171234567" placeholder="Example: 09171234567" class="@error('mobile_number') border-red-500 @enderror">@error('mobile_number')<p class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="sm:col-span-2"><label for="email">School email address <b>*</b></label><input id="email" name="email" value="{{ old('email') }}" required type="email" maxlength="150" autocomplete="email" pattern=".+@clcst\.edu\.ph$" title="Use your school email ending in @clcst.edu.ph" placeholder="juan.delacruz@clcst.edu.ph" aria-describedby="email-help" class="@error('email') border-red-500 @enderror"><p id="email-help" class="field-help">Only school-registered emails ending in <strong>@clcst.edu.ph</strong> are accepted.</p>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
                    </div>
                </section>

                <section class="registration-form-section" aria-labelledby="security-heading">
                    <div class="registration-step">
                        <span>03</span>
                        <div><h2 id="security-heading">Security and verification</h2><p>Create a strong password and attach proof of your CLCST affiliation.</p></div>
                    </div>
                    <div class="registration-fields">
                        <div>
                            <label for="password">Password <b>*</b></label>
                            <div class="relative"><input id="password" name="password" required type="password" autocomplete="new-password" class="pr-11 @error('password') border-red-500 @enderror"><button class="registration-password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><span class="material-symbols-outlined text-lg">visibility</span></button></div>
                            <p class="field-help">At least 10 characters with uppercase, lowercase, and a number.</p>@error('password')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation">Confirm password <b>*</b></label>
                            <div class="relative"><input id="password_confirmation" name="password_confirmation" required type="password" autocomplete="new-password" aria-describedby="password-match-message" class="pr-11"><button class="registration-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password confirmation"><span class="material-symbols-outlined text-lg">visibility</span></button></div>
                            <p id="password-match-message" class="mt-1 hidden items-center gap-1 text-xs font-semibold" aria-live="polite"><span class="material-symbols-outlined text-sm" aria-hidden="true"></span><span data-password-match-text></span></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="credential_file">Supporting school document <b>*</b></label>
                            <label class="registration-upload" for="credential_file"><span class="material-symbols-outlined">upload_file</span><span><strong>Choose your COR, school ID, or employee ID</strong><small>PDF, JPG, or PNG · Maximum file size: 10 MB</small><small id="file-name" class="hidden selected-file"></small></span></label>
                            <input id="credential_file" name="credential_file" required type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">
                            @error('credential_file')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="registration-submit">
                    <label class="flex cursor-pointer items-start gap-3 text-xs leading-6 text-slate-700"><input class="mt-1 rounded border-slate-300 text-clcst-700 focus:ring-clcst-600" name="privacy_consent" required type="checkbox" value="1" @checked(old('privacy_consent'))><span>I confirm that the information is accurate and consent to its use for account verification and library services under the Data Privacy Act. <b class="text-red-600">*</b></span></label>
                    @error('privacy_consent')<p class="field-error">{{ $message }}</p>@enderror
                    <div class="registration-actions"><a href="{{ route('login') }}">Cancel</a><button type="submit"><span>Submit registration</span><span class="material-symbols-outlined text-lg">arrow_forward</span></button></div>
                    <div class="registration-next">
                        <span><b>1</b> Submit</span><span class="material-symbols-outlined">arrow_forward</span><span><b>2</b> Library review</span><span class="material-symbols-outlined">arrow_forward</span><span><b>3</b> Sign in after approval</span>
                    </div>
                </section>
            </form>
            <footer class="login-footer"><span>CLCST Library Management System</span><span>HCI102 Project</span></footer>
        </div>
    </main>
</div>
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
        passwordConfirmation.classList.toggle('border-emerald-600',confirmationStarted&&passwordsMatch);
        passwordMatchMessage.classList.toggle('text-red-700',confirmationStarted&&!passwordsMatch);
        passwordMatchMessage.classList.toggle('text-emerald-700',confirmationStarted&&passwordsMatch);
        if(!confirmationStarted){passwordMatchIcon.textContent='';passwordMatchText.textContent='';}
        else if(passwordsMatch){passwordMatchIcon.textContent='check_circle';passwordMatchText.textContent='Passwords match.';}
        else{passwordMatchIcon.textContent='error';passwordMatchText.textContent='Passwords do not match.';}
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
