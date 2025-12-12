<?php
session_start();
include "koneksi.php";
$baseUrl = "http://localhost/project_pbd";

// CEK LOGIN
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: $baseUrl/login.php");
    exit();
}

// HANYA SUPER ADMIN
if ($_SESSION['role'] !== 'Super Admin') {
    echo "<div style='padding:50px; text-align:center;'>
            <h3 style='color:red;'>🚫 Akses Ditolak</h3>
            <p>Dashboard ini hanya untuk <b>Super Admin</b>.</p>
            <a href='$baseUrl/logout.php' class='btn btn-primary'>Kembali ke Login</a>
          </div>";
    exit();
}

// HITUNG DATA
$total_user = $conn->query("SELECT COUNT(*) AS total FROM user")->fetch_assoc()['total'];
$total_role = $conn->query("SELECT COUNT(*) AS total FROM role")->fetch_assoc()['total'];
$total_satuan = $conn->query("SELECT COUNT(*) AS total FROM satuan WHERE status=1")->fetch_assoc()['total'];
$total_vendor = $conn->query("SELECT COUNT(*) AS total FROM vendor WHERE status='A'")->fetch_assoc()['total'];
$total_barang = $conn->query("SELECT COUNT(*) AS total FROM barang WHERE status=1")->fetch_assoc()['total'];
$total_margin = $conn->query("SELECT COUNT(*) AS total FROM margin_penjualan WHERE status=1")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | PBD Project</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    html, body {
        height: 100%;
        width: 100%;
        overflow-x: hidden;
        font-family: "Poppins", sans-serif;
        background: #f9fafb;
    }

    .main-content {
        position: relative;
        margin-left: 240px; /* sesuai sidebar */
        width: calc(100% - 240px); /* <== penting biar full kanan */
        min-height: 100vh;
        padding: 40px 50px;
        background: #f9fafb;
    }

    h1 span.wave {
        font-size: 38px;
    }

    .summary-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        text-align: center;
        padding: 25px 10px;
        transition: 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }

    .summary-card i {
        font-size: 40px;
        margin-bottom: 10px;
    }

    .summary-card h5 {
        font-weight: 600;
        color: #374151;
    }

    .summary-card p {
        font-size: 20px;
        font-weight: bold;
        color: #1f2937;
    }

    .section-title {
        font-weight: bold;
        color: #374151;
        border-left: 4px solid #6366f1;
        padding-left: 10px;
        margin-top: 40px;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 20px;
        }
    }
</style>
</head>

<body>
<?php include "navbar.php"; ?>

<div class="main-content">
    <div class="mb-4 text-center">
        <h1 class="fw-bold text-dark">
            <span class="wave">👋</span> Hai! Selamat Datang Kembali
        </h1>
        <p class="text-muted">
            Senang melihatmu lagi, <b><?= $_SESSION['username']; ?></b>!<br>
            Berikut ringkasan aktivitas dan data terkini sistem.
        </p>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-4 mb-5 justify-content-center">
        <div class="col-md-4 col-lg-2">
            <div class="summary-card">
                <i class="bi bi-people text-primary"></i>
                <h5>User</h5>
                <p><?= $total_user ?></p>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="summary-card">
                <i class="bi bi-person-badge text-info"></i>
                <h5>Role</h5>
                <p><?= $total_role ?></p>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="summary-card">
                <i class="bi bi-tags text-success"></i>
                <h5>Satuan</h5>
                <p><?= $total_satuan ?></p>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="summary-card">
                <i class="bi bi-box-seam text-warning"></i>
                <h5>Barang</h5>
                <p><?= $total_barang ?></p>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="summary-card">
                <i class="bi bi-truck text-success"></i>
                <h5>Vendor</h5>
                <p><?= $total_vendor ?></p>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="summary-card">
                <i class="bi bi-graph-up text-danger"></i>
                <h5>Margin Penjualan</h5>
                <p><?= $total_margin ?></p>
            </div>
        </div>
    </div>


<!-- STATISTIK PENJUALAN -->
<h4 class="section-title"><i class="bi bi-bar-chart-line"></i> Statistik Penjualan Mingguan</h4>
<div class="card p-4 mt-3 shadow-sm">
    <?php
    // Ambil data penjualan 7 hari terakhir
    $query = $conn->query("
        SELECT DATE(created_at) AS tanggal, SUM(total_nilai) AS total
        FROM penjualan
        GROUP BY DATE(created_at)
        ORDER BY tanggal DESC
        LIMIT 7
    ");

    $tanggal = [];
    $total = [];
    while ($row = $query->fetch_assoc()) {
        $tanggal[] = date('d M', strtotime($row['tanggal']));
        $total[] = (int)$row['total'];
    }

    // Dibalik supaya dari hari lama ke baru
    $tanggal = array_reverse($tanggal);
    $total = array_reverse($total);
    ?>

    <?php if (count($tanggal) > 0): ?>
        <canvas id="chartPenjualan" height="100"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        const ctxSales = document.getElementById('chartPenjualan');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: <?= json_encode($tanggal) ?>,
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: <?= json_encode($total) ?>,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.parsed.y.toLocaleString('id-ID');
                                return 'Rp ' + val;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
        </script>
    <?php else: ?>
        <p class="text-muted mb-0">
            Belum ada data penjualan untuk ditampilkan. Tambahkan transaksi baru agar grafik muncul di sini!
        </p>
    <?php endif; ?>
</div>

</body>
</html>
