<?php
require_once __DIR__ . '/../includes/bootstrap.php';

session_unset();
session_destroy();

session_start();
set_flash('success', 'Administrator session ended.');
redirect('login.php');
