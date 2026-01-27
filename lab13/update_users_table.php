<?php
require_once 'cfg.php';

// Add columns for verification and password reset
$sqls = [
    "ALTER TABLE `users` ADD COLUMN `verification_code` VARCHAR(10) DEFAULT NULL AFTER `password`",
    "ALTER TABLE `users` ADD COLUMN `is_verified` TINYINT(1) DEFAULT 0 AFTER `verification_code`",
    "ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(64) DEFAULT NULL AFTER `is_verified`",
    "ALTER TABLE `users` ADD COLUMN `reset_expires` DATETIME DEFAULT NULL AFTER `reset_token`"
];

foreach ($sqls as $sql) {
    if (mysqli_query($link, $sql)) {
        echo "Executed: " . htmlspecialchars($sql) . "<br>";
    } else {
        echo "Error (maybe exists): " . mysqli_error($link) . "<br>";
    }
}
echo "Done.";
?>
