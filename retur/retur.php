<?php
$pageTitle = "Transaksi Retur Barang | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

/* =======================
   LIST DATA
   ======================= */
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_retur_barang ORDER BY idretur DESC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-arrow-return-left"></i> Data Retur Barang</h3>
        <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Retur</a>
    </div>

    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-primary">
            <tr>
                <th>ID</th><th>Tanggal</th><th>ID Penerimaan</th><th>Dibuat Oleh</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $r['idretur'] ?></td>
                <td><?= $r['tanggal_retur'] ?></td>
                <td><?= $r['idpenerimaan'] ?></td>
                <td><?= htmlspecialchars($r['dibuat_oleh']) ?></td>
                <td>
                    <a href="?action=view&id=<?= $r['idretur'] ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                    <a href="?action=delete&id=<?= $r['idretur'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Hapus retur ini?')">
                       <i class="bi bi-trash"></i> Hapus
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php
}

/* =======================
   FORM TAMBAH
   ======================= */
elseif ($action == 'add') {
    $penerimaan = $conn->query("SELECT idpenerimaan FROM penerimaan");
    $user = $conn->query("SELECT iduser, username FROM user");
    ?>
    <h3 class="text-gradient fw-bold mb-3"><i class="bi bi-plus-circle"></i> Tambah Retur Barang</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <div class="mb-3">
                <label class="form-label fw-bold">ID Penerimaan</label>
                <select name="idpenerimaan" class="form-select" required>
                    <?php while($p = $penerimaan->fetch_assoc()): ?>
                        <option value="<?= $p['idpenerimaan'] ?>"><?= $p['idpenerimaan'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">User</label>
                <select name="iduser" class="form-select" required>
                    <?php while($u = $user->fetch_assoc()): ?>
                        <option value="<?= $u['iduser'] ?>"><?= $u['username'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="retur.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php
}

/* =======================
   INSERT DATA
   ======================= */
elseif ($action == 'insert') {
    $idpenerimaan = (int)$_POST['idpenerimaan'];
    $iduser = (int)$_POST['iduser'];
    $conn->query("INSERT INTO retur_barang (created_at, idpenerimaan, iduser)
                  VALUES (NOW(), $idpenerimaan, $iduser)");
    header("Location: retur.php");
    exit;
}

/* =======================
   LIHAT DETAIL
   ======================= */
elseif ($action == 'view') {
    $id = (int)$_GET['id'];
    $header = $conn->query("SELECT * FROM v_retur_barang WHERE idretur=$id")->fetch_assoc();
    $detail = $conn->query("SELECT * FROM v_detail_retur WHERE idretur=$id");
    ?>
    <h3 class="text-gradient fw-bold mb-3"><i class="bi bi-receipt"></i> Detail Retur Barang #<?= $id ?></h3>
    <div class="card p-4 mb-3 shadow-sm">
        <p><strong>ID Penerimaan:</strong> <?= $header['idpenerimaan'] ?></p>
        <p><strong>Dibuat Oleh:</strong> <?= $header['dibuat_oleh'] ?></p>
        <p><strong>Tanggal Retur:</strong> <?= $header['tanggal_retur'] ?></p>
    </div>

    <table class="table table-bordered align-middle text-center shadow-sm">
        <thead class="table-secondary">
            <tr>
                <th>Nama Barang</th><th>Jumlah</th><th>Alasan</th>
            </tr>
        </thead>
        <tbody>
        <?php while($d = $detail->fetch_assoc()): ?>
            <tr>
                <td><?= $d['nama_barang'] ?></td>
                <td><?= $d['jumlah'] ?></td>
                <td><?= $d['alasan'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <div class="text-end mt-3">
        <a href="retur.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
<?php
}

/* =======================
   DELETE
   ======================= */
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM retur_barang WHERE idretur=$id");
    header("Location: retur.php");
    exit;
}

include "../footer.php";
?>
