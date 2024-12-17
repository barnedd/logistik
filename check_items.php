<?php
session_start();

// Periksa apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Ambil flight_id dari parameter GET, gunakan htmlspecialchars untuk keamanan
$flight_id = isset($_GET['flight_id']) ? htmlspecialchars($_GET['flight_id']) : 'Unknown';

// Daftar barang (simulasi data)
$items = [
    ['name' => 'Laptop', 'type' => 'Berbahaya', 'quantity' => rand(1, 10), 'price' => 15000000],
    ['name' => 'Smartphone', 'type' => 'Berbahaya', 'quantity' => rand(1, 10), 'price' => 10000000],
    ['name' => 'Baterai', 'type' => 'Berbahaya', 'quantity' => rand(1, 10), 'price' => 200000],
    ['name' => 'Kopi', 'type' => 'Tidak Berbahaya', 'quantity' => rand(1, 10), 'price' => 50000],
    ['name' => 'Teh', 'type' => 'Tidak Berbahaya', 'quantity' => rand(1, 10), 'price' => 30000],
    ['name' => 'Beras', 'type' => 'Tidak Berbahaya', 'quantity' => rand(1, 10), 'price' => 120000],
];

// Konstanta untuk biaya tambahan
define('ADMIN_FEE_PERCENTAGE', 0.05); // Biaya admin 5%
define('TAX_PERCENTAGE', 0.1);        // Pajak 10%

$total_price = 0; // Inisialisasi total harga

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Informasi koneksi database
    $servername = "localhost"; // Default servername
    $username = "root";        // Username database
    $password = "";            // Password database
    $dbname = "warehouse";     // Nama database

    // Membuat koneksi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Mulai proses penyimpanan barang ke database
    foreach ($items as $item) {
        // Siapkan query untuk barang berdasarkan tipe
        if ($item['type'] === 'Berbahaya') {
            $stmt = $conn->prepare("INSERT INTO barang_berbahaya (flight_id, name, quantity, type) VALUES (?, ?, ?, ?)");
        } else {
            $stmt = $conn->prepare("INSERT INTO barang_tidak_berbahaya (flight_id, name, quantity, type) VALUES (?, ?, ?, ?)");
        }

        // Bind parameter dan eksekusi query
        if ($stmt) {
            $stmt->bind_param("ssis", $flight_id, $item['name'], $item['quantity'], $item['type']);
            $stmt->execute();
            $stmt->close();
        }

        // Hitung total harga barang
        $total_price += $item['quantity'] * $item['price'];
    }

    // Tutup koneksi
    $conn->close();

    // Hitung biaya admin, pajak, dan total keseluruhan
    $admin_fee = $total_price * ADMIN_FEE_PERCENTAGE;
    $tax = $total_price * TAX_PERCENTAGE;
    $grand_total = $total_price + $admin_fee + $tax;

    // Redirect ke halaman welcome dengan parameter total harga
    header("Location: welcome.php?total=" . urlencode($grand_total));
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        .container {
            padding: 20px;
            background-color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #333;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .dangerous {
            background-color: #ffdddd;
        }
        .send-btn {
            background-color: #8BC34A;
            color: #fff;
            padding: 10px 20px;
            border: none;
            margin-top: 20px;
            cursor: pointer;
        }
        .send-btn:hover {
            background-color: #7cb342;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daftar Barang</h1>
        <form method="POST">
            <table>
                <tr>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan (IDR)</th>
                    <th>Total Harga (IDR)</th>
                    <th>Tipe</th>
                </tr>
                <?php foreach ($items as $item): ?>
                <tr class="<?php echo $item['type'] === 'Berbahaya' ? 'dangerous' : ''; ?>">
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                    <td><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($item['type']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <p><strong>Total Harga:</strong> IDR <?php echo number_format($total_price, 0, ',', '.'); ?></p>
            <p><strong>Biaya Admin (5%):</strong> IDR <?php echo number_format($total_price * ADMIN_FEE_PERCENTAGE, 0, ',', '.'); ?></p>
            <p><strong>Pajak (10%):</strong> IDR <?php echo number_format($total_price * TAX_PERCENTAGE, 0, ',', '.'); ?></p>
            <p><strong>Grand Total:</strong> IDR <?php echo number_format($total_price + $total_price * ADMIN_FEE_PERCENTAGE + $total_price * TAX_PERCENTAGE, 0, ',', '.'); ?></p>

            <button class="send-btn" type="submit">Kirim ke Gudang</button>
        </form>
    </div>
</body>
</html>
