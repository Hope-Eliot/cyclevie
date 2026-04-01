<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
$nbNotifs = estConnecte() ? getNbNotificationsNonLues() : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CYCLEVIE</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="/pages/dashboard.php" class="nav-brand">CYCLEVIE</a>
    <?php if (estConnecte()): ?>
    <ul class="nav-links">
        <li><a href="/pages/dashboard.php">Tableau de bord</a></li>
        <li><a href="/pages/equipements.php">Équipements</a></li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="/pages/admin.php">Administration</a></li>
        <?php endif; ?>
        <li>
            <a href="/pages/notifications.php" class="notif-link">
                Notifications
                <?php if ($nbNotifs > 0): ?>
                <span class="badge"><?= $nbNotifs ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li><a href="/pages/logout.php">Déconnexion</a></li>
    </ul>
    <span class="nav-user"><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></span>
    <?php endif; ?>
</nav>
<main class="container">
