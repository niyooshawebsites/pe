<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    session_regenerate_id();
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>