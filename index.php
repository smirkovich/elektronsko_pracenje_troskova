<?php
session_start();

// Ako je korisnik prijavljen, preusmeri ga na dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<?php include 'templates/header.php'; ?>

<div class="container text-center">
    <h1>Dobrodošli na aplikaciju za praćenje troškova domaćinstva</h1>
    <p>Da biste započeli, prijavite se ili registrujte.</p>
    <a href="login.php" class="btn btn-primary m-2">Prijava</a>
    <a href="register.php" class="btn btn-secondary m-2">Registracija</a>
</div>

<?php include 'templates/footer.php'; ?>
