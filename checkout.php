<?php
session_start();
include "config.php";

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Function to format price
function formatRupiah($price)
{
    return 'Rp' . number_format($price, 0, ',', '.');
}

// Fetch total price from the carts table
$totalQuery = $pdo->prepare("SELECT SUM(total_price) as total FROM carts WHERE username = ?");
$totalQuery->execute([$username]);
$totalResult = $totalQuery->fetch(PDO::FETCH_ASSOC);
$totalPrice = $totalResult['total'];

// Process checkout only when the checkout button is pressed
if(isset($_POST['checkout'])) {
    // Insert checkout data into the orders table
    $insertOrderQuery = $pdo->prepare("INSERT INTO orders (username, total_price, order_date) VALUES (?, ?, NOW())");
    $insertOrderQuery->execute([$username, $totalPrice]);

    // Clear the cart after checkout
    $clearCartQuery = $pdo->prepare("DELETE FROM carts WHERE username = ?");
    $clearCartQuery->execute([$username]);

    // Redirect to cart page or any other page
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
</head>
<body>
    <h1>Checkout</h1>
    <p>Total Price: <?php echo formatRupiah($totalPrice); ?></p>
    <form method="post">
        <button type="submit" name="checkout">Bayar</button>
    </form>
    <!-- Add checkout form or any other relevant content -->
</body>
</html>
