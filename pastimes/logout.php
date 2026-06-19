<?php
require_once 'includes/session_check.php';
session_destroy();
header('Location: login.php?logged_out=1');
exit;
