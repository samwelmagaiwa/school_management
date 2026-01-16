<?php

return [
    // Number of days a book can be borrowed
    'loan_period_days' => 14,

    // Maximum number of active (not yet returned) loans allowed per user
    'max_active_loans_per_user' => 3,

    // Fine charged per day for overdue books
    'daily_fine' => 50.00,
];
