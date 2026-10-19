<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Terminal LinuxLab Sesuai SPEC Bagian 4.2 & Bagian 13
    |--------------------------------------------------------------------------
    */

    'image' => env('TERMINAL_IMAGE', 'linuxlab-terminal:latest'),

    'cpu_limit' => env('TERMINAL_CPU_LIMIT', '0.5'),

    'memory_limit' => env('TERMINAL_MEMORY_LIMIT', '256m'),

    'session_ttl' => (int) env('TERMINAL_SESSION_TTL', 30),

    'max_sessions' => (int) env('TERMINAL_MAX_SESSIONS', 5),

    'pids_limit' => (int) env('TERMINAL_PIDS_LIMIT', 64),

    'ws_port' => (int) env('TERMINAL_WS_PORT', 3001),
];
