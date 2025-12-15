<?php
session_start();
$pageTitle = "Transaksi Penerimaan | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

/* =======================
   LIST DATA PENERIMAAN
   ======================= */
if ($action == 'list') {
    $result = $conn->query("
        SELECT p.idpenerimaan, p.created_at, p.status, pg.idpengadaan, v.nama_vendor, u.username AS diterima_oleh
        FROM penerimaan p
        JOIN pengadaan pg ON p.idpengadaan = pg.idpengadaan
        JOIN vendor v ON pg.vendor_idvendor = v.idvendor
        JOIN user u ON p.iduser = u.iduser
        ORDER BY p.idpenerimaan DESC
    ");
    ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-gradient fw-bold"><i class="bi bi-box-arrow-in-down"></i> Data Penerimaan</h3>
        <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Terima Barang</a>
    </div>

    <table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>PO</th>
                <th>Vendor</th>
                <th>Diterima Oleh</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $result->fetch_assoc()): 
            $statusText = ($r['status'] == 'Y') ? 'Selesai' : 'Draft';
            $badge = ($r['status'] == 'Y') ? 'bg-success' : 'bg-warning text-dark';
        ?>
            <tr>
                <td><?= $r['idpenerimaan'] ?></td>
                <td>#<?= $r['idpengadaan'] ?></td>
                <td><?= $r['nama_vendor'] ?></td>
                <td><?= $r['diterima_oleh'] ?></td>
                <td><?= $r['created_at'] ?></td>
                <td><span class="badge <?= $badge ?>"><?= $statusText ?></span></td>
                <td>
                    <a href="?action=view&id=<?= $r['idpenerimaan'] ?>" class="btn btn-info btn-sm">
                        <i class="bi bi-eye"></i> Detail
                    </a>

                    <?php
                    // cek apakah pengadaan masih ada sisa barang belum diterima
                    $cek = $conn->query("
                        SELECT COUNT(*) AS sisa
                        FROM detail_pengadaan d
                        WHERE d.idpengadaan = {$r['idpengadaan']}
                        AND d.jumlah > (
                            SELECT COALESCE(SUM(dp.jumlah_terima),0)
                            FROM detail_penerimaan dp
                            JOIN penerimaan p2 ON dp.idpenerimaan = p2.idpenerimaan
                            WHERE p2.idpengadaan = d.idpengadaan
                            AND dp.barang_idbarang = d.idbarang
                        )
                    ")->fetch_assoc()['sisa'];

                    if ($r['status'] == 'T' && $cek > 0): ?>
                        <a href="../penerimaan/penerimaan_input_detail.php?id_penerimaan=<?= $r['idpenerimaan'] ?>&id_pengadaan=<?= $r['idpengadaan'] ?>" 
                           class="btn btn-primary btn-sm">
                           <i class="bi bi-pencil"></i> Input Detail
                        </a>
                    <?php endif; ?>

                    <?php if ($r['status'] == 'T' && $cek == 0): ?>
                        <a href="?action=finalize&id=<?= $r['idpenerimaan'] ?>" 
                           onclick="return confirm('Tandai penerimaan ini selesai?')"
                           class="btn btn-success btn-sm">
                           <i class="bi bi-check-circle"></i> Selesai
                        </a>
                    <?php endif; ?>

                    <a href="?action=delete&id=<?= $r['idpenerimaan'] ?>" 
                       onclick="return confirm('Yakin ingin menghapus data penerimaan ini? Semua detail akan ikut terhapus.')"
                       class="btn btn-danger btn-sm">
                       <i class="bi bi-trash"></i> Hapus
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php } ?>


<?php
/* =======================
   TAMBAH PENERIMAAN (Langkah 1)
   ======================= */
if ($action == 'add') {
    // hanya tampilkan pengadaan yang masih punya barang belum diterima penuh
    $pengadaan = $conn->query("
        SELECT p.idpengadaan, v.nama_vendor
        FROM pengadaan p
        JOIN vendor v ON p.vendor_idvendor = v.idvendor
        WHERE EXISTS (
            SELECT 1 FROM detail_pengadaan d
            WHERE d.idpengadaan = p.idpengadaan
            AND d.jumlah > (
                SELECT COALESCE(SUM(dp.jumlah_terima),0)
                FROM detail_penerimaan dp
                JOIN penerimaan pn ON dp.idpenerimaan = pn.idpenerimaan
                WHERE pn.idpengadaan = p.idpengadaan
                AND dp.barang_idbarang = d.idbarang
            )
        )
        ORDER BY p.idpengadaan DESC
    ");
?>
    <h3 class="text-gradient fw-bold mb-3"><i class="bi bi-plus-circle"></i> Terima Barang (Langkah 1/2)</h3>
    <div class="card p-4 w-75 mx-auto shadow-sm border-0">
        <form method="POST" action="?action=insert">
            <label class="form-label fw-bold">Pilih Pengadaan (PO)</label>
            <select name="idpengadaan" class="form-select mb-3" required>
                <option value="">-- Pilih Pengadaan --</option>
                <?php while($p = $pengadaan->fetch_assoc()): ?>
                    <option value="<?= $p['idpengadaan'] ?>">PO #<?= $p['idpengadaan'] ?> - <?= $p['nama_vendor'] ?></option>
                <?php endwhile; ?>
            </select>

            <div class="text-end">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-arrow-right-circle"></i> Lanjut ke Input Detail
                </button>
                <a href="penerimaan.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
<?php } ?>

<?php
/* =======================
   VIEW DETAIL
   ======================= */
if ($action == 'view') {
    $id = (int)$_GET['id'];
    $header = $conn->query("
    SELECT p.*, pg.idpengadaan, v.nama_vendor, u.username AS diterima_oleh
    FROM penerimaan p
    JOIN pengadaan pg ON p.idpengadaan = pg.idpengadaan
    JOIN vendor v ON pg.vendor_idvendor = v.idvendor
    JOIN user u ON p.iduser = u.iduser
    WHERE p.idpenerimaan = $id
    ")->fetch_assoc();

    $statusCode = $header['status']; // ambil langsung dari tabel penerimaan, bukan view

    $detail = $conn->query("SELECT * FROM v_detail_penerimaan WHERE idpenerimaan=$id");

    $statusCode = $header['status'] ?? 'T';
    $isDraft = ($statusCode == 'T');
?>
    <h3 class="text-gradient fw-bold mb-3"><i class="bi bi-box-arrow-in-down"></i> Detail Penerimaan #<?= $id ?></h3>

    <div class="card p-4 mb-3 shadow-sm">
        <p><strong>Vendor:</strong> <?= $header['nama_vendor'] ?></p>
        <p><strong>Diterima Oleh:</strong> <?= $header['diterima_oleh'] ?></p>
        <p><strong>Tanggal:</strong> <?= $header['created_at'] ?> </p>
        <p><strong>Status:</strong> <?= ($isDraft ? 'Draft' : 'Selesai') ?></p>
    </div>

    <?php if ($isDraft): ?>
        <a href="../penerimaan/penerimaan_input_detail.php?id_penerimaan=<?= $id ?>&id_pengadaan=<?= $header['idpengadaan'] ?>" 
           class="btn btn-primary mb-3">
           <i class="bi bi-plus-circle"></i> Tambah Barang Penerimaan
        </a>
    <?php endif; ?>

    <table class="table table-bordered text-center shadow-sm">
        <thead class="table-secondary">
            <tr>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php $total = 0; while($d = $detail->fetch_assoc()): $total += $d['sub_total_terima']; ?>
            <tr>
                <td><?= $d['nama_barang'] ?></td>
                <td><?= $d['jumlah_terima'] ?></td>
                <td>Rp <?= number_format($d['harga_satuan_terima'], 0, ",", ".") ?></td>
                <td>Rp <?= number_format($d['sub_total_terima'], 0, ",", ".") ?></td>
            </tr>
        <?php endwhile; ?>
        <tr class="table-light fw-bold">
            <td colspan="3">Total</td>
            <td>Rp <?= number_format($total, 0, ",", ".") ?></td>
        </tr>
        </tbody>
    </table>

    <div class="text-end mt-3">
        <a href="penerimaan.php" class="btn btn-secondary">Kembali</a>
    </div>
<?php } ?>

<?php
if ($action == 'insert') {
    $idpengadaan = (int)$_POST['idpengadaan'];
    $iduser = $_SESSION['iduser'];

    $conn->query("
        INSERT INTO penerimaan (created_at, status, idpengadaan, iduser)
        VALUES (NOW(), 'T', $idpengadaan, $iduser)
    ");

    $lastId = $conn->insert_id;

    header("Location: ../penerimaan/penerimaan_input_detail.php?id_penerimaan=$lastId&id_pengadaan=$idpengadaan");
    exit;
}

/* =======================
   FINALISASI PENERIMAAN
   ======================= */
if ($action == 'finalize') {
    $id = (int)$_GET['id'];

    // ubah status penerimaan ke selesai (Y)
    $conn->query("UPDATE penerimaan SET status='Y' WHERE idpenerimaan=$id");

    $conn->query("
        UPDATE pengadaan 
        SET status_pengadaan='Selesai' 
        WHERE idpengadaan = (SELECT idpengadaan FROM penerimaan WHERE idpenerimaan=$id)
    ");

    header("Location: penerimaan.php?action=view&id=$id");
    exit;
}

/* =======================
   HAPUS PENERIMAAN
   ======================= */
if ($action == 'delete') {
    $id = (int)$_GET['id'];

    $conn->query("DELETE FROM detail_penerimaan WHERE idpenerimaan=$id");
    $conn->query("DELETE FROM penerimaan WHERE idpenerimaan=$id");

    header("Location: penerimaan.php");
    exit;
}

include "../footer.php";
?>
