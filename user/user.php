<?php
$pageTitle = "Manajemen User | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

// ================= LIST DATA =================
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_user ORDER BY iduser ASC");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-people"></i> Data User</h3>
        <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah User</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['iduser'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['password']) ?></td>
                    <td><?= htmlspecialchars($row['role'] ?? '(Belum Ada Role)') ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $row['iduser'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <a href="?action=delete&id=<?= $row['iduser'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Hapus user ini?')">
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
elseif ($action == 'add') {
    $roles = $conn->query("SELECT * FROM role ORDER BY idrole ASC");
    ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-plus-circle"></i> Tambah User</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Role</label>
                <select name="idrole" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    <?php while($r = $roles->fetch_assoc()): ?>
                        <option value="<?= $r['idrole'] ?>"><?= $r['nama_role'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="user.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
    <?php
}

// ================= INSERT =================
elseif ($action == 'insert') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $idrole   = intval($_POST['idrole']);
    $conn->query("INSERT INTO user (username, password, idrole) VALUES ('$username', '$password', $idrole)");
    header("Location: user.php");
    exit;
}

// ================= FORM EDIT =================
elseif ($action == 'edit') {
    $id = intval($_GET['id']);
    $data = $conn->query("SELECT * FROM user WHERE iduser=$id")->fetch_assoc();
    $roles = $conn->query("SELECT * FROM role ORDER BY idrole ASC");
    ?>
    <h3 class="mb-3 text-gradient fw-bold"><i class="bi bi-pencil-square"></i> Edit User</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=update">
            <input type="hidden" name="iduser" value="<?= $data['iduser'] ?>">

            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input type="text" name="password" value="<?= htmlspecialchars($data['password']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Role</label>
                <select name="idrole" class="form-select" required>
                    <?php while($r = $roles->fetch_assoc()): ?>
                        <option value="<?= $r['idrole'] ?>" <?= ($r['idrole'] == $data['idrole']) ? 'selected' : '' ?>>
                            <?= $r['nama_role'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Update</button>
                <a href="user.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
    <?php
}

// ================= UPDATE =================
elseif ($action == 'update') {
    $iduser   = intval($_POST['iduser']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $idrole   = intval($_POST['idrole']);
    $conn->query("UPDATE user SET username='$username', password='$password', idrole=$idrole WHERE iduser=$iduser");
    header("Location: user.php");
    exit;
}

// ================= DELETE =================
elseif ($action == 'delete') {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM user WHERE iduser=$id");
    header("Location: user.php");
    exit;
}

include "../footer.php";
?>
