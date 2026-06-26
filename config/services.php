<?php

return [
    // Optional SMS via Twilio. When unset, SMS notifications are logged instead
    // (mirrors the previous app's console fallback).
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],
];
