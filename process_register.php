<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($role)) {
        echo "<script>alert('All fields are required');</script>";
        echo "<script>window.location.replace('register.php');</script>";
        exit();
    }

    // Konfigurasi koneksi database
    $servername = "localhost"; // Jangan gunakan localhost:8080
    $username_db = "root";
    $password_db = "";
    $dbname = "account";

    // Buat koneksi
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Periksa apakah username sudah ada
    $sql_check_username = "SELECT * FROM user WHERE username = ?";
    $stmt_check_username = $conn->prepare($sql_check_username);
    if (!$stmt_check_username) {
        die("Statement preparation failed: " . $conn->error);
    }
    $stmt_check_username->bind_param("s", $username);
    $stmt_check_username->execute();
    $result_check_username = $stmt_check_username->get_result();

    if ($result_check_username->num_rows > 0) {
        echo "<script>alert('Username already exists');</script>";
        echo "<script>window.location.replace('register.php');</script>";
        exit();
    }

    // Hash password sebelum menyimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Masukkan data ke tabel
    $sql = "INSERT INTO user (username,email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Statement preparation failed: " . $conn->error);
    }
    $stmt->bind_param("ssss", $username,$email, $hashed_password, $role);

    if ($stmt->execute() === TRUE) {
        echo "<script>alert('Registration successful! Click OK to proceed to login.');</script>";
        echo "<script>window.location.replace('login.php');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
