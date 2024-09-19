<?php
session_start();
require 'includes/config.php';

// Proveri da li je korisnik prijavljen i ima ulogu admina
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']);

    // Brisanje kategorije iz baze
    $sql = "DELETE FROM categories WHERE id = :category_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':category_id', $category_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Kategorija je uspešno obrisana.';
    } else {
        $_SESSION['error_message'] = 'Greška pri brisanju kategorije. Pokušajte ponovo.';
    }

    header('Location: manage_categories.php');
    exit();
} else {
    header('Location: manage_categories.php');
    exit();
}
