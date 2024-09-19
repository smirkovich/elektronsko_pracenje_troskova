<?php
session_start();
require 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Provera da li je korisnik sys_admin
if ($_SESSION['role'] === 'sys_admin') {
    header('Location: sys_admin_dashboard.php');
    exit;
}

// Učitaj sve troškove domaćinstva ako je korisnik član domaćinstva
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = :user_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$is_admin = ($_SESSION['role'] === 'admin');

$household_name = '';
$household_exists = false;


//$sql = "SELECT households.name AS household_name FROM users
//        JOIN households ON users.household_id = households.id
//        WHERE users.id = :user_id";


$sql = "SELECT h.name as household_name
        FROM household_members hm 
        JOIN households h ON hm.household_id = h.id 
        WHERE hm.user_id = :user_id 
        LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$household = $stmt->fetch(PDO::FETCH_ASSOC);

if ($household) {
    $household_name = $household['household_name'];
    $household_exists = true;
}

include 'templates/header.php';
?>
<?php if ($_SESSION['success_message']): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['success_message']; ?>
        <?php $_SESSION['success_message'] = ''?>
    </div>
<?php endif; ?>
<?php if ($_SESSION['$error_message']): ?>
    <div class="alert alert-danger">
        <?php echo $_SESSION['$error_message']; ?>
        <?php $_SESSION['$error_message'] = ''?>
    </div>
<?php endif; ?>
<div class="container">
    <h1>Dobrodošli, <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h1>
    <p>Vaša rola: <?php echo ucfirst($_SESSION['role']); ?></p>

    <?php if ($is_admin): ?>
        <div class="alert alert-info">
            <h3>Administratorski Panel</h3>
            <p>Dobrodošli, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</p>
            <?php if (!$household_exists): ?>
                <a href="create_household.php" class="btn btn-primary">Kreiraj Domaćinstvo</a>
            <?php else: ?>
                <a href="send_invitation.php" class="btn btn-primary">Dodaj Članove Domaćinstva</a>
                <a href="manage_categories.php" class="btn btn-secondary">Upravljaj Kategorijama</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($household_exists): ?>
        <div class="alert alert-info">
            <h3>Vaše Domaćinstvo</h3>
            <p>Ime domaćinstva: <?php echo htmlspecialchars($household_name); ?></p>
            <a href="add_expense.php" class="btn btn-primary">Unesi Trošak</a>
            <a href="view_expenses.php" class="btn btn-secondary">Pogledaj Troškove</a>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <p>Ne pripadate nijednom domaćinstvu. Molimo Kreirajte Domaćinstvo.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>
