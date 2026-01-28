# 🧪 Guide de Test Manuel des Routes API

## Pourquoi les routes ne s'affichent pas dans le navigateur ?

Les routes API sont des **endpoints REST**, pas des pages HTML. Elles retournent du **JSON**, pas du HTML. C'est normal qu'elles ne s'affichent pas comme des pages web classiques.

## ✅ Solution : Page de test créée

J'ai créé une page d'accueil qui liste toutes les routes :
- **URL :** http://localhost:8000/
- **Routes JSON :** http://localhost:8000/api/routes

## 🧪 Méthodes de Test

### 1️⃣ Test dans le Navigateur (Routes GET uniquement)

Pour les routes **GET**, vous pouvez les tester directement dans le navigateur :

```
http://localhost:8000/api/routes
```

**Note :** Les routes POST/PUT/DELETE nécessitent des outils spéciaux car elles envoient des données.

---

### 2️⃣ Test avec curl (Terminal)

#### Étape 1 : Inscription
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

**Réponse attendue :**
```json
{
  "message": "Utilisateur créé avec succès",
  "user": {
    "id": 1,
    "email": "test@example.com"
  }
}
```

#### Étape 2 : Connexion (obtenir le token)
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

**Réponse attendue :**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**⚠️ Important :** Copiez le token pour les requêtes suivantes !

#### Étape 3 : Récupérer les informations utilisateur
```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

#### Étape 4 : Liste du portefeuille
```bash
curl -X GET http://localhost:8000/api/portfolio \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

#### Étape 5 : Ajouter un actif
```bash
curl -X POST http://localhost:8000/api/portfolio \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "symbol":"BTC",
    "name":"Bitcoin",
    "quantity":"0.5",
    "purchasePrice":"45000.00",
    "purchaseDate":"2024-01-15 10:30:00"
  }'
```

#### Étape 6 : Modifier un actif (remplacez {id} par l'ID réel)
```bash
curl -X PUT http://localhost:8000/api/portfolio/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI" \
  -d '{
    "quantity":"0.75",
    "purchasePrice":"46000.00"
  }'
```

#### Étape 7 : Supprimer un actif
```bash
curl -X DELETE http://localhost:8000/api/portfolio/1 \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

#### Étape 8 : Statistiques - Valeur totale
```bash
curl -X GET http://localhost:8000/api/stats/portfolio/value \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

#### Étape 9 : Statistiques - Résumé
```bash
curl -X GET http://localhost:8000/api/stats/portfolio/summary \
  -H "Authorization: Bearer VOTRE_TOKEN_ICI"
```

---

### 3️⃣ Test avec le Script Automatique

Le script `test-api.sh` teste automatiquement toutes les routes :

```bash
# Assurez-vous que le serveur est démarré
php -S localhost:8000 -t public

# Dans un autre terminal
./test-api.sh
```

---

### 4️⃣ Test avec Postman

1. **Installer Postman** : https://www.postman.com/downloads/

2. **Créer une nouvelle requête** :
   - Méthode : `POST`
   - URL : `http://localhost:8000/api/login`
   - Headers : `Content-Type: application/json`
   - Body (raw JSON) :
     ```json
     {
       "email": "test@example.com",
       "password": "test123"
     }
     ```

3. **Enregistrer le token** :
   - Dans la réponse, copiez le token
   - Créez une variable d'environnement `token` dans Postman
   - Utilisez `{{token}}` dans les headers suivants

4. **Tester les autres routes** :
   - Ajoutez le header : `Authorization: Bearer {{token}}`
   - Changez la méthode et l'URL selon la route

---

### 5️⃣ Test avec Insomnia

1. **Installer Insomnia** : https://insomnia.rest/download

2. **Créer une requête** :
   - Méthode : `POST`
   - URL : `http://localhost:8000/api/login`
   - Body : JSON avec email et password

3. **Gérer le token** :
   - Dans la réponse, cliquez sur le token
   - Insomnia peut automatiquement l'utiliser dans les requêtes suivantes

---

### 6️⃣ Test avec JavaScript (Console du Navigateur)

Ouvrez la console du navigateur (F12) sur http://localhost:8000 et exécutez :

```javascript
// 1. Inscription
fetch('http://localhost:8000/api/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'test@example.com', password: 'test123' })
})
.then(r => r.json())
.then(data => console.log('Inscription:', data));

// 2. Connexion
fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'test@example.com', password: 'test123' })
})
.then(r => r.json())
.then(data => {
  console.log('Token:', data.token);
  window.token = data.token; // Sauvegarder le token
});

// 3. Récupérer le portefeuille (après connexion)
fetch('http://localhost:8000/api/portfolio', {
  headers: { 'Authorization': `Bearer ${window.token}` }
})
.then(r => r.json())
.then(data => console.log('Portefeuille:', data));
```

---

## 📋 Checklist de Test Rapide

```bash
# 1. Vérifier que le serveur est démarré
curl http://localhost:8000/

# 2. Voir toutes les routes en JSON
curl http://localhost:8000/api/routes

# 3. Tester l'inscription
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'

# 4. Tester la connexion (copier le token)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'

# 5. Tester une route protégée (remplacer TOKEN)
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer TOKEN"
```

---

## ⚠️ Erreurs Courantes

### Erreur 401 Unauthorized
- **Cause :** Token manquant ou invalide
- **Solution :** Vérifiez que vous avez bien le header `Authorization: Bearer {token}`

### Erreur 404 Not Found
- **Cause :** Route incorrecte ou serveur non démarré
- **Solution :** Vérifiez l'URL et que le serveur est démarré (`php -S localhost:8000 -t public`)

### Erreur CORS
- **Cause :** Le CORS est déjà configuré, mais vérifiez que le serveur est bien démarré
- **Solution :** Le CORS est automatiquement géré par le `CorsSubscriber`

---

## ✅ Résumé

- **Page d'accueil** : http://localhost:8000/ (liste toutes les routes)
- **Routes JSON** : http://localhost:8000/api/routes
- **Test automatique** : `./test-api.sh`
- **Test manuel** : Utilisez curl, Postman, ou la console du navigateur

Toutes les routes sont fonctionnelles ! 🚀
