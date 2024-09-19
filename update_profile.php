<?php
session_start();
require 'includes/config.php';

// Proveri da li je korisnik prijavljen i ima ulogu admina
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Inicijalizuj varijable za greške
$errors = [];
$success_message = '';

// Učitaj trenutne podatke korisnika
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = :user_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $password = htmlspecialchars($_POST['password']);
    $new_password = htmlspecialchars($_POST['new_password']);
    $confirm_new_password = htmlspecialchars($_POST['confirm_new_password']);

    // Validacija
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($address)) {
        $errors[] = 'Sva polja su obavezna osim lozinke.';
    }

    if (!empty($new_password)) {
        if ($new_password !== $confirm_new_password) {
            $errors[] = 'Nova lozinka i potvrda lozinke se ne poklapaju.';
        } else {
            $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, phone = :phone, address = :address";

        if (!empty($new_password)) {
            $sql .= ", password = :password";
        }

        $sql .= " WHERE id = :user_id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':user_id', $user_id);

        if (!empty($new_password)) {
            $stmt->bindParam(':password', $password_hash);
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Profil je uspešno ažuriran.';
        } else {
            $errors[] = 'Greška pri ažuriranju profila. Pokušajte ponovo.';
        }
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Ažuriraj Profil</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($_SESSION['success_message']): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; ?>
            <?php $_SESSION['success_message'] = ''?>
        </div>
    <?php endif; ?>

    <form action="update_profile.php" method="POST">
        <div class="mb-3">
            <label for="first_name" class="form-label">Ime</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Prezime</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Broj Telefona</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Adresa</label>
            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Stara Lozinka (ako menjate lozinku)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">Nova Lozinka</label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>
        <div class="mb-3">
            <label for="confirm_new_password" class="form-label">Potvrda Nove Lozinke</label>
            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password">
        </div>
        <button type="submit" class="btn btn-primary">Ažuriraj Profil</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
