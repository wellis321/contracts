<?php
/**
 * Logout Page
 */
require_once dirname(__DIR__) . '/config/config.php';

Auth::logout();
header('Location: ' . url('index.php'));
exit;
