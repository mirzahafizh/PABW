<?php
session_start();

include "config.php";



if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Function to handle the checkout process
function handleCheckout($username) {
    global $pdo;

    // Assuming you have an 'orders' table to store order information
    $stmt = $pdo->prepare("INSERT INTO orders (username, order_date) VALUES (?, NOW())");
    $stmt->execute([$username]);

    // Assuming you have an 'order_items' table to store items in an order
    $orderId = $pdo->lastInsertId();
    $selectedItems = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];

    foreach ($selectedItems as $cartItemId) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, cart_item_id) VALUES (?, ?)");
        $stmt->execute([$orderId, $cartItemId]);

        // You may want to update the cart item status or remove it from the cart table
        // depending on your business logic
    }

    // Redirect to a confirmation page or handle as needed
    header("Location: confirmation.php");
    exit();
}

// Function to delete a cart item
function deleteCartItem($cartItemId, $username) {
    global $pdo;

    // Assuming you have a 'carts' table to store cart items
    $stmt = $pdo->prepare("DELETE FROM carts WHERE id_cart = ? AND username = ?");
    $stmt->execute([$cartItemId, $username]);

    // Redirect back to the cart page or handle as needed
    header("Location: cart.php");
    exit();
}



// Handle checkout button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Assuming you have a function to handle the checkout process
    // Replace 'handleCheckout' with your actual function
    handleCheckout($username);
}

// Updated PHP code
// Handle delete button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $cartItemIds = isset($_POST['delete']) ? $_POST['delete'] : [];

    foreach ($cartItemIds as $cartItemId) {
        // Assuming you have a function to handle item deletion
        // Replace 'deleteCartItem' with your actual function
        deleteCartItem($cartItemId, $username);
    }
}





$stmt = $pdo->prepare("SELECT * FROM carts WHERE username = ?");
$stmt->execute([$username]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatRupiah($price)
{
    return 'Rp' . number_format($price, 0, ',', '.');
}


// Calculate total price for selected item
// Fetch total price from the database
$totalQuery = $pdo->prepare("SELECT SUM(total_price) as total FROM carts WHERE username = ?");
$totalQuery->execute([$username]);
$totalResult = $totalQuery->fetch(PDO::FETCH_ASSOC);
$total = $totalResult['total'];

// Handle Checkout button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Implement your checkout logic using $total
    // For example, redirect to a checkout page passing the total price
    header("Location: checkout.php?total_price=" . $total);
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart <?php echo $_SESSION['username']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom CSS */
        /* Add your custom CSS here */
        .behind-navbar {
            position: relative;
            z-index: -1; /* Set z-index to a lower value */
        }
        .navbar {
            position: relative;
            z-index: 0; /* Set z-index to a higher value */
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navbar -->
    <?php include "navbar.php"; ?>
    
    <div class="container mx-auto font-serif p-6 mt-[200px]">
        <div class="max-w-2xl mx-auto ">
            <h2 class="text-3xl font-bold mb-4">Your Cart</h2>
            <?php if (empty($cartItems)) : ?>
                <p>Your cart is empty.</p>
            <?php else : ?>
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="overflow-x-auto">
                        <table class="border-collapse w-full table-auto">
                            <!-- Table headers -->
                            <thead>
                                <tr>
                                    <th class="border border-gray-400 px-4 py-2">Select</th>
                                    <th class="border border-gray-400 px-4 py-2">Name</th>
                                    <th class="border border-gray-400 px-4 py-2">Quantity</th>
                                    <th class="border border-gray-400 px-4 py-2">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $cartItem) : ?>
                                    <tr>
                                        <!-- Checkbox for selecting items -->
                                        <td class="border border-gray-400 px-4 py-2">
                                            <input type="checkbox" name="selected_items[]" value="<?php echo $cartItem['id_cart']; ?>">
                                        </td>
                                        <!-- Cart item details -->
                                        <td class="border border-gray-400 px-4 py-2"><?php echo $cartItem['name']; ?></td>
                                        <td class="border border-gray-400 px-4 py-2"><?php echo $cartItem['quantity']; ?></td>
                                        <td class="border border-gray-400 px-4 py-2"><?php echo formatRupiah($cartItem['total_price']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Checkout and delete buttons -->
                    <div class="mt-4">
                        <button type="submit" name="checkout" class="bg-blue-500 text-white px-4 py-2">Checkout</button>
                    </div>
                </form>
                <form method="post" action="">
                    <?php foreach ($cartItems as $cartItem) : ?>
                        <button type="submit" name="delete[]" value="<?php echo $cartItem['id_cart']; ?>" class="bg-red-500 text-white px-4 py-2 mt-4">Delete</button>
                    <?php endforeach; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
