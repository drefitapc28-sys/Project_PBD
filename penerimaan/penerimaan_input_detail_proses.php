<?php
include "../koneksi.php";

$id_penerimaan = (int)$_POST['id_penerimaan'];
$idbarang = $_POST['idbarang'];
$jumlah_terima = $_POST['jumlah_terima'];
$harga_satuan = $_POST['harga_satuan'];

// Simpan semua detail ke tabel detail_penerimaan
for ($i = 0; $i < count($idbarang); $i++) {
    $jumlah = (int)$jumlah_terima[$i];
    $harga = (int)$harga_satuan[$i]; // harga otomatis dari pengadaan
    $subtotal = $jumlah * $harga;

    // hanya insert kalau jumlah > 0
    if ($jumlah > 0) {
        $conn->query("
            INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima)
            VALUES ($id_penerimaan, {$idbarang[$i]}, $jumlah, $harga, $subtotal)
        ");
    }
}

// ubah status penerimaan jadi selesai (Y)
$conn->query("UPDATE penerimaan SET status='Y' WHERE idpenerimaan=$id_penerimaan");

header("Location: penerimaan.php?action=view&id=$id_penerimaan");
exit;
?>
