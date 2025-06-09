==========================================================
Programme de Fidélité - Guide d'installation & Sécurité
==========================================================

SOMMAIRE
--------
1. Structure du projet
2. Installation de la base de données
3. Configuration de l'application
4. Premiers accès
5. Conseils de sécurité (Apache, PHP)
6. Redirections et accès au site vitrine
7. Notifications par mail (sans support)
8. Informations à personnaliser avant mise en ligne
9. Autres conseils de bonnes pratiques
10. Assistance

----------------------------------------------------------
1. STRUCTURE DU PROJET
----------------------------------------------------------

programme-fidelite/
├── admin/               # Pages et outils pour les administrateurs
│   ├── admin_ajout.php
│   ├── admin_modif.php
│   ├── admin_suppr.php
│   ├── admins.php
│   ├── ajouter_vendeur.php
│   ├── clients.php
│   ├── deconnexion.php
│   ├── entites.php
│   ├── index.php
│   ├── login.php
│   ├── modifier_client.php
│   ├── modifier_entite.php
│   ├── modifier_site.php
│   ├── modifier_vendeur.php
│   ├── offres.php
│   ├── sites.php
│   ├── supprimer_client.php
│   ├── supprimer_vendeur.php
│   └── vendeurs.php
├── assets/              # Ressources diverses (polices, icônes, etc.)
├── css/                 # Feuilles de style CSS
│   └── style.css
├── includes/            # Fichiers PHP partagés/utilitaires
│   ├── barcode39/
│   │   └── Barcode39.php
│   ├── config.php       # Configuration de la connexion à la base de données
│   └── log.php
├── public/              # Pages accessibles aux clients/utilisateurs
│   ├── images/
│   │   └── icone-magasin.png
│   ├── barcode.php
│   ├── cgu.php
│   ├── connexion.php
│   ├── connexion_codebarre.php
│   ├── dashboard.php
│   ├── deconnexion.php
│   ├── index.php
│   ├── inscription.php
│   ├── mentions_legales.php
│   ├── modifier_infos.php
│   ├── mon-site-favori.php
│   ├── reset_mdp.php
│   └── mreset_mdp_nouveau.php
├── sql/
│   └── fidelite.sql     # Fichier SQL pour créer la base de données et les tables
├── vendeur/             # Pages pour les vendeurs (en magasin)
│   ├── clients.php
│   ├── deconnexion.php
│   ├── index.php
│   ├── login.php
│   └── profil.php

----------------------------------------------------------
2. INSTALLATION DE LA BASE DE DONNÉES
----------------------------------------------------------

1. Ouvrez phpMyAdmin et connectez-vous à votre serveur MySQL.

2. Créez une nouvelle base de données nommée :
      fidelite

   (Collation recommandée : utf8mb4_unicode_ci)

3. Sélectionnez la base "fidelite" dans la colonne de gauche.

4. Allez dans l’onglet "Importer".

5. Cliquez sur "Choisir un fichier", sélectionnez le fichier :
      sql/fidelite.sql

6. Cliquez sur "Exécuter" pour lancer l’import.

7. Vérifiez que toutes les tables sont présentes, notamment la table "admins".

----------------------------------------------------------
3. CONFIGURATION DE L’APPLICATION
----------------------------------------------------------

Ouvrez le fichier `includes/config.php` et vérifiez/modifiez les paramètres suivants :

    $host = 'localhost';        // Hôte MySQL
    $db   = 'fidelite';         // Nom de la base de données
    $user = 'root';             // Utilisateur MySQL
    $pass = '';                 // Mot de passe MySQL

Adaptez-les selon votre environnement si nécessaire.

----------------------------------------------------------
4. PREMIERS ACCÈS
----------------------------------------------------------

Après l’import, un compte administrateur natif est disponible :

    Email        : root@root.local
    Mot de passe : 8b$V2=VW@j32#=pVzk9X

Utilisez ces identifiants pour vous connecter à l’espace d’administration
et créer d’autres comptes si besoin.

----------------------------------------------------------
5. CONSEILS DE SÉCURITÉ (APACHE, PHP)
----------------------------------------------------------

Pour protéger votre serveur et vos données en production, appliquez ces recommandations :

A. **Masquer la version du serveur Apache**

Dans le fichier de configuration Apache (ex : `/etc/apache2/conf-enabled/security.conf` ou `/etc/apache2/apache2.conf`), ajoutez ou modifiez :

    ServerTokens Prod
    ServerSignature Off

- `ServerTokens Prod` : n’affiche que “Server: Apache” dans les en-têtes HTTP.
- `ServerSignature Off` : supprime la signature Apache dans les pages d’erreur.

Redémarrez Apache après modification :

    sudo systemctl restart apache2

B. **Cacher la version de PHP**

Dans votre fichier `php.ini`, définissez :

    expose_php = Off

Cela empêche la version de PHP d’apparaître dans les en-têtes HTTP.

C. **Cacher les erreurs en production**

Dans `php.ini` ou via `.htaccess` :

    display_errors = Off
    log_errors = On

Ainsi, les erreurs ne seront pas affichées aux visiteurs mais bien enregistrées dans les logs.

D. **Mettre à jour régulièrement**

Maintenez Apache, PHP et toutes vos dépendances à jour pour bénéficier des derniers correctifs de sécurité.

E. **Autres conseils**

- Modifiez le mot de passe du compte admin par défaut dès la première connexion.
- Supprimez ou déplacez le fichier SQL après installation.
- Vérifiez les droits d’accès sur les dossiers sensibles (`includes/`, `sql/`).
- Désactivez l’indexation des dossiers dans Apache (Options -Indexes).

----------------------------------------------------------
6. REDIRECTIONS ET ACCÈS AU SITE VITRINE
----------------------------------------------------------

Pour garantir une bonne expérience utilisateur et éviter l’accès direct à des dossiers sensibles (admin, vendeur, includes, sql, etc.), il est recommandé de configurer des redirections vers le site vitrine (page d’accueil publique).

A. **Redirection depuis la racine ou les dossiers sensibles**

Placez un fichier `.htaccess` dans chaque dossier à protéger (par exemple, `admin/`, `vendeur/`, `includes/`, `sql/`) avec le contenu suivant :

    # Redirige tout accès direct vers le site vitrine
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^.*$ /public/index.php [L,R=302]

- Adaptez `/public/index.php` à l’URL de votre site vitrine si besoin.

B. **Redirection de la racine du site**

Si vous souhaitez que tout accès à la racine du site redirige automatiquement vers la page vitrine :

Dans le `.htaccess` à la racine du projet :

    DirectoryIndex public/index.php

Ou, pour une redirection automatique :

    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/$
    RewriteRule ^$ /public/index.php [L,R=302]

C. **Conseils supplémentaires**

- Ne laissez jamais d’accès public à `includes/` ou `sql/`.
- Testez vos redirections pour éviter les boucles infinies.
- Pour une redirection permanente, utilisez `R=301` au lieu de `R=302`.

Ainsi, les utilisateurs et visiteurs seront toujours dirigés vers le site vitrine, et les dossiers sensibles resteront inaccessibles.

----------------------------------------------------------
7. NOTIFICATIONS PAR MAIL (SANS SUPPORT)
----------------------------------------------------------

Le projet inclut une fonctionnalité d’envoi de notifications par email (ex : confirmation d’inscription, réinitialisation de mot de passe, alertes administrateur, etc.).

**Important :**
- Cette fonctionnalité a été développée, mais n’a pas encore été testée en conditions réelles sur tous les environnements.
- Avant la mise en production, il est fortement recommandé de :
    - Vérifier la configuration de l’envoi d’emails dans votre hébergement (serveur SMTP, fonction mail PHP, etc.).
    - Tester l’envoi et la réception des emails pour tous les scénarios (inscription, réinitialisation, notifications…).
    - Contrôler que les emails ne sont pas classés comme spam.
    - Adapter le contenu et l’adresse d’expéditeur si besoin.

**À noter :**
- Cette fonctionnalité est fournie **en l’état** et **aucun support** n’est assuré sur la configuration, le fonctionnement ou le dépannage des notifications par mail.
- Il appartient à chaque utilisateur ou administrateur de tester et d’adapter cette partie selon son environnement.

**Conseil :**
- Consultez la documentation de votre hébergeur pour la configuration SMTP.
- Utilisez des outils comme [Mailtrap](https://mailtrap.io/) ou [Mailhog](https://github.com/mailhog/MailHog) pour tester l’envoi des emails en environnement de développement.

N’hésitez pas à signaler tout dysfonctionnement ou à proposer des améliorations pour cette fonctionnalité, mais aucune assistance directe ne sera assurée sur ce point.

----------------------------------------------------------
8. INFORMATIONS À PERSONNALISER AVANT MISE EN LIGNE
----------------------------------------------------------

A. **Mentions légales et CGV**

- Les pages **mentions légales** (`public/mentions_legales.php`) et **conditions générales de vente (CGV)** (`public/cgu.php`) existent déjà dans le projet.
- Il est **obligatoire** de les compléter avec vos propres informations avant toute mise en ligne, conformément à la législation française (LCEN du 21 juin 2004).
- Les mentions légales doivent permettre d’identifier clairement le responsable du site, l’hébergeur, les coordonnées de contact, etc. Leur absence expose à des sanctions pénales pouvant aller jusqu’à un an d’emprisonnement et 75 000 € d’amende pour une personne physique, ou 375 000 € pour une personne morale.
- Les CGV doivent informer vos clients de leurs droits et obligations lors de l’utilisation du service ou de la vente de produits.

**Conseil** : Placez un lien vers ces pages en pied de page du site, et vérifiez que leur contenu est bien à jour et adapté à votre activité.

B. **Paramétrage des entités, sites et vendeurs**

- Avant toute utilisation du programme de fidélité, il est indispensable de renseigner et personnaliser :
    - Les **entités** (groupes, enseignes, franchises…) via l’espace d’administration.
    - Les **sites** (magasins physiques, points de vente…).
    - Les **vendeurs** (comptes utilisateurs pour le personnel en magasin).
- Ces informations sont essentielles pour le bon fonctionnement du logiciel, la gestion des droits et la traçabilité.

**À faire dès l’installation** :
- Complétez la partie “Entités” dans l’espace d’administration.
- Ajoutez vos sites/magasins et rattachez-les aux entités.
- Créez les comptes vendeurs nécessaires et attribuez-les aux bons sites.

**Ne négligez pas ces étapes : elles sont indispensables pour la conformité légale et le bon usage du logiciel.**

----------------------------------------------------------
9. AUTRES CONSEILS DE BONNES PRATIQUES
----------------------------------------------------------

- Utilisez toujours des mots de passe forts et changez-les régulièrement.
- Protégez vos formulaires contre les attaques CSRF et validez toutes les entrées utilisateurs.
- Activez le HTTPS sur votre site pour sécuriser les échanges de données.
- Sauvegardez régulièrement la base de données et testez la restauration.
- Limitez les tentatives de connexion pour éviter les attaques par force brute.
- Supprimez les fichiers inutilisés ou de test après la mise en production.
- Surveillez les logs d’accès et d’erreur pour détecter toute activité suspecte.
- Fournissez une politique de confidentialité et des mentions légales à jour.

----------------------------------------------------------
10. ASSISTANCE
----------------------------------------------------------

En cas de problème d’import ou de connexion, consultez la documentation ou contactez le support du projet.

----------------------------------------------------------
