<?php
// Database setup and verification script
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without database first
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Setup and Verification</h2>";

// Check if hkt database exists
$result = $conn->query("SHOW DATABASES LIKE 'hkt'");
if ($result->num_rows > 0) {
    echo "✅ Database 'hkt' exists<br>";
} else {
    echo "❌ Database 'hkt' does not exist<br>";
    echo "Creating database 'hkt'...<br>";
    if ($conn->query("CREATE DATABASE hkt CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
        echo "✅ Database 'hkt' created successfully<br>";
    } else {
        echo "❌ Error creating database: " . $conn->error . "<br>";
    }
}

// Select the database
$conn->select_db("hkt");

// Check if categories table exists
$result = $conn->query("SHOW TABLES LIKE 'categories'");
if ($result->num_rows > 0) {
    echo "✅ Categories table exists<br>";
    
    // Check if status column exists
    $result = $conn->query("SHOW COLUMNS FROM categories LIKE 'status'");
    if ($result->num_rows > 0) {
        echo "✅ Status column exists in categories table<br>";
    } else {
        echo "❌ Status column missing. Adding it...<br>";
        if ($conn->query("ALTER TABLE categories ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER description")) {
            echo "✅ Status column added successfully<br>";
            $conn->query("UPDATE categories SET status = 'active' WHERE status IS NULL");
            echo "✅ Existing categories updated to active status<br>";
        } else {
            echo "❌ Error adding status column: " . $conn->error . "<br>";
        }
    }
} else {
    echo "❌ Categories table does not exist<br>";
    echo "Creating categories table...<br>";
    $create_table = "CREATE TABLE categories (
        category_id int(11) NOT NULL AUTO_INCREMENT,
        category_name varchar(50) NOT NULL,
        description text DEFAULT NULL,
        status enum('active','inactive') DEFAULT 'active',
        PRIMARY KEY (category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($create_table)) {
        echo "✅ Categories table created successfully<br>";
        
        // Insert default categories
        $insert_categories = "INSERT INTO categories (category_name, description, status) VALUES
            ('tuong', 'Tượng trang trí', 'active'),
            ('tranh', 'Tranh treo tường', 'active'),
            ('den', 'Đèn trang trí', 'active'),
            ('khac', 'Sản phẩm khác', 'active')";
        
        if ($conn->query($insert_categories)) {
            echo "✅ Default categories inserted successfully<br>";
        } else {
            echo "❌ Error inserting default categories: " . $conn->error . "<br>";
        }
    } else {
        echo "❌ Error creating categories table: " . $conn->error . "<br>";
    }
}

// Display current categories
echo "<h3>Current Categories:</h3>";
$result = $conn->query("SELECT * FROM categories ORDER BY category_id");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Name</th><th>Description</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['category_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['description'] ?? '') . "</td>";
        echo "<td>" . ($row['status'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No categories found or error: " . $conn->error;
}

$conn->close();
?>

<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
h2, h3 { color: #333; }
</style>