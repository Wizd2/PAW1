<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Email Test Script</h2>";

$to = "admin@localhost"; // Try a local address first
$subject = "Test Email from PHP";
$message = "This is a test email sent at " . date('Y-m-d H:i:s');
$headers = "From: test@localhost\r\n";

if(mail($to, $subject, $message, $headers)) {
    echo "Mail command sent successfully to $to.<br>";
} else {
    echo "Mail command failed for $to.<br>";
    print_r(error_get_last());
}

// Try the user's likely email if they used one in registration
// But we don't know it, so let's just test sending to a 'non-local' one to see behavior
$to2 = "test@example.com";
if(mail($to2, $subject . " (Remote)", $message, $headers)) {
    echo "Mail command sent successfully to $to2.<br>";
} else {
    echo "Mail command failed for $to2.<br>";
}

echo "<h3>Check these locations:</h3>";
echo "1. D:\samp\MERCURYMAIL\MAIL\Admin (or similar)<br>";
echo "2. D:\samp\MERCURYMAIL\Queue<br>";
?>
