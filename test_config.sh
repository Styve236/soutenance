#!/bin/bash
# ============================================
# DOUALA EATS v2.0 - Test Configuration
# ============================================
# Script pour tester les 10 améliorations

echo "🍔 Douala Eats v2.0 - Test Configuration"
echo "=========================================="
echo ""

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
HOST="http://localhost/Soutenance"
MYSQL_USER="root"
MYSQL_PASS=""
MYSQL_DB="douala_eats"

echo -e "${BLUE}Configuration:${NC}"
echo "  Host: $HOST"
echo "  DB: $MYSQL_DB"
echo ""

# 1. Test Fichiers
echo -e "${YELLOW}[1/5] Vérification des fichiers...${NC}"
files=(
    "admin_dashboard.php"
    "includes/avis.php"
    "includes/panier_api.php"
    "includes/notifications_api.php"
    "includes/search_api.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "  ${GREEN}✓${NC} $file"
    else
        echo -e "  ${RED}✗${NC} $file - MANQUANT"
    fi
done

# 2. Test Base de Données
echo ""
echo -e "${YELLOW}[2/5] Vérification Base de Données...${NC}"

# Test connection
if mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "USE $MYSQL_DB;" 2>/dev/null; then
    echo -e "  ${GREEN}✓${NC} Connexion MySQL OK"
    
    # Vérifier les tables
    tables=("avis" "panier" "notifications")
    for table in "${tables[@]}"; do
        result=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -D "$MYSQL_DB" -e "SHOW TABLES LIKE '$table';" 2>/dev/null | grep "$table")
        if [ ! -z "$result" ]; then
            echo -e "  ${GREEN}✓${NC} Table $table existe"
        else
            echo -e "  ${RED}✗${NC} Table $table MANQUANTE"
        fi
    done
else
    echo -e "  ${RED}✗${NC} Erreur connexion MySQL"
fi

# 3. Test URLs
echo ""
echo -e "${YELLOW}[3/5] Vérification des URLs...${NC}"

urls=(
    "$HOST/admin_dashboard.php"
    "$HOST/includes/search_api.php?q=test"
    "$HOST/includes/avis.php?restaurant_id=1"
)

for url in "${urls[@]}"; do
    status=$(curl -s -o /dev/null -w "%{http_code}" "$url" 2>/dev/null)
    if [ "$status" = "200" ] || [ "$status" = "302" ]; then
        echo -e "  ${GREEN}✓${NC} $url (HTTP $status)"
    else
        echo -e "  ${YELLOW}⚠${NC} $url (HTTP $status)"
    fi
done

# 4. Test Sécurité
echo ""
echo -e "${YELLOW}[4/5] Vérification Sécurité...${NC}"

# Vérifier prepared statements dans admin_preview.php
if grep -q "bind_param" admin_preview.php; then
    echo -e "  ${GREEN}✓${NC} Prepared statements trouvées"
else
    echo -e "  ${RED}✗${NC} Prepared statements manquantes"
fi

# Vérifier htmlspecialchars
if grep -q "htmlspecialchars" includes/header.php; then
    echo -e "  ${GREEN}✓${NC} Protection XSS trouvée"
else
    echo -e "  ${YELLOW}⚠${NC} Vérifiez la protection XSS"
fi

# 5. Recommandations
echo ""
echo -e "${YELLOW}[5/5] Recommandations...${NC}"
echo ""
echo "Étapes suivantes:"
echo "  1. Configurer includes/db.php si nécessaire"
echo "  2. Importer database.sql: mysql -u root -p $MYSQL_DB < database.sql"
echo "  3. Visiter: $HOST"
echo "  4. Créer un compte et tester les fonctionnalités"
echo ""
echo "Test des APIs avec curl:"
echo ""
echo "  Search API:"
echo "    curl \"$HOST/includes/search_api.php?q=ndole&type=restaurants\""
echo ""
echo "  Cart API:"
echo "    curl -X POST \"$HOST/includes/panier_api.php\" \\"
echo "         -d 'action=get'"
echo ""
echo "  Reviews API:"
echo "    curl \"$HOST/includes/avis.php?restaurant_id=1\""
echo ""
echo "Documentation:"
echo "  - README.md - Vue d'ensemble"
echo "  - API_DOCUMENTATION.js - Specs API"
echo "  - INSTALLATION_GUIDE.md - Guide install"
echo "  - CHANGELOG.md - Historique"
echo ""
echo -e "${GREEN}Configuration terminée!${NC}"
