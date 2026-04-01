<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db = getDB();

// Statistiques équipements de l'utilisateur
$stmt = $db->prepare('SELECT etat, COUNT(*) as nb FROM equipements WHERE utilisateur_id = ? GROUP BY etat');
$stmt->execute([$_SESSION['utilisateur_id']]);
$stats = [];
foreach ($stmt->fetchAll() as $row) {
    $stats[$row['etat']] = $row['nb'];
}
$total = array_sum($stats);

// Derniers équipements
$stmt = $db->prepare('SELECT * FROM equipements WHERE utilisateur_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$_SESSION['utilisateur_id']]);
$derniers = $stmt->fetchAll();

// Notifications non lues
$stmt = $db->prepare('SELECT * FROM notifications WHERE utilisateur_id = ? AND lu = 0 ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$_SESSION['utilisateur_id']]);
$notifs = $stmt->fetchAll();

$etats_labels = [
    'neuf'           => 'Neuf',
    'en_service'     => 'En service',
    'en_maintenance' => 'En maintenance',
    'hors_service'   => 'Hors service',
    'mis_au_rebut'   => 'Mis au rebut',
];
$etats_colors = [
    'neuf'           => 'blue',
    'en_service'     => 'green',
    'en_maintenance' => 'orange',
    'hors_service'   => 'red',
    'mis_au_rebut'   => 'gray',
];
?>

<div class="page-header">
    <h1>Bonjour, <?= htmlspecialchars($_SESSION['prenom']) ?> !</h1>
    <a href="/pages/equipements.php?action=ajouter" class="btn btn-primary">+ Ajouter un équipement</a>
</div>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-number"><?= $total ?></span>
        <span class="stat-label">Total équipements</span>
    </div>
    <div class="stat-card stat-green">
        <span class="stat-number"><?= $stats['en_service'] ?? 0 ?></span>
        <span class="stat-label">En service</span>
    </div>
    <div class="stat-card stat-orange">
        <span class="stat-number"><?= $stats['en_maintenance'] ?? 0 ?></span>
        <span class="stat-label">En maintenance</span>
    </div>
    <div class="stat-card stat-red">
        <span class="stat-number"><?= $stats['hors_service'] ?? 0 ?></span>
        <span class="stat-label">Hors service</span>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Derniers équipements -->
    <section class="card">
        <h2>Derniers équipements</h2>
        <?php if (empty($derniers)): ?>
        <p class="empty">Aucun équipement. <a href="/pages/equipements.php?action=ajouter">Ajouter le premier</a></p>
        <?php else: ?>
        <table class="table">
            <thead><tr><th>Nom</th><th>Type</th><th>État</th></tr></thead>
            <tbody>
            <?php foreach ($derniers as $eq): ?>
            <tr>
                <td><a href="/pages/equipements.php?action=detail&id=<?= $eq['id'] ?>"><?= htmlspecialchars($eq['nom']) ?></a></td>
                <td><?= htmlspecialchars($eq['type']) ?></td>
                <td><span class="badge badge-<?= $etats_colors[$eq['etat']] ?>"><?= $etats_labels[$eq['etat']] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <a href="/pages/equipements.php" class="btn btn-outline">Voir tous</a>
        <?php endif; ?>
    </section>

    <!-- Notifications -->
    <section class="card">
        <h2>Alertes récentes</h2>
        <?php if (empty($notifs)): ?>
        <p class="empty">Aucune alerte.</p>
        <?php else: ?>
        <ul class="notif-list">
            <?php foreach ($notifs as $n): ?>
            <li class="notif-item">
                <span class="notif-msg"><?= htmlspecialchars($n['message']) ?></span>
                <span class="notif-date"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <a href="/pages/notifications.php" class="btn btn-outline">Voir toutes</a>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
