<?php
session_start();

require_once "config.php";

function addProduct($pdo, $id_toko, $store_name, $name, $price, $description, $photo, $stock)
{
    $query = "INSERT INTO products (id_toko, store_name, name, price, description, photo, stock) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $statement = $pdo->prepare($query);
    $statement->execute([$id_toko, $store_name, $name, $price, $description, $photo, $stock]);
}

function addStoreInfo($pdo, $username, $storeName, $storeField, $storeAddress)
{
    $queryUserId = "SELECT id FROM tb_user WHERE username = ?";
    $statementUserId = $pdo->prepare($queryUserId);
    $statementUserId->execute([$username]);
    $user = $statementUserId->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['id'];

        $queryStoreInfo = "INSERT INTO store_info (id, username, store_name, store_field, store_address) VALUES (?, ?, ?, ?, ?)";
        $statementStoreInfo = $pdo->prepare($queryStoreInfo);
        $statementStoreInfo->execute([$userId, $username, $storeName, $storeField, $storeAddress]);
    } else {
        echo '<script>alert("User not found.");</script>';
    }
}


// Check if the store information exists
$queryCheckStore = "SELECT * FROM store_info";
$statementCheckStore = $pdo->prepare($queryCheckStore);
$statementCheckStore->execute();
$storeInfo = $statementCheckStore->fetch(PDO::FETCH_ASSOC);


// Check if the store information exists
$queryCheckStore = "SELECT * FROM store_info";
$statementCheckStore = $pdo->prepare($queryCheckStore);
$statementCheckStore->execute();
$storeInfo = $statementCheckStore->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['tambah_info_toko'])) {
    $storeName = $_POST['store_name'];
    $storeField = $_POST['store_field'];
    $storeAddress = $_POST['store_address'];

    $username = $_SESSION['username'];

    addStoreInfo($pdo, $username, $storeName, $storeField, $storeAddress);

    echo '<script>alert("Informasi toko berhasil ditambahkan.");</script>';
    header("Location: toko.php");
    exit();
}

// Proses tambah produk jika form disubmit
if (isset($_POST['tambah_produk'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_description = $_POST['product_description'];
    $product_stock = $_POST['product_stock']; // Assuming you have an input field for stock


    // Assuming you have $username available
    $username = $_SESSION['username'];

    // Fetch id_toko and store_name from store_info based on the username
    $queryStoreInfo = "SELECT id_toko, store_name FROM store_info WHERE username = ?";
    $statementStoreInfo = $pdo->prepare($queryStoreInfo);
    $statementStoreInfo->execute([$username]);
    $storeInfoRow = $statementStoreInfo->fetch(PDO::FETCH_ASSOC);

    // Check if store_info exists for the given username
    if ($storeInfoRow) {
        $id_toko = $storeInfoRow['id_toko'];
        $store_name = $storeInfoRow['store_name'];

        // Initialize $uploadOk
        $uploadOk = 1;
        // Handle file upload only if a file is selected
        if (!empty($_FILES["product_photo"]["name"])) {
            $targetDirectory = "barang/";
            $targetFile = $targetDirectory . basename($_FILES["product_photo"]["name"]);

            // ... (rest of the file upload code)

            if ($uploadOk == 0) {
                echo '<script>alert("Sorry, your file was not uploaded.");</script>';
            } else {
                // If everything is ok, try to upload file
                if (move_uploaded_file($_FILES["product_photo"]["tmp_name"], $targetFile)) {
                    echo '<script>alert("The file ' . htmlspecialchars(basename($_FILES["product_photo"]["name"])) . ' has been uploaded.");</script>';

                    // Add product to the database with the file name
                    addProduct($pdo, $id_toko, $store_name, $product_name, $product_price, $product_description, basename($_FILES["product_photo"]["name"]), $product_stock);

                } else {
                    echo '<script>alert("Sorry, there was an error uploading your file.");</script>';
                }
            }
        } else {
            // If no file is selected, add the product without a photo
            addProduct($pdo, $id_toko, $store_name, $product_name, $product_price, $product_description,$stock, null);

            // Show a JavaScript alert
            echo '<script>alert("Produk berhasil ditambahkan.");</script>';
        }
    } else {
        // Handle the case where store_info doesn't exist for the given username
        echo '<script>alert("User not found.");</script>';
    }
}




// Proses edit produk jika form disubmit
if (isset($_POST['edit_produk'])) {
    $edited_product_id = $_POST['edited_product_id'];
    $edited_product_name = $_POST['edited_product_name'];
    $edited_product_price = $_POST['edited_product_price'];
    $edited_product_description = $_POST['edited_product_description'];
    $edited_product_stock = $_POST['edited_product_stock'];

    $queryUpdateProduct = "UPDATE products SET name = ?, price = ?, description = ?, stock = ? WHERE id_produk = ?";
    $statementUpdateProduct = $pdo->prepare($queryUpdateProduct);
    $statementUpdateProduct->execute([$edited_product_name, $edited_product_price, $edited_product_description, $edited_product_stock, $edited_product_id]);

    echo '<script>alert("Product updated successfully.");</script>';
}



function formatRupiah($price)
{
    return 'Rp' . number_format($price, 0, ',', '.');
}

// Sisipkan file koneksi ke database
include "config.php";

$username = $_SESSION['username'];
$role ='';


// Ambil email dan lokasi foto profil berdasarkan username
$stmt = $pdo->prepare("SELECT role FROM tb_user WHERE username = ?");
$stmt->execute([$username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $role = $row['role'];

}

// Proses logout ketika tombol logout ditekan
if (isset($_POST['logout'])) {
    // Hapus semua data sesi
    session_unset();
    // Hancurkan sesi
    session_destroy();
    // Redirect ke halaman login.php
    header("Location: login.php");
    exit();
}


$query = "SELECT * FROM products";
$statement = $pdo->prepare($query);
$statement->execute();
$products = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MS STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <?php include "navbar.php"; ?>

    <h1 class="text-3xl font-bold mb-4 mt-[80px]">Informasi Toko</h1>

    <?php
    $username = $_SESSION['username'];
    $queryCheckStore = "SELECT * FROM store_info WHERE username = ?";
    $statementCheckStore = $pdo->prepare($queryCheckStore);
    $statementCheckStore->execute([$username]);
    $storeInfo = $statementCheckStore->fetch(PDO::FETCH_ASSOC);

    if (!$storeInfo) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_info_toko'])) {
            $storeName = $_POST['store_name'];
            $storeField = $_POST['store_field'];
            $storeAddress = $_POST['store_address'];

            $username = $_SESSION['username'];

            addStoreInfo($pdo, $username, $storeName, $storeField, $storeAddress);

            echo '<script>alert("Informasi toko berhasil ditambahkan.");</script>';
            header("Location: toko.php");
            exit();
        }
        ?>
        <form method="post" action="" class="mb-8">
            <div class="mb-4">
                <label for="store_name" class="block text-sm font-medium text-gray-700">Nama Toko:</label>
                <input type="text" name="store_name" required class="border rounded px-3 py-2 w-full">
            </div>

            <div class="mb-4">
                <label for="store_field" class="block text-sm font-medium text-gray-700">Bidang Toko:</label>
                <input type="text" name="store_field" required class="border rounded px-3 py-2 w-full">
            </div>

            <div class="mb-4">
                <label for="store_address" class="block text-sm font-medium text-gray-700">Alamat Toko:</label>
                <textarea name="store_address" required class="border rounded px-3 py-2 w-full"></textarea>
            </div>

            <button type="submit" name="tambah_info_toko" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Tambah Informasi Toko</button>
        </form>
    <?php
    } else {
        echo '<p class="mb-8">Toko: ' . $storeInfo['store_name'] . ' | Bidang: ' . $storeInfo['store_field'] . ' | Alamat: ' . $storeInfo['store_address'] . '</p>';
    }
    ?>



    <h2 class="text-2xl mb-4">Tambah Produk</h2>
    <form method="post" action="" enctype="multipart/form-data" class="mb-8">
        <div class="mb-4">
            <label for="product_name" class="block text-sm font-medium text-gray-700">Nama Produk:</label>
            <input type="text" name="product_name" required class="border rounded px-3 py-2 w-full">
        </div>

        <div class="mb-4">
            <label for="product_price" class="block text-sm font-medium text-gray-700">Harga:</label>
            <input type="number" name="product_price" required class="border rounded px-3 py-2 w-full">
        </div>

        <div class="mb-4">
            <label for="product_description" class="block text-sm font-medium text-gray-700">Deskripsi:</label>
            <textarea name="product_description" required class="border rounded px-3 py-2 w-full"></textarea>
        </div>

        <div class="mb-4">
            <label for="product_stock" class="block text-sm font-medium text-gray-700">Stok:</label>
            <input type="number" name="product_stock" required class="border rounded px-3 py-2 w-full">
        </div>

        <div class="mb-4">
            <label for="product_photo" class="block text-sm font-medium text-gray-700">Foto Produk:</label>
            <input type="file" name="product_photo" accept="image/*" required class="border rounded px-3 py-2 w-full">
        </div>



        <button type="submit" name="tambah_produk" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Tambah Produk</button>
    </form>

    <h2 class="text-2xl mb-4">Daftar Produk</h2>
    <?php
        // Fetch products based on the logged-in user's store information
        $username = $_SESSION['username'];
        $queryProducts = "SELECT products.* FROM products JOIN store_info ON products.id_toko = store_info.id_toko WHERE store_info.username = ?";
        $statementProducts = $pdo->prepare($queryProducts);
        $statementProducts->execute([$username]);
        $products = $statementProducts->fetchAll(PDO::FETCH_ASSOC);
        ?>

<div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <?php foreach ($products as $product) : ?>
            <div class="bg-white  shadow-md">
                <?php if ($product['photo']) : ?>
                    <img src="barang/<?= $product['photo']; ?>" alt="<?= $product['name']; ?>" class="w-full h-[120px] fluid">
                <?php endif; ?>
                <div class="p-6">

                    <!-- Edit Product Form -->
                    <form method="post" action="">
                        <input type="hidden" name="edited_product_id" value="<?= $product['id_produk']; ?>">
                        <label for="edited_product_name" class="block text-sm font-medium text-gray-700">Edit Nama Produk:</label>
                        <input type="text" name="edited_product_name" value="<?= $product['name']; ?>" required class="border rounded px-3 py-2 w-full">

                        <label for="edited_product_price" class="block text-sm font-medium text-gray-700">Edit Harga:</label>
                        <input type="number" name="edited_product_price" value="<?= $product['price']; ?>" required class="border rounded px-3 py-2 w-full">

                        <label for="edited_product_description" class="block text-sm font-medium text-gray-700">Edit Deskripsi:</label>
                        <textarea name="edited_product_description" required class="border rounded px-3 py-2 w-full"><?= $product['description']; ?></textarea>

                        <label for="edited_product_stock" class="block text-sm font-medium text-gray-700">Edit Stok:</label>
                        <input type="number" name="edited_product_stock" value="<?= $product['stock']; ?>" required class="border rounded px-3 py-2 w-full">

                        <button type="submit" name="edit_produk" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded mt-2">Update Produk</button>
                    </form>

                </div>
            </div>
        <?php endforeach; ?>
    </div>


    </div>

</body>

</html>
