<?php
session_start();
include 'config.php';

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

// Query untuk mengambil data daftar user
$sqlUser = "SELECT * FROM tb_user";
$resultUser = $conn->query($sqlUser);

// Query untuk mengambil data daftar barang
$sqlBarang = "SELECT * FROM products";
$resultBarang = $conn->query($sqlBarang);

// Proses delete user ketika tombol delete ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['id'];

    // Query untuk menghapus user berdasarkan user_id
    $deleteSql = "DELETE FROM tb_user WHERE id = $user_id";

    if ($conn->query($deleteSql) === TRUE) {
        echo "User berhasil dihapus";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin Page</title>
</head>

<body class="bg-gray-200">

    <!-- Sidebar -->
    <div class="flex h-full w-[1520px] bg-gray-800 text-white">
        <div class="flex-shrink-0 w-64 bg-gray-700">
            <div class="p-4">
                <h2 class="text-2xl font-bold">Admin Panel</h2>
            </div>

            <nav class="mt-4">
                <ul>
                    <li class="mb-2">
                        <a href="berhasil_login.php" class="block p-2 bg-gray-600 hover:bg-gray-500">Dashboard</a>
                    </li>
                    <li class="mb-2">
                        <a href="admin.php?page=daftar_user" class="block p-2 bg-gray-600 hover:bg-gray-500">Daftar User</a>
                    </li>
                    <li class="mb-2">
                        <a href="admin.php?page=daftar_barang" class="block p-2 bg-gray-600 hover:bg-gray-500">Daftar Barang</a>
                    </li>
                    <li class="mb-2">
                        <a href="admin.php?page=tambah_user" class="block p-2 bg-gray-600 hover:bg-gray-500">Tambah User</a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Content -->
        <div class="flex-1 p-8 min-h-screen ">
            <?php
            // Logika kondisional untuk menentukan konten yang akan ditampilkan
            if (isset($_GET['page']) && $_GET['page'] === 'dashboard') {
                // Tampilkan konten dashboard
                echo "<h1 class='text-3xl font-bold mb-4'>Dashboard</h1>";
                // ... Isi dengan konten dashboard ...
                echo "<p>Ini adalah halaman dashboard.</p>";
            } elseif (isset($_GET['page']) && $_GET['page'] === 'daftar_user') {
 // Tabel untuk menampilkan daftar user
                echo "<h1 class='text-3xl font-bold mb-4'>Daftar Pengguna</h1>";

                echo "<table class='min-w-full bg-white border text-black border-gray-300'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th class='py-2 px-4 border'>Full Name</th>";
                echo "<th class='py-2 px-4 border'>Email</th>";
                echo "<th class='py-2 px-4 border'>Phone</th>";
                echo "<th class='py-2 px-4 border'>Username</th>";
                echo "<th class='py-2 px-4 border'>Role</th>";
                echo "<th class='py-2 px-4 border'>Actions</th>"; // Actions column
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";

                if ($resultUser->num_rows > 0) {
                    while ($row = $resultUser->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='py-2 px-4 border'>" . $row["fullname"] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row["email"] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row["phone"] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row["username"] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row["role"] . "</td>";
                        echo "<td class='py-2 px-4 border'>";

                        // Action Buttons Container
                        echo "<div class='flex border border-black mx-auto'>";

                        // Edit Button
                        echo "<form method='post' action='edit_user.php' class='w-full'>";
                        echo "<input type='hidden' name='id' value='" . $row["id"] . "'>";
                        echo "<button type='submit' class='bg-blue-500 text-white px-4 py-2 rounded-md mr-[-30px] w-[100px]'>Edit</button>";
                        echo "</form>";

                        // Ubah bagian tombol delete
                        echo "<form method='post' onsubmit='return confirmDelete()'>";
                        echo "<input type='hidden' name='id' value='" . $row["id"] . "'>";
                        echo "<button type='submit' name='delete_user' class='bg-red-500 text-white px-4 py-2 rounded-md w-[100px]'>Delete</button>";
                        echo "</form>";

                        echo "</div>"; // Close Action Buttons Container


                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='py-2 px-4 border text-center'>No users found</td></tr>";
                }
                echo "</tbody>";
                echo "</table>";


            } elseif (isset($_GET['page']) && $_GET['page'] === 'daftar_barang') {
                // Tampilkan konten daftar barang
                echo "<h1 class='text-3xl font-bold mb-4'>Daftar Barang</h1>";

                // Tabel untuk menampilkan daftar barang
                echo "<table class='min-w-full bg-white border text-black border-gray-300'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th class='py-2 px-4 border-b'>Nama Barang</th>";
                echo "<th class='py-2 px-4 border-b'>Harga</th>";
                echo "<th class='py-2 px-4 border-b'>Deskripsi</th>";
                echo "<th class='py-2 px-4 border-b'>Foto Barang</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                // Loop through hasil query barang dan tampilkan dalam tabel
                if ($resultBarang->num_rows > 0) {
                    while ($row = $resultBarang->fetch_assoc()) {
                        echo "<tr>";
                        // Pastikan menggunakan nama kolom yang benar sesuai dengan struktur tabel
                        echo "<td class='py-2 px-4 border-b'>" . $row["name"] . "</td>";
                        echo "<td class='py-2 px-4 border-b'>" . $row["price"] . "</td>";
                        echo "<td class='py-2 px-4 border-b'>" . $row["description"] . "</td>";
                        echo "<td class='py-2 px-4 border-b'>" . $row["photo"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='py-2 px-4 border-b text-center'>No products found</td></tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } elseif (isset($_GET['page']) && $_GET['page'] === 'tambah_user') {
                // Tampilkan konten tambah user
                echo "<h1 class='text-3xl font-bold mb-4'>Tambah User</h1>";
                // ... Isi dengan konten tambah user ...
                echo "<p>Ini adalah halaman tambah user.</p>";
            } else {
                // Jika parameter page tidak ada, tampilkan konten dashboard secara default
                echo "<h1 class='text-3xl font-bold mb-4'>Dashboard</h1>";
                // ... Isi dengan konten dashboard ...
                echo "<p>Ini adalah halaman dashboard.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm("Apakah Anda yakin ingin menghapus user?");
        }
    </script>

</body>

</html>

<?php
// Tutup koneksi database
$conn->close();
?>
