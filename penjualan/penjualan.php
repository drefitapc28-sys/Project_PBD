<?php 
session_start();
$pageTitle = "Transaksi Penjualan | PBD Project";
include "../header.php";

$action = $_GET['action'] ?? 'list';

/* =======================
   LIST DATA PENJUALAN
   ======================= */
if ($action == 'list') {
    $result = $conn->query("SELECT * FROM v_penjualan ORDER BY idpenjualan DESC");
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-gradient fw-bold"><i class="bi bi-currency-dollar"></i> Data Penjualan</h3>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Penjualan</a>
</div>

<div class="table-responsive">
<table class="table table-hover table-bordered align-middle text-center shadow-sm bg-white">
    <thead class="table-primary">
        <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Kasir</th>
            <th>Subtotal</th>
            <th>PPN</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php while($r = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $r['idpenjualan'] ?></td>
            <td><?= $r['tanggal_penjualan'] ?></td>
            <td><?= htmlspecialchars($r['kasir']) ?></td>
            <td>Rp <?= number_format($r['subtotal_nilai'], 0, ",", ".") ?></td>
            <td><?= $r['ppn'] ?>%</td>
            <td>Rp <?= number_format($r['total_nilai'], 0, ",", ".") ?></td>
            <td>
                <a href="?action=view&id=<?= $r['idpenjualan'] ?>" class="btn btn-info btn-sm">
                    <i class="bi bi-eye"></i> Detail
                </a>
                <a href="?action=delete&id=<?= $r['idpenjualan'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Yakin ingin menghapus data ini?')">
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
   TAMBAH PENJUALAN OTOMATIS
   ======================= */
elseif ($action == 'add') {
    $iduser = $_SESSION['iduser'] ?? 0;

    $m = $conn->query("
        SELECT idmargin_penjualan 
        FROM margin_penjualan 
        WHERE status = 1 
        ORDER BY updated_at DESC 
        LIMIT 1
    ");

    if ($m && $m->num_rows > 0) {
        $margin = $m->fetch_assoc();
        $idmargin = $margin['idmargin_penjualan'];
    } else {
        $q = $conn->query("SELECT idmargin_penjualan FROM margin_penjualan ORDER BY updated_at DESC LIMIT 1");
        $latest = $q->fetch_assoc();
        $idmargin = $latest['idmargin_penjualan'] ?? 1;
    }

    $conn->query("
        INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
        VALUES (NOW(), 0, 10, 0, $iduser, $idmargin)
    ");

    $lastID = $conn->insert_id;
    header("Location: penjualan.php?action=view&id=$lastID");
    exit;
}

/* =======================
   LIHAT DETAIL PENJUALAN
   ======================= */
elseif ($action == 'view') {
    $id = (int)$_GET['id'];
    $header = $conn->query("SELECT * FROM v_penjualan WHERE idpenjualan=$id")->fetch_assoc();
    $detail = $conn->query("SELECT * FROM v_detail_penjualan WHERE idpenjualan=$id");
?>
<h3 class="text-gradient fw-bold mb-3"><i class="bi bi-receipt"></i> Detail Penjualan #<?= $id ?></h3>

<div class="card p-4 mb-3 shadow-sm">
    <p><strong>Kasir:</strong> <?= $header['kasir'] ?></p>
    <p><strong>Tanggal:</strong> <?= $header['tanggal_penjualan'] ?></p>
    <p><strong>Margin:</strong> <?= $header['margin_persen'] ?>%</p>
    <p><strong>Total:</strong> Rp <?= number_format($header['total_nilai'], 0, ",", ".") ?></p>
</div>

<a href="?action=add_detail&id=<?= $id ?>" class="btn btn-primary mb-3">
    <i class="bi bi-plus-circle"></i> Tambah Barang Penjualan
</a>

<table class="table table-bordered text-center shadow-sm">
    <thead class="table-secondary">
        <tr>
            <th>Barang</th>
            <th>Harga Jual</th>
            <th>Jumlah</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php $total = 0; while($d = $detail->fetch_assoc()): $total += $d['subtotal']; ?>
        <tr>
            <td><?= $d['nama_barang'] ?></td>
            <td>Rp <?= number_format($d['harga_satuan'], 0, ",", ".") ?></td>
            <td><?= $d['jumlah'] ?></td>
            <td>Rp <?= number_format($d['subtotal'], 0, ",", ".") ?></td>
        </tr>
        <?php endwhile; ?>
        <tr class="table-light fw-bold">
            <td colspan="3">Total</td>
            <td>Rp <?= number_format($total, 0, ",", ".") ?></td>
        </tr>
    </tbody>
</table>

<div class="text-end mt-3">
    <a href="penjualan.php" class="btn btn-secondary">Kembali</a>
</div>
<?php }

/* =======================
   FORM TAMBAH DETAIL
   ======================= */
elseif ($action == 'add_detail') {
    $id = (int)$_GET['id'];
    
    $barang = $conn->query("SELECT idbarang, nama_barang, stok_terakhir FROM v_stok_barang ORDER BY nama_barang ASC");
?>
<h3 class="text-gradient fw-bold mb-3"><i class="bi bi-plus-circle"></i> Tambah Barang Penjualan</h3>

<div class="card p-4 w-75 mx-auto shadow-sm border-0">
<form method="POST" action="?action=insert_detail">
    <input type="hidden" name="idpenjualan" value="<?= $id ?>">

    <label class="form-label fw-bold">Barang</label>
    <select name="barang_idbarang" id="barang_idbarang" class="form-select mb-3" required onchange="tampilStok()">
        <option value="">-- Pilih Barang --</option>
        <?php while($b = $barang->fetch_assoc()): ?>
            <option value="<?= $b['idbarang'] ?>" data-stok="<?= $b['stok_terakhir'] ?>">
                <?= $b['nama_barang'] ?> (Stok: <?= $b['stok_terakhir'] ?>)
            </option>
        <?php endwhile; ?>
    </select>

    <label class="form-label fw-bold">Stok Tersedia</label>
    <input type="text" id="stok_tersedia" class="form-control mb-3" readonly placeholder="Pilih barang terlebih dahulu">

    <label class="form-label fw-bold">Jumlah</label>
    <input type="number" name="jumlah" id="jumlah" class="form-control mb-3" min="1" required>

    <div class="text-end">
        <button class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
        <a href="penjualan.php?action=view&id=<?= $id ?>" class="btn btn-secondary">Kembali</a>
    </div>
</form>
</div>

<script>
function tampilStok() {
    const select = document.getElementById('barang_idbarang');
    const stokField = document.getElementById('stok_tersedia');
    const jumlahInput = document.getElementById('jumlah');
    
    const stok = select.options[select.selectedIndex].getAttribute('data-stok');
    stokField.value = stok ? stok : '';
    jumlahInput.max = stok;
}
</script>
<?php }

/* =======================
   INSERT DETAIL PENJUALAN
   ======================= */
elseif ($action == 'insert_detail') {
    $id = (int)$_POST['idpenjualan'];
    $barang = (int)$_POST['barang_idbarang'];
    $jumlah = (int)$_POST['jumlah'];

    // ambil harga & margin dari transaksi + stok terkini dari v_stok_barang
    $data = $conn->query("
        SELECT b.harga, s.stok_terakhir AS stok, m.persen 
        FROM penjualan p
        JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan
        JOIN barang b ON b.idbarang = $barang
        JOIN v_stok_barang s ON s.idbarang = b.idbarang
        WHERE p.idpenjualan = $id
    ")->fetch_assoc();

    if (!$data) {
        echo "<script>alert('❌ Data barang atau margin tidak ditemukan.');history.back();</script>";
        exit;
    }

    $stok = (int)$data['stok'];
    $harga_modal = (int)$data['harga'];
    $margin = (float)$data['persen'];

    if ($jumlah > $stok) {
        echo "<script>alert('❌ Stok barang tidak mencukupi. Sisa stok: $stok');history.back();</script>";
        exit;
    }

    $harga_jual = $harga_modal + ($harga_modal * $margin / 100);
    $subtotal = $harga_jual * $jumlah;

    $conn->query("
        INSERT INTO detail_penjualan (penjualan_idpenjualan, idbarang, jumlah, harga_satuan, subtotal)
        VALUES ($id, $barang, $jumlah, $harga_jual, $subtotal)
    ");

    // perbarui subtotal dan total penjualan
    $sub = $conn->query("SELECT SUM(subtotal) AS subtotal FROM detail_penjualan WHERE penjualan_idpenjualan=$id")->fetch_assoc()['subtotal'] ?? 0;
    $ppn = $conn->query("SELECT ppn FROM penjualan WHERE idpenjualan=$id")->fetch_assoc()['ppn'] ?? 0;
    $total = $sub + ($sub * $ppn / 100);
    $conn->query("UPDATE penjualan SET subtotal_nilai=$sub, total_nilai=$total WHERE idpenjualan=$id");

    echo "<script>alert('✅ Barang berhasil ditambahkan ke penjualan.');window.location='penjualan.php?action=view&id=$id';</script>";
    exit;
}

/* =======================
   HAPUS PENJUALAN
   ======================= */
elseif ($action == 'delete') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM detail_penjualan WHERE penjualan_idpenjualan=$id");
    $conn->query("DELETE FROM penjualan WHERE idpenjualan=$id");
    header("Location: penjualan.php");
    exit;
}

include "../footer.php";
?>
