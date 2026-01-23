<?php

return [
    'messages' => [
        'unauthorized' => 'You are not authorized to access this resource.',
        'suspended' => 'Your account has been suspended.',
        'invalid_role' => 'Invalid role specified.',
        'invalid_privilege' => 'Invalid privilege specified.',
        'invalid_ability' => 'Invalid ability specified.',

        'role_required' => 'Role :role is required.',
        'any_role_required' => 'One of these roles is required: :roles.',
        'privilege_required' => 'Privilege :privilege is required.',
        'any_privilege_required' => 'One of these privileges is required: :privileges.',
        'ability_required' => 'Ability :ability is required.',
    ],

    'errors' => [
        'middleware' => [
            'missing_parameter' => 'Middleware :middleware requires a parameter.',
            'invalid_parameter' => 'Invalid parameter for middleware :middleware.',
            'configuration_error' => 'Middleware configuration error.',
        ],

        'suspension' => [
            'active' => 'Your account is suspended until :date.',
            'indefinite' => 'Your account is suspended indefinitely.',
            'reason' => 'Reason: :reason',
            'contact_admin' => 'Please contact the administrator for more information.',
        ],
    ],

    'http' => [
        '403' => [
            'title' => 'Forbidden',
            'message' => 'You don\'t have permission to access this page.',
            'suggestion' => 'Please contact your administrator if you believe this is an error.',
        ],

        '419' => [
            'title' => 'Session Expired',
            'message' => 'Your session has expired.',
            'suggestion' => 'Please refresh the page and try again.',
        ],

        '429' => [
            'title' => 'Too Many Requests',
            'message' => 'You have made too many requests.',
            'suggestion' => 'Please wait before trying again.',
        ],

        '500' => [
            'title' => 'Server Error',
            'message' => 'Something went wrong on our servers.',
            'suggestion' => 'Please try again later or contact support.',
        ],

        '503' => [
            'title' => 'Service Unavailable',
            'message' => 'The service is temporarily unavailable.',
            'suggestion' => 'Please try again in a few minutes.',
        ],
    ],
];
