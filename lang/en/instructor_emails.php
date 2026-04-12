<?php

return [
    'title' => 'Instructor Emails',
    'description' => 'Manage the email addresses used to identify instructors during QR scans. Each instructor account must be linked to the instructor_name returned by the room schedule API.',
    'add_new' => 'Add Instructor',
    'create_title' => 'Add Instructor Account',
    'edit_title' => 'Edit Instructor Account',
    'password' => 'Initial Password',
    'password_placeholder' => 'Leave blank to auto-generate',
    'password_hint' => 'Optional. If blank, a random password will be generated. Share it with the instructor so they can log in.',
    'confirm_delete' => 'Delete this instructor account? This cannot be undone.',
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
