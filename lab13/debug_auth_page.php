<?php
require_once 'cfg.php';
$stmt = $link->prepare("SELECT page_content FROM page_list WHERE page_title = 'auth'");
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    echo "Current Content:\n\n";
    echo $row['page_content'];
} else {
    echo "Page 'auth' not found.";
}
?>
