<?php
$pageTitle = "Master Role | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

// ================= LIST DATA =================
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_role ORDER BY idrole ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-person-badge"></i> Master Role</h3>
        <a href="?action=add" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Role
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Nama Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($r = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $r['idrole'] ?></td>
                    <td><?= htmlspecialchars($r['nama_role']) ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $r['idrole'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <a href="?action=delete&id=<?= $r['idrole'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Yakin ingin menghapus role ini?')">
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

// ================= FORM TAMBAH =================
elseif ($action == 'add') { ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-plus-circle"></i> Tambah Role</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Role</label>
                <input type="text" name="nama_role" class="form-control" required>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="role.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php }

// ================= INSERT =================
elseif ($action == 'insert') {
    $nama = $conn->real_escape_string($_POST['nama_role']);
    $conn->query("INSERT INTO role (nama_role) VALUES ('$nama')");
    header("Location: role.php");
    exit;
}

// ================= FORM EDIT =================
elseif ($action == 'edit') {
    $id = (int)$_GET['id'];
    $data = $conn->query("SELECT * FROM role WHERE idrole=$id")->fetch_assoc(); ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-pencil-square"></i> Edit Role</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=update">
            <input type="hidden" name="idrole" value="<?= $data['idrole'] ?>">
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Role</label>
                <input type="text" name="nama_role" value="<?= htmlspecialchars($data['nama_role']) ?>" class="form-control" required>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Update</button>
                <a href="role.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
<?php }

// ================= UPDATE =================
elseif ($action == 'update') {
    $id = (int)$_POST['idrole'];
    $nama = $conn->real_escape_string($_POST['nama_role']);
    $conn->query("UPDATE role SET nama_role='$nama' WHERE idrole=$id");
    header("Location: role.php");
    exit;
}

// ================= DELETE =================
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM role WHERE idrole=$id");
    header("Location: role.php");
    exit;
}

include "../footer.php";
?>
