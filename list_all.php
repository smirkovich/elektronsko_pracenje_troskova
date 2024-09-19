<?php
require 'includes/config.php';
session_start();

//Provjera za sys admina
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'sys_admin') {
    header('Location: index.php');
    exit;
}

// Dohvati sve korisnike
$sql_users = "SELECT u.id, u.first_name, u.last_name, u.email, u.role, h.name AS household_name
              FROM users u
              LEFT JOIN household_members hm ON u.id = hm.user_id
              LEFT JOIN households h ON hm.household_id = h.id";
$stmt_users = $db->prepare($sql_users);
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Dohvati sva domaćinstva
$sql_households = "SELECT * FROM households";
$stmt_households = $db->prepare($sql_households);
$stmt_households->execute();
$households = $stmt_households->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'templates/header.php'; ?>

<!-- HTML deo za prikaz korisnika i domaćinstava -->
<div class="container">
    <h2>Svi korisnici</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Ime</th>
            <th>Prezime</th>
            <th>Email</th>
            <th>Rola</th>
            <th>Domaćinstvo</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo htmlspecialchars($user['household_name'] ? $user['household_name'] : 'Nema domaćinstvo'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Sva domaćinstva</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Naziv domaćinstva</th>
            <th>Datum kreiranja</th>
            <th>Admin domaćinstva</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($households as $household): ?>
            <tr>
                <td><?php echo htmlspecialchars($household['id']); ?></td>
                <td><?php echo htmlspecialchars($household['name']); ?></td>
                <td><?php echo htmlspecialchars($household['created_at']); ?></td>
                <td><?php echo htmlspecialchars($household['admin_id']); ?></td> <!-- Admin ID -->
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'templates/footer.php'; ?>