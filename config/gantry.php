<?php

return [
    // Dashboard Next.js milik Dafa (source of truth: session, DB, control)
    'dashboard_url' => env('GANTRY_DASHBOARD_URL', 'http://100.83.153.6:3006'),

    // API key partner (shared secret dari Dafa)
    'api_key' => env('GANTRY_API_KEY'),

    // Raspberry Pi milik Dafa (live stream SSE + gantry)
    'rpi_url' => env('GANTRY_RPI_URL', 'http://100.127.114.61:8000'),

    // Bed ID (default 1, sesuai info Dafa)
    'bed_id' => env('GANTRY_BED_ID', 1),
];