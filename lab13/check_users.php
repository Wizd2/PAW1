<?php
require_once 'cfg.php';

echo "<h2>Users List</h2>";
echo "<table border='1'><tr><th>ID</th><th>Email</th><th>Verified?</th><th>Code</th></tr>";

$res = mysqli_query($link, "SELECT id, email, is_verified, verification_code FROM users");
while ($row = mysqli_fetch_assoc($res)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>{$row['is_verified']}</td>";
    echo "<td>" . htmlspecialchars($row['verification_code']) . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
