<?php
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDB();

// Action désactiver/activer compte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user'])) {
    $uid = (int)$_POST['toggle_user'];
    if ($uid !== $_SESSION['utilisateur_id']) {
        $stmt = $db->prepare('SELECT role FROM utilisateurs WHERE id=?');
        $stmt->execute([$uid]);
        $u = $stmt->fetch();
        if ($u && $u['role'] !== 'admin') {
            // On utilise le rôle pour simuler activation : 'desactive' = désactivé
            $nouveau = ($u['role'] === 'desactive') ? 'etudiant' : 'desactive';
            $db->prepare('UPDATE utilisateurs SET role=? WHERE id=?')->execute([$nouveau, $uid]);
        }
    }
    header('Location: /pages/admin.php');
    exit;
}

// Stats globales
$stats = $db->query('SELECT COUNT(*) FROM utilisateurs WHERE role != "admin"')->fetchColumn();
$nbEq  = $db->query('SELECT COUNT(*) FROM equipements')->fetchColumn();
$nbAlerts = $db->query('SELECT COUNT(*) FROM equipements WHERE etat IN ("hors_service","mis_au_rebut")')->fetchColumn();

// Liste utilisateurs
$utilisateurs = $db->query('SELECT u.*, COUNT(e.id) as nb_eq FROM utilisateurs u LEFT JOIN equipements e ON e.utilisateur_id = u.id GROUP BY u.id ORDER BY u.created_at DESC')->fetchAll();

// Tous les équipements
$equipements = $db->query('SELECT e.*, u.nom as u_nom, u.prenom as u_prenom FROM equipements e JOIN utilisateurs u ON u.id = e.utilisateur_id ORDER BY e.created_at DESC')->fetchAll();

$etats_labels = ['neuf'=>'Neuf','en_service'=>'En service','en_maintenance'=>'En maintenance','hors_service'=>'Hors service','mis_au_rebut'=>'Mis au rebut'];
$etats_colors = ['neuf'=>'blue','en_service'=>'green','en_maintenance'=>'orange','hors_service'=>'red','mis_au_rebut'=>'gray'];
?>

<div class="page-header"><h1>Administration</h1></div>

<div class="stats-grid">
    <div class="stat-card"><span class="stat-number"><?= $stats ?></span><span class="stat-label">Étudiants</span></div>
    <div class="stat-card stat-green"><span class="stat-number"><?= $nbEq ?></span><span class="stat-label">Équipements total</span></div>
    <div class="stat-card stat-red"><span class="stat-number"><?= $nbAlerts ?></span><span class="stat-label">États critiques</span></div>
</div>

<section class="card" style="margin-top:2rem">
    <h2>Comptes étudiants</h2>
    <table class="table">
        <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Équipements</th><th>Inscrit le</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($utilisateurs as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'blue' : ($u['role'] === 'desactive' ? 'gray' : 'green') ?>"><?= $u['role'] ?></span></td>
            <td><?= $u['nb_eq'] ?></td>
            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
            <?php if ($u['role'] !== 'admin' && $u['id'] !== $_SESSION['utilisateur_id']): ?>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="toggle_user" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-sm <?= $u['role'] === 'desactive' ? 'btn-primary' : 'btn-danger' ?>">
                        <?= $u['role'] === 'desactive' ? 'Activer' : 'Désactiver' ?>
                    </button>
                </form>
            <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card" style="margin-top:2rem">
    <h2>Tous les équipements</h2>
    <table class="table">
        <thead><tr><th>Nom</th><th>Type</th><th>État</th><th>Étudiant</th></tr></thead>
        <tbody>
        <?php foreach ($equipements as $eq): ?>
        <tr>
            <td><?= htmlspecialchars($eq['nom']) ?></td>
            <td><?= htmlspecialchars($eq['type']) ?></td>
            <td><span class="badge badge-<?= $etats_colors[$eq['etat']] ?>"><?= $etats_labels[$eq['etat']] ?></span></td>
            <td><?= htmlspecialchars($eq['u_prenom'] . ' ' . $eq['u_nom']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
