<?php
session_start(); // pastikan session login aktif
$pageTitle = "Transaksi Pengadaan | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

/* =====================================================
   1. LIST DATA PENGADAAN
   ===================================================== */
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_pengadaan ORDER BY idpengadaan DESC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-basket"></i> Data Pengadaan</h3>
        <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Pengadaan</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Vendor</th>
                    <th>Dibuat Oleh</th>
                    <th>Subtotal</th>
                    <th>PPN</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while($r = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['idpengadaan'] ?></td>
                    <td><?= $r['tanggal_pengadaan'] ?></td>
                    <td><?= $r['nama_vendor'] ?></td>
                    <td><?= htmlspecialchars($r['dibuat_oleh']) ?></td>
                    <td>Rp <?= number_format($r['subtotal_nilai'], 0, ",", ".") ?></td>
                    <td><?= $r['ppn'] ?>%</td>
                    <td>Rp <?= number_format($r['total_nilai'], 0, ",", ".") ?></td>
                    <td>
                        <?php if($r['status_pengadaan'] == 'Selesai'): ?>
                            <span class="badge bg-success">Selesai</span>
                        <?php elseif($r['status_pengadaan'] == 'Cancel'): ?>
                            <span class="badge bg-danger">Cancel</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Diproses</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?action=view&id=<?= $r['idpengadaan'] ?>" class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i> Detail
                        </a>
                        <?php if($r['status_pengadaan'] != 'Selesai'): ?>
                        <a href="?action=cancel&id=<?= $r['idpengadaan'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Yakin ingin membatalkan pengadaan ini?')">
                           <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php
}

/* =====================================================
   2. FORM TAMBAH PENGADAAN (HEADER)
   ===================================================== */
elseif ($action == 'add') {
    $vendor = $conn->query("SELECT idvendor, nama_vendor FROM vendor WHERE status='A'");
    ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-plus-circle"></i> Tambah Pengadaan</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <div class="mb-3">
                <label class="form-label fw-bold">Vendor</label>
                <select name="vendor_idvendor" class="form-select" required>
                    <option value="">-- Pilih Vendor --</option>
                    <?php while($v = $vendor->fetch_assoc()): ?>
                        <option value="<?= $v['idvendor'] ?>"><?= $v['nama_vendor'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-person-circle"></i> Pengadaan ini akan otomatis dibuat oleh user login:
                <strong><?= $_SESSION['username'] ?? 'Tidak diketahui' ?></strong>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="pengadaan.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php
}

/* =====================================================
   3. INSERT HEADER PENGADAAN
   ===================================================== */
elseif ($action == 'insert') {
    $vendor = (int)$_POST['vendor_idvendor'];
    $user = (int)($_SESSION['iduser'] ?? 0);

    if ($user == 0) {
        die("<div class='alert alert-danger'>Error: User login tidak terdeteksi. Pastikan sudah login terlebih dahulu.</div>");
    }

    $conn->query("INSERT INTO pengadaan 
        (timestamp, user_iduser, vendor_idvendor, status, subtotal_nilai, ppn, total_nilai, status_pengadaan)
        VALUES (NOW(), $user, $vendor, 'A', 0, 10, 0, 'Diproses')");
    
    header("Location: pengadaan.php");
    exit;
}

/* =====================================================
   4. DETAIL PENGADAAN
   ===================================================== */
elseif ($action == 'view') {
    $id = (int)$_GET['id'];
    $header = $conn->query("SELECT * FROM v_pengadaan WHERE idpengadaan=$id")->fetch_assoc();
    $detail = $conn->query("SELECT * FROM v_detail_pengadaan WHERE idpengadaan=$id");
    ?>
    <h3 class="text-gradient fw-bold mb-3"><i class="bi bi-receipt"></i> Detail Pengadaan #<?= $id ?></h3>

    <div class="card p-4 mb-3 shadow-sm">
        <p><strong>Vendor:</strong> <?= $header['nama_vendor'] ?></p>
        <p><strong>Dibuat Oleh:</strong> <?= $header['dibuat_oleh'] ?></p>
        <p><strong>Tanggal:</strong> <?= $header['tanggal_pengadaan'] ?></p>
        <p><strong>Status:</strong> 
            <?php if($header['status_pengadaan'] == 'Selesai'): ?>
                <span class="badge bg-success">Selesai</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark">Diproses</span>
            <?php endif; ?>
        </p>
        <p><strong>Total:</strong> Rp <?= number_format($header['total_nilai'], 0, ",", ".") ?></p>
    </div>

    <?php if($header['status_pengadaan'] != 'Selesai'): ?>
    <a href="?action=add_detail&id=<?= $id ?>" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Tambah Barang Pengadaan
    </a>
    <?php endif; ?>

    <table class="table table-bordered align-middle text-center shadow-sm">
        <thead class="table-secondary">
            <tr>
                <th>Nama Barang</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while($d = $detail->fetch_assoc()): ?>
            <tr>
                <td><?= $d['nama_barang'] ?></td>
                <td>Rp <?= number_format($d['harga_satuan'], 0, ",", ".") ?></td>
                <td><?= $d['jumlah'] ?></td>
                <td>Rp <?= number_format($d['subtotal'], 0, ",", ".") ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="text-end mt-3">
        <a href="pengadaan.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
<?php
}

/* =====================================================
   5. FORM TAMBAH DETAIL
   ===================================================== */
elseif ($action == 'add_detail') {
    $id = (int)$_GET['id'];
    $barang = $conn->query("SELECT idbarang, nama, harga FROM barang WHERE status=1");
?>
<h3 class="text-gradient fw-bold mb-3"><i class="bi bi-plus-circle"></i> Tambah Barang Pengadaan</h3>

<div class="card p-4 w-75 mx-auto shadow-sm border-0">
<form method="POST" action="?action=insert_detail">
    <input type="hidden" name="idpengadaan" value="<?= $id ?>">

    <div class="mb-3">
        <label class="form-label fw-bold">Barang</label>
        <select name="idbarang" class="form-select" required>
            <option value="">-- Pilih Barang --</option>
            <?php while($b = $barang->fetch_assoc()): ?>
                <option value="<?= $b['idbarang'] ?>"><?= $b['nama'] ?> - Rp <?= number_format($b['harga'], 0, ",", ".") ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold">Jumlah</label>
        <input type="number" name="jumlah" class="form-control" min="1" required>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
        <a href="pengadaan.php?action=view&id=<?= $id ?>" class="btn btn-secondary">Kembali</a>
    </div>
</form>
</div>
<?php
}

/* =====================================================
   6. INSERT DETAIL
   ===================================================== */
elseif ($action == 'insert_detail') {
    $idpengadaan = (int)$_POST['idpengadaan'];
    $idbarang = (int)$_POST['idbarang'];
    $jumlah = (int)$_POST['jumlah'];

    // Ambil harga dari tabel barang
    $harga = $conn->query("SELECT harga FROM barang WHERE idbarang=$idbarang")->fetch_assoc()['harga'];
    $subtotal = $harga * $jumlah;

    $conn->query("INSERT INTO detail_pengadaan (idpengadaan, idbarang, harga_satuan, jumlah, subtotal)
                  VALUES ($idpengadaan, $idbarang, $harga, $jumlah, $subtotal)");

    header("Location: pengadaan.php?action=view&id=$idpengadaan");
    exit;
}


/* =====================================================
   8. CANCEL PENGADAAN (tidak dihapus!)
   ===================================================== */
elseif ($action == 'cancel') {
    $id = (int)$_GET['id'];

    // hanya bisa cancel jika status masih Diproses
    $cek = $conn->query("SELECT status_pengadaan FROM pengadaan WHERE idpengadaan=$id")->fetch_assoc();

    if ($cek['status_pengadaan'] == 'Diproses') {
        $conn->query("UPDATE pengadaan SET status_pengadaan='Cancel' WHERE idpengadaan=$id");
    }

    header("Location: pengadaan.php");
    exit;
}

include "../footer.php";
?>
