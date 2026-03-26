-- Script SQL pour créer les tables manquantes pour Douala Eats

-- Table des avis/notations
CREATE TABLE IF NOT EXISTS avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX(restaurant_id),
    INDEX(user_id)
);

-- Table du panier persistant
CREATE TABLE IF NOT EXISTS panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart (user_id, menu_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    INDEX(user_id)
);

-- Table des notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50),  -- 'order', 'delivery', 'review', 'info'
    message TEXT,
    data JSON,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX(user_id),
    INDEX(is_read),
    INDEX(created_at)
);

-- Ajouter colonne image_logo à restaurants s'il n'existe pas
ALTER TABLE restaurants ADD COLUMN image_logo VARCHAR(255) DEFAULT NULL;

-- Ajouter colonne categorie à restaurants s'il n'existe pas
ALTER TABLE restaurants ADD COLUMN categorie VARCHAR(100) DEFAULT 'Général';
