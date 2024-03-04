<!-- daftar_barang.php -->
<div>
    <h3 class="text-xl font-semibold mb-2">Daftar Barang</h3>
    
    <!-- Input pencarian -->
    <input type="text" id="searchInput" onkeyup="searchProducts()" placeholder="Cari nama barang..." class="border p-2 mb-4">

    <?php $items = getItems($pdo); ?>
    <?php foreach ($items as $item): ?>
        <div class="border p-4 mb-4 product-item">
            <!-- Product photo -->
            <?php
            $image_path = '../barang/' . $item['photo']; // Path to the product photo folder
            if (file_exists($image_path)) {
                echo '<img src="' . $image_path . '" alt="Product Photo" class="w-24 h-24">';
            } else {
                echo '<p>Gambar tidak ditemukan</p>';
            }
            ?>

            <!-- Product details -->
            <p><strong>Name:</strong> <?php echo $item['name']; ?></p>
            <p><strong>Price:</strong> <?php echo $item['price']; ?></p>
            <p><strong>Store Name:</strong> <?php echo $item['store_name']; ?></p>
            <!-- Add more product details as needed -->
        </div>
    <?php endforeach; ?>
</div>

<script>
    function searchProducts() {
        // Get the search input value
        var input, filter, products, productName;
        input = document.getElementById('searchInput');
        filter = input.value.toUpperCase();
        products = document.getElementsByClassName('product-item');

        // Iterate over each product and hide those that do not match the search criteria
        for (var i = 0; i < products.length; i++) {
            productName = products[i].getElementsByTagName('p')[0].innerText.toUpperCase(); // Get the product name
            if (productName.indexOf(filter) > -1) {
                products[i].style.display = "";
            } else {
                products[i].style.display = "none";
            }
        }
    }
</script>
