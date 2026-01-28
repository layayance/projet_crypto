#!/bin/bash

echo "🚀 Démarrage du projet Crypto Wallet Backend"
echo ""

# Vérifier si Docker est en cours d'exécution
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker n'est pas en cours d'exécution. Veuillez démarrer Docker Desktop."
    exit 1
fi

echo "✅ Docker est en cours d'exécution"
echo ""

# Démarrer les conteneurs Docker
echo "📦 Démarrage des conteneurs Docker..."
docker compose up -d

# Attendre que MySQL soit prêt
echo "⏳ Attente que MySQL soit prêt..."
sleep 15

# Vérifier l'état des conteneurs
echo ""
echo "📊 État des conteneurs :"
docker compose ps

# Vérifier que MySQL est healthy
echo ""
echo "🔍 Vérification de la santé de MySQL..."
if docker compose ps database | grep -q "healthy"; then
    echo "✅ MySQL est prêt"
else
    echo "⚠️  MySQL n'est pas encore prêt, attente supplémentaire..."
    sleep 5
fi

echo ""
echo "🔄 Exécution des migrations..."
if php bin/console doctrine:migrations:migrate --no-interaction; then
    echo "✅ Migrations exécutées avec succès"
else
    echo ""
    echo "❌ Erreur lors de l'exécution des migrations"
    echo ""
    echo "Si vous obtenez l'erreur 'role app does not exist', exécutez :"
    echo "  ./fix-database.sh"
    echo ""
    echo "Consultez TROUBLESHOOTING.md pour plus d'informations"
    exit 1
fi

echo ""
echo "✅ Backend prêt !"
echo ""
echo "🌐 Le backend est accessible sur : http://localhost:8000"
echo "📧 Mailpit (emails) : http://localhost:8025"
echo ""
echo "Pour démarrer le serveur Symfony :"
echo "  Option 1: symfony server:start"
echo "  Option 2: php -S localhost:8000 -t public"
echo ""
