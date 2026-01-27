<?php
// my_orders.php — Wyświetlanie listy zamówień LUB szczegółów zamówienia

if (!isset($_SESSION['user_id'])) {
    echo '<div class="card">Musisz być zalogowany, aby zobaczyć swoje zamówienia. <a href="index.php?idp=login">Zaloguj się</a></div>';
    return;
}

$userId = (int)$_SESSION['user_id'];
$showOrderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// =========================================================
// WIDOK 1: Szczegóły konkretnego zamówienia
// =========================================================
if ($showOrderId > 0) {
    // 1. Sprawdzamy czy zamowienie nalezy do usera
    $qOrder = mysqli_query($link, "SELECT * FROM orders WHERE id=$showOrderId AND user_id=$userId LIMIT 1");
    if (!$qOrder || mysqli_num_rows($qOrder) === 0) {
        echo '<div class="card">Nie znaleziono zamówienia lub brak dostępu. <a href="index.php?idp=my_orders">Wróć</a></div>';
        return;
    }
    $order = mysqli_fetch_assoc($qOrder);
    
    // 2. Pobieramy pozycje
    // order_items(order_id, product_id, quantity, price_gross)
    // products(title, image_url)
    $sqlItems = "SELECT oi.*, p.title, p.image_url 
                 FROM order_items oi
                 LEFT JOIN products p ON p.id = oi.product_id
                 WHERE oi.order_id = $showOrderId";
    $qItems = mysqli_query($link, $sqlItems);
    
    $date = date("d.m.Y H:i", strtotime($order['created_at']));
    $total = number_format((float)$order['total_amount'], 2, ',', ' ');
    $status = htmlspecialchars($order['status']);
    
    echo '<section class="card">';
    echo '<div style="display:flex; justify-content:space-between; align-items:center;">';
    echo '<h2>Zamówienie #'.$showOrderId.'</h2>';
    echo '<a class="btn secondary" href="index.php?idp=my_orders">← Wróć do listy</a>';
    echo '</div>';
    
    echo '<div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:8px; margin:15px 0;">';
    echo '<div><b>Data:</b> '.$date.'</div>';
    echo '<div><b>Status:</b> '.$status.'</div>';
    echo '</div>';
    
    echo '<table class="layout" style="width:100%; border-collapse:collapse; margin-top:10px;">';
    echo '<thead>';
    echo '<tr style="border-bottom:1px solid rgba(255,255,255,0.1); text-align:left;">';
    echo '<th style="padding:10px;">Produkt</th>';
    echo '<th style="padding:10px;">Cena</th>';
    echo '<th style="padding:10px;">Ilość</th>';
    echo '<th style="padding:10px;">Wartość</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if ($qItems) {
        while ($item = mysqli_fetch_assoc($qItems)) {
            $pTitle = htmlspecialchars($item['title'] ?? 'Produkt usunięty');
            $pImg = $item['image_url'] ?? '';
            $qty = (int)$item['quantity'];
            $price = (float)$item['price_gross'];
            $lineVal = $price * $qty;
            
            echo '<tr style="border-bottom:1px solid rgba(255,255,255,0.05);">';
            echo '<td style="padding:10px;">';
            if ($pImg) echo '<img src="'.$pImg.'" style="width:40px; height:40px; object-fit:cover; vertical-align:middle; margin-right:10px; border-radius:4px;">';
            echo '<b>'.$pTitle.'</b>';
            echo '</td>';
            echo '<td style="padding:10px;">'.number_format($price, 2, ',', ' ').' zł</td>';
            echo '<td style="padding:10px;">'.$qty.'</td>';
            echo '<td style="padding:10px;">'.number_format($lineVal, 2, ',', ' ').' zł</td>';
            echo '</tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';
    
    echo '<div style="text-align:right; margin-top:20px; font-size:1.2em;">';
    echo 'Razem: <b>'.$total.' zł</b>';
    echo '</div>';
    
    echo '</section>';
    return;
}

// =========================================================
// WIDOK 2: Lista zamówień
// =========================================================

$q = mysqli_query($link, "SELECT * FROM orders WHERE user_id=$userId ORDER BY created_at DESC");

if (!$q) {
    echo '<div class="card">Błąd bazy danych: ' . htmlspecialchars(mysqli_error($link)) . '</div>';
    return;
}

if (mysqli_num_rows($q) == 0) {
    echo '<section class="card">';
    echo '<h2>Moje zamówienia</h2>';
    echo '<p>Nie masz jeszcze żadnych zamówień.</p>';
    echo '<div style="margin-top:12px;"><a class="btn" href="index.php">Wróć do sklepu</a></div>';
    echo '</section>';
    return;
}
?>

<section class="card">
    <h2>Moje zamówienia</h2>
    <p>Poniżej lista Twoich złożonych zamówień.</p>

    <div style="overflow-x:auto; margin-top:15px;">
        <table class="layout" style="width:100%; border-collapse:collapse; min-width:600px;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th style="text-align:left; padding:10px;">Nr zamówienia</th>
                    <th style="text-align:left; padding:10px;">Data</th>
                    <th style="text-align:left; padding:10px;">Kwota</th>
                    <th style="text-align:left; padding:10px;">Status</th>
                    <th style="text-align:left; padding:10px;">Akcja</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($q)): ?>
                    <?php
                        $oid = (int)$row['id'];
                        $date = date("d.m.Y H:i", strtotime($row['created_at']));
                        $amount = number_format((float)$row['total_amount'], 2, ',', ' ') . ' zł';
                        $statusRaw = $row['status'];
                        $statusLabel = htmlspecialchars($statusRaw);
                        
                        if ($statusRaw === 'new') $statusLabel = 'Nowe';
                        if ($statusRaw === 'completed') $statusLabel = 'Zrealizowane';
                        if ($statusRaw === 'cancelled') $statusLabel = 'Anulowane';
                    ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding:10px;">#<?php echo $oid; ?></td>
                        <td style="padding:10px;"><?php echo $date; ?></td>
                        <td style="padding:10px;"><b><?php echo $amount; ?></b></td>
                        <td style="padding:10px;">
                            <span class="badge" style="background:rgba(255,255,255,0.1); padding:4px 8px; border-radius:4px; font-size:0.85em;">
                                <?php echo $statusLabel; ?>
                            </span>
                        </td>
                        <td style="padding:10px;">
                            <a class="btn" style="padding:6px 12px; font-size:0.9em;" href="index.php?idp=my_orders&order_id=<?php echo $oid; ?>">
                                Szczegóły
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top:20px;">
        <a class="btn secondary" href="index.php?idp=auth">Wróć do panelu</a>
    </div>
</section>
