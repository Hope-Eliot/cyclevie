<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';

if (estConnecte()) {
    header('Location: /pages/dashboard.php');
    exit;
}

$erreur  = '';
$succes  = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mdp    = $_POST['mot_de_passe'] ?? '';
    $mdp2   = $_POST['mot_de_passe2'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } elseif ($mdp !== $mdp2) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($mdp) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif (inscrire($nom, $prenom, $email, $mdp)) {
        $succes = 'Compte créé ! Vous pouvez vous connecter.';
    } else {
        $erreur = 'Cet email est déjà utilisé.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription — CYCLEVIE</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <h1 class="auth-title">CYCLEVIE</h1>
    <p class="auth-subtitle">Créer un compte</p>

    <?php if ($erreur): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>
    <?php if ($succes): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required
                       value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <div class="form-group">
            <label for="mot_de_passe2">Confirmer le mot de passe</label>
            <input type="password" id="mot_de_passe2" name="mot_de_passe2" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">S'inscrire</button>
    </form>
    <p class="auth-link">Déjà un compte ? <a href="/pages/login.php">Se connecter</a></p>
</div>
</body>
</html>
