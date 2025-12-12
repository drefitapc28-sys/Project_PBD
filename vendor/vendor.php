<?php
$pageTitle = "Data Vendor | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

/* =======================================================
   1. LIST DATA (Aktif via VIEW)
   ======================================================= */
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_vendor ORDER BY idvendor ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-truck"></i> Data Vendor (Aktif)</h3>
        <div>
            <a href="?action=add" class="btn btn-primary me-2">
                <i class="bi bi-plus-circle"></i> Tambah Vendor
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
                <th>ID</th>
                <th>Nama Vendor</th>
                <th>Badan Hukum</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $r['idvendor'] ?></td>
            <td><?= htmlspecialchars($r['nama_vendor']) ?></td>
            <td><?= ($r['badan_hukum'] == 'Y') ? 'Ya' : 'Tidak' ?></td>
            <td><?= $r['status_vendor'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $r['idvendor'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="?action=delete&id=<?= $r['idvendor'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus data vendor ini?')">
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
   2. OPEN ALL (via VIEW v_vendor_all)
   ======================================================= */
elseif ($action == 'all') {
    $result = $conn->query("SELECT * FROM v_vendor_all ORDER BY idvendor ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-eye"></i> Data Vendor (Semua Data)</h3>
        <div>
            <a href="?action=add" class="btn btn-primary me-2">
                <i class="bi bi-plus-circle"></i> Tambah Vendor
            </a>
            <a href="vendor.php" class="btn btn-outline-primary">
                <i class="bi bi-eye-slash"></i> Tampilkan Aktif Saja
            </a>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-secondary">
            <tr>
                <th>ID</th>
                <th>Nama Vendor</th>
                <th>Badan Hukum</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $result->fetch_assoc()): ?>
        <tr class="<?= $r['status_vendor'] == 'Nonaktif' ? 'table-light text-muted' : '' ?>">
            <td><?= $r['idvendor'] ?></td>
            <td><?= htmlspecialchars($r['nama_vendor']) ?></td>
            <td><?= ($r['badan_hukum'] == 'Y') ? 'Ya' : 'Tidak' ?></td>
            <td><?= $r['status_vendor'] ?></td>
            <td>
                <a href="?action=edit&id=<?= $r['idvendor'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="?action=delete&id=<?= $r['idvendor'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus data vendor ini?')">
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
elseif ($action == 'add') { ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-plus-circle"></i> Tambah Vendor</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Vendor</label>
                <input type="text" name="nama_vendor" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Badan Hukum</label>
                <select name="badan_hukum" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="Y">Ya</option>
                    <option value="T">Tidak</option>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="vendor.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php
}

/* =======================================================
   4. INSERT
   ======================================================= */
elseif ($action == 'insert') {
    $nama = $_POST['nama_vendor'];
    $badan = $_POST['badan_hukum'];
    $stmt = $conn->prepare("INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES (?, ?, 'A')");
    $stmt->bind_param("ss", $nama, $badan);
    $stmt->execute();
    header("Location: vendor.php");
    exit;
}

/* =======================================================
   5. FORM EDIT
   ======================================================= */
elseif ($action == 'edit') {
    $id = (int)$_GET['id'];
    $data = $conn->query("SELECT * FROM vendor WHERE idvendor=$id")->fetch_assoc();
    ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-pencil-square"></i> Edit Vendor</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=update">
            <input type="hidden" name="idvendor" value="<?= $data['idvendor'] ?>">

            <div class="mb-3">
                <label class="form-label fw-bold">Nama Vendor</label>
                <input type="text" name="nama_vendor" value="<?= htmlspecialchars($data['nama_vendor']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Badan Hukum</label>
                <select name="badan_hukum" class="form-select" required>
                    <option value="Y" <?= ($data['badan_hukum'] == 'Y') ? 'selected' : '' ?>>Ya</option>
                    <option value="T" <?= ($data['badan_hukum'] == 'T') ? 'selected' : '' ?>>Tidak</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select" required>
                    <option value="A" <?= ($data['status'] == 'A') ? 'selected' : '' ?>>Aktif</option>
                    <option value="N" <?= ($data['status'] == 'N') ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Update</button>
                <a href="vendor.php?action=all" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php
}

/* =======================================================
   6. UPDATE
   ======================================================= */
elseif ($action == 'update') {
    $stmt = $conn->prepare("UPDATE vendor SET nama_vendor=?, badan_hukum=?, status=? WHERE idvendor=?");
    $stmt->bind_param("sssi", $_POST['nama_vendor'], $_POST['badan_hukum'], $_POST['status'], $_POST['idvendor']);
    $stmt->execute();
    header("Location: vendor.php?action=all");
    exit;
}

/* =======================================================
   7. DELETE (Soft Delete)
   ======================================================= */
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE vendor SET status='N' WHERE idvendor=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: vendor.php");
    exit;
}

include "../footer.php";
?>
