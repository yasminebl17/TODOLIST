<?php
require 'db.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un chef de groupe
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les groupes dont l'utilisateur est le chef
$stmt = $pdo->prepare('SELECT * FROM groups WHERE leader_id = ?');
$stmt->execute([$user_id]);
$groups = $stmt->fetchAll();

// Récupérer les tâches assignées par le chef de groupe
$stmt = $pdo->prepare('SELECT tasks.*, users.username AS assigned_to FROM tasks JOIN users ON tasks.assigned_to = users.id WHERE tasks.assigned_by = ?');
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = $_POST['group_id'];
    $task_description = $_POST['task_description'];
    $assigned_to = $_POST['assigned_to'];
    $deadline = $_POST['deadline'];

    // Vérifier que la date limite n'est pas dans le passé
    $current_date = date('Y-m-d');
    if ($deadline < $current_date) {
        echo "La date limite ne peut pas être dans le passé.";
    } else {
        $stmt = $pdo->prepare('INSERT INTO tasks (group_id, description, assigned_by, assigned_to, deadline) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$group_id, $task_description, $user_id, $assigned_to, $deadline]);

        header('Location: leader_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord Chef de groupe</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> (Chef de groupe)</h1>
        <a href="login.php">Déconnexion</a>
    </header>
    <main>
        <h2>Vos Groupes</h2>
        <ul>
            <?php foreach ($groups as $group): ?>
                <li><?php echo htmlspecialchars($group['name']); ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Attribuer une tâche</h2>
        <form method="POST" action="leader_dashboard.php">
            <label for="group_id">Groupe:</label>
            <select id="group_id" name="group_id" required>
                <?php foreach ($groups as $group): ?>
                    <option value="<?php echo $group['id']; ?>"><?php echo htmlspecialchars($group['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="task_description">Description de la tâche:</label>
            <input type="text" id="task_description" name="task_description" required>
            <br>
            <label for="assigned_to">Assigner à:</label>
            <select id="assigned_to" name="assigned_to" required>
                <?php
                // Récupérer les membres du groupe
                foreach ($groups as $group) {
                    $stmt = $pdo->prepare('SELECT users.id, users.username FROM users JOIN group_members ON users.id = group_members.user_id WHERE group_members.group_id = ?');
                    $stmt->execute([$group['id']]);
                    $members = $stmt->fetchAll();
                    foreach ($members as $member) {
                        echo "<option value=\"{$member['id']}\">{$member['username']} (Groupe: {$group['name']})</option>";
                    }
                }
                ?>
            </select>
            <br>
            <label for="deadline">Date limite:</label>
            <input type="date" id="deadline" name="deadline" required>
            <br>
            <button type="submit">Attribuer la tâche</button>
        </form>

        <h2>Tâches assignées</h2>
        <ul>
            <?php foreach ($tasks as $task): ?>
                <li>
                    <?php echo htmlspecialchars($task['description']); ?> - Assignée à <?php echo htmlspecialchars($task['assigned_to']); ?> (Date limite: <?php echo htmlspecialchars($task['deadline']); ?>)
                    <?php if ($task['completed']): ?>
                        <strong>(Terminée)</strong>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>
