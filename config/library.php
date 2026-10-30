<?php

/*
| Default circulation policy. Super administrators can override these values
| from Borrowing Rules and Fine Settings; overrides are stored in the
| library_settings table and read through App\Services\LibrarySettings.
*/

return [
    'loan_days_student' => 7,
    'loan_days_faculty' => 30,
    'max_loans_student' => 5,
    'max_loans_faculty' => 10,
    'max_renewals' => 1,
    'renewal_days' => 7,
    'max_reservations' => 3,
    'pickup_days' => 3,
    'fine_per_day' => 5,
    'max_fine_per_item' => 200,
    'allowed_email_domain' => 'clcst.edu.ph',
];
