<?php

return [
    'token' => env('FONNTE_TOKEN'),
    'enabled' => (bool) env('FONNTE_ENABLED', false),
    'delay_per_message' => (int) env('FONNTE_DELAY_MINUTES', 1),
];
