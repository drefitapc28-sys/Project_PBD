<?php 
$pageTitle = "Master Satuan | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

/* =======================
   1. LIST (Aktif via VIEW)
   ======================= */
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_satuan ORDER BY idsatuan ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-tags"></i> Master Satuan (Aktif)</h3>
        <div>
            <a href="?action=add" class="btn btn-primary me-2"><i class="bi bi-plus-circle"></i> Tambah Satuan</a>
            <a href="?action=all" class="btn btn-outline-success"><i class="bi bi-eye"></i> Open All </a>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nama Satuan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $r['idsatuan'] ?></td>
            <td><?= htmlspecialchars($r['nama_satuan']) ?></td>
            <td><?= $r['status_satuan'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $r['idsatuan'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="?action=delete&id=<?= $r['idsatuan'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus satuan ini?')">
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
   2. LIST ALL (via Stored Procedure)
   ======================= */
elseif ($action == 'all') {
    $result = $conn->query("SELECT * FROM v_satuan_all ORDER BY idsatuan ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-eye"></i> Master Satuan </h3>
        <div>
            <a href="?action=add" class="btn btn-primary me-2"><i class="bi bi-plus-circle"></i> Tambah Satuan</a>
            <a href="satuan.php" class="btn btn-outline-primary"><i class="bi bi-eye-slash"></i> Tampilkan Aktif Saja</a>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-secondary">
            <tr>
                <th>ID</th>
                <th>Nama Satuan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
     <?php while($r = $result->fetch_assoc()): ?>
        <tr class="<?= $r['status_satuan'] == 'Tidak Aktif' ? 'table-light text-muted' : '' ?>">
            <td><?= $r['idsatuan'] ?></td>
            <td><?= htmlspecialchars($r['nama_satuan']) ?></td>
            <td><?= $r['status_satuan'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $r['idsatuan'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="?action=delete&id=<?= $r['idsatuan'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus satuan ini?')">
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
   3. FORM TAMBAH
   ======================= */
elseif ($action == 'add') { ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-plus-circle"></i> Tambah Satuan</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Satuan</label>
                <input type="text" name="nama_satuan" class="form-control" required>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="satuan.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php }

/* =======================
   4. INSERT
   ======================= */
elseif ($action == 'insert') {
    $nama = $conn->real_escape_string($_POST['nama_satuan']);
    $conn->query("INSERT INTO satuan (nama_satuan, status) VALUES ('$nama', 1)");
    header("Location: satuan.php");
    exit;
}

/* =======================
   5. EDIT & UPDATE
   ======================= */
elseif ($action == 'edit') {
    $id = (int)$_GET['id'];
    $data = $conn->query("SELECT * FROM satuan WHERE idsatuan=$id")->fetch_assoc();
    ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-pencil-square"></i> Edit Satuan</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=update">
            <input type="hidden" name="idsatuan" value="<?= $data['idsatuan'] ?>">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Satuan</label>
                <input type="text" name="nama_satuan" value="<?= htmlspecialchars($data['nama_satuan']) ?>" class="form-control" required>
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
                <a href="satuan.php?show=all" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php
}
elseif ($action == 'update') {
    $id = (int)$_POST['idsatuan'];
    $nama = $conn->real_escape_string($_POST['nama_satuan']);
    $status = (int)$_POST['status'];
    $conn->query("UPDATE satuan SET nama_satuan='$nama', status=$status WHERE idsatuan=$id");
    header("Location: satuan.php?show=all");
    exit;
}

/* =======================
   6. DELETE (Soft Delete)
   ======================= */
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE satuan SET status=0 WHERE idsatuan=$id");
    header("Location: satuan.php");
    exit;
}

include "../footer.php";
?>
