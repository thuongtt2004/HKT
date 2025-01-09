<?php
require_once 'config/connect.php';

// Xử lý xóa sản phẩm
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    
    try {
        // Debug để kiểm tra ID sản phẩm
        error_log("Đang xóa sản phẩm ID: " . $product_id);
        
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        // Sử dụng Prepared Statement để tránh SQL injection
        $delete_query = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $delete_query->bind_param("s", $product_id);
        
        if ($delete_query->execute()) {
            $conn->commit();
            echo "<script>
                alert('Xóa sản phẩm ID: " . $product_id . " thành công!');
                window.location.href = 'admin_products.php';
            </script>";
        } else {
            throw new Exception($conn->error);
        }
        
        $delete_query->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('Lỗi khi xóa sản phẩm: " . $e->getMessage() . "');
            window.location.href = 'admin_products.php';
        </script>";
    }
}

// Xử lý cập nhật sản phẩm
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];

    $sql = "UPDATE products SET 
            product_name = '$product_name',
            price = $price,
            stock_quantity = $stock_quantity,
            category_id = $category_id,
            description = '$description'
            WHERE product_id = $product_id";

    if ($conn->query($sql)) {
        echo "<script>alert('Cập nhật sản phẩm thành công!');</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật sản phẩm: " . $conn->error . "');</script>";
    }
}

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";
$result = $conn->query($sql);

// Lấy danh sách danh mục cho form cập nhật
$categories_sql = "SELECT * FROM categories";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - TTHUONG Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin: 20px 0;
            font-size: 2em;
        }

        /* Bảng sản phẩm */
        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-collapse: collapse;
            margin: 20px 0;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #4a90e2;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        /* Nút thao tác */
        button {
            padding: 8px 16px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
        }

        button:first-child {
            background-color: #4CAF50;
            color: white;
        }

        button:last-child {
            background-color: #f44336;
            color: white;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
            display: none;
            justify-content: center;
            align-items: center;
        }

        @keyframes fadeIn {
            from {opacity: 0}
            to {opacity: 1}
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            margin: 0;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #000;
        }

        /* Form trong modal */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button[type="submit"] {
            background-color: #4a90e2;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background-color: #357abd;
        }

        /* Hình ảnh sản phẩm */
        td img {
            border-radius: 4px;
            object-fit: cover;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            th, td {
                padding: 10px;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }

        .modal-content input[type="text"],
        .modal-content input[type="number"],
        .modal-content select,
        .modal-content textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }

        .close {
            float: right;
            cursor: pointer;
            font-size: 28px;
        }

        /* Style chung cho các nút */
        .back-btn, .add-product-btn {
            display: inline-flex;
            align-items: center;
            background-color: #333333;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover, .add-product-btn:hover {
            background-color: #444444;
            transform: translateY(-2px);
        }

        .back-btn i, .add-product-btn i {
            margin-right: 8px;
        }

        .button-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }
    </style>

    </style>
</head>
<body>
    <div class="container">
        <h1>Quản Lý Sản Phẩm</h1>
        
        <div class="button-container">
            <a href="QTVindex.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay về Trang Quản Trị
            </a>
            <a href="add_product.php" class="add-product-btn">
                <i class="fas fa-plus"></i> Thêm Sản Phẩm Mới
            </a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Danh Mục</th>
                    <th>Giá</th>
                    <th>Số Lượng</th>
                    <th>Đã Bán</th>
                    <th>Hình Ảnh</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</td>
                        <td><?php echo $row['stock_quantity']; ?></td>
                        <td><?php echo $row['sold_quantity']; ?></td>
                        <td><img src="<?php echo $row['image_url']; ?>" width="50"></td>
                        <td>
                            <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                Sửa
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirmDelete(<?php echo $row['product_id']; ?>)">
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" class="btn-delete">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Sửa Sản Phẩm -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Sửa Thông Tin Sản Phẩm</h2>
            <form id="editForm" method="POST">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="product_name" id="edit_product_name" required>
                </div>
                
                <div class="form-group">
                    <label>Giá:</label>
                    <input type="number" name="price" id="edit_price" required>
                </div>
                
                <label>Số lượng:</label>
                <input type="number" name="stock_quantity" id="edit_stock_quantity" required>
                
                <div class="form-group">
                    <label>Danh mục:</label>
                    <select name="category_id" id="edit_category_id" required>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo $category['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Mô tả:</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                
                <button type="submit" name="update_product">Cập nhật</button>
            </form>
        </div>
    </div>

    <script>
        // Xử lý xóa sản phẩm
        function confirmDelete(productId) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm có ID: ' + productId + '?')) {
                return true;
            }
            return false;
        }

        // Xử lý modal sửa sản phẩm
        function openEditModal(product) {
            const modal = document.getElementById('editModal');
            modal.style.display = 'flex';
            
            // Điền thông tin vào form
            document.getElementById('edit_product_id').value = product.product_id;
            document.getElementById('edit_product_name').value = product.product_name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock_quantity').value = product.stock_quantity;
            document.getElementById('edit_category_id').value = product.category_id;
            document.getElementById('edit_description').value = product.description;
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
