<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDB();

// Marquer toutes comme lues
$db->prepare('UPDATE notifications SET lu=1 WHERE utilisateur_id=?')->execute([$_SESSION['utilisateur_id']]);

// Récupérer toutes les notifications
$stmt = $db->prepare('SELECT * FROM notifications WHERE utilisateur_id=? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['utilisateur_id']]);
$notifs = $stmt->fetchAll();
?>

<div class="page-header"><h1>Notifications</h1></div>
<div class="card">
<?php if (empty($notifs)): ?>
    <p class="empty">Aucune notification.</p>
<?php else: ?>
    <ul class="notif-list notif-list-full">
    <?php foreach ($notifs as $n): ?>
        <li class="notif-item">
            <span class="notif-msg"><?= htmlspecialchars($n['message']) ?></span>
            <span class="notif-date"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></span>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
