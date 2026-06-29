<?php
session_start();
header('Content-Type: application/json');

$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

echo json_encode(['cart_count' => $cart_count]);
