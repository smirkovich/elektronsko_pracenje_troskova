<?php
session_start();
require 'includes/config.php';
//require 'PHPMailer/PHPMailerAutoload.php'; // Učitaj PHPMailer

// Proveri da li je korisnik prijavljen i da li je admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Učitaj domaćinstvo admina
$sql = "SELECT household_id FROM users WHERE id = :user_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['household_id']) {
    $_SESSION['error_message'] = 'Niste dodeljeni nijednom domaćinstvu.';
    header('Location: dashboard.php');
    exit();
}

$household_id = $user['household_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $emails = explode(',', $_POST['email']);
    $emails = array_map('trim', $emails);
    $errors = [];
    $success_message = '';

    foreach ($emails as $email) {
        // Validacija email adrese
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email adresa $email nije validna.";
            continue;
        }

        // Proveri da li email već postoji u bazi
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $errors[] = "Korisnik sa email adresom $email već postoji.";
            continue;
        }  else {
            // Ako korisnik ne postoji, dodaj ga kao placeholder
            $sql = "INSERT INTO users (email, role, household_id) VALUES (:email, 'member', :household_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':household_id', $household_id);

            if ($stmt->execute()) {
                $userId = $db->lastInsertId(); // Uzmi ID novog korisnika

                // Dodaj pozivnicu u tabelu household_members
                $sql = "INSERT INTO household_members (household_id, user_id, invitation_sent_at) 
                VALUES (:household_id, :user_id, NOW())";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':household_id', $household_id);
                $stmt->bindParam(':user_id', $userId);

                if ($stmt->execute()) {
                    $success_message = "Pozivnica je poslata korisniku: $email";
                } else {
                    $errors[] = "Greška pri dodavanju korisnika sa email adresom $email.";
                    continue;
                }

            } else {
                $errors[] = "Greška pri dodavanju korisnika sa email adresom $email.";
                continue;
            }
        }

//        if ($stmt->execute()) {
//            // Pošalji email pozivnicu
//            $mail = new PHPMailer\PHPMailer\PHPMailer();
//            $mail->isSMTP();
//            $mail->Host = 'smtp.example.com'; // SMTP server
//            $mail->SMTPAuth = true;
//            $mail->Username = 'your_email@example.com'; // email
//            $mail->Password = 'your_password'; // lozinka
//            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
//            $mail->Port = 587;
//
//            $mail->setFrom('your_email@example.com', 'Your Name');
//            $mail->addAddress($email);
//            $mail->Subject = 'Poziv za registraciju na domaćinstvo';
//            $mail->Body = "Pozivamo vas da se registrujete na domaćinstvo. Kliknite na link da biste se registrovali.";
//
//            if ($mail->send()) {
//                $success_message = 'Pozivnice su uspešno poslate.';
//            } else {
//                $errors[] = 'Greška pri slanju pozivnice. ' . $mail->ErrorInfo;
//            }
//        } else {
//            $errors[] = 'Greška pri dodavanju pozivnice u bazu.';
//        }
    }

    // Prikaz poruka
    $_SESSION['errors'] = $errors;
    $_SESSION['success_message'] = $success_message;
    header('Location: send_invitation.php');
    exit();
}

?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <h2>Dashboard</h2>
    <h3>Pošaljite pozivnice članovima domaćinstva</h3>

    <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form action="send_invitation.php" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email Adrese (odvojene zarezom)</label>
            <input type="text" class="form-control" id="email" name="email" required>
            <div class="form-text">Unesite email adrese članova domaćinstva, odvojene zarezom.</div>
        </div>
        <button type="submit" class="btn btn-primary">Pošaljite Pozivnice</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
