# CYCLEVIE

Application web de gestion du cycle de vie des équipements réseau.

> Projet BUT Réseaux & Télécommunications — Module R5.04

---

## Présentation

CYCLEVIE permet aux étudiants de suivre et d'historiser l'état des équipements réseau (switchs, routeurs, serveurs...) tout au long de leur cycle de vie.

## Fonctionnalités

| # | Fonctionnalité | Description |
|---|---------------|-------------|
| 1 | Authentification | Inscription, connexion et déconnexion des étudiants |
| 2 | Tableau de bord | Vue d'ensemble : équipements, états, alertes |
| 3 | Gestion des équipements | Ajouter, consulter, modifier, supprimer (CRUD) |
| 4 | Cycle de vie | Suivi des changements d'état avec historique |
| 5 | Notifications | Alertes lors d'événements critiques |
| 6 | Administration | Gestion des comptes et vue globale (rôle admin) |

## États d'un équipement

```
Neuf → En service → En maintenance → Hors service → Mis au rebut
```

## Utilisateurs

- **Étudiant** : consulte et gère ses équipements, reçoit des alertes
- **Administrateur** : gère tous les comptes et équipements

## Organisation du projet

Les tâches sont suivies via [GitHub Issues](../../issues) organisées en 5 phases :

- [Phase 1 - Analyse du besoin](../../milestone/1)
- [Phase 2 - Conception](../../milestone/2)
- [Phase 3 - Développement](../../milestone/3)
- [Phase 4 - Tests](../../milestone/4)
- [Phase 5 - Livraison](../../milestone/5)

## Installation

> Documentation à compléter lors de la Phase 3.

```bash
# Cloner le dépôt
git clone https://github.com/Hope-Eliot/cyclevie.git
cd cyclevie
```

## Équipe

Projet réalisé dans le cadre du module R5.04 — IUT Kourou, BUT Réseaux & Télécommunications.
