/**
 * DOUALA EATS - DOCUMENTATION DES APIS IMPLÉMENTÉES
 * 
 * Toutes les APIs utilisent des Prepared Statements pour la sécurité
 * et retournent des réponses JSON
 */

/**
 * ========================================
 * 1. API RECHERCHE (AUTOCOMPLETE)
 * ========================================
 * 
 * URL: includes/search_api.php
 * Méthode: GET
 * 
 * Paramètres:
 *   - q (string, requis): Terme de recherche (min 2 caractères)
 *   - type (string, optionnel): 'restaurants' | 'menus' | 'plats' (défaut: restaurants)
 * 
 * Exemple:
 *   GET includes/search_api.php?q=ndole&type=restaurants
 * 
 * Réponse:
 *   [
 *     {
 *       "id": 5,
 *       "name": "Ndolé Express",
 *       "quartier": "Akwa",
 *       "image_logo": "logo.jpg"
 *     },
 *     ...
 *   ]
 * 
 * Utilisation Frontend:
 *   const query = 'ndole';
 *   fetch(`includes/search_api.php?q=${query}&type=restaurants`)
 *     .then(r => r.json())
 *     .then(data => console.log(data));
 */

/**
 * ========================================
 * 2. API PANIER PERSISTANT
 * ========================================
 * 
 * URL: includes/panier_api.php
 * Méthode: POST
 * Authentification: Requiert $_SESSION['user_id']
 * 
 * AJOUTER UN ARTICLE:
 * ─────────────────────
 *   POST includes/panier_api.php
 *   Paramètres: action=add&menu_id=5&quantity=2
 *   
 *   Réponse:
 *   {
 *     "success": true,
 *     "items": [
 *       {"menu_id": 5, "quantity": 2, "prix": 5000, "subtotal": 10000, ...},
 *       ...
 *     ],
 *     "total": 25000
 *   }
 *   
 *   Note: Si menu_id existe déjà, la quantité est augmentée (ON DUPLICATE KEY)
 * 
 * RÉCUPÉRER LE PANIER:
 * ─────────────────────
 *   POST includes/panier_api.php
 *   Paramètres: action=get
 *   
 *   Réponse:
 *   {
 *     "items": [...],
 *     "total": 25000,
 *     "item_count": 3
 *   }
 * 
 * RETIRER UN ARTICLE:
 * ─────────────────────
 *   POST includes/panier_api.php
 *   Paramètres: action=remove&menu_id=5
 *   
 *   Réponse:
 *   {
 *     "success": true,
 *     "total": 20000
 *   }
 * 
 * VIDER LE PANIER:
 * ─────────────────────
 *   POST includes/panier_api.php
 *   Paramètres: action=clear
 *   
 *   Réponse:
 *   {
 *     "success": true,
 *     "message": "Panier vidé"
 *   }
 * 
 * Utilisation Frontend:
 *   const addToCart = async (menuId, quantity = 1) => {
 *     const response = await fetch('includes/panier_api.php', {
 *       method: 'POST',
 *       headers: {'Content-Type': 'application/x-www-form-urlencoded'},
 *       body: `action=add&menu_id=${menuId}&quantity=${quantity}`
 *     });
 *     return response.json();
 *   };
 */

/**
 * ========================================
 * 3. API AVIS ET NOTATIONS
 * ========================================
 * 
 * URL: includes/avis.php
 * 
 * AJOUTER UN AVIS:
 * ─────────────────────
 *   Méthode: POST
 *   Authentification: Requiert $_SESSION['user_id']
 *   Paramètres:
 *     - restaurant_id (int, requis)
 *     - rating (int, requis): Entre 1 et 5
 *     - comment (string, optionnel): Commentaire libre
 *   
 *   Exemple:
 *   POST includes/avis.php
 *   Data: restaurant_id=5&rating=5&comment=Excellent!
 *   
 *   Réponse:
 *   {
 *     "success": true,
 *     "message": "Avis enregistré avec succès!"
 *   }
 * 
 * RÉCUPÉRER LES AVIS:
 * ─────────────────────
 *   Méthode: GET
 *   Paramètres:
 *     - restaurant_id (int, requis)
 *   
 *   Exemple:
 *   GET includes/avis.php?restaurant_id=5
 *   
 *   Réponse:
 *   [
 *     {
 *       "id": 1,
 *       "user_id": 3,
 *       "user_nom": "John Doe",
 *       "rating": 5,
 *       "comment": "Excellent!",
 *       "created_at": "2024-01-15 10:30:45"
 *     },
 *     ...
 *   ]
 * 
 * Utilisation Frontend:
 *   // Charger les avis
 *   const loadReviews = async (restaurantId) => {
 *     const response = await fetch(`includes/avis.php?restaurant_id=${restaurantId}`);
 *     return response.json();
 *   };
 *   
 *   // Ajouter un avis
 *   const submitReview = async (restaurantId, rating, comment) => {
 *     const formData = new FormData();
 *     formData.append('restaurant_id', restaurantId);
 *     formData.append('rating', rating);
 *     formData.append('comment', comment);
 *     
 *     const response = await fetch('includes/avis.php', {
 *       method: 'POST',
 *       body: formData
 *     });
 *     return response.json();
 *   };
 */

/**
 * ========================================
 * 4. API NOTIFICATIONS
 * ========================================
 * 
 * URL: includes/notifications_api.php
 * Méthode: POST/GET
 * Authentification: Requiert $_SESSION['user_id']
 * 
 * CRÉER UNE NOTIFICATION:
 * ─────────────────────
 *   Méthode: POST
 *   Paramètres:
 *     - action=create
 *     - user_id (int): ID utilisateur destinataire
 *     - type (string): 'order' | 'delivery' | 'review' | 'info'
 *     - message (string): Contenu du message
 *     - data (JSON, optionnel): Données supplémentaires
 *   
 *   Exemple:
 *   POST includes/notifications_api.php?action=create
 *   Data: user_id=5&type=order&message=Votre commande est prête!
 *   
 *   Réponse:
 *   {"success": true}
 * 
 * RÉCUPÉRER LES NOTIFICATIONS NON LUES:
 * ─────────────────────────────────────
 *   Méthode: GET
 *   Paramètres:
 *     - action=unread
 *   
 *   Exemple:
 *   GET includes/notifications_api.php?action=unread
 *   
 *   Réponse:
 *   [
 *     {
 *       "id": 1,
 *       "type": "order",
 *       "message": "Votre commande est prête!",
 *       "created_at": "2024-01-15 10:30:45"
 *     },
 *     ...
 *   ]
 * 
 * MARQUER COMME LUE:
 * ─────────────────────
 *   Méthode: POST
 *   Paramètres:
 *     - action=read
 *     - notification_id (int): ID de la notification
 *   
 *   Réponse:
 *   {"success": true}
 * 
 * RÉCUPÉRER TOUTES LES NOTIFICATIONS:
 * ─────────────────────────────────────
 *   Méthode: GET
 *   Paramètres:
 *     - action=all
 *   
 *   Réponse: Array de toutes les notifications (50 dernières)
 * 
 * Utilisation Frontend (Polling):
 *   // Poller les notifications toutes les 30 secondes
 *   const pollNotifications = () => {
 *     fetch('includes/notifications_api.php?action=unread')
 *       .then(r => r.json())
 *       .then(notifications => {
 *         notifications.forEach(notif => {
 *           showNotificationToast(notif.message, notif.type);
 *         });
 *       });
 *   };
 *   
 *   setInterval(pollNotifications, 30000);
 */

/**
 * ========================================
 * 5. GESTION DES RESTAURANTS (ADMIN)
 * ========================================
 * 
 * Fichier: admin_preview.php
 * 
 * FONCTIONNALITÉS:
 * ─────────────────────────────
 * 
 * 1. AJOUTER UN RESTAURANT:
 *    - Formulaire avec champs:
 *      * nom_resto (text, requis)
 *      * quartier (text, requis)
 *      * description (textarea)
 *      * image_logo (file: jpg, png)
 *      * categorie (select)
 *    - Upload sécurisé avec uniqid()
 *    - Prepared statements
 * 
 * 2. ÉDITER UN RESTAURANT:
 *    - Bouton "Éditer" dans le tableau
 *    - Fonction JavaScript editRestaurant()
 *    - Peuple le formulaire avec les données existantes
 * 
 * 3. SUPPRIMER UN RESTAURANT:
 *    - Lien "Supprimer" avec confirmation
 *    - Cascade suppression des menus associés
 * 
 * 4. GESTION DES MENUS:
 *    - Ajouter/Éditer/Supprimer des plats
 *    - Même pattern que restaurants
 * 
 * Sécurité Implémentée:
 *   ✓ Prepared statements
 *   ✓ Validation type (int, float)
 *   ✓ Vérification user_id en session
 *   ✓ Gestion d'images sécurisée
 *   ✓ Messages d'erreur appropriés
 */

/**
 * ========================================
 * 6. DASHBOARD ADMIN
 * ========================================
 * 
 * Fichier: admin_dashboard.php
 * 
 * STATISTIQUES AFFICHÉES:
 * ─────────────────────────
 * 1. Nombre de restaurants
 * 2. Nombre de menus disponibles
 * 3. Nombre d'utilisateurs
 * 4. Total des commandes
 * 5. Revenu total (SUM des montants)
 * 6. Commandes du jour (WHERE DATE = TODAY)
 * 
 * PANNEAUX D'INFORMATION:
 * ─────────────────────────
 * - Restaurant le plus populaire (JOIN avec commandes)
 * - Dernière commande effectuée
 * - Actions rapides (liens vers autres pages)
 * 
 * Requêtes Optimisées:
 *   - SUM(total) pour revenu
 *   - COUNT(DISTINCT) pour éviter doublons
 *   - LEFT JOIN pour inclure restaurants sans commandes
 *   - DATE(date_commande) = CURDATE() pour commandes jour
 */

/**
 * ========================================
 * RECOMMANDATIONS DE SÉCURITÉ
 * ========================================
 * 
 * ✓ Tous les inputs sont validés
 * ✓ Prepared statements partout
 * ✓ Type casting: (int), (float), (string)
 * ✓ Vérification session avant opérations sensibles
 * ✓ Noms de fichiers: uniqid() + extension
 * ✓ Répertoires images en lecture seule pour utilisateurs
 * ✓ Erreurs logguées, pas affichées aux utilisateurs
 * ✓ CORS prevenu par sessions PHP
 * 
 * À VÉRIFIER:
 *   1. Base de données créée avec database.sql
 *   2. Permissions dossiers assets/images/
 *   3. PHP short_tags désactivé (sinon <?= peut causer problèmes)
 *   4. max_upload_filesize suffisant (2MB+)
 *   5. Extension mysqli installée
 */

/**
 * ========================================
 * EXEMPLE D'IMPLÉMENTATION COMPLÈTE
 * ========================================
 */

// Exemple 1: Ajouter au panier via API
async function addToCartExample() {
    const menuId = 5;
    const quantity = 2;

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('menu_id', menuId);
    formData.append('quantity', quantity);

    try {
        const response = await fetch('includes/panier_api.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Panier:', data);
            console.log('Total:', data.total + ' FCFA');
        } else {
            console.error('Erreur:', data.error);
        }
    } catch (error) {
        console.error('Erreur réseau:', error);
    }
}

// Exemple 2: Chercher avec autocomplete
async function searchRestaurantsExample() {
    const query = 'ndole';
    
    try {
        const response = await fetch(`includes/search_api.php?q=${encodeURIComponent(query)}&type=restaurants`);
        const results = await response.json();
        
        results.forEach(restaurant => {
            console.log(`${restaurant.name} - ${restaurant.quartier}`);
        });
    } catch (error) {
        console.error('Erreur recherche:', error);
    }
}

// Exemple 3: Envoyer un avis
async function submitReviewExample() {
    const restaurantId = 5;
    const rating = 5;
    const comment = 'Excellent restaurant!';

    const formData = new FormData();
    formData.append('restaurant_id', restaurantId);
    formData.append('rating', rating);
    formData.append('comment', comment);

    try {
        const response = await fetch('includes/avis.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Avis enregistré!');
        } else {
            console.error('Erreur:', data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}
