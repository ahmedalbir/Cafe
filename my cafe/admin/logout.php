<?php
require_once __DIR__ . '/../includes/bootstrap.php';

session_destroy();
redirect_to(site_url('admin/login.php'));
