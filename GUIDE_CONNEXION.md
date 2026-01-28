# 🔐 Guide de Résolution - Erreur 401 Connexion

## Problème : Erreur 401 lors de la connexion

Si vous obtenez une erreur **401 Unauthorized** lors de la tentative de connexion, voici comment résoudre le problème.

## ✅ Vérifications à faire

### 1. Vérifier que l'utilisateur existe

Assurez-vous d'avoir créé un compte avec `/api/register` avant de vous connecter.

**Test d'inscription :**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

**Réponse attendue (201) :**
```json
{
  "message": "Utilisateur créé avec succès",
  "user": {
    "id": 1,
    "email": "test@example.com"
  }
}
```

### 2. Vérifier les identifiants

**Erreurs courantes :**
- ❌ Email incorrect
- ❌ Mot de passe incorrect
- ❌ Espaces avant/après l'email ou le mot de passe
- ❌ Majuscules/minuscules dans l'email

**Test de connexion :**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

**Réponse attendue (200) :**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### 3. Vérifier que les clés JWT existent

```bash
ls -la config/jwt/
```

Vous devriez voir :
- `private.pem`
- `public.pem`

**Si les fichiers n'existent pas :**
```bash
php bin/console lexik:jwt:generate-keypair
```

### 4. Vérifier le format de la requête

**Format correct :**
```json
{
  "email": "test@example.com",
  "password": "test123"
}
```

**Erreurs courantes :**
- ❌ `username` au lieu de `email`
- ❌ `pass` au lieu de `password`
- ❌ Oubli du header `Content-Type: application/json`

### 5. Vérifier que le serveur est démarré

```bash
# Vérifier que le serveur répond
curl http://localhost:8000/

# Vérifier les routes
curl http://localhost:8000/api/routes
```

## 🔧 Solutions

### Solution 1 : Recréer l'utilisateur

Si vous n'êtes pas sûr que l'utilisateur existe :

```bash
# 1. Inscription
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'

# 2. Connexion immédiatement après
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

### Solution 2 : Vérifier dans la base de données

```bash
# Se connecter à MySQL
docker compose exec database mysql -u symfony -psymfony123 crypto_wallet

# Vérifier les utilisateurs
SELECT id, email FROM user;
```

### Solution 3 : Régénérer les clés JWT

```bash
# Supprimer les anciennes clés
rm config/jwt/private.pem config/jwt/public.pem

# Régénérer
php bin/console lexik:jwt:generate-keypair
```

### Solution 4 : Vider le cache Symfony

```bash
php bin/console cache:clear
```

## 📝 Exemple complet de test

```bash
#!/bin/bash

API_URL="http://localhost:8000/api"
EMAIL="test@example.com"
PASSWORD="test123"

echo "1. Inscription..."
REGISTER_RESPONSE=$(curl -s -X POST "$API_URL/register" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

echo "$REGISTER_RESPONSE" | python3 -m json.tool

echo ""
echo "2. Connexion..."
LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

echo "$LOGIN_RESPONSE" | python3 -m json.tool

# Extraire le token
TOKEN=$(echo "$LOGIN_RESPONSE" | python3 -c "import sys, json; print(json.load(sys.stdin).get('token', ''))" 2>/dev/null)

if [ -n "$TOKEN" ]; then
    echo ""
    echo "✅ Connexion réussie !"
    echo "Token: ${TOKEN:0:50}..."
    
    echo ""
    echo "3. Test avec le token..."
    curl -s -X GET "$API_URL/me" \
      -H "Authorization: Bearer $TOKEN" | python3 -m json.tool
else
    echo ""
    echo "❌ Échec de la connexion"
    echo "Réponse: $LOGIN_RESPONSE"
fi
```

## 🆘 Si rien ne fonctionne

1. **Vérifier les logs du serveur PHP**
   - Regardez la sortie du serveur pour voir les erreurs détaillées

2. **Vérifier les logs Symfony**
   ```bash
   tail -f var/log/dev.log
   ```

3. **Tester avec Postman**
   - Importez `Crypto_Wallet_API.postman_collection.json`
   - Testez la route "Connexion"

4. **Vérifier la configuration**
   ```bash
   php bin/console debug:router | grep login
   php bin/console debug:config security
   ```

## ✅ Checklist de vérification

- [ ] L'utilisateur a été créé avec `/api/register`
- [ ] L'email et le mot de passe sont corrects (sans espaces)
- [ ] Le header `Content-Type: application/json` est présent
- [ ] Le format JSON est correct (`email` et `password`)
- [ ] Les clés JWT existent (`ls config/jwt/`)
- [ ] Le serveur est démarré (`php -S localhost:8000 -t public`)
- [ ] MySQL est démarré (`docker compose ps`)

## 💡 Bonnes pratiques

1. **Toujours créer un compte avant de se connecter**
2. **Utiliser le même email et mot de passe pour l'inscription et la connexion**
3. **Vérifier la réponse de l'inscription avant de tenter la connexion**
4. **Stocker le token immédiatement après la connexion**
