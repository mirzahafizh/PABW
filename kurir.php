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
function orderHasBeenTaken($orderId) {
    global $pdo;
    
    $checkQuery = $pdo->prepare("SELECT COUNT(*) AS count FROM order_status WHERE id_order = ? AND status = 'Sedang Diambil'");
    $checkQuery->execute([$orderId]);
    $result = $checkQuery->fetch(PDO::FETCH_ASSOC);

    return $result['count'] > 0;
}

// Jika tombol "Ambil Orderan" ditekan
if(isset($_POST['ambil_orderan'])) {
    $order_id = $_POST['order_id'];
    $username = $_SESSION['username'];

    // Pemeriksaan apakah ID pesanan yang akan dimasukkan sudah ada di tabel "orders"
    $checkOrderQuery = $pdo->prepare("SELECT COUNT(*) AS count FROM orders WHERE id_order = ?");
    $checkOrderQuery->execute([$order_id]);
    $orderExists = $checkOrderQuery->fetch(PDO::FETCH_ASSOC)['count'];

    if ($orderExists > 0) {
        // Tambahkan data ke tabel order_status
        $insertQuery = $pdo->prepare("INSERT INTO order_status (id_order, nama_kurir, status, status_date) VALUES (?, ?, 'Sedang Diambil', NOW())");
        $insertQuery->execute([$order_id, $username]);

        // Update status pesanan menjadi "Sedang Dikirim" pada tabel orders
        $updateQuery = $pdo->prepare("UPDATE orders SET status_pesanan = 'Sedang Dikirim' WHERE id_order = ?");
        $updateQuery->execute([$order_id]);
    }
}

// Mendapatkan data orderan dari tabel orders
$ordersQuery = $pdo->query("SELECT * FROM orders");
$orders = $ordersQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Kurir</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex">
    <div class="bg-gray-800 min-h-screen text-white w-1/5 py-4 px-6">
            <h1 class="text-2xl font-semibold mb-4">Menu Kurir</h1>
            <ul>
                <li><a href="kurir.php" class="block py-2">Semua Orderan</a></li>
                <li><a href="orderan_diambil.php" class="block py-2">List Orderan Yang Diambil</a></li>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <button type="submit" name="logout" class="block py-2    text-left w-full">Logout</button>
                </form>
                <!-- Tambahkan menu tambahan jika diperlukan -->
            </ul>
        </div>

        <!-- Konten -->
        <div class="container mx-auto py-8 px-4">
            <h1 class="text-3xl font-semibold mb-4">Semua Orderan</h1>
            <?php if (empty($orders)): ?>
                <p class="text-gray-600">Tidak ada orderan saat ini.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-xl font-semibold mb-2">Order ID: <?php echo $order['id_order']; ?></h2>
                            <h2 class="text-gray-600 mb-2">Username: <?php echo $order['username']; ?></h2>
                            <h2 class="text-gray-600 mb-2">Nomor Telepon: <?php echo $order['phone']; ?></h2>
                            <p class="text-gray-600 mb-2">Total Harga: Rp<?php echo number_format($order['total_price'], 0, ',', '.'); ?></p>
                            <p class="text-gray-600 mb-2">Tanggal Pesanan: <?php echo $order['order_date']; ?></p>
                            <p class="text-gray-600 mb-2">Status Pesanan: <?php echo $order['status_pesanan']; ?></p>
                            <p class="text-gray-600 mb-2">Alamat Pembeli: <?php echo $order['address']; ?></p>
                            <p class="text-gray-600 mb-2">Alamat Toko: <?php echo $order['store_address']; ?></p>
                            <!-- Tampilkan tombol Ambil Orderan hanya jika status_pesanan masih menunggu -->
                            <?php if ($order['status_pesanan'] === 'menunggu kurir'): ?>
                                <!-- Tampilkan tombol Ambil Orderan hanya jika belum diambil oleh kurir -->
                                <?php if (!orderHasBeenTaken($order['id_order'])): ?>
                                    <form action="kurir.php" method="post">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id_order']; ?>">
                                        <button type="submit" name="ambil_orderan" class="bg-blue-500 text-white py-2 px-4 rounded">Ambil Orderan</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
