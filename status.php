<?php
session_start();
include "config.php";

// Pastikan pengguna telah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna yang sedang login
$username = $_SESSION['username'];

// Panggil fungsi untuk mengambil status pesanan
$statuses = getStatuses($pdo, $username);

// Fungsi untuk mendapatkan status pesanan
function getStatuses($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk memperbarui status pesanan
function updateOrderStatus($pdo, $order_id, $new_status, $username) {
    // Update status pesanan dalam tabel orders
    $updateOrdersQuery = $pdo->prepare("UPDATE orders SET status_pesanan = ? WHERE id_order = ?");
    $updateOrdersQuery->execute([$new_status, $order_id]);

    // Update status pesanan dalam tabel order_status
    $updateOrderStatusQuery = $pdo->prepare("UPDATE order_status SET status = ? WHERE id_order = ?");
    $updateOrderStatusQuery->execute([$new_status, $order_id]);

    // Jika status pesanan adalah "diterima pembeli", lakukan penyesuaian saldo
    if ($new_status === 'diterima pembeli') {
        adjustBalance($pdo, $order_id, $username);
    }

    // Redirect kembali ke halaman ini setelah memperbarui status
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fungsi untuk menyesuaikan saldo
function adjustBalance($pdo, $order_id, $username) {
    // Ambil total harga pesanan
    $getTotalPriceQuery = $pdo->prepare("SELECT total_price, seller_username FROM orders WHERE id_order = ?");
    $getTotalPriceQuery->execute([$order_id]);
    $order = $getTotalPriceQuery->fetch(PDO::FETCH_ASSOC);
    $total_price = $order['total_price'];
    $seller_username = $order['seller_username'];

    // Kurangi saldo pembeli
    $getBuyerBalanceQuery = $pdo->prepare("SELECT saldo FROM tb_user WHERE username = ?");
    $getBuyerBalanceQuery->execute([$username]);
    $buyer = $getBuyerBalanceQuery->fetch(PDO::FETCH_ASSOC);
    $buyerBalance = $buyer['saldo'];
    $newBuyerBalance = $buyerBalance - $total_price;

    // Tambah saldo penjual
    $getSellerBalanceQuery = $pdo->prepare("SELECT saldo FROM tb_user WHERE username = ?");
    $getSellerBalanceQuery->execute([$seller_username]);
    $seller = $getSellerBalanceQuery->fetch(PDO::FETCH_ASSOC);
    $sellerBalance = $seller['saldo'];
    $newSellerBalance = $sellerBalance + $total_price;

    // Update saldo pembeli
    $updateBuyerBalanceQuery = $pdo->prepare("UPDATE tb_user SET saldo = ? WHERE username = ?");
    $updateBuyerBalanceQuery->execute([$newBuyerBalance, $username]);

    // Update saldo penjual
    $updateSellerBalanceQuery = $pdo->prepare("UPDATE tb_user SET saldo = ? WHERE username = ?");
    $updateSellerBalanceQuery->execute([$newSellerBalance, $seller_username]);
}

// Proses form jika metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pastikan ada data yang dikirimkan melalui metode POST
    if (isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status']; // Menggunakan 'status' bukan 'new_status'

        // Perbarui status pesanan
        updateOrderStatus($pdo, $order_id, $new_status, $username);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
    <div class="flex justify-center items-center h-screen">
        <div>
            <h1 class="text-2xl font-bold mb-4">Status Pesanan</h1>
            <?php if (empty($statuses)) : ?>
                <p class="text-gray-600">Tidak ada data status pesanan.</p>
            <?php else : ?>
                <div class="overflow-x-auto shadow-lg">
                    <table class="table-auto border-collapse border  border-gray-300">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="px-4 py-2">Nama Barang</th>
                                <th class="px-4 py-2">Status Pesanan</th>
                                <th class="px-4 py-2">Nama Kurir</th>
                                <th class="px-4 py-2">Total Harga</th>
                                <th class="px-4 py-2">Tanggal Status</th>
                                <th class="px-4 py-2">Aksi</th> <!-- Tambah kolom untuk aksi -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statuses as $status) : ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo $status['name']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $status['status_pesanan']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $status['nama_kurir']; ?></td>
                                    <td class="border px-4 py-2">Rp <?php echo number_format($status['total_price'], 0, ',', '.'); ?></td>
                                    <td class="border px-4 py-2"><?php echo $status['order_date']; ?></td>
                                    <td class="border px-4 py-2">
                                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                            <input type="hidden" name="order_id" value="<?php echo $status['id_order']; ?>">
                                            <?php if ($status['status_pesanan'] !== 'diterima pembeli') : ?>
                                                <button type="submit" name="status" value="diterima pembeli" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                                    Sudah Diterima
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
