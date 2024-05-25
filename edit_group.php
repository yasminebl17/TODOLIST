<?php
require 'db.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = $_POST['group_id'];
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $leader_id = isset($_POST['leader_id']) ? $_POST['leader_id'] : '';
    $members = isset($_POST['members']) ? $_POST['members'] : array();

    // Vérifiez que les valeurs ne sont pas vides avant d'exécuter le traitement
    if (!empty($name) && !empty($leader_id)) {
        // Mettre à jour les détails du groupe dans la base de données
        $stmt = $pdo->prepare('UPDATE groups SET name = ?, leader_id = ? WHERE id = ?');
        if ($stmt->execute([$name, $leader_id, $group_id])) {
            // Affecter le rôle "leader" au chef de groupe
            $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->execute(['leader', $leader_id]);

            // Supprimer les membres existants du groupe
            $stmt = $pdo->prepare('DELETE FROM group_members WHERE group_id = ?');
            $stmt->execute([$group_id]);

            // Réinsérer les membres mis à jour dans le groupe et affecter le rôle "member"
            $stmt = $pdo->prepare('INSERT INTO group_members (group_id, user_id) VALUES (?, ?)');
            foreach ($members as $member_id) {
                $stmt->execute([$group_id, $member_id]);
                $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
                $stmt->execute(['member', $member_id]);
            }

            echo "Le groupe a été modifié avec succès.";
        } else {
            echo "Erreur lors de la modification du groupe.";
        }
    } else {
        echo "Veuillez remplir tous les champs obligatoires.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['group_id'])) {
        $group_id = $_GET['group_id'];

        // Récupérer les détails du groupe
        $stmt = $pdo->prepare('SELECT * FROM groups WHERE id = ?');
        $stmt->execute([$group_id]);
        $group = $stmt->fetch();

        // Récupérer tous les utilisateurs
        $stmt = $pdo->query('SELECT * FROM users');
        $users = $stmt->fetchAll();

        // Récupérer les membres actuels du groupe
        $stmt = $pdo->prepare('SELECT user_id FROM group_members WHERE group_id = ?');
        $stmt->execute([$group_id]);
        $group_members = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        header('Location: admin_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier un Groupe</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Modifier un Groupe</h2>
    <?php if (isset($group)): ?>
        <form method="POST" action="edit_group.php">
            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
            <label for="name">Nom du groupe:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($group['name']); ?>" required>
            <br>
            <label for="leader_id">Chef de groupe:</label>
            <select id="leader_id" name="leader_id" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php if ($user['id'] == $group['leader_id']) echo 'selected'; ?>><?php echo htmlspecialchars($user['username']); ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="members">Membres:</label>
            <br>
            <?php foreach ($users as $user): ?>
                <input type="checkbox" id="member_<?php echo $user['id']; ?>" name="members[]" value="<?php echo $user['id']; ?>" <?php if (in_array($user['id'], $group_members)) echo 'checked'; ?>>
                <label for="member_<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></label><br>
            <?php endforeach; ?>
            <br>
            <button type="submit">Enregistrer les modifications</button>
        </form>
    <?php else: ?>
        <p>Le groupe n'a pas été trouvé.</p>
    <?php endif; ?>
</body>
</html>

