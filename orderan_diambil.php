<?php
session_start();
include "config.php";

// Pastikan pengguna telah login sebagai kurir
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pastikan ada data yang dikirimkan melalui metode POST
    if (isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status']; // Menggunakan 'status' bukan 'new_status'

        // Update status pesanan dalam tabel orders
        $updateOrdersQuery = $pdo->prepare("UPDATE orders SET status_pesanan = ? WHERE id_order = ?");
        $updateOrdersQuery->execute([$new_status, $order_id]);
        
        // Update status pesanan dalam tabel order_status
        $updateOrderStatusQuery = $pdo->prepare("UPDATE order_status SET status = ? WHERE id_order = ?");
        $updateOrderStatusQuery->execute([$new_status, $order_id]);

        // Redirect kembali ke halaman ini setelah memperbarui status
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['logout'])) {
        // Logout pengguna
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}


// Mendapatkan data orderan yang telah diambil dari tabel order_status
$orderanQuery = $pdo->prepare("SELECT o.id_order, o.username, o.phone, o.total_price, o.order_date, o.status_pesanan, o.address, o.store_address
                                FROM orders o
                                INNER JOIN order_status os ON o.id_order = os.id_order
                                WHERE os.nama_kurir = ?");
$orderanQuery->execute([$_SESSION['username']]);
$orderan_diambil = $orderanQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Orderan yang Telah Diambil</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 min-h-screen text-white w-1/5 py-4 px-6">
            <h1 class="text-2xl font-semibold mb-4">Menu Kurir</h1>
            <ul>
                <li><a href="kurir.php" class="block py-2">Semua Orderan</a></li>
                <li><a href="#" class="block py-2">List Orderan Yang Diambil</a></li>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" name="logout" class="block py-2 text-left w-full">Logout</button>
                </form>
                <!-- Tambahkan menu tambahan jika diperlukan -->
            </ul>
        </div>

        <!-- Konten -->
        <div class="container mx-auto py-8 px-4">
            <h1 class="text-3xl font-semibold mb-4">Daftar Orderan yang Telah Diambil</h1>
            <?php if (empty($orderan_diambil)): ?>
                <p class="text-gray-600">Anda belum mengambil orderan apapun saat ini.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($orderan_diambil as $order): ?>
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-xl font-semibold mb-2">Order ID: <?php echo $order['id_order']; ?></h2>
                            <h2 class="text-gray-600 mb-2">Username: <?php echo $order['username']; ?></h2>
                            <h2 class="text-gray-600 mb-2">Nomor Telepon: <?php echo $order['phone']; ?></h2>
                            <p class="text-gray-600 mb-2">Total Harga: Rp<?php echo number_format($order['total_price'], 0, ',', '.'); ?></p>
                            <p class="text-gray-600 mb-2">Tanggal Pesanan: <?php echo $order['order_date']; ?></p>
                            <p class="text-gray-600 mb-2">Status Pesanan: <?php echo $order['status_pesanan']; ?></p>
                            <p class="text-gray-600 mb-2">Alamat Pembeli: <?php echo $order['address']; ?></p>
                            <p class="text-gray-600 mb-2">Alamat Toko: <?php echo $order['store_address']; ?></p>
                            <!-- Form untuk mengubah status pesanan -->
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                <input type="hidden" name="order_id" value="<?php echo $order['id_order']; ?>">
                                <select name="status" class="border border-gray-300 rounded-md px-3 py-1">
                                    <option value="menunggu kurir" <?php if ($order['status_pesanan'] === 'menunggu kurir') echo 'selected'; ?>>menunggu kurir</option>
                                    <option value="sedang dikirim" <?php if ($order['status_pesanan'] === 'sedang dikirim') echo 'selected'; ?>>sedang dikirim</option>
                                    <option value="sampai ditujuan" <?php if ($order['status_pesanan'] === 'sampai ditujuan') echo 'selected'; ?>>sampai ditujuan</option>
                                    <option value="dikirim balik" <?php if ($order['status_pesanan'] === 'dikirim balik') echo 'selected'; ?>>dikirim balik</option>
                                </select>

                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Ubah Status
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
