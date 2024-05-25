<?php
require 'db.php';

$username = 'admin'; // Nom d'utilisateur de l'admin
$password = 'adminpassword'; // Mot de passe de l'admin
$hashed_password = password_hash($password, PASSWORD_BCRYPT); // Hacher le mot de passe

// Insérer l'admin dans la base de données
$stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
if ($stmt->execute([$username, $hashed_password, 'admin'])) {
    echo "Admin créé avec succès.";
} else {
    echo "Erreur lors de la création de l'admin.";
}
?>
