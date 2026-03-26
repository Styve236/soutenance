#!/bin/bash
# Script d'installation et configuration pour Douala Eats

echo "========================================="
echo "Installation Douala Eats v2.0"
echo "========================================="

# Couleurs pour l'affichage
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Vérifier PHP
echo -e "${YELLOW}[1/5] Vérification PHP...${NC}"
if command -v php &> /dev/null; then
    echo -e "${GREEN}✓ PHP trouvé${NC}"
    php -v | head -n 1
else
    echo -e "${RED}✗ PHP non trouvé${NC}"
    exit 1
fi

# 2. Vérifier MySQL
echo ""
echo -e "${YELLOW}[2/5] Vérification MySQL...${NC}"
if command -v mysql &> /dev/null; then
    echo -e "${GREEN}✓ MySQL trouvé${NC}"
else
    echo -e "${YELLOW}⚠ MySQL CLI non trouvé (installer MySQL Server)${NC}"
fi

# 3. Créer les dossiers
echo ""
echo -e "${YELLOW}[3/5] Création des dossiers...${NC}"
mkdir -p assets/images/plats
mkdir -p assets/images/restos
mkdir -p uploads
chmod 755 assets/images/plats
chmod 755 assets/images/restos
chmod 755 uploads
echo -e "${GREEN}✓ Dossiers créés${NC}"

# 4. Vérifier les fichiers
echo ""
echo -e "${YELLOW}[4/5] Vérification des fichiers...${NC}"
files=("includes/db.php" "includes/header.php" "includes/footer.php" "admin_dashboard.php" "admin_preview.php" "database.sql")
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓ $file${NC}"
    else
        echo -e "${RED}✗ $file manquant${NC}"
    fi
done

# 5. Importer la base de données
echo ""
echo -e "${YELLOW}[5/5] Importer la base de données...${NC}"
echo -e "${YELLOW}Entrez le mot de passe MySQL (appuyez sur Entrée si vide):${NC}"
read -s PASSWORD

if [ -z "$PASSWORD" ]; then
    mysql -u root < database.sql 2>/dev/null
else
    mysql -u root -p"$PASSWORD" < database.sql 2>/dev/null
fi

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Base de données importée${NC}"
else
    echo -e "${RED}✗ Erreur lors de l'import${NC}"
    echo "Vous pouvez importer manuellement: mysql -u root -p douala_eats < database.sql"
fi

echo ""
echo "========================================="
echo -e "${GREEN}Installation terminée!${NC}"
echo "========================================="
echo ""
echo "Prochaines étapes:"
echo "1. Configurer includes/db.php avec vos paramètres MySQL"
echo "2. Visiter: http://localhost/Soutenance"
echo "3. Consulter README.md pour plus d'informations"
echo ""
