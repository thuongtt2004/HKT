<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['admin_id'])) {
    header('Location: dangnhapAdmin.php');
    exit();
}

require_once('config/connect.php');
$targetDir = "uploads/";
if (!is_writable($targetDir)) {
    echo "Thư mục $targetDir không có quyền ghi";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = $_POST['product-id'];
    $productName = $_POST['product-name'];
    $productPrice = $_POST['product-price'];
    $productQuantity = $_POST['product-quantity'];
    $productDescription = $_POST['product-description'];
    $categoryId = $_POST['category-id'];

    // Xử lý upload hình ảnh
    if (isset($_FILES['product-image']) && $_FILES['product-image']['error'] == 0) {
        $targetDir = "uploads/";
        $fileName = time() . '_' . basename($_FILES["product-image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Kiểm tra file hình ảnh
        $validExtensions = array("jpg", "jpeg", "png", "gif");
        if (in_array($imageFileType, $validExtensions)) {
            if (move_uploaded_file($_FILES["product-image"]["tmp_name"], $targetFile)) {
                $imageUrl = $targetFile;
            } else {
                echo "<script>alert('Lỗi khi tải lên hình ảnh!');</script>";
                exit;
            }
        } else {
            echo "<script>alert('Chỉ chấp nhận file JPG, JPEG, PNG & GIF!');</script>";
            exit;
        }
    } else {
        echo "<script>alert('Lỗi khi tải lên hình ảnh!');</script>";
    }

    // Thêm sản phẩm vào database
    $stmt = $conn->prepare("INSERT INTO products (product_id, product_name, price, description, image_url, stock_quantity, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssis", $productId, $productName, $productPrice, $productDescription, $imageUrl, $productQuantity, $categoryId);

    if ($stmt->execute()) {
        echo "<script>
            alert('Thêm sản phẩm thành công!');
            window.location.href = 'admin_products.php';
        </script>";
    } else {
        echo "<script>alert('Lỗi: " . $stmt->error . "');</script>";
    }
    $allowedMimes = array('image/jpeg', 'image/png', 'image/gif');
if (!in_array($_FILES['product-image']['type'], $allowedMimes)) {
    echo "<script>alert('Loại file không hợp lệ!');</script>";
    exit;
}
if ($_FILES["product-image"]["size"] > 5000000) { // 5MB
    echo "<script>alert('File quá lớn (tối đa 5MB)');</script>";
    exit;
}
if (isset($_FILES['product-image']) && $_FILES['product-image']['error'] == 0) {
    $targetDir = "uploads/";
    
    // Tạo thư mục nếu chưa tồn tại
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES["product-image"]["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Debug thông tin
    echo "Upload path: " . $targetFile . "<br>";
    echo "File type: " . $imageFileType . "<br>";
    echo "Temp file: " . $_FILES["product-image"]["tmp_name"] . "<br>";
    
    // Kiểm tra file hình ảnh
    $validExtensions = array("jpg", "jpeg", "png", "gif");
    if (in_array($imageFileType, $validExtensions)) {
        if (!move_uploaded_file($_FILES["product-image"]["tmp_name"], $targetFile)) {
            echo "Chi tiết lỗi upload: " . error_get_last()['message'];
            exit;
        }
        $imageUrl = $targetFile;
    } else {
        echo "<script>alert('Chỉ chấp nhận file JPG, JPEG, PNG & GIF!');</script>";
        exit;
    }
}
    $stmt->close();
}

// Lấy danh sách categories cho dropdown
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm - TTHUONG STORE</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        .submit-btn {
            background-color: #ebe9e5;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        
        .submit-btn:hover {
            background-color: #d3cbb8;
        }
        
        .preview-image {
            max-width: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Thêm Sản Phẩm Mới</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product-id">Mã Sản Phẩm:</label>
                <input type="text" id="product-id" name="product-id" required>
            </div>

            <div class="form-group">
                <label for="product-name">Tên Sản Phẩm:</label>
                <input type="text" id="product-name" name="product-name" required>
            </div>

            <div class="form-group">
                <label for="category-id">Danh Mục:</label>
                <select id="category-id" name="category-id" required>
                    <?php while($category = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product-price">Giá:</label>
                <input type="number" id="product-price" name="product-price" required>
            </div>

            <div class="form-group">
                <label for="product-quantity">Số Lượng:</label>
                <input type="number" id="product-quantity" name="product-quantity" required>
            </div>

            <div class="form-group">
                <label for="product-description">Mô Tả:</label>
                <textarea id="product-description" name="product-description" required></textarea>
            </div>

            <div class="form-group">
                <label for="product-image">Hình Ảnh:</label>
                <input type="file" id="product-image" name="product-image" accept="image/*" required>
                <img id="preview" class="preview-image" style="display: none;">
            </div>

            <button type="submit" class="submit-btn">Thêm Sản Phẩm</button>
        </form>
    </div>

    <script>
        // Preview hình ảnh trước khi upload
        document.getElementById('product-image').onchange = function(evt) {
            const [file] = this.files;
            if (file) {
                const preview = document.getElementById('preview');
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        };
    </script>
</body>
</html>
