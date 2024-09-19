<?php
session_start();

// Uništi sve podatke u sesiji
session_unset();
session_destroy();

// Redirekcija na početnu stranicu ili stranicu za prijavu
header('Location: index.php');
exit();
?>
