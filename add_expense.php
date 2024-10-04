<?php
session_start();
require 'includes/config.php';

// Proveri da li je korisnik prijavljen i ima ulogu člana domaćinstva
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'guest' || $_SESSION['role'] === 'sys_admin') {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Učitaj domaćinstvo korisnika
$sql = "SELECT household_id FROM household_members WHERE user_id = :user_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['household_id']) {
    $_SESSION['error_message'] = 'Niste dodeljeni nijednom domaćinstvu. Ne možete dodavati troškove.';
    header('Location: dashboard.php');
    exit();
}

$household_id = $user['household_id'];

// Inicijalizuj varijable za greške
$errors = [];
$success_message = '';

// Obradi POST zahtev
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = intval($_POST['category_id']);
    $name = htmlspecialchars($_POST['name']);
    $amount = floatval($_POST['amount']);
    $description = htmlspecialchars($_POST['description']);
    $date = $_POST['date'];

    // Validacija
    if (empty($category_id) || empty($name) || $amount <= 0 || empty($date)) {
        $errors[] = 'Sva polja su obavezna i iznos mora biti pozitivan.';
    }

    if (empty($errors)) {
        // Dodaj trošak u bazu podataka
        $sql = "INSERT INTO expenses (category_id, name, amount, description, date, household_id, user_id) 
                VALUES (:category_id, :name, :amount, :description, :date, :household_id, :user_id)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':household_id', $household_id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Trošak je uspešno dodat.';
            header('Location: view_expenses.php');
            exit();
        } else {
            $errors[] = 'Greška pri dodavanju troška. Pokušajte ponovo.';
        }
    }
}

// Učitaj kategorije za selekt polje
$sql = "SELECT * FROM categories WHERE household_id = :household_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':household_id', $household_id);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Dodaj Trošak</h2>

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

    <form action="add_expense.php" method="POST" id="expenseForm" onsubmit="return validateForm('expenseForm');">
        <div class="mb-3">
            <label for="category_id" class="form-label">Kategorija</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="">Izaberite kategoriju</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Naziv Troška</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Iznos</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Opis</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Datum</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <button type="submit" class="btn btn-primary">Dodaj Trošak</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
