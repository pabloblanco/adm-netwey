<?php

return [
    'log_level' => env('SLACK_LOG_LEVEL', null),

    'webhook_url' => env('SLACK_WEBHOOK_URL', ''),

    'add_memory_usage' => true,

    'add_request_headers' => true,

    'add_session_data' => true,

    'add_input_data' => true,

    // You can specify the inputs from the user that should not be sent to Slack
    //'ignore_request_fields' => ['password', 'confirm_password']
];
