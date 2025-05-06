<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");

$role = isset($_GET['role']) && in_array($_GET['role'], ['user', 'admin']) ? $_GET['role'] : null;

if ($role) {
    $session_name = ($role === 'admin') ? 'SESSION_ADMIN' : 'SESSION_USER';
    session_name($session_name);
    
    if (session_start()) {
        $session_id = session_id();
        session_unset();
        session_destroy();
        
        if (isset($_COOKIE[$session_name])) {
            setcookie($session_name, '', time() - 3600, '/', '', true, true);
        }
        if (isset($_COOKIE['PHPSESSID'])) {
            setcookie('PHPSESSID', '', time() - 3600, '/', '', true, true);
        }
    }
}

header("Location: ../login.php");
exit();
?>