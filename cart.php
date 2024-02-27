<?php
session_start();

include "config.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Handle Delete button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;

    if ($itemId > 0) {
        $stmt = $pdo->prepare("DELETE FROM carts WHERE id_cart = ? AND username = ?");
        $stmt->execute([$itemId, $username]);
    }

    // Redirect back to the cart page after deleting an item
    header("Location: cart.php");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM carts WHERE username = ?");
$stmt->execute([$username]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatRupiah($price)
{
    return 'Rp' . number_format($price, 0, ',', '.');
}

// Handle Checkout button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Get selected item IDs for checkout
    $selectedItems = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];

    // Implement your checkout logic using $selectedItems
    // For example, redirect to a checkout page passing the selected item IDs
    header("Location: checkout.php?selected_items=" . implode(',', $selectedItems));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MS STORE - <?php echo htmlspecialchars($product['name']); ?></title>
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
    <?php include "navbar.php"; ?>
    <div class="container mx-auto font-serif p-6 mt-[90px]">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold mb-4">Your Cart</h2>
            <?php if (empty($cartItems)) : ?>
                <p>Your cart is empty.</p>
            <?php else : ?>
                <form method="post" action="">
                    <div class="overflow-x-auto">
                        <table class="border-collapse w-full table-auto">
                            <thead>
                                <tr>
                                    <th class="border border-gray-400 px-4 py-2">Select</th>
                                    <th class="border border-gray-400 px-4 py-2">Product</th>
                                    <th class="border border-gray-400 px-4 py-2">Quantity</th>
                                    <th class="border border-gray-400 px-4 py-2">Total Price</th>
                                    <th class="border border-gray-400 px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $cartItem) : ?>
                                    <tr>
                                        <td class="border border-gray-400 px-4 py-2">
                                            <input type="checkbox" name="selected_items[]" value="<?php echo $cartItem['id_cart']; ?>">
                                        </td>
                                        <td class="border border-gray-400 px-4 py-2"><?php echo $cartItem['name']; ?></td>
                                        <td class="border border-gray-400 px-4 py-2"><?php echo $cartItem['quantity']; ?></td>
                                        <td class="border border-gray-400 px-4 py-2"><?php echo formatRupiah($cartItem['total_price']); ?></td>
                                        <td class="border border-gray-400 px-4 py-2">
                                            <form method="post" action="">
                                                <input type="hidden" name="item_id" value="<?php echo $cartItem['id_cart']; ?>">
                                                <button type="submit" name="delete_item" class="bg-red-500 text-white px-2 py-1">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="checkout" class="bg-blue-500 text-white px-4 py-2">Checkout</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
