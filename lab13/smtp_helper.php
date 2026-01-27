<?php
// smtp_helper.php - Simple SMTP Sender for Gmail (No PHPMailer)
require_once 'cfg.php';

function send_smtp_mail($to, $subject, $body) {
    global $cfg_smtp_host, $cfg_smtp_port, $cfg_smtp_user, $cfg_smtp_pass, $cfg_smtp_from, $cfg_smtp_name;

    $errStr = '';
    $errNo = 0;

    $socket = stream_socket_client($cfg_smtp_host . ":" . $cfg_smtp_port, $errNo, $errStr, 10);
    if (!$socket) {
        // Log error
        file_put_contents('email_log.txt', date('[Y-m-d H:i:s] ') . "Connection failed: $errStr\n", FILE_APPEND);
        return false;
    }

    // Helper to read response
    function server_parse($socket, $response) {
        $server_response = '';
        while (substr($server_response, 3, 1) != ' ') {
            if (!($server_response = fgets($socket, 256))) {
                return false;
            }
        }
        if (!(substr($server_response, 0, 3) == $response)) {
            return false;
        }
        return true;
    }

    server_parse($socket, '220');

    $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
    fwrite($socket, 'EHLO ' . $serverName . "\r\n");
    server_parse($socket, '250');

    fwrite($socket, 'AUTH LOGIN' . "\r\n");
    server_parse($socket, '334');

    fwrite($socket, base64_encode($cfg_smtp_user) . "\r\n");
    server_parse($socket, '334');

    fwrite($socket, base64_encode(str_replace(' ', '', $cfg_smtp_pass)) . "\r\n"); // Remove spaces from app password logic just in case, though usually fine
    if (!server_parse($socket, '235')) {
        file_put_contents('email_log.txt', date('[Y-m-d H:i:s] ') . "Auth failed\n", FILE_APPEND);
        fclose($socket);
        return false;
    }

    fwrite($socket, 'MAIL FROM: <' . $cfg_smtp_user . '>' . "\r\n");
    server_parse($socket, '250');

    fwrite($socket, 'RCPT TO: <' . $to . '>' . "\r\n");
    server_parse($socket, '250');

    fwrite($socket, 'DATA' . "\r\n");
    server_parse($socket, '354');

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: $cfg_smtp_name <$cfg_smtp_from>\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";

    fwrite($socket, $headers . "\r\n" . $body . "\r\n");
    fwrite($socket, '.' . "\r\n");
    
    $result = server_parse($socket, '250');
    
    fwrite($socket, 'QUIT' . "\r\n");
    fclose($socket);

    if ($result) {
        // Log success
        file_put_contents('email_log.txt', date('[Y-m-d H:i:s] ') . "SMTP SENT to $to | Subj: $subject\n", FILE_APPEND);
    } else {
        file_put_contents('email_log.txt', date('[Y-m-d H:i:s] ') . "SMTP FAILED to $to\n", FILE_APPEND);
    }

    return $result;
}
?>
