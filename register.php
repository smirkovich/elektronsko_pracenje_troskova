<?php
session_start();
require 'includes/config.php'; // Uključi PDO konekciju sa bazom

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = 'admin'; // Default rola prilikom registracije jer member-i ne mogu koreirati nista

    // Validacija lozinki
    if ($password !== $confirm_password) {
        $errors[] = 'Lozinke se ne podudaraju.';
    }

    // Proveri da li već postoji korisnik sa istim emailom
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        if ($user['password'] ) {
            $errors[] = 'Korisnik sa ovom email adresom već postoji.';
        } else {
            // Korisnik je dobio invite sa email-om
            //TODO: kada proradi email ref, promijeni logiku

            $user_id = trim($user['id']);  // ID korisnika
//            print_r($user_id);
//            coninue;

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, password = :password";
            $sql .= " WHERE id = " .$user_id;

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Uspešno ste se registrovali. Molimo prijavite se.';
                header('Location: login.php');
                exit();
            } else {
                $errors[] = 'Greška pri registraciji korisnika. Pokušajte ponovo.';
            }

        }


    }

    // Ako nema grešaka, hashuj lozinku i dodaj korisnika
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (first_name, last_name, email, password, role) 
                VALUES (:first_name, :last_name, :email, :password, :role)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Uspešno ste se registrovali. Molimo prijavite se.';
            header('Location: login.php');
            exit();
        } else {
            $errors[] = 'Greška pri registraciji korisnika. Pokušajte ponovo.';
        }
    }
}
?>

<?php include 'templates/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center">Registracija</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="first_name" class="form-label">Ime</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Prezime</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email adresa</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Lozinka</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Potvrdi lozinku</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Registruj se</button>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
