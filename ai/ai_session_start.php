<?php
// session_start.php

// Check if the session is already started
if (session_status() == PHP_SESSION_NONE) {
    // If the session is not started, start the session
    session_start();
}
?>
