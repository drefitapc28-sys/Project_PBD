<?php
$pageTitle = "Data Barang | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

/* =======================================================
   1. LIST DATA (Aktif via VIEW)
   ======================================================= */
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_barang ORDER BY idbarang ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-box-seam"></i> Data Barang (Aktif)</h3>
        <div>
            <a href="?action=add" class="btn btn-primary me-2"><i class="bi bi-plus-circle"></i> Tambah Barang</a>
            <a href="?action=all" class="btn btn-outline-success"><i class="bi bi-eye"></i> Open All</a>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nama Barang</th>
                <th>Harga</th>
                <th>Satuan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $r['idbarang'] ?></td>
            <td><?= htmlspecialchars($r['nama_barang']) ?></td>
            <td>Rp <?= number_format($r['harga'], 0, ",", ".") ?></td>
            <td><?= htmlspecialchars($r['nama_satuan']) ?></td>
            <td><?= $r['status_barang'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $r['idbarang'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="?action=delete&id=<?= $r['idbarang'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus barang ini?')">
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

/* =======================================================
   2. OPEN ALL (via VIEW v_barang_all)
   ======================================================= */
elseif ($action == 'all') {
    $result = $conn->query("SELECT * FROM v_barang_all ORDER BY idbarang ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-eye"></i> Data Barang (Semua Data)</h3>
        <div>
            <a href="?action=add" class="btn btn-primary me-2"><i class="bi bi-plus-circle"></i> Tambah Barang</a>
            <a href="barang.php" class="btn btn-outline-primary"><i class="bi bi-eye-slash"></i> Tampilkan Aktif Saja</a>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-secondary">
            <tr>
                <th>ID</th>
                <th>Nama Barang</th>
                <th>Harga</th>
                <th>Satuan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $result->fetch_assoc()): ?>
        <tr class="<?= $r['status_barang'] == 'Tidak Aktif' ? 'table-light text-muted' : '' ?>">
            <td><?= $r['idbarang'] ?></td>
            <td><?= htmlspecialchars($r['nama_barang']) ?></td>
            <td>Rp <?= number_format($r['harga'], 0, ",", ".") ?></td>
            <td><?= htmlspecialchars($r['nama_satuan']) ?></td>
            <td><?= $r['status_barang'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $r['idbarang'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="?action=delete&id=<?= $r['idbarang'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus barang ini?')">
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

/* =======================================================
   3. FORM TAMBAH
   ======================================================= */
elseif ($action == 'add') {
    $satuan = $conn->query("SELECT * FROM satuan WHERE status=1 ORDER BY idsatuan ASC");
    ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-plus-circle"></i> Tambah Barang</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Barang</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Harga</label>
                <input type="number" name="harga" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Satuan</label>
                <select name="idsatuan" class="form-select" required>
                    <option value="">-- Pilih Satuan --</option>
                    <?php while($s = $satuan->fetch_assoc()): ?>
                        <option value="<?= $s['idsatuan'] ?>"><?= $s['nama_satuan'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="barang.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php
}

/* =======================================================
   4. INSERT
   ======================================================= */
elseif ($action == 'insert') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $harga = (float)$_POST['harga'];
    $idsatuan = (int)$_POST['idsatuan'];
    $conn->query("INSERT INTO barang (nama, jenis, harga, idsatuan, status) VALUES ('$nama', 'B', $harga, $idsatuan, 1)");
    header("Location: barang.php");
    exit;
}

/* =======================================================
   5. FORM EDIT
   ======================================================= */
elseif ($action == 'edit') {
    $id = (int)$_GET['id'];
    $data = $conn->query("SELECT * FROM barang WHERE idbarang=$id")->fetch_assoc();
    $satuan = $conn->query("SELECT * FROM satuan ORDER BY idsatuan ASC");
    ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-pencil-square"></i> Edit Barang</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=update">
            <input type="hidden" name="idbarang" value="<?= $data['idbarang'] ?>">

            <div class="mb-3">
                <label class="form-label fw-bold">Nama Barang</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Harga</label>
                <input type="number" name="harga" value="<?= $data['harga'] ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Satuan</label>
                <select name="idsatuan" class="form-select" required>
                    <?php while($s = $satuan->fetch_assoc()): ?>
                        <option value="<?= $s['idsatuan'] ?>" <?= ($s['idsatuan'] == $data['idsatuan']) ? 'selected' : '' ?>>
                            <?= $s['nama_satuan'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="1" <?= $data['status']==1?'selected':''; ?>>Aktif</option>
                    <option value="0" <?= $data['status']==0?'selected':''; ?>>Tidak Aktif</option>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Update</button>
                <a href="barang.php?action=all" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php
}

/* =======================================================
   6. UPDATE
   ======================================================= */
elseif ($action == 'update') {
    $id = (int)$_POST['idbarang'];
    $nama = $conn->real_escape_string($_POST['nama']);
    $harga = (float)$_POST['harga'];
    $idsatuan = (int)$_POST['idsatuan'];
    $status = (int)$_POST['status'];
    $conn->query("UPDATE barang SET nama='$nama', harga=$harga, idsatuan=$idsatuan, status=$status WHERE idbarang=$id");
    header("Location: barang.php?action=all");
    exit;
}

/* =======================================================
   7. DELETE (Soft Delete)
   ======================================================= */
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE barang SET status=0 WHERE idbarang=$id");
    header("Location: barang.php");
    exit;
}

include "../footer.php";
?>
