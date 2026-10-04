<?php
require_once __DIR__ . '/inc/auth.php';
$_SESSION = [];
session_destroy();
redirect('login.php');
