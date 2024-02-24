<?php
session_start();

require_once "config.php";

function addProduct($pdo, $name, $price, $description, $photo)
{
    $query = "INSERT INTO products (name, price, description, photo) VALUES (?, ?, ?, ?)";
    $statement = $pdo->prepare($query);
    $statement->execute([$name, $price, $description, $photo]);
}

function addStoreInfo($pdo, $storeName, $storeField)
{
    $query = "INSERT INTO store_info (store_name, store_field) VALUES (?, ?)";
    $statement = $pdo->prepare($query);
    $statement->execute([$storeName, $storeField]);
}

// Check if the store information exists
$queryCheckStore = "SELECT * FROM store_info";
$statementCheckStore = $pdo->prepare($queryCheckStore);
$statementCheckStore->execute();
$storeInfo = $statementCheckStore->fetch(PDO::FETCH_ASSOC);

// Proses tambah informasi toko jika form disubmit
if (isset($_POST['tambah_info_toko'])) {
    $storeName = $_POST['store_name'];
    $storeField = $_POST['store_field'];

    // Add store information to the database
    addStoreInfo($pdo, $storeName, $storeField);

    // Refresh the page to reflect the changes
    header("Location: toko.php");
    exit();
}

// Proses tambah produk jika form disubmit
if (isset($_POST['tambah_produk'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_description = $_POST['product_description'];

    // Handle file upload only if a file is selected
    if (!empty($_FILES["product_photo"]["name"])) {
        $targetDirectory = "barang/";
        $targetFile = $targetDirectory . basename($_FILES["product_photo"]["name"]);
        
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is an actual image
        $check = getimagesize($_FILES["product_photo"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($targetFile)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["product_photo"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        $allowedFormats = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowedFormats)) {
            echo "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            // If everything is ok, try to upload file
            if (move_uploaded_file($_FILES["product_photo"]["tmp_name"], $targetFile)) {
                echo "The file " . htmlspecialchars(basename($_FILES["product_photo"]["name"])) . " has been uploaded.";

                // Add product to the database with the file name
                addProduct($pdo, $product_name, $product_price, $product_description, basename($_FILES["product_photo"]["name"]));
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        // If no file is selected, add the product without a photo
        addProduct($pdo, $product_name, $product_price, $product_description, null);
    }
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
    <title>Toko Page</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 p-6">
    <?php include "navbar.php"; ?>

    <h1 class="text-3xl font-bold mb-4 mt-[60px]">Informasi Toko</h1>
    

    <?php if (!$storeInfo) : ?>
        <!-- Form for adding store information if it doesn't exist -->
        <form method="post" action="" class="mb-8">
            <div class="mb-4">
                <label for="store_name" class="block text-sm font-medium text-gray-700">Nama Toko:</label>
                <input type="text" name="store_name" required class="border rounded px-3 py-2 w-full">
            </div>

            <div class="mb-4">
                <label for="store_field" class="block text-sm font-medium text-gray-700">Bidang Toko:</label>
                <input type="text" name="store_field" required class="border rounded px-3 py-2 w-full">
            </div>

            <button type="submit" name="tambah_info_toko" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Tambah Informasi Toko</button>
        </form>
    <?php else : ?>
        <p class="mb-8">Toko: <?= $storeInfo['store_name']; ?> | Bidang: <?= $storeInfo['store_field']; ?></p>
    <?php endif; ?>

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
            <label for="product_photo" class="block text-sm font-medium text-gray-700">Foto Produk:</label>
            <input type="file" name="product_photo" accept="image/*" required class="border rounded px-3 py-2 w-full">
        </div>

        <button type="submit" name="tambah_produk" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Tambah Produk</button>
    </form>

    <h2 class="text-2xl mb-4">Daftar Produk</h2>
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <?php foreach ($products as $product) : ?>
            <div class="bg-white rounded-lg shadow-md">
                <?php if ($product['photo']) : ?>
                    <img src="barang/<?= $product['photo']; ?>" alt="<?= $product['name']; ?>" class="w-full h-[120px] fluid">
                <?php endif; ?>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2"><?= $product['name']; ?></h3>
                    <p class="text-gray-800 "><?= formatRupiah($product['price']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>
