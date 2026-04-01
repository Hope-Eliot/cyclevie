<?php
require_once __DIR__ . '/../config/database.php';

function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}

function requireLogin() {
    if (!estConnecte()) {
        header('Location: /pages/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /pages/dashboard.php');
        exit;
    }
}

function connecter($email, $mot_de_passe) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM utilisateurs WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['utilisateur_id'] = $user['id'];
        $_SESSION['nom']            = $user['nom'];
        $_SESSION['prenom']         = $user['prenom'];
        $_SESSION['role']           = $user['role'];
        return true;
    }
    return false;
}

function inscrire($nom, $prenom, $email, $mot_de_passe) {
    $db = getDB();
    $stmt = $db->prepare('SELECT id FROM utilisateurs WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) return false; // email déjà utilisé

    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nom, $prenom, $email, $hash]);
    return true;
}

function deconnecter() {
    session_destroy();
    header('Location: /pages/login.php');
    exit;
}

function getNbNotificationsNonLues() {
    if (!estConnecte()) return 0;
    $db = getDB();
    $stmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = 0');
    $stmt->execute([$_SESSION['utilisateur_id']]);
    return $stmt->fetchColumn();
}
