<?php

return [
    'title' => 'Instructor Emails',
    'description' => 'Manage the email addresses used to identify instructors during QR scans. Each instructor account must be linked to the instructor_name returned by the room schedule API.',
    'edit_title' => 'Edit Instructor Account',
    'account_name' => 'Account Name',
    'email' => 'Personal Email',
    'email_hint' => 'The instructor uses this email to log in. Scans are identified by this address.',
    'instructor_name' => 'Linked Instructor Name',
    'instructor_name_placeholder' => 'e.g. Dr. Mohammad Rajha',
    'instructor_name_hint' => 'This must match the instructor_name returned by the room schedule API exactly.',
    'not_linked' => 'Not linked',
    'search_placeholder' => 'Search by name, email, or instructor name...',
    'empty' => 'No instructor accounts found.',
];
