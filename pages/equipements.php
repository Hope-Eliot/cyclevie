<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db     = getDB();
$action = $_GET['action'] ?? 'liste';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$erreur = '';
$succes = '';

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
$types = ['switch', 'routeur', 'serveur', 'firewall', 'autre'];

// ── AJOUTER ──
if ($action === 'ajouter' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom'] ?? '');
    $type   = $_POST['type'] ?? '';
    $serie  = trim($_POST['numero_serie'] ?? '');
    $date   = $_POST['date_achat'] ?? null;
    $etat   = $_POST['etat'] ?? 'neuf';

    if (empty($nom) || empty($type)) {
        $erreur = 'Le nom et le type sont obligatoires.';
    } else {
        $stmt = $db->prepare('INSERT INTO equipements (nom, type, numero_serie, date_achat, etat, utilisateur_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nom, $type, $serie ?: null, $date ?: null, $etat, $_SESSION['utilisateur_id']]);
        $newId = $db->lastInsertId();
        // historique
        $db->prepare('INSERT INTO historique (equipement_id, ancien_etat, nouvel_etat, commentaire) VALUES (?, NULL, ?, ?)')
           ->execute([$newId, $etat, 'Création de l\'équipement']);
        header('Location: /pages/equipements.php?succes=ajoute');
        exit;
    }
}

// ── MODIFIER ──
if ($action === 'modifier' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom'] ?? '');
    $type  = $_POST['type'] ?? '';
    $serie = trim($_POST['numero_serie'] ?? '');
    $date  = $_POST['date_achat'] ?? null;

    if (empty($nom) || empty($type)) {
        $erreur = 'Le nom et le type sont obligatoires.';
    } else {
        $stmt = $db->prepare('UPDATE equipements SET nom=?, type=?, numero_serie=?, date_achat=? WHERE id=? AND utilisateur_id=?');
        $stmt->execute([$nom, $type, $serie ?: null, $date ?: null, $id, $_SESSION['utilisateur_id']]);
        header('Location: /pages/equipements.php?action=detail&id=' . $id . '&succes=modifie');
        exit;
    }
}

// ── CHANGER ÉTAT ──
if ($action === 'changer_etat' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvel_etat  = $_POST['etat'] ?? '';
    $commentaire  = trim($_POST['commentaire'] ?? '');

    $stmt = $db->prepare('SELECT etat FROM equipements WHERE id=? AND utilisateur_id=?');
    $stmt->execute([$id, $_SESSION['utilisateur_id']]);
    $eq = $stmt->fetch();

    if ($eq && array_key_exists($nouvel_etat, $etats_labels)) {
        $db->prepare('UPDATE equipements SET etat=? WHERE id=?')->execute([$nouvel_etat, $id]);
        $db->prepare('INSERT INTO historique (equipement_id, ancien_etat, nouvel_etat, commentaire) VALUES (?, ?, ?, ?)')
           ->execute([$id, $eq['etat'], $nouvel_etat, $commentaire ?: null]);

        // Notification si état critique
        if (in_array($nouvel_etat, ['hors_service', 'mis_au_rebut'])) {
            $stmt2 = $db->prepare('SELECT nom FROM equipements WHERE id=?');
            $stmt2->execute([$id]);
            $nom_eq = $stmt2->fetchColumn();
            $msg = "L'équipement \"$nom_eq\" est passé à l'état : " . $etats_labels[$nouvel_etat];
            $db->prepare('INSERT INTO notifications (utilisateur_id, message) VALUES (?, ?)')
               ->execute([$_SESSION['utilisateur_id'], $msg]);
        }
        header('Location: /pages/equipements.php?action=detail&id=' . $id . '&succes=etat');
        exit;
    }
}

// ── SUPPRIMER ──
if ($action === 'supprimer' && $id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->prepare('DELETE FROM equipements WHERE id=? AND utilisateur_id=?')->execute([$id, $_SESSION['utilisateur_id']]);
    header('Location: /pages/equipements.php?succes=supprime');
    exit;
}

// ── Succès GET ──
$msgs = ['ajoute' => 'Équipement ajouté.', 'supprime' => 'Équipement supprimé.', 'modifie' => 'Équipement modifié.', 'etat' => 'État mis à jour.'];
if (isset($_GET['succes']) && isset($msgs[$_GET['succes']])) {
    $succes = $msgs[$_GET['succes']];
}

// ── DONNÉES SELON ACTION ──
if (in_array($action, ['detail', 'modifier', 'changer_etat', 'supprimer'])) {
    $stmt = $db->prepare('SELECT * FROM equipements WHERE id=? AND utilisateur_id=?');
    $stmt->execute([$id, $_SESSION['utilisateur_id']]);
    $equipement = $stmt->fetch();
    if (!$equipement) { echo '<p>Équipement introuvable.</p>'; require_once __DIR__ . '/../includes/footer.php'; exit; }
}

if ($action === 'detail') {
    $stmt = $db->prepare('SELECT * FROM historique WHERE equipement_id=? ORDER BY date_changement DESC');
    $stmt->execute([$id]);
    $historique = $stmt->fetchAll();
}

if ($action === 'liste') {
    $filtre_type  = $_GET['type'] ?? '';
    $filtre_etat  = $_GET['etat'] ?? '';
    $search       = trim($_GET['q'] ?? '');
    $where = 'WHERE utilisateur_id = ?';
    $params = [$_SESSION['utilisateur_id']];
    if ($filtre_type) { $where .= ' AND type = ?'; $params[] = $filtre_type; }
    if ($filtre_etat) { $where .= ' AND etat = ?'; $params[] = $filtre_etat; }
    if ($search)      { $where .= ' AND nom LIKE ?'; $params[] = "%$search%"; }
    $stmt = $db->prepare("SELECT * FROM equipements $where ORDER BY created_at DESC");
    $stmt->execute($params);
    $equipements = $stmt->fetchAll();
}
?>

<?php if ($succes): ?><div class="alert alert-success"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
<?php if ($erreur): ?><div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>

<?php if ($action === 'liste'): ?>
<div class="page-header">
    <h1>Mes équipements</h1>
    <a href="?action=ajouter" class="btn btn-primary">+ Ajouter</a>
</div>
<form class="filter-bar" method="GET" action="">
    <input type="hidden" name="action" value="liste">
    <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($search ?? '') ?>">
    <select name="type"><option value="">Tous les types</option>
        <?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= ($filtre_type === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option><?php endforeach; ?>
    </select>
    <select name="etat"><option value="">Tous les états</option>
        <?php foreach ($etats_labels as $k => $v): ?><option value="<?= $k ?>" <?= ($filtre_etat === $k) ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline">Filtrer</button>
</form>
<?php if (empty($equipements)): ?>
<p class="empty">Aucun équipement trouvé.</p>
<?php else: ?>
<table class="table">
    <thead><tr><th>Nom</th><th>Type</th><th>N° Série</th><th>Date achat</th><th>État</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($equipements as $eq): ?>
    <tr>
        <td><?= htmlspecialchars($eq['nom']) ?></td>
        <td><?= htmlspecialchars($eq['type']) ?></td>
        <td><?= htmlspecialchars($eq['numero_serie'] ?? '—') ?></td>
        <td><?= $eq['date_achat'] ? date('d/m/Y', strtotime($eq['date_achat'])) : '—' ?></td>
        <td><span class="badge badge-<?= $etats_colors[$eq['etat']] ?>"><?= $etats_labels[$eq['etat']] ?></span></td>
        <td class="actions">
            <a href="?action=detail&id=<?= $eq['id'] ?>" class="btn btn-sm">Détail</a>
            <a href="?action=modifier&id=<?= $eq['id'] ?>" class="btn btn-sm btn-outline">Modifier</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php elseif ($action === 'ajouter'): ?>
<div class="page-header"><h1>Ajouter un équipement</h1></div>
<div class="card form-card">
<form method="POST" action="">
    <div class="form-group"><label>Nom *</label><input type="text" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"></div>
    <div class="form-row">
        <div class="form-group"><label>Type *</label>
            <select name="type" required>
                <?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= (($_POST['type'] ?? '') === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>État initial</label>
            <select name="etat">
                <?php foreach ($etats_labels as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Numéro de série</label><input type="text" name="numero_serie" value="<?= htmlspecialchars($_POST['numero_serie'] ?? '') ?>"></div>
        <div class="form-group"><label>Date d'achat</label><input type="date" name="date_achat" value="<?= htmlspecialchars($_POST['date_achat'] ?? '') ?>"></div>
    </div>
    <div class="form-actions">
        <a href="?" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </div>
</form>
</div>

<?php elseif ($action === 'detail'): ?>
<div class="page-header">
    <h1><?= htmlspecialchars($equipement['nom']) ?></h1>
    <div>
        <a href="?action=modifier&id=<?= $id ?>" class="btn btn-outline">Modifier</a>
        <form method="POST" action="?action=supprimer&id=<?= $id ?>" style="display:inline" onsubmit="return confirm('Supprimer cet équipement ?')">
            <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
    </div>
</div>
<div class="dashboard-grid">
    <div class="card">
        <h2>Informations</h2>
        <dl class="info-list">
            <dt>Type</dt><dd><?= htmlspecialchars($equipement['type']) ?></dd>
            <dt>N° de série</dt><dd><?= htmlspecialchars($equipement['numero_serie'] ?? '—') ?></dd>
            <dt>Date d'achat</dt><dd><?= $equipement['date_achat'] ? date('d/m/Y', strtotime($equipement['date_achat'])) : '—' ?></dd>
            <dt>État actuel</dt><dd><span class="badge badge-<?= $etats_colors[$equipement['etat']] ?>"><?= $etats_labels[$equipement['etat']] ?></span></dd>
            <dt>Ajouté le</dt><dd><?= date('d/m/Y', strtotime($equipement['created_at'])) ?></dd>
        </dl>
        <h2 style="margin-top:1.5rem">Changer l'état</h2>
        <form method="POST" action="?action=changer_etat&id=<?= $id ?>">
            <div class="form-group"><label>Nouvel état</label>
                <select name="etat">
                    <?php foreach ($etats_labels as $k => $v): ?><option value="<?= $k ?>" <?= ($equipement['etat'] === $k) ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Commentaire</label><input type="text" name="commentaire" placeholder="Motif du changement..."></div>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
    <div class="card">
        <h2>Historique du cycle de vie</h2>
        <?php if (empty($historique)): ?>
        <p class="empty">Aucun historique.</p>
        <?php else: ?>
        <ul class="historique-list">
            <?php foreach ($historique as $h): ?>
            <li class="historique-item">
                <div class="historique-etats">
                    <?php if ($h['ancien_etat']): ?><span class="badge badge-<?= $etats_colors[$h['ancien_etat']] ?>"><?= $etats_labels[$h['ancien_etat']] ?></span> → <?php endif; ?>
                    <span class="badge badge-<?= $etats_colors[$h['nouvel_etat']] ?>"><?= $etats_labels[$h['nouvel_etat']] ?></span>
                </div>
                <?php if ($h['commentaire']): ?><p class="historique-comment"><?= htmlspecialchars($h['commentaire']) ?></p><?php endif; ?>
                <span class="historique-date"><?= date('d/m/Y H:i', strtotime($h['date_changement'])) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<?php elseif ($action === 'modifier'): ?>
<div class="page-header"><h1>Modifier — <?= htmlspecialchars($equipement['nom']) ?></h1></div>
<div class="card form-card">
<form method="POST" action="">
    <div class="form-group"><label>Nom *</label><input type="text" name="nom" required value="<?= htmlspecialchars($equipement['nom']) ?>"></div>
    <div class="form-row">
        <div class="form-group"><label>Type *</label>
            <select name="type" required>
                <?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= ($equipement['type'] === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Numéro de série</label><input type="text" name="numero_serie" value="<?= htmlspecialchars($equipement['numero_serie'] ?? '') ?>"></div>
        <div class="form-group"><label>Date d'achat</label><input type="date" name="date_achat" value="<?= htmlspecialchars($equipement['date_achat'] ?? '') ?>"></div>
    </div>
    <div class="form-actions">
        <a href="?action=detail&id=<?= $id ?>" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
