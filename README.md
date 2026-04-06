# Guide Utilisateur - Application de Bibliothèque

## Comptes Utilisateurs

L'application dispose de trois comptes par défaut créés via la commande `php bin/console create-admin` :

### Administrateur
- **ID** : 1
- **Email** : admin@example.com
- **Mot de passe** : admin123
- **Rôles** : ROLE_ADMIN

### Bibliothécaire
- **ID** : 4
- **Email** : librarian@example.com
- **Mot de passe** : librarian123
- **Rôles** : ROLE_LIBRARIAN

### Utilisateur Standard
- **ID** : 5
- **Email** : user@example.com
- **Mot de passe** : user123
- **Rôles** : ROLE_USER

## Fonctionnalités par Rôle

### Utilisateur Standard (ROLE_USER)
Les utilisateurs standards peuvent :
- **S'inscrire et se connecter** via un espace personnel avec vérification des champs (email valide, mot de passe fort) et double vérification du mot de passe
- Parcourir le catalogue de livres
- Rechercher des livres par titre, auteur ou catégorie
- Voir les détails d'un livre (description, stock disponible, avis)
- Réserver des livres disponibles
- Voir leurs réservations personnelles dans le profil
- Laisser des avis et notes sur les livres empruntés
- Modifier leur profil

**Accès** : Page d'accueil, recherche, réservation, profil, inscription/connexion

### Bibliothécaire (ROLE_LIBRARIAN)
En plus des droits utilisateur, les bibliothécaires peuvent :
- Accéder au tableau de bord bibliothécaire (`/librarian`)
- Voir les KPI : Livres totaux, Stock faible, Réservations en attente, Utilisateurs, Livres empruntés
- Consulter l'historique complet des réservations
- Approuver ou rejeter les réservations en attente
- Retourner les livres empruntés
- Ajouter de nouveaux livres
- Modifier les livres existants
- Gérer le catalogue

**Accès** : Tout ce qui est utilisateur + tableau de bord bibliothécaire + gestion des livres + actions sur réservations

### Administrateur (ROLE_ADMIN)
En plus des droits bibliothécaire, les administrateurs peuvent :
- Accéder au tableau de bord administrateur complet (`/admin`)
- Voir tous les utilisateurs avec leurs mots de passe hashés
- Modifier les rôles des utilisateurs
- Modérer les avis des utilisateurs
- Accéder à toutes les fonctionnalités de gestion

**Accès** : Tout ce qui est bibliothécaire + gestion des utilisateurs + modération des avis

## Flux de Réservation

1. **Utilisateur** réserve un livre via la fiche livre ou la recherche
2. **Bibliothécaire/Administrateur** approuve ou rejette la réservation depuis le tableau de bord
3. Si approuvée : statut devient "active", stock diminue, date de début enregistrée
4. **Utilisateur** peut emprunter le livre (logique métier)
5. **Bibliothécaire/Administrateur** retourne le livre, statut devient "returned", stock augmente

## Gestion du Stock

### Stock Disponible vs Stock Brut

- **Stock Brut** : Le nombre total de livres physiques en bibliothèque (inchangé par les réservations)
- **Stock Disponible** : `Stock Brut - Réservations Actives`
  - Exemple : Un livre a 4 copies avec 2 réservations actives → Stock disponible = 2

### Notification de Stock Faible

- Un livre est signalé comme **"Stock Faible"** quand : **Stock disponible ≤ 2**
- Cela signifie que le nombre de copies disponibles pour une nouvelle réservation est ≤ 2
- Les livres en stock faible apparaissent dans le KPI "Stock faible" des dashboards admin et bibliothécaire
- **Important** : Le stock faible tient compte des réservations actives, pas du stock brut seul

### Exemple Concret

| Situation | Stock Brut | Réservations Actives | Stock Disponible | Statut |
|-----------|-----------|-------------------|------------------|--------|
| Livre neuf | 5 | 0 | 5 | ✅ Normal |
| Populaire | 4 | 2 | 2 | ⚠️ Stock faible |
| Rupture | 3 | 3 | 0 | 🔴 Indisponible |
| Très demandé | 4 | 3 | 1 | 🔴 Stock critique |

### Cycle de Vie des Réservations

- **Pending** : Réservation en attente d'approbation, ne déduit pas le stock
- **Active** : Réservation approuvée, déduit temporairement du stock disponible
- **Returned** : Livre retourné, stock restitué
- **Rejected** : Réservation refusée, aucun impact sur le stock

## Fonctionnalités Clés

- **Stock dynamique** : Le stock affiché tient compte des réservations actives
- **Notifications de retard** : Les réservations en retard sont mises en évidence en rouge
- **Recherche avancée** : Filtrage par titre, auteur, catégorie
- **Gestion des rôles** : Système de permissions basé sur les rôles Symfony
- **Interface responsive** : Utilise Bootstrap 5 pour une expérience mobile-friendly

## Démarrage

1. Lancer le serveur : `symfony server:start` ou `php -S localhost:8000 -t public`
2. Créer les utilisateurs : `php bin/console create-admin`
3. Accéder à l'application : `http://localhost:8000`

## Sécurité

- Mots de passe hashés avec bcrypt
- Protection CSRF sur les formulaires
- Contrôle d'accès basé sur les rôles
- Séparation des espaces admin/bibliothécaire/utilisateur</content>
<parameter name="filePath">c:\Users\flori\Desktop\my_project\README.md