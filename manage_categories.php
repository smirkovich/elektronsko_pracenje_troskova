<?php
session_start();
require 'includes/config.php';

// Proveri da li je korisnik prijavljen i da li je admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Učitaj domaćinstvo admina
$sql = "SELECT id FROM households WHERE admin_id = :user_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$household = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$household) {
    $_SESSION['error_message'] = 'Nemate dodeljeno domaćinstvo. Ne možete upravljati kategorijama.';
    header('Location: dashboard.php');
    exit();
}

$household_id = $household['id'];

// Inicijalizuj varijable za greške
$errors = [];
$success_message = '';

// Učitaj sve kategorije vezane za domaćinstvo
$sql = "SELECT * FROM categories WHERE household_id = :household_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':household_id', $household_id);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['edit'])) {
    $category_id = intval($_GET['edit']);
    $sql = "SELECT * FROM categories WHERE id = :category_id AND household_id = :household_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':household_id', $household_id);
    $stmt->execute();
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = intval($_POST['category_id']);
    $category_name = htmlspecialchars($_POST['category_name']);

    // Validacija
    if (empty($category_name)) {
        $errors[] = 'Naziv kategorije je obavezan.';
    }

    if (empty($errors)) {
        if ($category_id > 0) {
            // Izmena kategorije u bazi
            $sql = "UPDATE categories SET name = :category_name WHERE id = :category_id AND household_id = :household_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':category_name', $category_name);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':household_id', $household_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Kategorija je uspešno ažurirana.';
                header('Location: manage_categories.php');
                exit();
            } else {
                $errors[] = 'Greška pri ažuriranju kategorije. Pokušajte ponovo.';
            }
        } else {
            // Dodavanje nove kategorije u bazi
            $sql = "INSERT INTO categories (name, household_id) VALUES (:category_name, :household_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':category_name', $category_name);
            $stmt->bindParam(':household_id', $household_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Kategorija je uspešno kreirana.';
                header('Location: manage_categories.php');
                exit();
            } else {
                $errors[] = 'Greška pri kreiranju kategorije. Pokušajte ponovo.';
            }
        }
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Upravljanje Kategorijama</h2>

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

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Naziv</th>
            <th>Akcije</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $category): ?>
            <tr>
                <td><?php echo htmlspecialchars($category['name']); ?></td>
                <td>
                    <a href="manage_categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-warning btn-sm">Izmeni</a>
                    <a href="delete_category.php?id=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm">Obriši</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (isset($_GET['edit'])): ?>
        <h3>Izmeni Kategoriju</h3>
        <form action="manage_categories.php" method="POST">
            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
            <div class="mb-3">
                <label for="category_name" class="form-label">Naziv Kategorije</label>
                <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Ažuriraj Kategoriju</button>
        </form>
    <?php else: ?>
        <h3>Kreiraj Novu Kategoriju</h3>
        <form action="manage_categories.php" method="POST">
            <div class="mb-3">
                <label for="category_name" class="form-label">Naziv Kategorije</label>
                <input type="text" class="form-control" id="category_name" name="category_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Kreiraj Kategoriju</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>
