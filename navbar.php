<?php
$baseUrl = "http://localhost/project_pbd";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
/* Sidebar Style */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 240px;
  background: linear-gradient(180deg, #1e3a8a, #2563eb); /* navy → blue */
  color: #f3f4f6;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 20px 0;
  box-shadow: 2px 0 15px rgba(0,0,0,0.15);
  z-index: 1000;
  transition: all 0.3s ease;
}

.sidebar .brand {
  font-size: 20px;
  font-weight: 700;
  text-align: center;
  color: #fff;
  margin-bottom: 25px;
  letter-spacing: 0.5px;
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar ul li {
  width: 100%;
}

.sidebar ul li a {
  color: #e0e7ff;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 20px;
  border-radius: 8px;
  transition: all 0.25s ease;
}

.sidebar ul li a:hover {
  background-color: rgba(255, 255, 255, 0.15);
  color: #fff;
  transform: translateX(3px);
}

.sidebar ul li a.active {
  background-color: rgba(255, 255, 255, 0.25);
  color: #fff;
  box-shadow: inset 2px 0 0 #fff;
}

.sidebar i {
  width: 22px;
  font-size: 18px;
}

.submenu {
  padding-left: 20px;
  display: none;
  animation: fadeSlide 0.3s ease forwards;
}

.submenu.show {
  display: block;
}

@keyframes fadeSlide {
  from {opacity: 0; transform: translateY(-5px);}
  to {opacity: 1; transform: translateY(0);}
}

.sidebar .logout {
  margin-top: auto;
  padding: 10px 20px;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.sidebar .logout a {
  color: #f9fafb;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  transition: 0.2s;
}

.sidebar .logout a:hover {
  color: #fff;
  transform: translateX(3px);
}
</style>

<!-- Sidebar -->
<div class="sidebar">
  <div>
    <div class="brand">
      <i class="bi bi-database-fill-gear"></i> PBD Project
    </div>
    <ul>
      <li>
        <a href="<?= $baseUrl ?>/index.php" class="<?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
      </li>

      <!-- Data Master Dropdown -->
      <li>
        <a href="#" onclick="toggleMenu('masterMenu')">
          <i class="bi bi-folder2-open"></i> Data Master
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="masterMenu" class="submenu">
          <li><a href="<?= $baseUrl ?>/barang/barang.php" class="<?= basename($_SERVER['PHP_SELF'])=='barang.php'?'active':'' ?>"><i class="bi bi-box-seam"></i> Barang</a></li>
          <li><a href="<?= $baseUrl ?>/vendor/vendor.php" class="<?= basename($_SERVER['PHP_SELF'])=='vendor.php'?'active':'' ?>"><i class="bi bi-truck"></i> Vendor</a></li>
          <li><a href="<?= $baseUrl ?>/user/user.php" class="<?= basename($_SERVER['PHP_SELF'])=='user.php'?'active':'' ?>"><i class="bi bi-people"></i> User</a></li>
          <li><a href="<?= $baseUrl ?>/satuan/satuan.php" class="<?= basename($_SERVER['PHP_SELF'])=='satuan.php'?'active':'' ?>"><i class="bi bi-tag"></i> Satuan</a></li>
          <li><a href="<?= $baseUrl ?>/role/role.php" class="<?= basename($_SERVER['PHP_SELF'])=='role.php'?'active':'' ?>"><i class="bi bi-person-badge"></i> Role</a></li>
          <li><a href="<?= $baseUrl ?>/margin/margin_penjualan.php" class="<?= basename($_SERVER['PHP_SELF'])=='margin_penjualan.php'?'active':'' ?>"><i class="bi bi-graph-up"></i> Margin</a></li>
        </ul>
      </li>

      <!-- Transaksi Dropdown -->
      <li>
        <a href="#" onclick="toggleMenu('transMenu')">
          <i class="bi bi-cash-coin"></i> Transaksi
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="transMenu" class="submenu">
          <li><a href="<?= $baseUrl ?>/pengadaan/pengadaan.php"><i class="bi bi-basket"></i> Pengadaan</a></li>
          <li><a href="<?= $baseUrl ?>/penerimaan/penerimaan.php"><i class="bi bi-box-arrow-in-down"></i> Penerimaan</a></li>
          <li><a href="<?= $baseUrl ?>/retur/retur.php"><i class="bi bi-arrow-return-left"></i> Retur</a></li>
          <li><a href="<?= $baseUrl ?>/penjualan/penjualan.php"><i class="bi bi-currency-dollar"></i> Penjualan</a></li>
          <li><a href="<?= $baseUrl ?>/stok/kartu_stok.php"><i class="bi bi-card-list"></i> Kartu Stok</a></li>
        </ul>
      </li>
    </ul>
  </div>

  <div class="logout">
    <a href="<?= $baseUrl ?>/logout.php">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</div>

<!-- JS Toggle Dropdown -->
<script>
function toggleMenu(id) {
  const menu = document.getElementById(id);
  menu.classList.toggle('show');
}
</script>
