# Résultats des Tests - API Crypto Wallet

## ✅ Routes Enregistrées (14 routes)

### Authentification (3 routes)
- ✅ `POST /api/register` - Inscription
- ✅ `POST /api/login` - Connexion (JWT)
- ✅ `GET /api/me` - Informations utilisateur

### Portefeuille - CRUD (5 routes)
- ✅ `GET /api/portfolio` - Liste des actifs
- ✅ `GET /api/portfolio/{id}` - Détails d'un actif
- ✅ `POST /api/portfolio` - Ajouter un actif
- ✅ `PUT/PATCH /api/portfolio/{id}` - Modifier un actif
- ✅ `DELETE /api/portfolio/{id}` - Supprimer un actif

### Statistiques (4 routes)
- ✅ `GET /api/stats/portfolio/value` - Valeur totale
- ✅ `GET /api/stats/portfolio/summary` - Résumé détaillé
- ✅ `GET /api/stats/portfolio/history` - Historique
- ✅ `GET /api/stats/portfolio/distribution` - Distribution

### Page d'accueil (1 route)
- ✅ `GET /` - Page d'accueil

## 🧪 Comment Tester

### Option 1 : Script automatique
```bash
# Assurez-vous que le serveur est démarré
php -S localhost:8000 -t public

# Dans un autre terminal, exécutez :
./test-api.sh
```

### Option 2 : Tests manuels avec curl

#### 1. Inscription
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

#### 2. Connexion
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```
**Réponse attendue :** `{"token":"..."}`

#### 3. Récupérer les informations utilisateur
```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

#### 4. Ajouter un actif
```bash
curl -X POST http://localhost:8000/api/portfolio \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -d '{
    "symbol":"BTC",
    "name":"Bitcoin",
    "quantity":"0.5",
    "purchasePrice":"45000.00",
    "purchaseDate":"2024-01-15 10:30:00"
  }'
```

#### 5. Liste du portefeuille
```bash
curl -X GET http://localhost:8000/api/portfolio \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

#### 6. Statistiques - Valeur totale
```bash
curl -X GET http://localhost:8000/api/stats/portfolio/value \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

## 📋 Checklist de Vérification

Avant de passer au frontend, vérifiez :

- [ ] Docker MySQL est démarré : `docker compose ps`
- [ ] Les migrations sont exécutées : `php bin/console doctrine:migrations:migrate`
- [ ] Les clés JWT existent : `ls config/jwt/` (sinon : `php bin/console lexik:jwt:generate-keypair`)
- [ ] Le serveur Symfony est démarré : `php -S localhost:8000 -t public`
- [ ] Les routes répondent correctement (utilisez `./test-api.sh`)

## 🔧 Configuration Frontend

### URL de l'API
```
http://localhost:8000/api
```

### Headers requis pour les requêtes authentifiées
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Exemple d'intégration JavaScript

```javascript
const API_URL = 'http://localhost:8000/api';

// 1. Inscription
async function register(email, password) {
  const response = await fetch(`${API_URL}/register`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  return response.json();
}

// 2. Connexion
async function login(email, password) {
  const response = await fetch(`${API_URL}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  localStorage.setItem('token', data.token);
  return data;
}

// 3. Récupérer le portefeuille
async function getPortfolio() {
  const token = localStorage.getItem('token');
  const response = await fetch(`${API_URL}/portfolio`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return response.json();
}

// 4. Ajouter un actif
async function addAsset(asset) {
  const token = localStorage.getItem('token');
  const response = await fetch(`${API_URL}/portfolio`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(asset)
  });
  return response.json();
}
```

## ✅ Statut

**Toutes les routes sont créées et enregistrées !**

Le backend est prêt pour le développement du frontend. 🚀
