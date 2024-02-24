<?php
session_start();

// Periksa jika pengguna belum login, maka arahkan ke halaman login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Sisipkan file koneksi ke database
include "config.php";

// Ambil informasi pengguna dari sesi
$username = $_SESSION['username'];
$phone ='';
$email = '';
$fullname = '';
$profile_image = ''; // Tambahkan variabel untuk menyimpan lokasi file foto profil

// Ambil email dan lokasi foto profil berdasarkan username
$stmt = $pdo->prepare("SELECT email, profile_image, phone, fullname FROM tb_user WHERE username = ?");
$stmt->execute([$username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $email = $row['email'];
    $profile_image = $row['profile_image']; // Simpan lokasi file foto profil ke dalam variabel
    $phone = $row['phone'];
    $fullname = $row['fullname'];
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
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
<?php include "navbar.php"; ?>

    <div class="container mx-auto py-8 mt-20">
        <div class="max-w-md mx-auto bg-white p-8 rounded-md shadow-md">
            <!-- Foto profil -->
            <div class="text-center">
                <form action="" method="post" enctype="multipart/form-data">
                    <?php if ($profile_image): ?>
                        <img src="<?php echo $profile_image; ?>" alt="User Avatar" class="w-24 h-24 mx-auto rounded-full mb-4">
                    <?php else: ?>
                        <img src="assets/gg_profile.png" alt="User Avatar" class="w-24 h-24 mx-auto rounded-full mb-4">
                    <?php endif; ?>
                    <input type="file" name="profile_image" accept="image/*" class="block mx-auto mb-4">
                    <h2 class="text-xl font-semibold mb-2"><?php echo $fullname; ?></h2>
                    <p class="text-gray-600 mb-4"><?php echo $email; ?> <a href="ubah_email.php">(Ubah)</a></p>
                    <p class="text-gray-600 mb-4"><?php echo $phone; ?> <a href="ubah_telepon.php">(Ubah)</a></p>
                </form>
            </div>
        </div>
    </div>
    <script>

        
    </script>
</body>

</html>
