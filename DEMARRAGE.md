# 🚀 Guide de Démarrage - Backend Crypto Wallet

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- ✅ **PHP 8.2+** (vérifier avec `php -v`)
- ✅ **Composer** (vérifier avec `composer --version`)
- ✅ **Docker Desktop** (vérifier avec `docker --version`)
- ✅ **Docker Compose** (vérifier avec `docker compose version`)

## 🎯 Vue d'ensemble

Ce projet est un **backend Symfony 7.4** qui fournit une API REST pour la gestion de portefeuille crypto.

**Stack technique :**
- Backend : Symfony 7.4
- Base de données : MySQL 8.0
- Authentification : JWT (Lexik JWT Authentication Bundle)
- API : REST avec JSON

## 🔧 Configuration CORS

Le backend est configuré pour accepter les requêtes depuis **n'importe quelle origine** en développement. 

**Fichier de configuration :** `src/EventSubscriber/CorsSubscriber.php`

Pour la production, modifiez ce fichier pour spécifier l'URL de votre frontend :
```php
$response->headers->set('Access-Control-Allow-Origin', 'https://votre-frontend.com');
```

## 🚀 Démarrage du Backend

### Méthode 1 : Script automatique (Recommandé)

```bash
./start.sh
```

Ce script fait automatiquement :
1. ✅ Vérifie que Docker est démarré
2. ✅ Démarre les conteneurs Docker
3. ✅ Attend que MySQL soit prêt
4. ✅ Exécute les migrations
5. ✅ Affiche les instructions pour démarrer le serveur

### Méthode 2 : Démarrage manuel

#### Étape 1 : Installer les dépendances PHP

```bash
composer install
```

#### Étape 2 : Démarrer Docker Compose

```bash
docker compose up -d
```

Cela démarre :
- **MySQL** sur le port **3306** (base de données : `crypto_wallet`)
- **Mailpit** pour les emails (ports 1025 et 8025)

**Vérification :**
```bash
docker compose ps
```

Vous devriez voir les conteneurs `database` et `mailer` en cours d'exécution avec le statut `healthy`.

#### Étape 3 : Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

**Si vous obtenez une erreur de connexion :**
- Attendez 15-20 secondes que MySQL soit complètement initialisé
- Vérifiez les logs : `docker compose logs database`
- Vérifiez que le conteneur est démarré : `docker compose ps`

#### Étape 4 : Générer les clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

**Note :** Cette commande crée les fichiers `config/jwt/private.pem` et `config/jwt/public.pem` nécessaires pour l'authentification JWT.

#### Étape 5 : Lancer le serveur Symfony

**Option 1 : Avec PHP intégré (Recommandé)**
```bash
php -S localhost:8000 -t public
```

**Option 2 : Avec Symfony CLI**
```bash
symfony server:start
```

**Option 3 : En arrière-plan**
```bash
php -S localhost:8000 -t public > /dev/null 2>&1 &
```

#### Étape 6 : Vérifier que tout fonctionne

Ouvrez dans votre navigateur : **http://localhost:8000/**

Vous devriez voir :
- ✅ La page d'accueil avec toutes les routes listées
- ✅ Un lien vers la liste des routes en JSON
- ✅ Des instructions pour tester les routes

**Test rapide :**
```bash
curl http://localhost:8000/api/routes
```

Vous devriez recevoir un JSON avec toutes les routes disponibles.

## 🌐 Configuration pour le Frontend

### URL de l'API

**Base URL :** `http://localhost:8000/api`

**Page d'accueil :** `http://localhost:8000/` (liste toutes les routes)

**Routes JSON :** `http://localhost:8000/api/routes` (liste au format JSON)

### Headers requis

Pour les requêtes authentifiées, ajoutez le header :
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Exemple de requête depuis le frontend

```javascript
const API_URL = 'http://localhost:8000/api';

// 1. Inscription
fetch(`${API_URL}/register`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
})
.then(response => response.json())
.then(data => console.log('Inscription:', data));

// 2. Connexion (obtenir le token)
fetch(`${API_URL}/login`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
})
.then(response => response.json())
.then(data => {
  const token = data.token;
  localStorage.setItem('token', token); // Stocker le token
  
  // 3. Utiliser le token pour les requêtes suivantes
  fetch(`${API_URL}/portfolio`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => console.log('Portefeuille:', data));
});
```

### Documentation complète pour le frontend

Consultez **`README_FRONTEND.md`** pour :
- ✅ Documentation complète de toutes les routes
- ✅ Exemples d'intégration React/Vue/Angular
- ✅ Gestion des erreurs
- ✅ Service API réutilisable

## 🔌 Ports utilisés

| Port | Service | Description |
|------|---------|-------------|
| **8000** | Backend Symfony | API REST principale |
| **3306** | MySQL | Base de données `crypto_wallet` |
| **8025** | Mailpit | Interface web pour voir les emails |
| **1025** | Mailpit | Serveur SMTP pour les emails |

**Accès :**
- Backend : http://localhost:8000
- Mailpit (emails) : http://localhost:8025
- MySQL : `mysql://symfony:symfony123@127.0.0.1:3306/crypto_wallet`

## 🧪 Tester le Backend

### Test rapide

1. **Page d'accueil** : http://localhost:8000/
   - Liste toutes les routes disponibles
   - Instructions de test

2. **Routes JSON** : http://localhost:8000/api/routes
   - Liste toutes les routes au format JSON

3. **Script de test automatique** :
   ```bash
   ./test-api.sh
   ```

4. **Test manuel avec curl** :
   ```bash
   # Voir les routes
   curl http://localhost:8000/api/routes
   
   # Tester l'inscription
   curl -X POST http://localhost:8000/api/register \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"test123"}'
   ```

### Collection Postman

Importez le fichier `Crypto_Wallet_API.postman_collection.json` dans Postman pour tester toutes les routes avec une interface graphique.

## 🆘 Résolution de Problèmes

### Erreur de connexion à MySQL

**Symptômes :**
```
SQLSTATE[HY000] [2002] Connection refused
ou
SQLSTATE[08006] [7] connection to server failed
```

**Solution rapide :**
```bash
./fix-database.sh
```

**Solution manuelle :**
1. Arrêter les conteneurs : `docker compose down`
2. Supprimer le volume : `docker volume rm projet_crypto_database_data`
3. Recréer les conteneurs : `docker compose up -d`
4. Attendre 15-20 secondes que MySQL soit initialisé
5. Exécuter les migrations : `php bin/console doctrine:migrations:migrate`

**Vérification :**
```bash
# Vérifier que Docker est démarré
docker compose ps

# Vérifier les logs
docker compose logs database

# Tester la connexion MySQL
docker compose exec database mysql -u symfony -psymfony123 crypto_wallet
```

### Erreur CORS depuis le frontend

**Symptômes :**
```
Access to fetch at 'http://localhost:8000/api/...' from origin '...' has been blocked by CORS policy
```

**Solutions :**
1. ✅ Vérifiez que le serveur Symfony est bien démarré
2. ✅ Vérifiez que vous utilisez bien l'URL `http://localhost:8000/api`
3. ✅ Vérifiez les headers dans la réponse (DevTools > Network > Headers)
4. ✅ Le CORS est automatiquement géré par `CorsSubscriber`

**Note :** Le backend accepte toutes les origines en développement. Si vous avez toujours des problèmes, vérifiez que le serveur est bien démarré.

### Erreur "Token JWT invalide"

**Symptômes :**
```
401 Unauthorized
Invalid JWT Token
```

**Solutions :**
1. Vérifiez que les clés JWT existent : `ls config/jwt/`
2. Si elles n'existent pas, générez-les : `php bin/console lexik:jwt:generate-keypair`
3. Vérifiez que le token est bien envoyé dans le header : `Authorization: Bearer {token}`
4. Le token peut avoir expiré, reconnectez-vous

### Erreur "Route non trouvée" (404)

**Solutions :**
1. Vérifiez que le serveur est démarré : `php -S localhost:8000 -t public`
2. Vérifiez l'URL : doit commencer par `http://localhost:8000/api/`
3. Consultez la liste des routes : http://localhost:8000/api/routes

### Erreur de migration

**Symptômes :**
```
Migration failed
Table already exists
```

**Solutions :**
1. Vérifiez l'état des migrations : `php bin/console doctrine:migrations:status`
2. Si nécessaire, réinitialisez : `php bin/console doctrine:migrations:migrate --no-interaction`
3. Vérifiez la connexion à la base de données

## 📚 Documentation Complémentaire

- **`README_FRONTEND.md`** : Guide complet pour l'équipe frontend
- **`API_ROUTES.md`** : Documentation détaillée de toutes les routes
- **`TEST_MANUEL.md`** : Guide de test manuel avec exemples
- **`TROUBLESHOOTING.md`** : Résolution détaillée des problèmes courants

## ✅ Checklist de Vérification

Avant de passer au développement frontend :

- [ ] Docker est démarré (`docker compose ps`)
- [ ] MySQL est démarré et healthy
- [ ] Les migrations sont exécutées (`php bin/console doctrine:migrations:migrate`)
- [ ] Les clés JWT existent (`ls config/jwt/`)
- [ ] Le serveur Symfony est démarré (`php -S localhost:8000 -t public`)
- [ ] La page d'accueil s'affiche (http://localhost:8000/)
- [ ] Les routes sont listées (http://localhost:8000/api/routes)
- [ ] Au moins une route a été testée avec succès

## 🎉 Prêt !

Une fois toutes les étapes complétées, le backend est prêt à recevoir les requêtes du frontend !

**Prochaines étapes :**
1. Partagez `README_FRONTEND.md` avec l'équipe frontend
2. Testez la connexion depuis le frontend
3. Développez les fonctionnalités ! 🚀
