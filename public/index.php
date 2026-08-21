<?php

declare(strict_types=1);

use App\PriceCalculator;

require __DIR__ . '/../vendor/autoload.php';

$calculator = new PriceCalculator();

$cart = [
    ['price' => 19.99, 'quantity' => 2],
    ['price' => 4.50, 'quantity' => 3],
];

$subtotal = $calculator->subtotal($cart);
$total = $calculator->total($cart, 'WELCOME10');

?>
<!doctype html>
<meta charset="utf-8">
<title>projstart</title>
<style>
    body { font-family: system-ui, sans-serif; max-width: 34rem; margin: 4rem auto; padding: 0 1rem; }
    table { border-collapse: collapse; width: 100%; margin: 1.5rem 0; }
    th, td { text-align: left; padding: .5rem .75rem; border-bottom: 1px solid #ddd; }
    td:last-child, th:last-child { text-align: right; font-variant-numeric: tabular-nums; }
    footer { color: #666; font-size: .875rem; }
</style>

<h1>projstart</h1>
<p>Deployed by GitHub Actions. Totals computed by <code>PriceCalculator</code>.</p>

<table>
    <tr><th>Item</th><th>Qty</th><th>Line</th></tr>
    <?php foreach ($cart as $item) { ?>
        <tr>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
        </tr>
    <?php } ?>
    <tr><th>Subtotal</th><td></td><td><?= number_format($subtotal, 2) ?></td></tr>
    <tr><th>Total (WELCOME10 + VAT)</th><td></td><td><strong><?= number_format($total, 2) ?></strong></td></tr>
</table>

<footer>
    <a href="health.php">health.php</a>
</footer>
