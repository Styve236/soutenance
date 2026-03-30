// Attendre que le document soit bien chargé
document.addEventListener('DOMContentLoaded', () => {

    // 1. Animation lors de l'ajout au panier
    const addButtons = document.querySelectorAll('button[name="add_to_cart"]');
    
    addButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            // Petite animation de bouton
            button.style.transform = "scale(0.95)";
            button.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
            button.style.background = "#27ae60";

            // On remet le bouton à l'état initial après 1.5 seconde
            setTimeout(() => {
                button.style.transform = "scale(1)";
                button.innerHTML = '<i class="fas fa-plus"></i> Ajouter';
                button.style.background = ""; // Reprend la couleur du CSS
            }, 1500);
        });
    });

    // 2. Confirmation avant de supprimer un article du panier
    const removeLinks = document.querySelectorAll('a[href*="remove"]');
    removeLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            if(!confirm("Voulez-vous retirer ce plat du panier ?")) {
                e.preventDefault();
            }
        });
    });

    // 3. Effet de défilement (Scroll) pour le Header
    const header = document.querySelector('.main-header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.style.padding = "10px 0";
            header.style.boxShadow = "0 4px 12px rgba(0,0,0,0.15)";
        } else {
            header.style.padding = "15px 0";
            header.style.boxShadow = "0 2px 10px rgba(0,0,0,0.1)";
        }
    });

    // 4. Gestion de la case Aperçu Admin
    const adminPreviewToggle = document.getElementById('admin-preview-toggle');
    if (adminPreviewToggle) {
        adminPreviewToggle.addEventListener('change', (e) => {
            if (e.target.checked) {
                window.open('admin_preview.php', '_blank');
                // Décoche automatiquement après ouverture
                setTimeout(() => {
                    e.target.checked = false;
                }, 100);
            }
        });
    }

    // 5. Gestion du formulaire Restaurant
    window.toggleRestaurantForm = function() {
        const form = document.getElementById('restaurant-form');
        const formTitle = document.getElementById('shop-form-title');
        const addBtn = document.getElementById('add-shop-btn');
        const editBtn = document.getElementById('edit-shop-btn');

        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            formTitle.innerHTML = '<i class="fas fa-plus"></i> Ajouter une Boutique';
            addBtn.style.display = 'inline-block';
            editBtn.style.display = 'none';
            // Reset form
            document.getElementById('restaurant-id').value = '';
            document.getElementById('nom_resto').value = '';
            document.getElementById('quartier').value = '';
            document.getElementById('description').value = '';
        } else {
            form.style.display = 'none';
        }
    }

    window.editRestaurant = function(id, nom, quartier, description) {
        const form = document.getElementById('restaurant-form');
        const formTitle = document.getElementById('shop-form-title');
        const addBtn = document.getElementById('add-shop-btn');
        const editBtn = document.getElementById('edit-shop-btn');

        form.style.display = 'block';
        formTitle.innerHTML = '<i class="fas fa-edit"></i> Modifier la Boutique';
        addBtn.style.display = 'none';
        editBtn.style.display = 'inline-block';

        document.getElementById('restaurant-id').value = id;
        document.getElementById('nom_resto').value = nom;
        document.getElementById('quartier').value = quartier;
        document.getElementById('description').value = description;
    }

    // 6. Gestion du formulaire Menu
    window.toggleMenuForm = function() {
        const form = document.getElementById('menu-form');
        const formTitle = document.getElementById('menu-form-title');
        const addBtn = document.getElementById('add-menu-btn');
        const editBtn = document.getElementById('edit-menu-btn');

        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            formTitle.innerHTML = '<i class="fas fa-plus"></i> Ajouter un Miel';
            addBtn.style.display = 'inline-block';
            editBtn.style.display = 'none';
            // Reset form
            document.getElementById('menu-id').value = '';
            document.getElementById('nom_plat').value = '';
            document.getElementById('restaurant_id').value = '';
            document.getElementById('prix').value = '';
            document.getElementById('categorie').value = '';
            document.getElementById('description_plat').value = '';
            document.getElementById('image_plat').value = '';
        } else {
            form.style.display = 'none';
        }
    }

    window.editMenu = function(id, nom_plat, restaurant_id, prix, categorie, description_plat) {
        const form = document.getElementById('menu-form');
        const formTitle = document.getElementById('menu-form-title');
        const addBtn = document.getElementById('add-menu-btn');
        const editBtn = document.getElementById('edit-menu-btn');

        form.style.display = 'block';
        formTitle.innerHTML = '<i class="fas fa-edit"></i> Modifier le Miel';
        addBtn.style.display = 'none';
        editBtn.style.display = 'inline-block';

        document.getElementById('menu-id').value = id;
        document.getElementById('nom_plat').value = nom_plat;
        document.getElementById('restaurant_id').value = restaurant_id;
        document.getElementById('prix').value = prix;
        document.getElementById('categorie').value = categorie;
        document.getElementById('description_plat').value = description_plat;
    }

    // 7. Système de recherche avec autocomplete
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                if (searchResults) searchResults.innerHTML = '';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                fetch(`includes/search_api.php?q=${encodeURIComponent(query)}&type=restaurants`)
                    .then(response => response.json())
                    .then(data => {
                        if (searchResults) {
                            if (data.length === 0) {
                                searchResults.innerHTML = '<div class="search-item">Aucun résultat trouvé</div>';
                            } else {
                                searchResults.innerHTML = data.map(item => `
                                    <div class="search-item" onclick="selectSearchResult(${item.id}, '${item.name}')">
                                        <i class="fas fa-utensils"></i>
                                        <span>
                                            <strong>${item.name}</strong>
                                            <small>${item.quartier || item.restaurant || ''}</small>
                                        </span>
                                    </div>
                                `).join('');
                            }
                            searchResults.style.display = 'block';
                        }
                    })
                    .catch(error => console.error('Erreur de recherche:', error));
            }, 300);
        });
        
        // Masquer les résultats quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (e.target !== searchInput && searchResults) {
                searchResults.style.display = 'none';
            }
        });
    }
    
    window.selectSearchResult = function(id, name) {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.value = name;
        }
        const searchResults = document.getElementById('search-results');
        if (searchResults) {
            searchResults.style.display = 'none';
        }
    }

    // 8. Système de notifications
    window.loadNotifications = function() {
        const notificationIcon = document.getElementById('notification-icon');
        if (!notificationIcon) return;
        
        fetch('includes/notifications_api.php?action=unread')
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    notificationIcon.innerHTML = `
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">${data.length}</span>
                    `;
                    
                    // Afficher les notifications
                    data.forEach(notif => {
                        showNotification(notif.message, notif.type);
                    });
                }
            })
            .catch(error => console.error('Erreur notifications:', error));
    }
    
    window.showNotification = function(message, type = 'info') {
        const notifContainer = document.createElement('div');
        notifContainer.className = `notification notification-${type}`;
        notifContainer.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'order' ? 'box' : type === 'delivery' ? 'truck' : 'star'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="notification-close">×</button>
            </div>
        `;
        
        document.body.appendChild(notifContainer);
        
        // Auto remove après 5 secondes
        setTimeout(() => {
            notifContainer.remove();
        }, 5000);
    }
    
    // Charger les notifications toutes les 30 secondes
    if (document.getElementById('notification-icon')) {
        loadNotifications();
        setInterval(loadNotifications, 30000);
    }

});
