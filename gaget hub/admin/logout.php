<?php
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['admin_id']); unset($_SESSION['admin_name']);
redirect(SITE_URL . '/admin/login.php');
