<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';

if (estConnecte()) {
    header('Location: /pages/dashboard.php');
    exit;
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($mdp)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } elseif (connecter($email, $mdp)) {
        header('Location: /pages/dashboard.php');
        exit;
    } else {
        $erreur = 'Email ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion — CYCLEVIE</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <h1 class="auth-title">CYCLEVIE</h1>
    <p class="auth-subtitle">Connexion</p>

    <?php if ($erreur): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
    </form>
    <p class="auth-link">Pas encore de compte ? <a href="/pages/register.php">S'inscrire</a></p>
</div>
</body>
</html>
