<?php
$db_config_path = __DIR__ . '/../../application/config/database.php';

if (!file_exists($db_config_path)) {
    die(json_encode(['status' => 'error', 'message' => 'Database config file not found.']));
}

// Load CI database config into $db array
require_once $db_config_path;

$active = $db[$active_group];

$link = $con = mysqli_connect(
    $active['hostname'],
    $active['username'],
    $active['password'],
    $active['database']
);

if ($link === false) {
    die(json_encode(['status' => 'error', 'message' => 'DB connection failed: ' . mysqli_connect_error()]));
}

$result = mysqli_query($link, "SELECT * FROM email_config WHERE id = 1 LIMIT 1");

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $email            = $row['email'];
    $email_protocol   = $row['email_protocol'] ?? 'smtp';
    $smtp_host        = $row['smtp_host'];
    $smtp_user        = $row['smtp_user'];
    $smtp_pass        = $row['smtp_pass'];
    $smtp_port        = $row['smtp_port'];
    $smtp_encryption  = $row['smtp_encryption'];
    mysqli_free_result($result);
} else {
    die(json_encode(['status' => 'error', 'message' => 'No email config found.']));
}
