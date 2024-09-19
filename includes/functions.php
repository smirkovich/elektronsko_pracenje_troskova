<?php
function log_user_login($conn, $user_id) {
// Dobijamo trenutni timestamp za login_time
$login_time = date('Y-m-d H:i:s');

// Dobijamo IP adresu korisnika
$ip_address = $_SERVER['REMOTE_ADDR'];

// Priprema upita za unos podataka u login_stats tabelu
$sql = "INSERT INTO login_stats (user_id, login_time, ip_address)
VALUES (:user_id, :login_time, :ip_address)";

$stmt = $conn->prepare($sql);

// Bind parametara
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':login_time', $login_time);
$stmt->bindParam(':ip_address', $ip_address);

// Izvrši upit
$stmt->execute();
}


?>