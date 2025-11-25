<?php
session_start();
require_once('config/connect.php');
require_once('config/google_config.php');

// Nhận authorization code từ Google
if (!isset($_GET['code'])) {
    header('Location: login_page.php?error=google_auth_failed');
    exit();
}

$auth_code = $_GET['code'];

// Exchange authorization code for access token
$token_url = 'https://oauth2.googleapis.com/token';
$token_data = array(
    'code' => $auth_code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Tắt verify SSL cho localhost

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200) {
    error_log("Google token error: " . $response);
    header('Location: login_page.php?error=token_failed');
    exit();
}

$token_response = json_decode($response, true);

if (!isset($token_response['access_token'])) {
    error_log("No access token in response: " . $response);
    header('Location: login_page.php?error=no_token');
    exit();
}

$access_token = $token_response['access_token'];

// Lấy thông tin người dùng từ Google
$userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userinfo_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$userinfo_response = curl_exec($ch);
curl_close($ch);

$userinfo = json_decode($userinfo_response, true);

if (!isset($userinfo['email'])) {
    error_log("No email in userinfo: " . $userinfo_response);
    header('Location: login_page.php?error=no_email');
    exit();
}

// Thông tin người dùng từ Google
$google_id = $userinfo['id'];
$email = $userinfo['email'];
$full_name = $userinfo['name'];
$first_name = isset($userinfo['given_name']) ? $userinfo['given_name'] : '';
$last_name = isset($userinfo['family_name']) ? $userinfo['family_name'] : '';
$picture = isset($userinfo['picture']) ? $userinfo['picture'] : '';
$verified_email = isset($userinfo['verified_email']) ? $userinfo['verified_email'] : false;

// Kiểm tra xem người dùng đã tồn tại chưa (theo email hoặc google_id)
$check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR google_id = ?");
$check_stmt->bind_param("ss", $email, $google_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Người dùng đã tồn tại - cập nhật thông tin Google nếu cần
    $user = $result->fetch_assoc();
    
    // Cập nhật google_id nếu chưa có
    if (empty($user['google_id'])) {
        $update_stmt = $conn->prepare("UPDATE users SET google_id = ?, google_picture = ? WHERE user_id = ?");
        $update_stmt->bind_param("ssi", $google_id, $picture, $user['user_id']);
        $update_stmt->execute();
    }
    
    // Đăng nhập
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['phone'] = $user['phone'];
    $_SESSION['address'] = $user['address'];
    $_SESSION['role'] = 'user';
    $_SESSION['google_picture'] = $picture;
    
    header('Location: home.php');
    exit();
} else {
    // Tạo tài khoản mới
    // Tạo username từ email (phần trước @)
    $username = explode('@', $email)[0];
    
    // Kiểm tra username đã tồn tại chưa, nếu có thì thêm số
    $check_username_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $check_username_stmt->bind_param("s", $username);
    $check_username_stmt->execute();
    $username_result = $check_username_stmt->get_result();
    
    if ($username_result->num_rows > 0) {
        $username = $username . rand(100, 999);
    }
    
    // Random password (không dùng, vì đăng nhập bằng Google)
    $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    
    // Thêm người dùng mới
    $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, google_id, google_picture, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
    $insert_stmt->bind_param("ssssss", $username, $email, $random_password, $full_name, $google_id, $picture);
    
    if ($insert_stmt->execute()) {
        $new_user_id = $conn->insert_id;
        
        // Đăng nhập với tài khoản mới
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = '';
        $_SESSION['address'] = '';
        $_SESSION['role'] = 'user';
        $_SESSION['google_picture'] = $picture;
        $_SESSION['new_google_user'] = true; // Đánh dấu là người dùng mới
        
        header('Location: home.php?welcome=1');
        exit();
    } else {
        error_log("Failed to create user: " . $conn->error);
        header('Location: login_page.php?error=create_failed');
        exit();
    }
}

$conn->close();
?>
