<?php
session_start();
require 'includes/config.php';

// Proveri da li je korisnik prijavljen i ima ulogu admina
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Inicijalizuj varijable za greške
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = htmlspecialchars($_POST['category_name']);

    // Validacija
    if (empty($category_name)) {
        $errors[] = 'Naziv kategorije je obavezan.';
    }

    if (empty($errors)) {
        // Unos kategorije u bazu
        $sql = "INSERT INTO categories (name) VALUES (:category_name)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category_name', $category_name);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Kategorija je uspešno kreirana.';
            header('Location: manage_categories.php');
            exit();
        } else {
            $errors[] = 'Greška pri kreiranju kategorije. Pokušajte ponovo.';
        }
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Kreiraj Kategoriju</h2>

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

    <form action="create_category.php" method="POST">
        <div class="mb-3">
            <label for="category_name" class="form-label">Naziv Kategorije</label>
            <input type="text" class="form-control" id="category_name" name="category_name" required>
        </div>
        <button type="submit" class="btn btn-primary">Kreiraj Kategoriju</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
