<?php
// Must be the very first thing — no whitespace before <?php
require_once __DIR__ . '/includes/functions.php';

// At this point session is already started by functions.php
// sendNoCacheHeaders() is also already called inside functions.php

// 1. Wipe session data from memory
$_SESSION = [];

// 2. Delete the session cookie from the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy the server-side session
session_destroy();

// 4. Start a fresh session just to carry the flash message
session_start();
setFlash('success', 'You have been logged out successfully.');

// 5. Redirect to login
redirect('/larong_pinoy/login.php');