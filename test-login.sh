#!/bin/bash

echo "🔐 Test de Connexion - Crypto Wallet API"
echo "=========================================="
echo ""

API_URL="http://localhost:8000/api"
EMAIL="test@example.com"
PASSWORD="test123"

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Vérifier que le serveur est démarré
echo "1️⃣  Vérification du serveur..."
if ! curl -s http://localhost:8000/ > /dev/null 2>&1; then
    echo -e "${RED}❌ Le serveur n'est pas démarré${NC}"
    echo "Démarrez-le avec: php -S localhost:8000 -t public"
    exit 1
fi
echo -e "${GREEN}✅ Serveur démarré${NC}"
echo ""

# Vérifier les clés JWT
echo "2️⃣  Vérification des clés JWT..."
if [ ! -f "config/jwt/private.pem" ] || [ ! -f "config/jwt/public.pem" ]; then
    echo -e "${YELLOW}⚠️  Les clés JWT n'existent pas${NC}"
    echo "Génération des clés..."
    php bin/console lexik:jwt:generate-keypair
else
    echo -e "${GREEN}✅ Clés JWT présentes${NC}"
fi
echo ""

# Test d'inscription
echo "3️⃣  Test d'inscription..."
REGISTER_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$API_URL/register" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

HTTP_CODE=$(echo "$REGISTER_RESPONSE" | tail -n1)
BODY=$(echo "$REGISTER_RESPONSE" | sed '$d')

if [ "$HTTP_CODE" -eq 201 ]; then
    echo -e "${GREEN}✅ Inscription réussie${NC}"
    echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
elif [ "$HTTP_CODE" -eq 409 ]; then
    echo -e "${YELLOW}⚠️  Utilisateur déjà existant (c'est OK)${NC}"
else
    echo -e "${RED}❌ Erreur lors de l'inscription (HTTP $HTTP_CODE)${NC}"
    echo "$BODY"
fi
echo ""

# Attendre un peu
sleep 1

# Test de connexion
echo "4️⃣  Test de connexion..."
LOGIN_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

HTTP_CODE=$(echo "$LOGIN_RESPONSE" | tail -n1)
BODY=$(echo "$LOGIN_RESPONSE" | sed '$d')

if [ "$HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}✅ Connexion réussie !${NC}"
    echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY"
    
    # Extraire le token
    TOKEN=$(echo "$BODY" | python3 -c "import sys, json; print(json.load(sys.stdin).get('token', ''))" 2>/dev/null)
    
    if [ -n "$TOKEN" ]; then
        echo ""
        echo "5️⃣  Test avec le token..."
        ME_RESPONSE=$(curl -s -w "\n%{http_code}" -X GET "$API_URL/me" \
          -H "Authorization: Bearer $TOKEN")
        
        ME_HTTP_CODE=$(echo "$ME_RESPONSE" | tail -n1)
        ME_BODY=$(echo "$ME_RESPONSE" | sed '$d')
        
        if [ "$ME_HTTP_CODE" -eq 200 ]; then
            echo -e "${GREEN}✅ Token valide !${NC}"
            echo "$ME_BODY" | python3 -m json.tool 2>/dev/null || echo "$ME_BODY"
        else
            echo -e "${RED}❌ Erreur avec le token (HTTP $ME_HTTP_CODE)${NC}"
            echo "$ME_BODY"
        fi
    fi
else
    echo -e "${RED}❌ Échec de la connexion (HTTP $HTTP_CODE)${NC}"
    echo "Réponse: $BODY"
    echo ""
    echo "🔍 Diagnostic :"
    echo "- Vérifiez que l'email et le mot de passe sont corrects"
    echo "- Assurez-vous d'avoir créé un compte avec /api/register"
    echo "- Vérifiez les logs du serveur PHP"
    echo ""
    echo "Consultez GUIDE_CONNEXION.md pour plus d'aide"
fi

echo ""
echo "=========================================="
echo "Test terminé"
