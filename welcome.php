<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Tamu';

// Fungsi untuk menghasilkan ID penerbangan acak
function generateRandomFlightId() {
    return 'GA' . rand(100, 999);
}

// Fungsi untuk menghasilkan waktu kedatangan dan keberangkatan acak
function generateRandomTime() {
    $hour = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT);
    $minute = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    return $hour . ':' . $minute;
}

// Jika belum ada daftar penerbangan dalam sesi, buat yang baru
if (!isset($_SESSION['flights'])) {
    $_SESSION['flights'] = [];
    for ($i = 0; $i < 3; $i++) {
        $arrival_time = generateRandomTime();
        $departure_time = generateRandomTime();
        while ($departure_time <= $arrival_time) {
            $departure_time = generateRandomTime();
        }
        $_SESSION['flights'][] = [
            'flight_id' => generateRandomFlightId(),
            'status' => 'landed',
            'arrival_time' => $arrival_time,
            'departure_time' => $departure_time
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['flight_id'])) {
    $flight_id = $_POST['flight_id'];
    foreach ($_SESSION['flights'] as &$flight) {
        if ($flight['flight_id'] == $flight_id) {
            $new_flight_id = generateRandomFlightId();
            $_SESSION['current_flight_id'] = $new_flight_id; // Simpan ID penerbangan baru dalam sesi
            $flight['flight_id'] = $new_flight_id;
            break;
        }
    }
    // Redirect ke check_items.php dengan ID penerbangan yang baru
    header("Location: check_items.php?flight_id=" . urlencode($_SESSION['current_flight_id']));
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f0f0f0;
        }

        .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #fff;
            border-bottom: 1px solid #ddd;
        }

        .profile {
            text-align: right;
            display: flex;
            align-items: center;
        }

        .profile h3 {
            margin: 0 10px 0 0;
            font-size: 1.2em;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            color: #333;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .check-btn {
            background-color: #8BC34A;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .check-btn:hover {
            background-color: #7cb342;
        }

        .sidebar {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1001;
            top: 0;
            left: 0;
            background-color: #333;
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar a {
            padding: 8px 8px 8px 32px;
            text-decoration: none;
            font-size: 25px;
            color: #818181;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            color: #f1f1f1;
        }

        .sidebar .closebtn {
            position: absolute;
            top: 0;
            right: 25px;
            font-size: 36px;
            margin-left: 50px;
        }

        .openbtn {
            font-size: 20px;
            cursor: pointer;
            background-color: #333;
            color: white;
            padding: 10px 15px;
            border: none;
        }

        .openbtn:hover {
            background-color: #444;
        }

        .logout-section {
            margin-bottom: 60px;
            padding: 8px 8px 8px 32px;
        }

        .logout-icon {
            margin-right: 10px;
        }

        .sidebar-content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
        }

        .sidebar-toggle {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <button class="openbtn" onclick="openNav()">☰</button>
        <div class="profile">
            <h3>Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h3>
        </div>
    </div>

    <div class="content">
        <div class="table-container">
            <table>
                <tr>
                    <th>ID Penerbangan</th>
                    <th>Status</th>
                    <th>Waktu Kedatangan</th>
                    <th>Waktu Keberangkatan</th>
                    <th>Aksi</th>
                </tr>
                <?php foreach ($_SESSION['flights'] as $flight): ?>
                <tr>
                    <td><?php echo htmlspecialchars($flight['flight_id']); ?></td>
                    <td><?php echo htmlspecialchars($flight['status']); ?></td>
                    <td><?php echo htmlspecialchars($flight['arrival_time']); ?></td>
                    <td><?php echo htmlspecialchars($flight['departure_time']); ?></td>
                    <td>
                        <form action="welcome.php" method="post">
                            <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($flight['flight_id']); ?>">
                            <button class="check-btn" type="submit">Cek</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <div id="mySidebar" class="sidebar">
        <div class="sidebar-content">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
            <a href="gudang_berbahaya.php">Gudang Berbahaya</a>
            <a href="gudang_tidak_berbahaya.php">Gudang Tidak Berbahaya</a>
        </div>
        <div class="logout-section">
            <a href="login.php" class="logout-icon"><i class='bx bx-log-out'></i>Keluar</a>
        </div>
    </div>

    <script>
        function openNav() {
            document.getElementById("mySidebar").style.width = "250px";
        }

        function closeNav() {
            document.getElementById("mySidebar").style.width = "0";
        }
    </script>
</body>

</html>
