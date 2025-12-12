<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // cek username dan password
    $stmt = $conn->prepare("SELECT u.*, r.nama_role FROM user u 
                            JOIN role r ON u.idrole = r.idrole 
                            WHERE u.username=? AND u.password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        if ($data['nama_role'] != "Super Admin") {
            $error = "❌ Hanya Super Admin yang boleh login!";
        } else {
            $_SESSION['iduser'] = $data['iduser'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role'] = $data['nama_role'];

            header("Location: index.php");
            exit;
        }
    } else {
        $error = "❌ Username atau password salah!";
    }

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Sistem Pengadaan Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height:100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-lg border-0 p-4">
                <h3 class="text-center mb-4 text-gradient">
                    <i class="bi bi-box-arrow-in-right"></i> Login Sistem
                </h3>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                </form>
                <hr>
                <p class="text-center text-muted small mb-0">© 2025 Project PBD</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
