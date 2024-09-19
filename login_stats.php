<?php
require 'includes/config.php';
session_start();

// Provera da li je korisnik sys_admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'sys_admin') {
    header('Location: index.php');
    exit;
}

// Dohvati sve podatke iz login_stats zajedno sa podacima o korisnicima
$sql = "SELECT ls.id, u.first_name, u.last_name, u.email, ls.login_time, ls.ip_address
        FROM login_stats ls
        JOIN users u ON ls.user_id = u.id
        ORDER BY ls.login_time DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$login_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pregled statistike logovanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'templates/header.php'; ?>

<div class="container mt-4">
    <h2>Statistika logovanja korisnika</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Ime</th>
            <th>Prezime</th>
            <th>Email</th>
            <th>Vreme logovanja</th>
            <th>IP adresa</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($login_stats as $log): ?>
            <tr>
                <td><?php echo htmlspecialchars($log['id']); ?></td>
                <td><?php echo htmlspecialchars($log['first_name']); ?></td>
                <td><?php echo htmlspecialchars($log['last_name']); ?></td>
                <td><?php echo htmlspecialchars($log['email']); ?></td>
                <td><?php echo htmlspecialchars($log['login_time']); ?></td>
                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'templates/footer.php'; ?>
</body>
</html>
