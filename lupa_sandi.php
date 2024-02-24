<?php
session_start();

// Load PHPMailer library
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fungsi untuk membuat koneksi ke database
function connectDB() {
    $servername = "localhost";
    $username = "root"; // Ganti dengan username MySQL Anda
    $password = ""; // Ganti dengan password MySQL Anda
    $dbname = "db_ecommerce"; // Ganti dengan nama database Anda

    // Buat koneksi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Koneksi ke database gagal: " . $conn->connect_error);
    }

    return $conn;
}

// Variabel untuk menyimpan status formulir
$email = $resetCode = "";

// Variabel untuk melacak langkah saat ini
$currentStep = isset($_POST['current_step']) ? $_POST['current_step'] : 'email';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($currentStep === 'email') {
        $email = $_POST['email'];

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Alamat email tidak valid');</script>";
            exit;
        }

        // Periksa apakah email ada dalam database
        $conn = connectDB();
        $email = $conn->real_escape_string($email);
        $query = "SELECT * FROM tb_user WHERE email = '$email'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            // Email ditemukan, lanjutkan dengan mengirim kode verifikasi
            $emailExists = true;

            // Generate random 6-digit code for password reset
            $resetCode = sprintf('%06d', mt_rand(0, 999999));

            // Simpan kode verifikasi dalam sesi
            $_SESSION['reset_code'] = $resetCode;

            // Kirim email dengan kode reset
            $mail = new PHPMailer(true);

            try {
                // Konfigurasi pengiriman email
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'mirzaakunyt1@gmail.com'; // Alamat Gmail Anda
                $mail->Password = 'ikzdrgntkfxukmfn'; // Password Gmail Anda
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                //Penerima email
                $mail->setFrom('mirzaakunyt1@gmail.com', 'PABW Kelompok');
                $mail->addAddress($email);

                //Content
                $mail->isHTML(true);
                $mail->Subject = 'Instruksi Reset Password';
                $mail->Body = "Halo,<br><br>Ini adalah kode reset password Anda: <strong>$resetCode</strong>.";

                $mail->send();
                $currentStep = 'verification';
            } catch (Exception $e) {
                echo "<script>alert('Gagal mengirim email: {$mail->ErrorInfo}');</script>";
            }
        } else {
            // Email tidak ditemukan di database
            echo "<script>alert('Alamat email tidak terdaftar');</script>";
        }

        // Tutup koneksi database
        $conn->close();
    } elseif ($currentStep === 'verification') {
       // Handle verification code submission and verification
       $verificationCode = $_POST['verificationCode'];
       
       // Memeriksa apakah kode verifikasi yang dimasukkan oleh pengguna cocok dengan kode yang disimpan dalam sesi
       if (!isset($_SESSION['reset_code']) || $verificationCode !== $_SESSION['reset_code']) {
           echo "<script>alert('Kode verifikasi tidak valid');</script>";
           // Atau tambahkan logika lain sesuai kebutuhan Anda, seperti mengarahkan pengguna kembali ke halaman verifikasi
       } else {
           // Kode verifikasi valid, lanjutkan ke langkah reset
           $currentStep = 'reset';
           
           // Hapus kode verifikasi dari sesi setelah verifikasi berhasil
           unset($_SESSION['reset_code']);
       }
    } elseif ($currentStep === 'reset') {
        // Handle password reset
        $email = $_POST['email']; // Dapatkan email dari formulir
        $password = $_POST['password']; // Dapatkan password baru dari formulir
        $confirmPassword = $_POST['confirmPassword']; // Dapatkan konfirmasi password baru dari formulir
    
        // Periksa apakah password cocok
        if ($password !== $confirmPassword) {
            echo "<script>alert('Password dan konfirmasi password tidak cocok');</script>";
        } else {
            // Buat koneksi ke database
            $conn = connectDB();
                
            // Escape input untuk menghindari SQL injection
            $email = $conn->real_escape_string($email);
            $password = $conn->real_escape_string($password);

            // Panggil prosedur tersimpan
            $stmt = $conn->prepare("CALL reset_password(?, ?)");
            $stmt->bind_param("ss", $email, $password); // Bind email and password parameters
            $stmt->execute();

            // Periksa apakah prosedur dijalankan tanpa kesalahan
            if ($stmt->error) {
                // Jika ada kesalahan dalam menjalankan prosedur, tampilkan pesan kesalahan
                echo "<script>alert('Gagal memperbarui password: " . $stmt->error . "');</script>";
            } else {
                // Jika pembaruan database berhasil, tampilkan pesan ke pengguna
                echo "<script>alert('Password berhasil direset');</script>";

                // Arahkan pengguna ke halaman login.php
                header("Location: login.php");
                exit(); // Pastikan untuk keluar dari skrip setelah melakukan pengalihan header
            }
                
            // Tutup koneksi database
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="lupasandi.css">
</head>

<body class="bg-gradient-to-b from-purple-700 to-purple-300 flex justify-center items-center h-screen">
    <form id="passwordResetForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
        class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4 max-w-lg">
        <h2 class="text-2xl font-semibold mb-4">Lupa Password</h2>
        <?php if ($currentStep === 'email') : ?>
        <p class="mb-4 ">Masukkan alamat email Anda untuk mereset password:</p>
        <input type="email" name="email" placeholder="Alamat Email" required
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
        <?php elseif ($currentStep === 'verification') : ?>
        <p class="mb-4">Masukkan kode verifikasi yang telah dikirim ke email Anda:</p>
        <input type="text" name="verificationCode" placeholder="Kode Verifikasi" required
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
        <?php elseif ($currentStep === 'reset') : ?>
        <p class="mb-4">Masukkan password baru:</p>
        <input type="password" name="password" placeholder="Password Baru" required
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
        <p class="mb-4">Konfirmasi password baru:</p>
        <input type="password" name="confirmPassword" placeholder="Konfirmasi Password Baru" required
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mb-4">
        <input type="hidden" name="action" value="update_password">
        <?php endif; ?>
        <input type="hidden" name="current_step" value="<?php echo $currentStep; ?>">
        <input type="submit" value="<?php echo $currentStep === 'reset' ? 'Reset Password' : 'Kirim'; ?>"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold w-full py-2 px-4 rounded focus:outline-none focus:shadow-outline cursor-pointer">
    </form>
</body>

</html>
