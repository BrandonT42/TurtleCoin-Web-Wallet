<?php
require_once "core.php";

// Reset session
resetSession();
 
// Redirect to login page
header("location: login.php");
exit;
?>