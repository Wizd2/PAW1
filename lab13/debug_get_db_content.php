<?php
require_once 'cfg.php';
$query = "SELECT page_content FROM page_list WHERE page_title='login' OR page_title='logowanie' OR id=4 LIMIT 1"; 
// I don't know the exact title or ID, but usually 'login'. Let's try to find it.
// Actually, earlier I saw `index.php` uses `$idp` to query. `showpage.php` likely uses `FROM page_list WHERE id='$id_str' LIMIT 1` (if id is used) or similar.
// In `showpage.php` logic is probably: `SELECT * FROM page_list WHERE id='$idp' LIMIT 1`? No, usually `page_title` or `alias`.
// Let's look at `showpage.php` first to see how it queries, or just try to dump all pages.

$result = mysqli_query($link, "SELECT id, page_title, page_content FROM page_list WHERE status=1");
while($row = mysqli_fetch_assoc($result)){
    echo "ID: " . $row['id'] . " | Title: " . $row['page_title'] . "\n";
    if ($row['page_title'] === 'login' || $row['page_title'] === 'Logowanie') {
        echo "---------------- CONTENT ----------------\n";
        echo $row['page_content'];
        echo "\n-----------------------------------------\n";
    }
}
?>
