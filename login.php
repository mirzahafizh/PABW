<?php
session_start();

// Lakukan koneksi ke database, misalnya dengan menggunakan mysqli atau PDO

// Konfigurasi koneksi
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_ecommerce";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mengecek apakah pengguna telah mengirimkan formulir login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil nilai yang dikirimkan oleh formulir
    $username = $_POST["username"];
    $password = $_POST["password"];
    $remember_me = isset($_POST["remember_me"]) ? true : false;

    // Query untuk memeriksa apakah informasi login yang diberikan cocok dengan data di database
    $sql = "SELECT * FROM tb_user WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

 // ...

if ($result->num_rows == 1) {
    // Login berhasil
    $_SESSION["username"] = $username;

    // Set cookie "Remember Me" jika dicentang
    if ($remember_me) {
        $token = bin2hex(random_bytes(16)); // Hasilkan token acak
        
        // Simpan token di database untuk identifikasi pengguna
        $sql = "UPDATE tb_user SET remember_token = '$token' WHERE username = '$username'";
        $conn->query($sql);

        // Set cookie pada sisi klien
        setcookie("remember_token", $token, time() + (30 * 24 * 60 * 60), "/"); // 30 hari kedaluwarsa
    }

    header("Location: berhasil_login.php"); // Redirect ke halaman utama setelah login berhasil
    exit();
} else {
    // Login gagal
    $error_message = "Username atau password salah. Silakan coba lagi.";
}

// ...
}
// Tutup koneksi ke database
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Add your custom styles here */
    </style>
    <script>
        // Function untuk menampilkan pesan kesalahan dalam bentuk popup
        function showError(errorMessage) {
            alert(errorMessage);
        }
    </script>
</head>

<body class="bg-gradient-to-b from-purple-700 to-purple-300 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-semibold text-center mb-6">Login</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="loginForm"
            class="space-y-4">
            <div class="flex flex-col space-y-1">
                <input type="text" name="username" id="username" placeholder="Username"
                    class="border border-gray-300 px-4 py-2 rounded-lg focus:outline-none focus:border-purple-500"
                    required>
            </div>
            <div class="flex flex-col space-y-1">
                <input type="password" name="password" id="password" placeholder="Password"
                    class="border border-gray-300 px-4 py-2 rounded-lg focus:outline-none focus:border-purple-500"
                    required>
            </div>
            <div class="flex items-center justify-between">
                <label for="remember_me" class="flex items-center">
                    <input type="checkbox" name="remember_me" id="remember_me" class="mr-2">
                    <span class="text-sm">Remember Me</span>
                </label>
                <a href="lupa_sandi.php" class="text-sm text-purple-600">Lupa Sandi?</a>
            </div>
            <button type="submit"
                class="bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 w-full">
                Login
            </button>
            <div class="text-center">
                <a href="registrasi.php" class="text-sm text-purple-600">Belum punya akun? Daftar disini</a>
            </div>
        </form>
        <?php
        // Menampilkan pesan kesalahan jika login gagal
        if (isset($error_message)) {
            // Memanggil JavaScript untuk menampilkan pesan kesalahan dalam bentuk popup
            echo '<script>showError("' . $error_message . '");</script>';
        }
        ?>
    </div>
</body>

<!-- ... (remaining code) ... -->

</html>
