<?php
ini_set('display_errors', 1);
echo "<h2>IMAP Test</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Extension imap: " . (extension_loaded('imap') ? '<b style="color:green">LOADED</b>' : '<b style="color:red">NOT LOADED</b>') . "<br>";

$server = "{localhost:143/imap/notls}INBOX";
$user = "newuser";
$pass = "wampp";

echo "Connecting to $server as $user...<br>";

$mbox = @imap_open($server, $user, $pass);

if ($mbox) {
    echo '<div style="background:#dcfce7; padding:10px; border:1px solid #22c55e;">Connected successfully to Mercury IMAP!</div>';
    
    $check = imap_check($mbox);
    echo "Messages: " . $check->Nmsgs . "<br>";
    echo "Recent: " . $check->Recent . "<br>";
    
    imap_close($mbox);
} else {
    echo '<div style="background:#fee2e2; padding:10px; border:1px solid #ef4444;">Connection failed: ' . imap_last_error() . '</div>';
    
    // Try Admin
    echo "<br>Trying Admin user (pass: mercury)...<br>";
    $mbox2 = @imap_open("{localhost:143/imap/notls}INBOX", "Admin", "mercury"); // Try default pass
    if ($mbox2) {
        echo '<div style="background:#dcfce7; padding:10px; border:1px solid #22c55e;">Connected successfully as Admin!</div>';
        imap_close($mbox2);
    } else {
        echo "Admin failed: " . imap_last_error() . "<br>";
        
        echo "<br>Trying Admin user (no pass)...<br>";
        $mbox3 = @imap_open("{localhost:143/imap/notls}INBOX", "Admin", ""); 
        if ($mbox3) {
             echo '<div style="background:#dcfce7; padding:10px; border:1px solid #22c55e;">Connected successfully as Admin (no pass)!</div>';
             imap_close($mbox3);
        } else {
             echo "Admin (no pass) failed: " . imap_last_error() . "<br>";
        }
    }
}
?>
