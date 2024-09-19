<?php
session_start();
require 'includes/config.php';

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);

    // Proveri da li korisnik postoji
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Generiši jedinstveni token
        $token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Sačuvaj token u bazu
        $sql = "INSERT INTO password_resets (email, token, token_expiry) 
                VALUES (:email, :token, :token_expiry)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':token_expiry', $token_expiry);
        $stmt->execute();

        // Pošalji email sa linkom za resetovanje lozinke
        require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require 'vendor/phpmailer/phpmailer/src/SMTP.php';
        require 'vendor/phpmailer/phpmailer/src/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Postavi na tvoj SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com'; // Postavi na tvoj email
        $mail->Password = 'your_email_password'; // Postavi na tvoju lozinku
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email@example.com', 'Your Site');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Zahtev za promenu lozinke';
        $mail->Body = "Kliknite na sledeći link za promenu lozinke: <a href='http://your_site.com/reset_password.php?token=$token'>Promeni lozinku</a>";

        if ($mail->send()) {
            $success_message = 'Email za promenu lozinke je poslat. Molimo proverite svoju inbox.';
        } else {
            $errors[] = 'Greška pri slanju email-a. Pokušajte ponovo.';
        }
    } else {
        $errors[] = 'Ne postoji korisnik sa ovom email adresom.';
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Zahtev za promenu lozinke</h2>

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

    <form action="request_password_reset.php" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email adresa</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Pošalji email za promenu lozinke</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
