# 📚 ReadSphere - Plateforme de Gestion de Bibliothèque Personnelle

ReadSphere est une application web complète développée en PHP natif avec une architecture modulaire, conçue pour gérer une bibliothèque personnelle de livres, partager des critiques et interagir avec d'autres lecteurs. Ce projet illustre des bonnes pratiques de développement web, notamment la sécurité, la structure de code et l'expérience utilisateur.

## 🌟 Fonctionnalités Principales

### 👤 Gestion des Utilisateurs
- Système d'inscription et d'authentification sécurisé
- Profils personnalisables avec avatars
- Tableau de bord personnalisé selon le rôle (Utilisateur/Administrateur)

### 📚 Gestion des Livres
- Ajout, édition et suppression de livres avec gestion des couvertures
- Système avancé de recherche et de filtrage
- Notation et critiques détaillées
- Catégorisation par genres et étiquettes

### 💬 Interactions Sociales
- Système complet de commentaires
- Notation des livres et des critiques
- Favoris et listes de lecture personnalisées
- Flux d'activité des utilisateurs suivis
- Notifications par email pour les interactions

## 🛠️ Architecture Technique

### Backend
- **Langage** : PHP 8.0+
- **Base de données** : MySQL 8.0+
- **Architecture** : Modèle-Vue-Contrôleur (MVC) personnalisé
- **API** : Endpoints RESTful pour les opérations asynchrones
- **Sécurité** : Protection CSRF, validation des entrées, hachage des mots de passe

### Frontend
- **HTML5** : Structure sémantique et accessible
- **CSS3** : Tailwind CSS pour un design moderne et responsive
- **JavaScript** : Vanilla JS avec manipulation du DOM et requêtes AJAX
- **UX/UI** : Interface intuitive avec retours utilisateur clairs

## 📂 Structure des Dossiers

```
ReadSphere/
├── admin/           # Panneau d'administration
├── api/             # Points d'API REST
├── assets/          # Fichiers statiques (CSS, JS, images, polices)
├── cache/           # Fichiers de cache (images, vues)
├── database/        # Schémas et migrations SQL
├── includes/        # Cœur de l'application
│   ├── config/      # Configuration et constantes
│   │   ├── paths.php     # Gestion des chemins système
│   │   ├── constants.php # Constantes globales
│   │   └── images.php    # Configuration des images
│   ├── utils/       # Utilitaires
│   │   └── AvatarManager.php # Gestion des avatars
│   ├── auth.php     # Authentification
│   ├── db.php       # Connexion à la base de données
│   ├── functions.php # Fonctions utilitaires
│   ├── init.php     # Initialisation de l'application
│   ├── mailer.php   # Gestion des emails avec PHPMailer
│   └── ...
├── logs/            # Fichiers de logs
├── public/          # Point d'entrée public
│   ├── assets/      # Fichiers statiques (CSS, JS, images, polices)
│   └── uploads/     # Fichiers téléchargés
├── resources/       # Assets sources (SCSS, JS non minifié)
├── storage/         # Stockage des fichiers générés
├── templates/       # Templates de vues
│   ├── 404.php       # Page d'erreur 404
│   ├── footer.php    # Footer
│   └── header.php    # Header
├── uploads/         # Fichiers téléchargés (couvertures, avatars, etc.)
├── login.php        # Page de connexion
├── signup.php     # Page d'inscription
├── index.php      # Page d'accueil
├── book.php       # Page de détail d'un livre
├── profile.php    # Page de profil
├── verify-email.php # Page de vérification d'email
├── logout.php     # Page de déconnexion
└── ...
```

## 🔧 Fichiers Clés et Leur Rôle

### Configuration Principale
- **paths.php** : Définit tous les chemins système et URLs de l'application
- **constants.php** : Constantes globales et configuration des chemins
- **init.php** : Initialise l'application, les sessions et les dépendances
- **db.php** : Gère la connexion à la base de données avec PDO
- **mailer.php** : Système d'envoi d'emails avec PHPMailer

### Composants Principaux
- **AvatarManager.php** : Gère le téléchargement et le redimensionnement des avatars
- **auth.php** : Gère l'authentification et les autorisations
- **functions.php** : Fonctions utilitaires réutilisables

## 🔒 Sécurité Implémentée

### Protection des Données
- Hachage des mots de passe avec Argon2id
- Protection CSRF sur tous les formulaires
- Validation et assainissement des entrées utilisateur
- Échappement systématique des sorties
- Protection contre les injections SQL avec PDO

### Sécurité des Fichiers
- Vérification des types MIME des fichiers uploadés
- Renommage des fichiers téléchargés
- Restrictions de taille et de type
- Séparation des fichiers sensibles du répertoire public

### Gestion des Sessions
- Régénération des ID de session
- Durée de vie limitée des sessions
- Protection contre le vol de session

## 👥 Rôles et Permissions

### Utilisateur Standard
- Gérer son profil et ses préférences
- Ajouter/modifier/supprimer ses livres
- Écrire des critiques et noter les livres
- Commenter les livres des autres utilisateurs
- Signaler des contenus inappropriés (commentaires)

### Administrateur
- Toutes les permissions utilisateur
- Modération des contenus (livres, commentaires)
- Gestion des utilisateurs
- Gestion des rôles et permissions
- Résolution des signalements
- Accès aux statistiques du site
- Configuration du système

## 📧 Système d'Emails

Le module `mailer.php` gère tous les envois d'emails de l'application avec PHPMailer :
- Emails de bienvenue et vérification de compte
- Réinitialisation de mot de passe
- Notifications de nouveaux commentaires
- Messages système importants

Configuration requise dans `.env` :
```
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USERNAME=user@example.com
SMTP_PASSWORD=votre_mot_de_passe
```

## 🚀 Démarrage Rapide

1. **Configuration requise**
   - PHP 8.0+ avec extensions PDO, GD, OpenSSL
   - MySQL 8.0+ ou MariaDB 10.3+
   - Serveur web (Apache/Nginx) avec réécriture d'URL
   - Compte email SMTP pour les notifications

2. **Configuration initiale**
   - Copier `.env.example` vers `.env` et ajuster les paramètres
   - Configurer les accès à la base de données
   - Définir les paramètres SMTP pour les emails
   - S'assurer que les dossiers `uploads/` et `cache/` sont accessibles en écriture

3. **Base de données**
   - Importer le schéma SQL
   - Vérifier les permissions de l'utilisateur de la base de données

## 📝 Améliorations Futures

- Suivre d'autres utilisateurs pour un flux d'actualités
- Système de suivi des livres lus/en cours/souhaités/abandonnés/planifiés/finis
- Système de messagerie privée
- API publique pour l'intégration tierce
- Application mobile native
- Système de recommandation base sur l'IA
- Export/Import des données utilisateur

## 🤝 Contribution

Bien que ce soit un projet personnel, les suggestions d'amélioration sont les bienvenues. Pour contribuer :

1. Ouvrir une issue pour discuter des changements proposés
2. Créer une branche pour votre fonctionnalité
3. Soumettre une Pull Request avec une description claire


## 🙋 Support

Pour toute question ou problème, veuillez ouvrir une issue sur le dépôt du projet.

## 📜 A propos

- Ce projet a été développé par Amine Nedjar.
- Contact : amine.nedjar4716@gmail.com

## 📝 Licence

Ce projet est sous licence [MIT](LICENSE).
