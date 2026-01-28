#!/bin/bash

echo "🔧 Correction de la configuration MySQL"
echo ""

# Arrêter et supprimer les conteneurs
echo "🛑 Arrêt des conteneurs..."
docker compose down

# Supprimer le volume de données (ATTENTION : cela supprime toutes les données)
echo "🗑️  Suppression de l'ancien volume..."
docker volume rm projet_crypto_database_data 2>/dev/null || echo "Volume déjà supprimé ou inexistant"

# Recréer les conteneurs avec la bonne configuration
echo "🚀 Recréation des conteneurs..."
docker compose up -d

# Attendre que MySQL soit prêt
echo "⏳ Attente que MySQL soit initialisé..."
sleep 15

# Vérifier l'état
echo ""
echo "📊 État des conteneurs :"
docker compose ps

echo ""
echo "✅ Conteneurs recréés. Vous pouvez maintenant exécuter :"
echo "   php bin/console doctrine:migrations:migrate"
