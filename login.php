<?php
session_start();
require 'includes/config.php'; // Uključi konekciju sa bazom
require 'includes/functions.php'; // Import funkcije za logovanje usera

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Provera da li korisnik postoji
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Proveri hash lozinke
        if (password_verify($password, $user['password'])) {
            // Postavljanje korisničkih sesija
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];

            log_user_login($db, $user['id']); // zabiljezi login usera

            // Redirekcija na dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = 'Pogrešna lozinka. Pokušajte ponovo.';
        }
    } else {
        $error_message = 'Ne postoji korisnik sa unetim emailom.';
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center">Prijava</h2>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['success_message']): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message']; ?>
                    <?php $_SESSION['success_message'] = ''?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email adresa</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Lozinka</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Prijavi se</button>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
