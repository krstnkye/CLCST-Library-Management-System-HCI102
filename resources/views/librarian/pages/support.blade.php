<section class="grid gap-4 md:grid-cols-2">
    @foreach ([
        ['outbox', 'Lending a book', 'Open Check Out Books, enter the patron ID or school email and the copy barcode. The system blocks the loan if the patron is suspended, has an overdue book, has reached the loan limit, or if the copy is set aside for someone on the waiting list.'],
        ['assignment_return', 'Receiving a return', 'Open Check In Returns and scan the barcode. Late returns create a fine automatically, and the next patron waiting for the title is shown so you can notify them.'],
        ['bookmark', 'Serving reservations', 'When a copy is on the shelf, press "Notify: ready" to hold it for the patron, or "Lend now" when they are at the desk.'],
        ['payments', 'Fines', 'Record a payment only after the cashier receives it. Waivers need an approved reason and are logged with your name.'],
        ['inventory_2', 'Damaged or lost copies', 'In Book Inventory, set a copy to "For repair" or "Lost". It leaves the shelf count until it is set back to "On shelf".'],
        ['how_to_reg', 'New accounts', 'Account Review lists applications for the roles you are allowed to approve. Open the uploaded ID before approving.'],
    ] as [$icon, $title, $body])
        <article class="rounded-lg border border-[#D6DED8] bg-white p-5 shadow-sm">
            <h2 class="flex items-center gap-2 font-display text-base font-bold"><span class="material-symbols-outlined text-[#0F5C2E]">{{ $icon }}</span>{{ $title }}</h2>
            <p class="mt-2 text-sm leading-6 text-[#44534B]">{{ $body }}</p>
        </article>
    @endforeach
    <p class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-950 md:col-span-2">Technical problem? Email <a class="font-bold underline" href="mailto:library@clcst.edu.ph">library@clcst.edu.ph</a> with a screenshot and the time it happened. Every action is recorded in the activity log, which helps us trace the issue.</p>
</section>
