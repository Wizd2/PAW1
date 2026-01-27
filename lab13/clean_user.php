<?php
require_once 'cfg.php';
$email = 'samorazwitie12@gmail.com';
$stmt = $link->prepare("DELETE FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
if($stmt->execute()) {
    echo "Deleted user $email. You can register again.";
} else {
    echo "Error deleting user: " . mysqli_error($link);
}
?>
