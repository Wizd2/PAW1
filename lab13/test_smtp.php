<?php
ini_set('display_errors', 1);
require_once 'smtp_helper.php';

$to = 'iliialeblankowicz@gmail.com'; // Send to self for test
echo "Sending test email to $to ...<br>";

if (send_smtp_mail($to, "ClickClick SMTP Test", "If you see this, SMTP is working!")) {
    echo "<b style='color:green'>SUCCESS! Check your inbox.</b>";
} else {
    echo "<b style='color:red'>FAILED. Check email_log.txt</b>";
}
?>
