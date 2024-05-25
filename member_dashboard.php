<?php
require 'db.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un membre
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les groupes dont l'utilisateur est membre
$stmt = $pdo->prepare('SELECT groups.* FROM groups JOIN group_members ON groups.id = group_members.group_id WHERE group_members.user_id = ?');
$stmt->execute([$user_id]);
$groups = $stmt->fetchAll();

// Récupérer les tâches assignées à l'utilisateur
$stmt = $pdo->prepare('SELECT tasks.*, users.username AS assigned_by FROM tasks JOIN users ON tasks.assigned_by = users.id WHERE tasks.assigned_to = ?');
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];

    // Marquer la tâche comme terminée
    $stmt = $pdo->prepare('UPDATE tasks SET completed = 1 WHERE id = ?');
    $stmt->execute([$task_id]);

    header('Location: member_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord Membre</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Bienvenue, <?php echo $_SESSION['username']; ?> (Membre)</h1>
        <a href="login.php">Déconnexion</a>
    </header>
    <main>
        <h2>Vos Groupes</h2>
        <ul>
            <?php foreach ($groups as $group): ?>
                <li><?php echo htmlspecialchars($group['name']); ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Tâches à faire</h2>
        <ul>
            <?php foreach ($tasks as $task): ?>
                <li>
                    <?php echo htmlspecialchars($task['description']); ?> - Assignée par <?php echo htmlspecialchars($task['assigned_by']); ?> (Date limite: <?php echo htmlspecialchars($task['deadline']); ?>)
                    <?php if (!$task['completed']): ?>
                        <form method="POST" action="member_dashboard.php" style="display:inline;">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <button type="submit">Marquer comme terminée</button>
                        </form>
                    <?php else: ?>
                        <strong>(Terminée)</strong>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>
