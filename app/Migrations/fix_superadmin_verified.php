<?php
require_once __DIR__ . '/../../config.php';

$pdo->exec("UPDATE admins SET email_verified = 1, verification_token = NULL WHERE username = 'superadmin'");
echo "superadmin marqué comme vérifié.\n";
