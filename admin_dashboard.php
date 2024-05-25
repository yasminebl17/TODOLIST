<?php
require 'db.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupérer tous les utilisateurs
$stmt = $pdo->query('SELECT * FROM users');
$users = $stmt->fetchAll();

// Récupérer toutes les tâches terminées
$stmt = $pdo->query('SELECT tasks.*, users.username AS assigned_to FROM tasks JOIN users ON tasks.assigned_to = users.id WHERE tasks.completed = 1');
$completed_tasks = $stmt->fetchAll();

// Récupérer tous les groupes
$stmt = $pdo->query('SELECT * FROM groups');
$groups = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord Admin</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Bienvenue, Admin</h1>
        <a href="login.php">Déconnexion</a>
    </header>
    <main>
        <h2>Gestion des Groupes</h2>
        <a href="create_group.php">Créer un Groupe</a>
        <ul>
            <?php foreach ($groups as $group): ?>
                <li>
                    <?php echo htmlspecialchars($group['name']); ?> 
                    <a href="edit_group.php?group_id=<?php echo $group['id']; ?>">Modifier</a>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <h2>Gestion des Utilisateurs</h2>
        <ul>
            <?php foreach ($users as $user): ?>
                <li><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</li>
            <?php endforeach; ?>
        </ul>

        <h2>Tâches terminées</h2>
        <ul>
            <?php foreach ($completed_tasks as $task): ?>
                <li>
                    <?php echo htmlspecialchars($task['description']); ?> - Assignée à <?php echo htmlspecialchars($task['assigned_to']); ?> (Date limite: <?php echo htmlspecialchars($task['deadline']); ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>
