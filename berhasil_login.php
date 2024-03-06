<?php
session_start();


// Periksa apakah pengguna belum login, jika belum, arahkan ke halaman login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
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
// Ambil data produk dari database
$stmtProducts = $pdo->query("SELECT * FROM products ORDER BY id_produk DESC LIMIT 8");
$products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MS STORE </title>
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


    <div class="bg-white container font-serif mx-auto flex items-center justify-center max-w-screen-2xl h-[453px] behind-navbar mt-[55px]">
        <div class="flex items-center justify-center w-full">
            <!-- Gambar -->
            <div class="w-1/2 flex justify-center mr-3">
                <img src="assets/kisspng-iphone-x-smartphone-hand-holding-smartphone-5a733fd16a99d6 1.png" alt="Top Up Image" class="max-w-full ml-auto h-[441px] rounded-lg">
            </div>
            <!-- Teks -->
            <div class="w-1/2 h-full flex justify-center items-center ">
                <div class="h-96 mr-auto flex justify-center items-center">
                    <p class="text-xl font-serif font-bold w-full text-center">TOP UP SALDO <br> HUBUNGI ADMIN.</p>
                </div>
            </div>
        </div>
    </div>



<!-- Container untuk kartu-kartu -->
<div class="bg-[#A69797] container mx-auto  max-w-screen-2xl  flex flex-wrap justify-center gap-8">
    <div class="card-container w-full  flex flex-wrap justify-center mb-8 gap-8  mt-8 ">
    <!-- Card 1 -->
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4  sm:w-8/12 gap-2 sm:gap-6">

    <?php
// Loop untuk membuat card dari data produk
foreach ($products as $product) {
    echo "<a href='detail_produk.php?id={$product['id_produk']}' class='bg-white shadow-lg h-62 hover:shadow-xl transition-transform transform hover:scale-105'>";
    echo "<div class=''>";
    echo "<img src='barang/" . $product['photo'] . "' alt='Product Image' class='w-full h-40 object-fit'>";
    echo "<h3 class='text-xl font-bold mb-1 px-3'>" . $product['name'] . "</h3>";
    echo "<p class='text-black px-3 mb-1'>" . formatRupiah($product['price']) . "</p>";
    echo "<p class='text-gray-400 px-3 mb-1 text-[10px]'>" . $product['store_name'] . "</p>";
    echo "</div>";
    echo "</a>";
}
?>


    </div>

    </div>
    </div>
</div>






    <!-- Bootstrap JS -->
    <!-- No need for Bootstrap JS since Tailwind doesn't depend on it -->

</body>

</html>
