<?php
session_start();

include "config.php";

if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id_produk = ?");
    $stmt->execute([$productId]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

function formatRupiah($price)
{
    return 'Rp' . number_format($price, 0, ',', '.');
}

// Handle Add to Cart button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Validate quantity (you can add more validation if needed)
    if ($quantity <= 0) {
        // Invalid quantity, handle accordingly
        echo "Invalid quantity";
        exit();
    }

    // Check if the user is logged in
    if (isset($_SESSION['username'])) {
        // User is logged in, proceed to add to cart
        $username = $_SESSION['username'];
        $totalPrice = $quantity * $product['price'];

        $stmt = $pdo->prepare("INSERT INTO carts (username, id_produk, quantity, name, price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $productId, $quantity, $product['name'], $product['price'], $totalPrice]);

        // Redirect to the cart page after adding to cart
        header("Location: cart.php");
        exit();
    } else {
        // User is not logged in, redirect to login or handle accordingly
        header("Location: login.php"); // Change this to your login page
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?></title>
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
        <div class="max-w-2xl mx-auto bg-white shadow-lg  ">
            <img src="barang/<?php echo $product['photo']; ?>" alt="Product Image" class="w-full h-60 object-cover mb-4">
            <h2 class="text-3xl font-bold mb-4 px-3"><?php echo $product['name']; ?></h2>
            <p class="text-gray-800 mb-2 px-3"><?php echo formatRupiah($product['price']); ?></p>
            <h3 class="text-sm text-gray-400 mb-4 px-3"><?php echo $product['store_name']; ?></h3>
            <p class="text-gray-600 px-3"><?php echo $product['description']; ?></p>

            <!-- Add to Cart form -->
            <form method="post" action="">
                <label for="quantity" class="px-3">Quantity:</label>
                <input type="number" id="quantity" class="shadow-sm border px-3 " name="quantity" value="1" min="1">
                <button type="submit" name="add_to_cart" class="bg-blue-500 text-white px-4 py-2 mt-4 mb-4">Add to Cart</button>
            </form>
        </div>
    </div>
</body>

</html>