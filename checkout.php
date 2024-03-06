<?php
session_start();
include "config.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch order details for the current user from 'pra_order' table
$stmt = $pdo->prepare("SELECT * FROM pra_order WHERE username = ?");
$stmt->execute([$username]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to format price
function formatRupiah($price)
{
    return 'Rp' . number_format($price, 0, ',', '.');
}

// Function to handle cancellation of all orders
function cancelOrders($username, $pdo) {
    $cancelQuery = $pdo->prepare("DELETE FROM pra_order WHERE username = ?");
    $cancelQuery->execute([$username]);
    // Redirect to checkout page after cancellation
    header("Location: cart.php");
    exit();
}

// Handle cancellation of all orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
    cancelOrders($username, $pdo);
}

// Function to handle payment and insert data into 'orders' table
// Function to handle payment and insert data into 'orders' table
function handlePayment($username, $orders, $pdo) {
    // Prepare statements
    $userStmt = $pdo->prepare("SELECT phone, address, saldo FROM tb_user WHERE username = ?");
    $productStmt = $pdo->prepare("SELECT id_produk, store_address, store_name, stock FROM products WHERE name = ?");
    $storeStmt = $pdo->prepare("SELECT username FROM store_info WHERE store_name = ?");
    $deleteCartStmt = $pdo->prepare("DELETE FROM carts WHERE id_cart = ?");
    $updateStatusStmt = $pdo->prepare("UPDATE pra_order SET status_pesanan = 'menunggu kurir' WHERE order_id = ?");
    $updateStockStmt = $pdo->prepare("UPDATE products SET stock = ? WHERE name = ?");

    $totalPriceToDeduct = 0; // Initialize total price to deduct

    foreach ($orders as $order) {
        $orderId = $order['order_id'];
        $storeName = $order['store_name'];
        $productName = $order['name'];
        $price = $order['price'];
        $quantity = $order['quantity'];

        // Fetch user data
        $userStmt->execute([$username]);
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
        $phone = $userData['phone'];
        $address = $userData['address'];

        // Fetch product data
        $productStmt->execute([$productName]);
        $productData = $productStmt->fetch(PDO::FETCH_ASSOC);
        $storeAddress = $productData['store_address'];
        $shippingCost = $order['shipping_cost'];
        $stock = $productData['stock'];

        // Fetch store data
        $storeStmt->execute([$storeName]);
        $storeData = $storeStmt->fetch(PDO::FETCH_ASSOC);
        $sellerName = $storeData['username'];

        // Calculate total price
        $totalPrice = $order['total_price'];

        // Deduct only if the status is 'diterima pembeli'
        if ($order['status_pesanan'] == 'diterima pembeli') {
            $totalPriceToDeduct += $totalPrice;
        }

        // Check if stock is sufficient
        if ($quantity > $stock) {
            // Prepare the error message
            $errorMessage = ("Stock tidak cukup untuk $productName");
            // Redirect back with error message using JavaScript alert
            echo "<script>alert('$errorMessage'); window.location.href='checkout.php';</script>";
            exit();
        }

        // Insert order data into 'orders' table
        $stmt = $pdo->prepare("INSERT INTO orders (username, name, status_pesanan, phone, address, store_address, store_name, shipping_cost, total_price, total_items_price, seller_username) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $productName, 'menunggu kurir', $phone, $address, $storeAddress, $storeName, $shippingCost, $totalPrice, $price * $quantity, $sellerName]);

        // Reduce stock
        $newStock = $stock - $quantity;
        $updateStockStmt->execute([$newStock, $productName]);
    }

    // Check if the user's balance is sufficient for the deducted total price
    $userStmt->execute([$username]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userBalance = $userData['saldo'];

    if ($userBalance < $totalPriceToDeduct) {
        // Prepare the error message
        $errorMessage = "Saldo tidak mencukupi. Silakan hubungi admin untuk top up saldo.";
        // Redirect back with error message using JavaScript alert
        echo "<script>alert('$errorMessage'); window.location.href='checkout.php';</script>";
        exit();
    }

    // Deduct the total price
    $newUserBalance = $userBalance - $totalPriceToDeduct;

    // Update the user's balance
    $updateBalanceStmt = $pdo->prepare("UPDATE tb_user SET saldo = ? WHERE username = ?");
    $updateBalanceStmt->execute([$newUserBalance, $username]);

    // Clear the pra_order table after payment
    $cancelQuery = $pdo->prepare("DELETE FROM pra_order WHERE username = ?");
    $cancelQuery->execute([$username]);

    // Delete items from 'carts' table
    foreach ($orders as $order) {
        $deleteCartStmt->execute([$order['id_cart']]);
    }

    // Redirect to cart page after payment
    header("Location: status.php");
    exit();
}





// Handle payment when "Pay" button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    handlePayment($username, $orders, $pdo);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-4">
    <div class="max-w-3xl mx-auto bg-white shadow-lg p-8 rounded-md mt-8">
        <h1 class="text-3xl font-bold mb-4">Checkout</h1>

        <?php if (empty($orders)) : ?>
            <p class="text-gray-600">No orders to checkout.</p>
        <?php else : ?>
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Order ID</th>
                        <th class="px-4 py-2">Store ID</th>
                        <th class="px-4 py-2">Item Name</th>
                        <th class="px-4 py-2">Store Name</th>
                        <th class="px-4 py-2">Price</th>
                        <th class="px-4 py-2">Quantity</th>
                        <th class="px-4 py-2">Total Price</th>
                        <th class="px-4 py-2">Shipping Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order) : ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo $order['order_id']; ?></td>
                            <td class="border px-4 py-2"><?php echo $order['id_toko']; ?></td>
                            <td class="border px-4 py-2"><?php echo $order['name']; ?></td>
                            <td class="border px-4 py-2"><?php echo $order['store_name']; ?></td>
                            <td class="border px-4 py-2"><?php echo formatRupiah($order['price']); ?></td>
                            <td class="border px-4 py-2"><?php echo $order['quantity']; ?></td>
                            <td class="border px-4 py-2"><?php echo formatRupiah($order['price'] * $order['quantity']); ?></td>
                            <td class="border px-4 py-2"><?php echo formatRupiah($order['shipping_cost']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!empty($orders)) : ?>
            <?php
            $totalPrice = 0;
            $totalItemsPrice = 0;
            $shippingCostMap = [];

            foreach ($orders as $order) {
                $totalItemsPrice += ($order['price'] * $order['quantity']);
                $totalPrice += $order['total_items_price'];
                $shippingCostMap[$order['id_toko']] = $order['shipping_cost'];
            }
            $totalShippingCost = array_sum($shippingCostMap);
            ?>

            <p class="text-right mt-2">Total Items Price: <?php echo formatRupiah($totalItemsPrice); ?></p>
            <p class="text-right mt-2">Total Shipping Cost: <?php echo formatRupiah($totalShippingCost); ?></p>
            <p class="text-right mt-2">Subtotal: <?php echo formatRupiah($totalItemsPrice + $totalShippingCost); ?></p>

                <!-- Rest of the HTML -->
            <?php endif; ?>

            <form method="post" action="" class="mt-6  flex justify-end">
                <!-- Add payment form fields -->
                <button type="submit" name="pay" class="bg-blue-500 w-20 text-white px-4 py-2 rounded-md">Pay</button>
                <!-- Cancel button to go back to cart -->
                <button type="submit" name="cancel" class="bg-gray-500 w-20 text-white px-4 py-2 ml-4 rounded-md">Cancel</button>
            </form>


        <?php endif; ?>
    </div>
</body>

</html>
