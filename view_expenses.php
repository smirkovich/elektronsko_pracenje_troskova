<?php
session_start();
require 'includes/config.php';

// Proveri da li je korisnik prijavljen i ima ulogu člana domaćinstva
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'guest' || $_SESSION['role'] === 'sys_admin') {
    header('Location: login.php');
    exit();
}

// Pretpostavljamo da je user_id iz sesije i household_id dohvaćen iz baze
$user_id = $_SESSION['user_id'];

// Dohvati household_id na osnovu user_id
$sql = "SELECT household_id FROM household_members WHERE user_id = :user_id LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $household_id = $result['household_id'];

    // 1. Svi troškovi domaćinstva, uključujući email korisnika koji je uneo trošak
    $sql_expenses = "SELECT e.name, e.amount, e.description, e.date, c.name AS category_name, u.email 
                     FROM expenses e
                     JOIN categories c ON e.category_id = c.id
                     JOIN users u ON e.user_id = u.id
                     WHERE e.household_id = :household_id";
    $stmt_expenses = $db->prepare($sql_expenses);
    $stmt_expenses->bindParam(':household_id', $household_id, PDO::PARAM_INT);
    $stmt_expenses->execute();
    $expenses = $stmt_expenses->fetchAll(PDO::FETCH_ASSOC);

    // 2. Ukupni troškovi za domaćinstvo
    $sql_total_expenses = "SELECT SUM(amount) AS total_expenses 
                           FROM expenses 
                           WHERE household_id = :household_id";
    $stmt_total = $db->prepare($sql_total_expenses);
    $stmt_total->bindParam(':household_id', $household_id, PDO::PARAM_INT);
    $stmt_total->execute();
    $total_expenses = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_expenses'];

    // 3. Troškovi za tekući mesec
    $sql_monthly_expenses = "SELECT SUM(amount) AS monthly_expenses 
                             FROM expenses 
                             WHERE household_id = :household_id 
                             AND MONTH(date) = MONTH(CURRENT_DATE()) 
                             AND YEAR(date) = YEAR(CURRENT_DATE())";
    $stmt_monthly = $db->prepare($sql_monthly_expenses);
    $stmt_monthly->bindParam(':household_id', $household_id, PDO::PARAM_INT);
    $stmt_monthly->execute();
    $monthly_expenses = $stmt_monthly->fetch(PDO::FETCH_ASSOC)['monthly_expenses'];

    // 4. Tri kategorije sa najviše troškova
    $sql_top_categories = "SELECT c.name AS category_name, SUM(e.amount) AS total_amount 
                           FROM expenses e
                           JOIN categories c ON e.category_id = c.id
                           WHERE e.household_id = :household_id
                           GROUP BY c.name
                           ORDER BY total_amount DESC
                           LIMIT 3";
    $stmt_top_categories = $db->prepare($sql_top_categories);
    $stmt_top_categories->bindParam(':household_id', $household_id, PDO::PARAM_INT);
    $stmt_top_categories->execute();
    $top_categories = $stmt_top_categories->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Korisnik nije član nijednog domaćinstva.";
}
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Pregled troškova</h2>

    <?php if ($_SESSION['success_message']): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; ?>
            <?php $_SESSION['success_message'] = ''?>
        </div>
    <?php endif; ?>

    <h3>Ukupni troškovi domaćinstva: <span id="totalExpenses"><?php echo htmlspecialchars($total_expenses); ?> RSD</span></h3>
    <h3>Troškovi za tekući mesec: <span id="currentMonthExpenses"><?php echo htmlspecialchars($monthly_expenses); ?> RSD</span></h3>

    <h3>Tri kategorije sa najviše troškova:</h3>
    <ul>
        <?php foreach ($top_categories as $category): ?>
            <li><?php echo htmlspecialchars($category['category_name']) . ': ' . htmlspecialchars($category['total_amount']) . ' RSD'; ?></li>
        <?php endforeach; ?>
    </ul>

    <!-- Dodaj grafikon troškova -->
    <h3>Grafikon troškova:</h3>
    <canvas id="expensesChart" width="400" height="100"></canvas>

    <h3>Detalji svih troškova:</h3>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Naziv troška</th>
            <th>Kategorija</th>
            <th>Iznos</th>
            <th>Opis</th>
            <th>Datum</th>
            <th>Korisnik</th>
        </tr>
        </thead>
        <tbody id="expenseTableBody">
        <?php foreach ($expenses as $expense): ?>
            <tr>
                <td><?php echo htmlspecialchars($expense['name']); ?></td>
                <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                <td><?php echo htmlspecialchars($expense['amount']); ?> RSD</td>
                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                <td><?php echo htmlspecialchars($expense['date']); ?></td>
                <td><?php echo htmlspecialchars($expense['email']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/script.js"></script>

<script>
    // Popunjavanje grafikona sa PHP podacima
    const labels = <?php echo json_encode(array_column($top_categories, 'category_name')); ?>;
    const data = <?php echo json_encode(array_column($top_categories, 'total_amount')); ?>;
    renderExpenseChart(labels, data);

    // Popunjavanje ukupnih troškova
    const expenses = <?php echo json_encode($expenses); ?>;
    calculateTotalExpenses(expenses);
</script>

<?php include 'templates/footer.php'; ?>
