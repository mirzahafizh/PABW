<?php
session_start();
include '../config.php';

// Proses logout ketika tombol logout ditekan
if (isset($_POST['logout'])) {
    // Hapus semua data sesi
    session_unset();
    // Hancurkan sesi
    session_destroy();
    // Redirect ke halaman login.php
    header("Location: ../login.php");
    exit();
}


// Handle form submission for updating user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $username = $_POST['edit_username'];
    $fullname = $_POST['edit_fullname'];
    $email = $_POST['edit_email'];
    // Add more fields as needed

    // Update user data in the database
    $stmt = $pdo->prepare("UPDATE tb_user SET fullname = ?, email = ? WHERE username = ?");
    $stmt->execute([$fullname, $email, $username]);

    // Redirect back to the daftar_user page after update
    header("Location: admin.php?action=daftar_user");
    exit();
}
// Ambil data semua user
$users = getUsers($pdo);

// Fungsi untuk mengambil data semua user
function getUsers($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM tb_user");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menghapus user
function deleteUser($pdo, $username) {
    $stmt = $pdo->prepare("DELETE FROM tb_user WHERE username = ?");
    $stmt->execute([$username]);
}

// Proses form delete user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $username = $_POST['username'];
    deleteUser($pdo, $username);
    header("Location:  admin.php?action=daftar_user");
    exit();
}
// Fungsi untuk menambahkan saldo
function addBalance($pdo, $username, $amount) {
    // Ambil saldo user
    $stmt = $pdo->prepare("SELECT saldo FROM tb_user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentBalance = $user['saldo'];

    // Tambahkan saldo baru
    $newBalance = $currentBalance + $amount;

    // Update saldo user
    $stmt = $pdo->prepare("UPDATE tb_user SET saldo = ? WHERE username = ?");
    $stmt->execute([$newBalance, $username]);
}

// Proses form tambah saldo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_balance'])) {
    $username = $_POST['username'];
    $amount = $_POST['amount'];
    addBalance($pdo, $username, $amount);
    header("Location: admin.php");
    exit();
}

// Proses form tambah user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['new_fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['new_phone']);
    $email = mysqli_real_escape_string($conn, $_POST['new_email']);
    $username = mysqli_real_escape_string($conn, $_POST['new_username']);
    $password = $_POST['new_password'];
    $role = $_POST['new_role'];

    // Query untuk memeriksa apakah email, nomor telepon, dan username sudah terdaftar sebelumnya
    $check_email_query = "SELECT * FROM tb_user WHERE email = '$email'";
    $check_phone_query = "SELECT * FROM tb_user WHERE phone = '$phone'";
    $check_username_query = "SELECT * FROM tb_user WHERE username = '$username'";

    $email_result = mysqli_query($conn, $check_email_query);
    $phone_result = mysqli_query($conn, $check_phone_query);
    $username_result = mysqli_query($conn, $check_username_query);

    if (mysqli_num_rows($email_result) > 0 || mysqli_num_rows($phone_result) > 0 || mysqli_num_rows($username_result) > 0) {
        echo "<script>alert('Email, nomor telepon, atau username sudah terdaftar. Silakan gunakan yang lain!')</script>";
    } else {
        // Query untuk menambahkan data pengguna baru ke database
        $sql = "INSERT INTO tb_user (fullname, email, phone, username, password, role) VALUES ('$fullname', '$email', '$phone', '$username', '$password', '$role')";

        // Jalankan query
        if (mysqli_query($conn, $sql)) {
            // Jika pendaftaran berhasil, arahkan pengguna ke halaman berhasil_login.php
            header("Location: admin.php?action=daftar_user");
            exit();
        } else {
            // Jika terjadi kesalahan, tampilkan pesan error
            echo "<script>alert('Registrasi gagal. Silakan coba lagi!')</script>";
        }
    }
}



// Fungsi untuk mengambil data semua barang
function getItems($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM products");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Proses form update user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $username = $_POST['edit_username'];
    $fullname = $_POST['edit_fullname'];
    $email = $_POST['edit_email'];
    $phone = $_POST['edit_phone'];
    $role = $_POST['edit_role'];
    $address = $_POST['edit_address'];
    $saldo = $_POST['edit_saldo'];

    // Update data pengguna di database
    $stmt = $pdo->prepare("UPDATE tb_user SET fullname = ?, email = ?, phone = ?, role = ?, address = ?, saldo = ? WHERE username = ?");
    $stmt->execute([$fullname, $email, $phone, $role, $address, $saldo, $username]);

    // Redirect kembali ke halaman admin setelah update
    header("Location: admin.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="font-sans">

<div class="flex">
<!-- Sidebar -->
<div class="flex flex-col justify-between bg-gray-800 text-white w-1/5 min-h-screen max-h-full py-6 px-4">
        <div>
            <h1 class="text-[34px] font-serif text-bold">MS STORE</h1>
            <a href="?action=daftar_user" class="block py-2 px-4 text-white hover:bg-gray-700">Daftar User</a>
            <a href="?action=daftar_barang" class="block py-2 px-4 text-white hover:bg-gray-700">Daftar Barang</a>
            <a href="?action=tambah_user" class="block py-2 px-4 text-white hover:bg-gray-700">Tambah User</a>
        </div>
        <!-- Logout Button -->
        <form method="post" action="" class="mt-auto">
            <button type="submit" name="logout" class="block py-2 px-4 w-full text-white bg-red-500 hover:bg-red-700">Logout</button>
        </form>
    </div>


    <!-- Content -->
    <div class="w-4/5 p-8">
        <!-- Content goes here -->
        <h2 class="text-2xl font-bold mb-4">Welcome to Admin Panel</h2>
        <?php
        // Handle different actions based on the selected menu item
        if (isset($_GET['action'])) {
            $action = $_GET['action'];

            switch ($action) {
                case 'daftar_user':
                    include 'daftar_user.php'; // You can create daftar_user.php for listing and managing users
                    break;
                case 'daftar_barang':
                    include 'daftar_barang.php'; // You can create daftar_barang.php for listing products
                    break;
                case 'tambah_user':
                    include 'tambah_user.php'; // You can create tambah_user.php for adding new users
                    break;
                case 'tambah_saldo':
                    include 'tambah_saldo.php'; // You can create tambah_saldo.php for adding balance to users
                    break;
                case 'edit_user':
                    include 'edit_user.php'; // You can create tambah_saldo.php for adding balance to users
                    break;         
                default:
                    // Default content if no action is specified
                    echo "Select a menu item from the sidebar.";
            }
        } else {
            // Default content if no action is specified
            echo "Select a menu item from the sidebar.";
        }
        ?>
    </div>
</div>

</body>
</html>
