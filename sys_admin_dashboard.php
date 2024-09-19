<?php
session_start();

// Provera da li je korisnik sys_admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'sys_admin') {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'templates/header.php'; ?>

<div class="container mt-4">
    <h2>Super Admin Dashboard</h2>
    <div class="d-grid gap-2">
        <a href="list_all.php" class="btn btn-primary btn-lg">Prikazivanje svih korisnika i domaćinstava</a>
        <a href="login_stats.php" class="btn btn-secondary btn-lg">Pregled statistike logovanja korisnika</a>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
</body>
</html>
