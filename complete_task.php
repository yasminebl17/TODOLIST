<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];

    $stmt = $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ?');
    if ($stmt->execute(['completed', $task_id])) {
        header('Location: dashboard.php');
    } else {
        echo "Erreur lors de la mise à jour de la tâche.";
    }
}
?>
