<?php
session_start();
require 'includes/config.php';

// Proveri da li je korisnik prijavljen i ima ulogu admina
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

// Proveri da li je korisnik admin i da li se njegov ID nalazi u tabeli households
if ($is_admin) {
    $sql = "SELECT COUNT(*) FROM households WHERE admin_id = :user_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $admin_household_count = $stmt->fetchColumn();

    if ($admin_household_count > 0) {
        // Admin ima domaćinstvo -> ne moze napraviti novo
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}

// Inicijalizuj varijable za greške
$errors = [];
$success_message = '';

// Proveravamo da li su podaci poslati putem POST zahteva
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Dobijamo podatke iz forme
    $household_name = htmlspecialchars($_POST['household_name']);

    // Unos novog domaćinstva u bazu podataka
    $sql = "INSERT INTO households (name, admin_id) VALUES (:name, :admin_id)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':name', $household_name);
    $stmt->bindParam(':admin_id', $_SESSION['user_id']);

    if ($stmt->execute()) {
        // Uzimamo ID novokreiranog domaćinstva
        $household_id = $db->lastInsertId();

        // Unos admina (trenutnog korisnika) u household_members tabelu
        $sql = "INSERT INTO household_members (household_id, user_id, invitation_sent_at, invitation_accepted_at)
                VALUES (:household_id, :user_id, NOW(), NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':household_id', $household_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);

        if ($stmt->execute()) {

            // Unos admina (trenutnog korisnika) u users tabelu
            $sql = "UPDATE users SET household_id = :household_id WHERE id = :user_id";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':household_id', $household_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);

            if ($stmt->execute()) {
                // Uspešno kreirano domaćinstvo i dodat član
                $_SESSION['success_message'] = 'Domaćinstvo je uspešno kreirano i vi ste dodani kao član.';
                header('Location: dashboard.php');
                exit();
            } else {
                $_SESSION['error_message'] = 'Došlo je do greške prilikom dodavanja admina domaćinstva za trenutnog usera.';
            }
        } else {
            $_SESSION['error_message'] = 'Došlo je do greške prilikom kreiranja domaćinstva.';
        }
    } else {
        $_SESSION['error_message'] = 'Došlo je do greške prilikom kreiranja domaćinstva.';
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Kreiraj Domaćinstvo</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <form action="create_household.php" method="POST">
        <div class="mb-3">
            <label for="household_name" class="form-label">Naziv Domaćinstva</label>
            <input type="text" class="form-control" id="household_name" name="household_name" required>
        </div>
        <button type="submit" class="btn btn-primary">Kreiraj Domaćinstvo</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
