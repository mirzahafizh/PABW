<!-- tambah_user.php -->
<div>
    <h3 class="text-xl font-semibold mb-2">Tambah User</h3>

    <form method="post" action="admin.php">
        <label for="new_fullname" class="block mb-2">Fullname:</label>
        <input type="text" name="new_fullname" required class="border p-2 mb-4">

        <label for="new_phone" class="block mb-2">Phone:</label>
        <input type="text" name="new_phone" required class="border p-2 mb-4">

        <label for="new_email" class="block mb-2">Email:</label>
        <input type="text" name="new_email" required class="border p-2 mb-4">

        <label for="new_username" class="block mb-2">Username:</label>
        <input type="text" name="new_username" required class="border p-2 mb-4">

        <label for="new_password" class="block mb-2">Password:</label>
        <input type="password" name="new_password" required class="border p-2 mb-4">

        <label for="new_role" class="block mb-2">Role:</label>
        <select name="new_role" required class="border p-2 mb-4">
            <option value="">Pilih Role</option>
            <option value="admin">Admin</option>
            <option value="pengguna">pengguna</option>
            <option value="kurir">kurir</option>
            <!-- Tambahkan opsi role lain sesuai kebutuhan -->
        </select>

        <button type="submit" name="add_user" class="bg-green-500 text-white py-2 px-4">Tambah User</button>
    </form>
</div>
