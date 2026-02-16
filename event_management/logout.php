<?php
session_start();
session_destroy();
header('Location: event_management.php');
exit;
?>
