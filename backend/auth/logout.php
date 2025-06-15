<?php
// backend/auth/logout.php

session_start();
$_SESSION = [];
session_destroy();

// ✅ Redirect to the main index.php in the root
header("Location: /attendance-system/index.php");
exit;
