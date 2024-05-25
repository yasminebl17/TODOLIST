<?php
require 'db.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $leader_id = $_POST['leader_id'];
    $members = isset($_POST['members']) ? $_POST['members'] : array();

    // Insérer le nouveau groupe
    $stmt = $pdo->prepare('INSERT INTO groups (name, leader_id) VALUES (?, ?)');
    $stmt->execute([$name, $leader_id]);
    $group_id = $pdo->lastInsertId();

    // Affecter le rôle "leader" au chef de groupe
    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute(['leader', $leader_id]);

    // Ajouter les membres au groupe et affecter le rôle "member"
    $stmt = $pdo->prepare('INSERT INTO group_members (group_id, user_id) VALUES (?, ?)');
    foreach ($members as $member_id) {
        $stmt->execute([$group_id, $member_id]);
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute(['member', $member_id]);
    }

    header('Location: admin_dashboard.php');
    exit();
}

// Récupérer tous les utilisateurs
$stmt = $pdo->query('SELECT * FROM users');
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Créer un Groupe</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Créer un Groupe</h2>
    <form method="POST" action="create_group.php">
        <label for="name">Nom du groupe:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="leader_id">Chef de groupe:</label>
        <select id="leader_id" name="leader_id" required>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="members">Membres:</label>
        <br>
        <?php foreach ($users as $user): ?>
            <input type="checkbox" id="member_<?php echo $user['id']; ?>" name="members[]" value="<?php echo $user['id']; ?>">
            <label for="member_<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></label><br>
        <?php endforeach; ?>
        <br>
        <button type="submit">Créer le groupe</button>
    </form>
</body>
</html>
