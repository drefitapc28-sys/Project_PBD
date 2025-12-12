<?php
$pageTitle = "Margin Penjualan | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

/* =======================
   1. LIST DATA (Aktif via VIEW)
   ======================= */
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_margin_penjualan ORDER BY idmargin_penjualan ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-graph-up"></i> Margin Penjualan (Aktif)</h3>
        <div>
            <a href="?action=add" class="btn btn-primary me-2">
                <i class="bi bi-plus-circle"></i> Tambah Margin
            </a>
            <a href="?action=all" class="btn btn-outline-success">
                <i class="bi bi-eye"></i> Open All
            </a>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-primary">
            <tr>
                <th>ID</th><th>Dibuat</th><th>Diupdate</th>
                <th>Persen Margin</th><th>Status</th><th>Dibuat Oleh</th>
                <th>Simulasi Harga Jual (Rp)</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $barang = $conn->query("SELECT harga FROM barang WHERE status=1 LIMIT 1");
        $b = $barang->fetch_assoc();
        $harga_pokok = $b['harga'] ?? 10000;

        while ($r = $result->fetch_assoc()):
            $harga_jual = $conn->query("SELECT fn_hitung_margin($harga_pokok, {$r['persen_margin']}) AS hasil")->fetch_assoc()['hasil'];
        ?>
            <tr>
                <td><?= $r['idmargin_penjualan'] ?></td>
                <td><?= $r['created_at'] ?></td>
                <td><?= $r['updated_at'] ?></td>
                <td><?= $r['persen_margin'] ?>%</td>
                <td><?= $r['status_margin'] ?></td>
                <td><?= htmlspecialchars($r['dibuat_oleh']) ?></td>
                <td>Rp <?= number_format($harga_jual, 0, ',', '.') ?></td>
                <td>
                    <a href="?action=delete&id=<?= $r['idmargin_penjualan'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Hapus margin ini?')">
                       <i class="bi bi-trash"></i> Hapus
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
<?php
}

/* =======================
   2. OPEN ALL (via VIEW)
   ======================= */
elseif ($action == 'all') {
    $result = $conn->query("SELECT * FROM v_margin_penjualan_all ORDER BY idmargin_penjualan ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-eye"></i> Margin Penjualan (Semua)</h3>
        <div>
            <a href="?action=add" class="btn btn-primary me-2">
                <i class="bi bi-plus-circle"></i> Tambah Margin
            </a>
            <a href="margin_penjualan.php" class="btn btn-outline-primary">
                <i class="bi bi-eye-slash"></i> Tampilkan Aktif Saja
            </a>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-secondary">
            <tr>
                <th>ID</th><th>Dibuat</th><th>Diupdate</th>
                <th>Persen Margin</th><th>Status</th><th>Dibuat Oleh</th>
                <th>Simulasi Harga Jual (Rp)</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $barang = $conn->query("SELECT harga FROM barang WHERE status=1 LIMIT 1");
        $b = $barang->fetch_assoc();
        $harga_pokok = $b['harga'] ?? 10000;

        while ($r = $result->fetch_assoc()):
            $harga_jual = $conn->query("SELECT fn_hitung_margin($harga_pokok, {$r['persen_margin']}) AS hasil")->fetch_assoc()['hasil'];
        ?>
            <tr class="<?= $r['status_margin'] == 'Tidak Aktif' ? 'table-light text-muted' : '' ?>">
                <td><?= $r['idmargin_penjualan'] ?></td>
                <td><?= $r['created_at'] ?></td>
                <td><?= $r['updated_at'] ?></td>
                <td><?= $r['persen_margin'] ?>%</td>
                <td><?= $r['status_margin'] ?></td>
                <td><?= htmlspecialchars($r['dibuat_oleh']) ?></td>
                <td>Rp <?= number_format($harga_jual, 0, ',', '.') ?></td>
                <td>
                    <a href="?action=delete&id=<?= $r['idmargin_penjualan'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Hapus margin ini?')">
                       <i class="bi bi-trash"></i> Hapus
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
<?php
}

/* =======================
   3. FORM TAMBAH, INSERT, DELETE 
   ======================= */
elseif ($action == 'add') {
    $user = $conn->query("SELECT iduser, username FROM user ORDER BY iduser ASC");
    ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-plus-circle"></i> Tambah Margin Penjualan</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <div class="mb-3">
                <label class="form-label fw-bold">Persen Margin (%)</label>
                <input type="number" step="0.01" name="persen" class="form-control" required>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-person-circle"></i> Margin ini akan dibuat oleh user:
                <strong><?= $_SESSION['username'] ?></strong>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="margin_penjualan.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php
}

/* =======================
   4. INSERT
   ======================= */
elseif ($action == 'insert') {
    $persen = (float)$_POST['persen'];
    $iduser = (int)($_SESSION['iduser'] ?? 0);

    if ($iduser === 0) {
        die("<div class='alert alert-danger'>Error: User tidak terdeteksi. Silakan login ulang.</div>");
    }

    // Tambahkan data baru
    $conn->query("INSERT INTO margin_penjualan (created_at, updated_at, persen, status, iduser)
                  VALUES (NOW(), NOW(), $persen, 1, $iduser)");

    $new_id = $conn->insert_id;
    $conn->query("CALL sp_set_margin_aktif($new_id)");

    header("Location: margin_penjualan.php");
    exit;
}


/* =======================
   5. DELETE
   ======================= */
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM margin_penjualan WHERE idmargin_penjualan=$id");
    header("Location: margin_penjualan.php");
    exit;
}

include "../footer.php";
?>
