<?php
session_start();
include "../header.php";

$id_penerimaan = (int)$_GET['id_penerimaan'];
$id_pengadaan  = (int)$_GET['id_pengadaan'];

// Ambil data header
$header = $conn->query("
    SELECT p.idpenerimaan, p.created_at, p.status, v.nama_vendor, u.username 
    FROM penerimaan p
    JOIN pengadaan pg ON p.idpengadaan = pg.idpengadaan
    JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    JOIN user u ON p.iduser = u.iduser
    WHERE p.idpenerimaan = $id_penerimaan
")->fetch_assoc();
?>

<h3 class="text-gradient fw-bold mb-3">
    <i class="bi bi-box-arrow-in-down"></i> Input Detail Penerimaan #<?= $id_penerimaan ?>
</h3>

<div class="card p-4 mb-3 shadow-sm">
    <p><strong>Vendor:</strong> <?= $header['nama_vendor'] ?></p>
    <p><strong>Diterima Oleh:</strong> <?= $header['username'] ?></p>
    <p><strong>Tanggal:</strong> <?= $header['created_at'] ?></p>
</div>

<?php
// proses insert detail
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idbarang = (int)$_POST['idbarang'];
    $jumlah_terima = (int)$_POST['jumlah_terima'];

    // ambil harga satuan dari pengadaan
    $q_harga = $conn->query("
        SELECT harga_satuan 
        FROM detail_pengadaan 
        WHERE idpengadaan = $id_pengadaan AND idbarang = $idbarang
    ")->fetch_assoc();
    $harga_satuan = (int)($q_harga['harga_satuan'] ?? 0);
    $subtotal = $harga_satuan * $jumlah_terima;

    // ambil jumlah yang dipesan
    $q_pesan = $conn->query("
        SELECT jumlah 
        FROM detail_pengadaan 
        WHERE idpengadaan = $id_pengadaan AND idbarang = $idbarang
    ")->fetch_assoc();
    $jumlah_pesan = (int)($q_pesan['jumlah'] ?? 0);

    // ambil total sudah diterima
    $q_terima = $conn->query("
        SELECT COALESCE(SUM(dp.jumlah_terima),0) AS total_terima
        FROM detail_penerimaan dp
        JOIN penerimaan p ON dp.idpenerimaan = p.idpenerimaan
        WHERE p.idpengadaan = $id_pengadaan AND dp.barang_idbarang = $idbarang
    ")->fetch_assoc();
    $total_terima = (int)$q_terima['total_terima'];

    // validasi tidak boleh melebihi pesanan
    if ($total_terima + $jumlah_terima > $jumlah_pesan) {
        echo "<script>
            alert('Jumlah diterima melebihi jumlah pengadaan! Dipesan: $jumlah_pesan, sudah diterima: $total_terima.');
            window.history.back();
        </script>";
        exit;
    }

    // insert ke detail penerimaan
    $conn->query("
        INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima)
        VALUES ($id_penerimaan, $idbarang, $jumlah_terima, $harga_satuan, $subtotal)
    ");

    // cek apakah semua barang sudah diterima penuh
    $cek_selesai = $conn->query("
        SELECT COUNT(*) AS sisa
        FROM detail_pengadaan d
        WHERE d.idpengadaan = $id_pengadaan
        AND d.jumlah > (
            SELECT COALESCE(SUM(dp.jumlah_terima),0)
            FROM detail_penerimaan dp
            JOIN penerimaan p ON dp.idpenerimaan = p.idpenerimaan
            WHERE p.idpengadaan = d.idpengadaan
            AND dp.barang_idbarang = d.idbarang
        )
    ")->fetch_assoc()['sisa'];

    // kalau tidak ada sisa, ubah status pengadaan jadi selesai
    if ($cek_selesai == 0) {
        $conn->query("UPDATE pengadaan SET status_pengadaan = 'Selesai' WHERE idpengadaan = $id_pengadaan");
    }

    echo "<script>window.location='penerimaan_input_detail.php?id_penerimaan=$id_penerimaan&id_pengadaan=$id_pengadaan';</script>";
    exit;
}
?>

<div class="card p-4 mb-4 w-75 mx-auto shadow-sm border-0">
<form method="POST">
    <label class="form-label fw-bold">Pilih Barang Pengadaan</label>
    <select name="idbarang" class="form-select mb-3" required>
        <option value="">-- Pilih Barang --</option>
        <?php
        $barang = $conn->query("
            SELECT b.idbarang, b.nama, dp.jumlah, dp.harga_satuan
            FROM detail_pengadaan dp
            JOIN barang b ON dp.idbarang = b.idbarang
            WHERE dp.idpengadaan = $id_pengadaan
        ");
        while($b = $barang->fetch_assoc()):
        ?>
        <option value="<?= $b['idbarang'] ?>">
            <?= $b['nama'] ?> (Pesan: <?= $b['jumlah'] ?>) - Rp <?= number_format($b['harga_satuan'], 0, ",", ".") ?>
        </option>
        <?php endwhile; ?>
    </select>

    <label class="form-label fw-bold">Jumlah Diterima</label>
    <input type="number" name="jumlah_terima" min="1" class="form-control mb-3" required>

    <div class="text-end">
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
        <a href="../penerimaan/penerimaan.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</form>
</div>

<table class="table table-bordered text-center shadow-sm">
    <thead class="table-secondary">
        <tr>
            <th>Nama Barang</th>
            <th>Jumlah Diterima</th>
            <th>Harga Satuan</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $detail = $conn->query("
            SELECT b.nama AS nama_barang, dp.jumlah_terima, dp.harga_satuan_terima, dp.sub_total_terima
            FROM detail_penerimaan dp
            JOIN barang b ON dp.barang_idbarang = b.idbarang
            WHERE dp.idpenerimaan = $id_penerimaan
        ");
        $grand_total = 0;
        while($d = $detail->fetch_assoc()):
            $grand_total += $d['sub_total_terima'];
        ?>
        <tr>
            <td><?= $d['nama_barang'] ?></td>
            <td><?= $d['jumlah_terima'] ?></td>
            <td>Rp <?= number_format($d['harga_satuan_terima'], 0, ",", ".") ?></td>
            <td>Rp <?= number_format($d['sub_total_terima'], 0, ",", ".") ?></td>
        </tr>
        <?php endwhile; ?>
        <tr class="table-light fw-bold">
            <td colspan="3">Total</td>
            <td>Rp <?= number_format($grand_total, 0, ",", ".") ?></td>
        </tr>
    </tbody>
</table>

<?php include "../footer.php"; ?>
