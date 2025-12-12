<?php
ob_start(); // Buffer output agar header() tidak error
include "../koneksi.php";
$pageTitle = $pageTitle ?? "PBD Project";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="http://localhost/project_pbd/assets/style.css" rel="stylesheet">
    <style>
        body { background-color: #f9fafb; }
        .main-content {
            margin-left: 240px;
            padding: 30px;
            min-height: 100vh;
        }
        .text-gradient {
            background: linear-gradient(90deg, #2563eb, #9333ea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
<?php include "../navbar.php"; ?>
<div class="main-content">
<div class="card shadow-lg p-4 border-0">
