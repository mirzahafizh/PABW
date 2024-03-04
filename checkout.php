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
function handlePayment($username, $orders, $pdo) {
    // Prepare statements
    $userStmt = $pdo->prepare("SELECT phone, address, saldo FROM tb_user WHERE username = ?");
    $productStmt = $pdo->prepare("SELECT id_produk, store_address, store_name, stock FROM products WHERE name = ?");
    $storeStmt = $pdo->prepare("SELECT username FROM store_info WHERE store_name = ?");
    $deleteCartStmt = $pdo->prepare("DELETE FROM carts WHERE id_cart = ?");
    $updateStatusStmt = $pdo->prepare("UPDATE pra_order SET status_pesanan = 'menunggu kurir' WHERE order_id = ?");
    $updateStockStmt = $pdo->prepare("UPDATE products SET stock = ? WHERE name = ?");

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

        
            // Fetch user balance
            $userStmt->execute([$username]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            $userBalance = $userData['saldo'];
    
            // Check if user balance is sufficient
            if ($userBalance < $totalPrice) {
                // Prepare the error message
                $errorMessage = "Saldo tidak mencukupi. Silakan hubungi admin untuk top up saldo.";
                // Redirect back with error message using JavaScript alert
                echo "<script>alert('$errorMessage'); window.location.href='checkout.php';</script>";
                exit();
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
</head>
<body>
    <h1>Checkout</h1>
    <?php if (empty($orders)) : ?>
        <p>No orders to checkout.</p>
    <?php else : ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Store ID</th>
                    <th>Item Name</th>
                    <th>Store Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Shipping Cost</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $totalPrice = 0;
                $totalItemsPrice = 0;
                $shippingCostMap = []; // Map to store shipping costs for each store

                foreach ($orders as $order) {
                    $totalItemsPrice += ($order['price'] * $order['quantity']); // Total harga untuk setiap item
                    $totalPrice += $order['total_items_price'];
                    $shippingCostMap[$order['id_toko']] = $order['shipping_cost'];
                }
                // Calculate total shipping cost
                $totalShippingCost = array_sum($shippingCostMap);
                ?>

                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['id_toko']; ?></td>
                        <td><?php echo $order['name']; ?></td>
                        <td><?php echo $order['store_name']; ?></td>
                        <td><?php echo formatRupiah($order['price']); ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td><?php echo formatRupiah($order['price'] * $order['quantity']); ?></td> <!-- Total harga untuk setiap item -->
                        <td><?php echo formatRupiah($shippingCostMap[$order['id_toko']]); ?></td>
                    </tr>
                <?php endforeach; ?>


            </tbody>
        </table>

    <?php endif; ?>
    <?php if (!empty($orders)) : ?>
    <p>Total Items Price: <?php echo formatRupiah($totalItemsPrice); ?></p> <!-- Total harga dari semua item -->
<?php endif; ?>

<?php if (!empty($orders)) : ?>
    <p>Total Shipping Cost: <?php echo formatRupiah($totalShippingCost); ?></p>
    <p>Subtotal: <?php echo formatRupiah($totalItemsPrice + $totalShippingCost); ?></p>
<?php endif; ?>

    <form method="post" action="">
        <!-- Add payment form fields -->
        <button type="submit" name="pay">Pay</button>
        <!-- Cancel button to go back to cart -->
    </form>
    <form method="post" action="">
        <button type="submit" name="cancel">Cancel</button>
    </form>
</body>
</html>
