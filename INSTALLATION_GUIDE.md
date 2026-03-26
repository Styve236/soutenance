# 📖 Guide d'Installation - Douala Eats v2.0

## 🎯 Prérequis

- **PHP:** 7.4+ (avec mysqli activé)
- **MySQL:** 5.7+ ou MariaDB
- **Serveur Web:** Apache (avec mod_rewrite) ou Nginx
- **Navigateur:** Chrome, Firefox, Safari, Edge (versions récentes)

## 📋 Checklist d'Installation

### 1. Configuration de la Base de Données

#### Option A: Ligne de commande
```bash
cd c:\xampp\htdocs\Soutenance
mysql -u root -p douala_eats < database.sql
```

#### Option B: PhpMyAdmin
1. Accédez à http://localhost/phpmyadmin
2. Créez une nouvelle base: `douala_eats`
3. Importez le fichier `database.sql` dans cette base

#### Option C: Requêtes manuelles
Copiez-collez le contenu de `database.sql` dans l'onglet SQL de PhpMyAdmin.

### 2. Configuration PHP

Éditez `includes/db.php` si nécessaire:
```php
$host = 'localhost';
$user = 'root';
$password = '';  // Votre mot de passe MySQL
$database = 'douala_eats';
```

### 3. Permissions des Dossiers

```bash
# Windows (dans PowerShell Admin)
icacls "c:\xampp\htdocs\Soutenance\assets\images\plats" /grant:r "%USERDOMAIN%\%USERNAME%":(F) /T
icacls "c:\xampp\htdocs\Soutenance\assets\images\restos" /grant:r "%USERDOMAIN%\%USERNAME%":(F) /T

# Linux/Mac
chmod 755 assets/images/plats
chmod 755 assets/images/restos
chmod 755 uploads
```

### 4. Vérifier l'Installation

Ouvrez votre navigateur et accédez à:
```
http://localhost/Soutenance/check_installation.php
```

Vous devriez voir une page verte avec tous les checks passés.

## 🚀 Démarrage Rapide

### 1. Lancez le serveur XAMPP
```bash
# Windows
"C:\xampp\xampp-control.exe"

# Mac
sudo /Applications/XAMPP/bin/mysql.server start
sudo /Applications/XAMPP/bin/apache_ctl start
```

### 2. Accédez à l'application
```
http://localhost/Soutenance
```

### 3. Créez un compte utilisateur
- Cliquez sur "S'inscrire"
- Remplissez le formulaire
- Connectez-vous avec vos identifiants

### 4. Explorez les fonctionnalités
- ✓ Recherchez un restaurant (autocomplete)
- ✓ Ajoutez des plats au panier
- ✓ Consultez votre panier persistant
- ✓ Laissez des avis sur les restaurants

## 👨‍💼 Panel Admin

### Accès Admin

#### Première fois
1. Cherchez le code d'accès: **"mention"**
2. Allez à l'admin preview (`admin_preview.php`)
3. Entrez le code d'accès
4. Votre rôle passera à "admin"

#### Panneau Admin Disponible
```
http://localhost/Soutenance/admin_dashboard.php    # Statistiques
http://localhost/Soutenance/admin_preview.php      # CRUD Restaurants/Menus
http://localhost/Soutenance/admin_commandes.php    # Gestion Commandes
```

### Tâches Admin Courantes

#### Ajouter un Restaurant
1. Allez à `admin_preview.php`
2. Remplissez le formulaire "Ajouter un Restaurant"
3. Upload un logo (JPG, PNG, max 2MB)
4. Cliquez "Ajouter"

#### Ajouter un Menu
1. Sélectionnez le restaurant
2. Remplissez le formulaire "Ajouter un Menu"
3. Upload l'image du plat
4. Cliquez "Ajouter"

#### Voir les Statistiques
1. Allez à `admin_dashboard.php`
2. Consultez les 6 cartes de statistiques
3. Vérifiez la popularité des restaurants

## 🧪 Tests Manuels

### Recherche Autocomplete
- [ ] Accédez à la page d'accueil (connecté)
- [ ] Tapez "ndole" dans la barre de recherche
- [ ] Vérifiez que les suggestions apparaissent
- [ ] Cliquez sur une suggestion
- [ ] Vérifiez le tri par distance

### Panier Persistant
- [ ] Connectez-vous
- [ ] Ajoutez 3 plats à votre panier
- [ ] Fermez l'onglet/navigateur
- [ ] Rouvrez et reconnectez-vous
- [ ] Vérifiez que les articles restent dans le panier

### Système d'Avis
- [ ] Allez sur la page d'un restaurant
- [ ] Scroll jusqu'à la section "Avis"
- [ ] Laissez une note (1-5 étoiles)
- [ ] Ajoutez un commentaire
- [ ] Cliquez "Envoyer l'avis"
- [ ] Vérifiez que l'avis s'affiche immédiatement

### Notifications
- [ ] Passez une commande
- [ ] Vérifiez l'icône cloche (badge)
- [ ] Attendez 30 secondes (polling)
- [ ] Une notification devrait s'afficher

### Responsive Design
- [ ] Ouvrez l'app sur mobile (F12 → Toggle device)
- [ ] Vérifiez que la nav est en vertical
- [ ] Testez la recherche sur petit écran
- [ ] Vérifiez le layout des cartes restaurants

## 🔧 Troubleshooting

### Erreur de Connexion MySQL
```
Error: Access Denied for user 'root'@'localhost'
```
**Solution:** Vérifiez le mot de passe dans `includes/db.php`

### Erreur d'Upload d'Image
```
Failed to move uploaded file
```
**Solution:** Vérifiez les permissions du dossier `assets/images/`
```bash
chmod 755 assets/images/plats
chmod 755 assets/images/restos
```

### Erreur 404 sur les APIs
```
GET includes/search_api.php returns 404
```
**Solution:** Vérifiez que les fichiers existent:
- `includes/search_api.php`
- `includes/panier_api.php`
- `includes/avis.php`
- `includes/notifications_api.php`

### Recherche ne fonctionne pas
```
autocomplete pas d'affichage
```
**Solution:**
1. Vérifiez la console (F12 > Console)
2. Vérifiez que PHP short_tags est activé dans `php.ini`
3. Testez manuellement: `GET includes/search_api.php?q=test`

### Panier ne se sauvegarde pas
```
Panier vide après refresh
```
**Solution:**
1. Vérifiez que vous êtes connecté (session active)
2. Vérifiez la table `panier` existe: `SHOW TABLES LIKE 'panier%'`
3. Vérifiez que `includes/panier_api.php` fonctionne

### Les avis ne s'affichent pas
```
Aucun avis affiché
```
**Solution:**
1. Vérifiez la table `avis` existe
2. Créez un nouvel avis et attendez le refresh
3. Vérifiez que JavaScript est activé

## 📱 Test sur Mobiles

### iPhone
1. Sur le même réseau WiFi
2. Trouvez l'IP de votre PC: `ipconfig` (Windows)
3. Accédez: `http://[IP]:80/Soutenance`

### Android
Même processus qu'iPhone

### DevTools Émulation
```
F12 > Toggle device toolbar
Ctrl+Shift+M (Windows/Linux)
Cmd+Shift+M (Mac)
```

## 🔐 Sécurité - Post-Installation

### Changer le Code Admin
1. Modifiez `config.php`
2. Changez `define('ADMIN_CODE', 'mention');`
3. Utilisez un code plus fort

### Sauvegarder la Base de Données
```bash
mysqldump -u root -p douala_eats > backup.sql
```

### Activer HTTPS (Production)
- Obtenez un certificat SSL
- Forcez la redirection HTTP → HTTPS
- Mettez à jour `includes/db.php` pour HTTPS

## 📊 Après l'Installation

### Premiers Utilisateurs
```sql
-- Vérifier les utilisateurs créés
SELECT * FROM users ORDER BY id DESC LIMIT 10;

-- Vérifier les restaurants
SELECT * FROM restaurants;

-- Vérifier les commandes
SELECT * FROM commandes ORDER BY id DESC LIMIT 10;
```

### Nettoyer les Données de Test
```sql
-- Supprimer les utilisateurs de test (attention!)
DELETE FROM users WHERE id > 1;

-- Vider les paniers de test
DELETE FROM panier;

-- Vider les notifications de test
DELETE FROM notifications;
```

## 📚 Documentation Complète

- **README.md** - Vue d'ensemble complète
- **API_DOCUMENTATION.js** - Documentation techniques des APIs
- **CHANGELOG.md** - Historique des versions
- **Cette page** - Guide d'installation

## ⚠️ Points Importants

1. **Backup Régulièrement**
   ```bash
   # Chaque semaine
   mysqldump -u root -p douala_eats > backup-$(date +%Y%m%d).sql
   ```

2. **Monitorer les Logs**
   ```
   Windows: C:\xampp\apache\logs\error.log
   Mac/Linux: /var/log/apache2/error.log
   ```

3. **Mettre à Jour PHP**
   - Vérifiez les mises à jour XAMPP régulièrement
   - Testez les mises à jour sur un serveur de développement d'abord

4. **Sauvegarder les Images**
   - Sauvegardez `assets/images/plats/` et `assets/images/restos/`
   - Envisagez un stockage cloud (AWS S3, Digital Ocean Spaces)

## 🎓 Formation Utilisateurs

### Pour les Clients
- Montrez comment rechercher des restaurants
- Explicitez le système d'avis
- Montrez comment tracker une commande
- Expliquez les notifications

### Pour les Admins
- Comment ajouter des restaurants
- Comment gérer les menus
- Comment lire les statistiques
- Comment gérer les commandes

## 📞 Support

En cas de problème:
1. Consultez la section Troubleshooting
2. Vérifiez les logs PHP/MySQL
3. Testez les APIs avec Postman
4. Consultez les fichiers de documentation

---

**Dernière mise à jour:** 2024
**Version:** 2.0
**Support:** [Douala Eats Team]
