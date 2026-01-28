# 🚀 Guide Frontend - API Crypto Wallet

## 📋 Informations Générales

**Backend Symfony 7.4** - API REST pour la gestion de portefeuille crypto

- ✅ **14 routes API** créées et fonctionnelles
- ✅ **Authentification JWT** configurée
- ✅ **CORS** configuré pour accepter toutes les origines en développement
- ✅ **Base de données MySQL** (`crypto_wallet`)
- ✅ **Toutes les opérations CRUD** fonctionnelles
- ✅ **Statistiques et visualisations** disponibles

## 🌐 URL de l'API

```
Base URL: http://localhost:8000/api
```

**Page d'accueil avec documentation :** http://localhost:8000/

**Liste des routes en JSON :** http://localhost:8000/api/routes

## ⚙️ Prérequis

Avant de commencer, assurez-vous que :

1. ✅ Le backend est démarré (`php -S localhost:8000 -t public`)
2. ✅ MySQL est démarré (`docker compose ps`)
3. ✅ Les migrations sont exécutées (`php bin/console doctrine:migrations:migrate`)
4. ✅ Les clés JWT existent (`ls config/jwt/`)

**Note :** Consultez `DEMARRAGE.md` pour les instructions complètes de démarrage.

## 🔐 Authentification

L'authentification utilise **JWT (JSON Web Tokens)**. Vous devez d'abord vous inscrire, puis vous connecter pour obtenir un token qui sera utilisé pour toutes les requêtes suivantes.

### 1. Inscription
```http
POST /api/register
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Réponse (201) :**
```json
{
  "message": "Utilisateur créé avec succès",
  "user": {
    "id": 1,
    "email": "user@example.com"
  }
}
```

### 2. Connexion
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Réponse (200) :**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**⚠️ Important :** 
- Stockez ce token (dans `localStorage`, `sessionStorage`, ou un state management)
- Le token doit être envoyé dans le header `Authorization: Bearer {token}` pour toutes les requêtes protégées
- Le token expire après un certain temps (vous devrez vous reconnecter)

**❌ Erreur 401 lors de la connexion ?**
- Vérifiez que l'email et le mot de passe sont corrects
- Assurez-vous d'avoir créé un compte avec `/api/register` avant de vous connecter
- Vérifiez que les clés JWT existent : `ls config/jwt/` (sinon : `php bin/console lexik:jwt:generate-keypair`)

### 3. Informations utilisateur
```http
GET /api/me
Authorization: Bearer {token}
```

**Réponse (200) :**
```json
{
  "id": 1,
  "email": "user@example.com",
  "roles": ["ROLE_USER"]
}
```

## 💼 Gestion du Portefeuille

### Liste des actifs
```http
GET /api/portfolio
Authorization: Bearer {token}
```

**Réponse (200) :**
```json
{
  "assets": [
    {
      "id": 1,
      "symbol": "BTC",
      "name": "Bitcoin",
      "quantity": "0.5",
      "purchasePrice": "45000.00",
      "purchaseDate": "2024-01-15 10:30:00",
      "createdAt": "2024-01-15 10:30:00",
      "updatedAt": "2024-01-15 10:30:00"
    }
  ],
  "count": 1
}
```

### Détails d'un actif
```http
GET /api/portfolio/{id}
Authorization: Bearer {token}
```

### Ajouter un actif
```http
POST /api/portfolio
Authorization: Bearer {token}
Content-Type: application/json

{
  "symbol": "BTC",
  "name": "Bitcoin",
  "quantity": "0.5",
  "purchasePrice": "45000.00",
  "purchaseDate": "2024-01-15 10:30:00"
}
```

**Note :** `purchaseDate` est optionnel. Si non fourni, la date actuelle sera utilisée.

### Modifier un actif
```http
PUT /api/portfolio/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": "0.75",
  "purchasePrice": "46000.00"
}
```

Tous les champs sont optionnels. Seuls les champs fournis seront mis à jour.

### Supprimer un actif
```http
DELETE /api/portfolio/{id}
Authorization: Bearer {token}
```

## 📊 Statistiques

### Valeur totale du portefeuille
```http
GET /api/stats/portfolio/value
Authorization: Bearer {token}
```

**Réponse (200) :**
```json
{
  "totalValue": "22500.00",
  "totalInvested": "22500.00",
  "profitLoss": "0.00",
  "profitLossPercentage": "0.00",
  "currency": "USD"
}
```

### Résumé détaillé
```http
GET /api/stats/portfolio/summary
Authorization: Bearer {token}
```

**Réponse (200) :**
```json
{
  "summary": [
    {
      "symbol": "BTC",
      "name": "Bitcoin",
      "totalQuantity": 0.5,
      "totalInvested": 22500.00,
      "currentValue": 22500.00,
      "profitLoss": 0.00,
      "profitLossPercentage": 0.00,
      "portfolioPercentage": 100.00,
      "count": 1
    }
  ],
  "totalAssets": 1,
  "uniqueCryptos": 1,
  "totalValue": "22500.00",
  "totalInvested": "22500.00",
  "totalProfitLoss": "0.00",
  "totalProfitLossPercentage": "0.00"
}
```

### Historique
```http
GET /api/stats/portfolio/history
Authorization: Bearer {token}
```

### Distribution
```http
GET /api/stats/portfolio/distribution
Authorization: Bearer {token}
```

**Réponse (200) :**
```json
{
  "distribution": [
    {
      "symbol": "BTC",
      "name": "Bitcoin",
      "value": "22500.00",
      "percentage": "100.00"
    }
  ],
  "totalValue": "22500.00"
}
```

## 🔧 Configuration CORS

Le backend est configuré pour accepter les requêtes depuis **n'importe quelle origine** en développement. Aucune configuration CORS supplémentaire n'est nécessaire côté frontend.

**Headers CORS automatiquement ajoutés :**
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With`

## 💻 Exemple d'Intégration Frontend

### Service API (JavaScript/TypeScript)

Créez un service API réutilisable pour votre application :

```javascript
class CryptoWalletAPI {
  constructor(baseURL = 'http://localhost:8000/api') {
    this.baseURL = baseURL;
    this.token = localStorage.getItem('token');
  }

  setToken(token) {
    this.token = token;
    localStorage.setItem('token', token);
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers,
    };

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    const response = await fetch(url, {
      ...options,
      headers,
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.error || 'Une erreur est survenue');
    }

    return response.json();
  }

  // Authentification
  async register(email, password) {
    return this.request('/register', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
  }

  async login(email, password) {
    const data = await this.request('/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
    this.setToken(data.token);
    return data;
  }

  async getMe() {
    return this.request('/me');
  }

  // Portefeuille
  async getPortfolio() {
    return this.request('/portfolio');
  }

  async getAsset(id) {
    return this.request(`/portfolio/${id}`);
  }

  async addAsset(asset) {
    return this.request('/portfolio', {
      method: 'POST',
      body: JSON.stringify(asset),
    });
  }

  async updateAsset(id, updates) {
    return this.request(`/portfolio/${id}`, {
      method: 'PUT',
      body: JSON.stringify(updates),
    });
  }

  async deleteAsset(id) {
    return this.request(`/portfolio/${id}`, {
      method: 'DELETE',
    });
  }

  // Statistiques
  async getPortfolioValue() {
    return this.request('/stats/portfolio/value');
  }

  async getPortfolioSummary() {
    return this.request('/stats/portfolio/summary');
  }

  async getPortfolioHistory() {
    return this.request('/stats/portfolio/history');
  }

  async getPortfolioDistribution() {
    return this.request('/stats/portfolio/distribution');
  }
}

// Utilisation
const api = new CryptoWalletAPI();

// Exemple d'utilisation dans votre composant
async function example() {
  try {
    // 1. Inscription (une seule fois)
    await api.register('user@example.com', 'password123');
    
    // 2. Connexion (obtenir le token)
    await api.login('user@example.com', 'password123');
    
    // 3. Récupérer le portefeuille
    const portfolio = await api.getPortfolio();
    console.log('Portefeuille:', portfolio);
    
    // 4. Ajouter un actif
    const newAsset = await api.addAsset({
      symbol: 'BTC',
      name: 'Bitcoin',
      quantity: '0.5',
      purchasePrice: '45000.00',
    });
    console.log('Actif ajouté:', newAsset);
    
    // 5. Statistiques
    const stats = await api.getPortfolioValue();
    console.log('Valeur totale:', stats);
  } catch (error) {
    console.error('Erreur:', error.message);
    // Gérer les erreurs (401 = token expiré, 400 = données invalides, etc.)
  }
}
```

### Exemple React Hook

```javascript
import { useState, useEffect } from 'react';

function usePortfolio() {
  const [portfolio, setPortfolio] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  const api = new CryptoWalletAPI();
  
  useEffect(() => {
    loadPortfolio();
  }, []);
  
  const loadPortfolio = async () => {
    try {
      setLoading(true);
      const data = await api.getPortfolio();
      setPortfolio(data.assets);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };
  
  const addAsset = async (asset) => {
    try {
      const newAsset = await api.addAsset(asset);
      await loadPortfolio(); // Recharger la liste
      return newAsset;
    } catch (err) {
      throw err;
    }
  };
  
  return { portfolio, loading, error, addAsset, loadPortfolio };
}
```

### Exemple Vue.js Composable

```javascript
import { ref, onMounted } from 'vue';

export function usePortfolio() {
  const portfolio = ref([]);
  const loading = ref(true);
  const error = ref(null);
  
  const api = new CryptoWalletAPI();
  
  const loadPortfolio = async () => {
    try {
      loading.value = true;
      const data = await api.getPortfolio();
      portfolio.value = data.assets;
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };
  
  onMounted(() => {
    loadPortfolio();
  });
  
  return {
    portfolio,
    loading,
    error,
    loadPortfolio
  };
}
```

## 🧪 Tester l'API

### Option 1 : Page d'accueil du backend
Ouvrez dans votre navigateur : **http://localhost:8000/**
- Liste toutes les routes disponibles
- Instructions de test
- Exemples de code

### Option 2 : Liste des routes en JSON
**http://localhost:8000/api/routes**
- Retourne toutes les routes au format JSON
- Utile pour générer automatiquement votre client API

### Option 3 : Script de test automatique
```bash
# Assurez-vous que le serveur est démarré
php -S localhost:8000 -t public

# Dans un autre terminal
./test-api.sh
```

### Option 4 : Postman
Importez le fichier `Crypto_Wallet_API.postman_collection.json` dans Postman pour tester toutes les routes avec une interface graphique.

### Option 5 : Test manuel avec curl
Consultez `TEST_MANUEL.md` pour des exemples détaillés avec curl.

## ⚠️ Codes de Réponse HTTP

| Code | Signification | Action recommandée |
|------|---------------|-------------------|
| `200 OK` | Requête réussie | Continuer normalement |
| `201 Created` | Ressource créée avec succès | Afficher un message de succès |
| `400 Bad Request` | Données invalides | Vérifier les données envoyées |
| `401 Unauthorized` | Authentification requise ou token invalide | Rediriger vers la page de connexion |
| `404 Not Found` | Ressource non trouvée | Vérifier l'URL et l'ID |
| `409 Conflict` | Conflit (ex: email déjà utilisé) | Afficher un message d'erreur approprié |

### Gestion des erreurs

```javascript
try {
  const response = await fetch(`${API_URL}/portfolio`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  
  if (!response.ok) {
    if (response.status === 401) {
      // Token expiré ou invalide
      localStorage.removeItem('token');
      // Rediriger vers la page de connexion
      window.location.href = '/login';
      return;
    }
    
    const error = await response.json();
    throw new Error(error.error || 'Une erreur est survenue');
  }
  
  const data = await response.json();
  return data;
} catch (error) {
  console.error('Erreur API:', error);
  // Afficher un message d'erreur à l'utilisateur
}
```

## 🔒 Sécurité

### Authentification JWT

- **Routes publiques** : `/api/login` et `/api/register` (pas de token requis)
- **Routes protégées** : Toutes les autres routes nécessitent un token JWT
- **Header requis** : `Authorization: Bearer {token}`
- **Expiration** : Le token expire après un certain temps (vous devrez vous reconnecter)

### Bonnes pratiques

1. **Stockage du token** : Utilisez `localStorage` ou `sessionStorage`
2. **Gestion de l'expiration** : Interceptez les erreurs 401 et redirigez vers la connexion
3. **HTTPS en production** : Assurez-vous d'utiliser HTTPS en production
4. **Ne pas exposer le token** : Ne loggez jamais le token dans la console en production

### Exemple de gestion de l'authentification

```javascript
class AuthService {
  constructor() {
    this.token = localStorage.getItem('token');
  }
  
  async login(email, password) {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    
    if (!response.ok) {
      throw new Error('Identifiants incorrects');
    }
    
    const data = await response.json();
    this.token = data.token;
    localStorage.setItem('token', this.token);
    return data;
  }
  
  logout() {
    this.token = null;
    localStorage.removeItem('token');
  }
  
  isAuthenticated() {
    return !!this.token;
  }
  
  getToken() {
    return this.token;
  }
}
```

## 📝 Notes Importantes

### Format des données

1. **Format des dates** : `Y-m-d H:i:s` (ex: `2024-01-15 10:30:00`)
   - Format ISO 8601 accepté également
   - Si non fourni, la date actuelle sera utilisée

2. **Quantités** : Précision de 8 décimales pour les cryptos
   - Format string recommandé pour éviter les problèmes de précision
   - Exemple : `"0.5"` au lieu de `0.5`

3. **Prix** : Précision de 2 décimales
   - Format string recommandé
   - Exemple : `"45000.00"` au lieu de `45000`

4. **Symboles** : Automatiquement convertis en majuscules
   - `"btc"` devient `"BTC"`
   - `"eth"` devient `"ETH"`

### Valeurs actuelles des cryptos

⚠️ **Important** : Actuellement, les valeurs actuelles utilisent le prix d'achat comme valeur de référence.

Pour obtenir les prix réels en temps réel, vous devrez intégrer une API externe :
- **CoinGecko** : https://www.coingecko.com/en/api
- **CoinMarketCap** : https://coinmarketcap.com/api/
- **Binance API** : https://binance-docs.github.io/apidocs/

### CORS

- ✅ Configuré pour accepter **toutes les origines** en développement
- ⚠️ En production, vous devrez peut-être configurer les origines autorisées

## 🏪 Cache et Performance Frontend

### Problème : Rechargement à chaque navigation

Si votre application Angular recharge les données à chaque changement de route, consultez **`GUIDE_ANGULAR_STORES.md`** pour implémenter :

- ✅ **Stores avec cache** (PortfolioStore, StatsStore)
- ✅ **TTL configurable** (ex: 30 secondes)
- ✅ **Rafraîchissement automatique** après add/update/delete
- ✅ **Bouton refresh** pour forcer le rechargement
- ✅ **État conservé** entre les navigations

### Headers HTTP de Cache

Le backend envoie automatiquement des headers de cache pour aider le frontend :

- `Cache-Control: private, max-age=30, must-revalidate`
- `X-Cache-TTL: 30` (TTL en secondes)
- `ETag` pour la validation conditionnelle (304 Not Modified)

**Utilisation côté frontend :**
```typescript
// Le frontend peut utiliser ces headers pour décider de mettre en cache
// Voir GUIDE_ANGULAR_STORES.md pour l'implémentation complète
```

## 📚 Documentation Complémentaire

- **`DEMARRAGE.md`** : Guide complet de démarrage du backend
- **`API_ROUTES.md`** : Documentation détaillée de toutes les routes
- **`TEST_MANUEL.md`** : Guide de test manuel avec exemples
- **`TROUBLESHOOTING.md`** : Résolution des problèmes courants
- **`GUIDE_ANGULAR_STORES.md`** : Guide d'implémentation des stores Angular avec cache
- **`GUIDE_CONNEXION.md`** : Guide de résolution des problèmes de connexion

## ✅ Checklist Frontend

Avant de commencer le développement frontend :

- [ ] Le backend est démarré (`php -S localhost:8000 -t public`)
- [ ] MySQL est démarré (`docker compose ps`)
- [ ] Les migrations sont exécutées (`php bin/console doctrine:migrations:migrate`)
- [ ] Les clés JWT existent (`ls config/jwt/`)
- [ ] Vous avez testé au moins une route (voir http://localhost:8000/)
- [ ] Vous avez importé la collection Postman (optionnel)

## 🆘 Support

### Problèmes de connexion (Erreur 401) ?

**Script de diagnostic automatique :**
```bash
./test-login.sh
```

Ce script va :
1. ✅ Vérifier que le serveur est démarré
2. ✅ Vérifier les clés JWT
3. ✅ Tester l'inscription
4. ✅ Tester la connexion
5. ✅ Tester le token obtenu

**Solutions manuelles :**

1. **Vérifiez que le backend est démarré** : http://localhost:8000/
2. **Créez un compte d'abord** avec `/api/register` avant de vous connecter
3. **Utilisez les mêmes identifiants** pour l'inscription et la connexion
4. **Vérifiez les logs du serveur PHP** pour voir les erreurs détaillées
5. **Consultez `GUIDE_CONNEXION.md`** pour un guide complet de résolution
6. **Consultez `TROUBLESHOOTING.md`** pour les problèmes courants

### Questions sur les routes ?

1. Consultez http://localhost:8000/ pour la liste complète
2. Voir `API_ROUTES.md` pour la documentation détaillée
3. Testez avec Postman en important `Crypto_Wallet_API.postman_collection.json`

## 🚀 Prêt pour le Frontend !

**Toutes les routes sont fonctionnelles et documentées.** 

Vous pouvez maintenant développer votre frontend avec confiance ! 🎉

**Bon développement ! 💻**
