<?php
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: summary.php');
} else {
    header('Location: login.php');
}
exit;
