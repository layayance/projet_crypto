# Documentation des Routes API - Crypto Wallet

## Routes d'authentification

### POST `/api/register`
Inscription d'un nouvel utilisateur.

**Corps de la requête (JSON):**
```json
{
  "email": "user@example.com",
  "password": "motdepasse123"
}
```

**Réponse (201 Created):**
```json
{
  "message": "Utilisateur créé avec succès",
  "user": {
    "id": 1,
    "email": "user@example.com"
  }
}
```

### POST `/api/login`
Connexion et obtention d'un token JWT.

**Corps de la requête (JSON):**
```json
{
  "email": "user@example.com",
  "password": "motdepasse123"
}
```

**Réponse (200 OK):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### GET `/api/me`
Récupère les informations de l'utilisateur connecté.

**Headers requis:**
```
Authorization: Bearer {token}
```

**Réponse (200 OK):**
```json
{
  "id": 1,
  "email": "user@example.com",
  "roles": ["ROLE_USER"]
}
```

---

## Routes de gestion du portefeuille

Toutes les routes suivantes nécessitent une authentification JWT.

### GET `/api/portfolio`
Récupère la liste de tous les actifs crypto de l'utilisateur.

**Réponse (200 OK):**
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

### GET `/api/portfolio/{id}`
Récupère les détails d'un actif spécifique.

**Réponse (200 OK):**
```json
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
```

### POST `/api/portfolio`
Ajoute un nouvel actif crypto au portefeuille.

**Corps de la requête (JSON):**
```json
{
  "symbol": "BTC",
  "name": "Bitcoin",
  "quantity": "0.5",
  "purchasePrice": "45000.00",
  "purchaseDate": "2024-01-15 10:30:00"
}
```

**Note:** `purchaseDate` est optionnel. Si non fourni, la date actuelle sera utilisée.

**Réponse (201 Created):**
```json
{
  "message": "Actif créé avec succès",
  "asset": {
    "id": 1,
    "symbol": "BTC",
    "name": "Bitcoin",
    "quantity": "0.5",
    "purchasePrice": "45000.00",
    "purchaseDate": "2024-01-15 10:30:00"
  }
}
```

### PUT/PATCH `/api/portfolio/{id}`
Met à jour un actif existant.

**Corps de la requête (JSON):**
```json
{
  "quantity": "0.75",
  "purchasePrice": "46000.00"
}
```

Tous les champs sont optionnels. Seuls les champs fournis seront mis à jour.

**Réponse (200 OK):**
```json
{
  "message": "Actif modifié avec succès",
  "asset": {
    "id": 1,
    "symbol": "BTC",
    "name": "Bitcoin",
    "quantity": "0.75",
    "purchasePrice": "46000.00",
    "purchaseDate": "2024-01-15 10:30:00"
  }
}
```

### DELETE `/api/portfolio/{id}`
Supprime un actif du portefeuille.

**Réponse (200 OK):**
```json
{
  "message": "Actif supprimé avec succès"
}
```

---

## Routes de statistiques et visualisation

Toutes les routes suivantes nécessitent une authentification JWT.

### GET `/api/stats/portfolio/value`
Récupère la valeur totale du portefeuille.

**Réponse (200 OK):**
```json
{
  "totalValue": "22500.00",
  "totalInvested": "22500.00",
  "profitLoss": "0.00",
  "profitLossPercentage": "0.00",
  "currency": "USD"
}
```

### GET `/api/stats/portfolio/summary`
Récupère un résumé détaillé du portefeuille par crypto.

**Réponse (200 OK):**
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

### GET `/api/stats/portfolio/history`
Récupère l'historique chronologique des achats.

**Réponse (200 OK):**
```json
{
  "history": [
    {
      "date": "2024-01-15",
      "symbol": "BTC",
      "name": "Bitcoin",
      "quantity": "0.5",
      "purchasePrice": "45000.00",
      "invested": "22500.00",
      "cumulativeInvested": "22500.00",
      "cumulativeValue": "22500.00"
    }
  ],
  "totalEntries": 1
}
```

### GET `/api/stats/portfolio/distribution`
Récupère la distribution du portefeuille par crypto (pourcentage).

**Réponse (200 OK):**
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

---

## Codes de réponse HTTP

- `200 OK` - Requête réussie
- `201 Created` - Ressource créée avec succès
- `400 Bad Request` - Données invalides
- `401 Unauthorized` - Authentification requise
- `404 Not Found` - Ressource non trouvée
- `409 Conflict` - Conflit (ex: email déjà utilisé)

---

## Notes importantes

1. **Authentification:** Toutes les routes (sauf `/api/login` et `/api/register`) nécessitent un token JWT dans le header `Authorization: Bearer {token}`.

2. **Format des dates:** Les dates doivent être au format `Y-m-d H:i:s` (ex: `2024-01-15 10:30:00`).

3. **Quantités:** Les quantités de crypto sont stockées avec une précision de 8 décimales.

4. **Prix:** Les prix sont stockés avec une précision de 2 décimales.

5. **Valeurs actuelles:** Actuellement, les valeurs actuelles utilisent le prix d'achat. Pour obtenir les prix réels, il faudrait intégrer une API externe (ex: CoinGecko, CoinMarketCap).
