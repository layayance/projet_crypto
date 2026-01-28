# Guide de dépannage - Crypto Wallet Backend

## Problème : Erreur de connexion MySQL

### Symptômes
```
SQLSTATE[HY000] [2002] Connection refused
ou
SQLSTATE[HY000] [1045] Access denied for user
```

### Cause
Le conteneur MySQL a été créé avec une configuration différente ou avant que les variables d'environnement ne soient correctement définies.

### Solution 1 : Recréer le conteneur (Recommandé)

```bash
./fix-database.sh
```

Ce script va :
1. Arrêter les conteneurs
2. Supprimer l'ancien volume de données
3. Recréer les conteneurs avec la bonne configuration
4. Attendre que PostgreSQL soit initialisé

### Solution 2 : Recréation manuelle

```bash
# 1. Arrêter les conteneurs
docker compose down

# 2. Supprimer le volume (ATTENTION : supprime toutes les données)
docker volume rm projet_crypto_database_data

# 3. Recréer les conteneurs
docker compose up -d

# 4. Attendre que PostgreSQL soit prêt (10-15 secondes)
sleep 10

# 5. Vérifier l'état
docker compose ps

# 6. Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

### Solution 3 : Utiliser le port actuel (temporaire)

Si vous ne voulez pas recréer le conteneur, vous pouvez utiliser le port actuel :

1. Vérifiez le port mappé :
   ```bash
   docker compose ps
   ```
   Notez le port (ex: 65461)

2. Modifiez temporairement le `.env` :
   ```env
   DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:65461/app?serverVersion=16&charset=utf8"
   ```

3. Exécutez les migrations :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

**Note :** Cette solution est temporaire. Il est recommandé de recréer le conteneur avec le bon port.

---

## Problème : Port MySQL incorrect

### Symptômes
- Le port affiché dans `docker compose ps` n'est pas 3306
- Erreur de connexion à la base de données

### Solution

Le fichier `compose.yaml` est configuré pour utiliser le port 3306. Si vous voyez un autre port, c'est que le conteneur a été créé avant cette configuration.

**Recréez le conteneur :**
```bash
./fix-database.sh
```

---

## Problème : Erreurs CORS depuis le frontend

### Symptômes
- Erreur "CORS policy" dans la console du navigateur
- Les requêtes depuis le frontend sont bloquées

### Vérifications

1. Vérifiez que le serveur Symfony est démarré :
   ```bash
   php -S localhost:8000 -t public
   ```

2. Vérifiez que le subscriber CORS est enregistré :
   ```bash
   php bin/console debug:event-dispatcher kernel.response
   ```
   Vous devriez voir `App\EventSubscriber\CorsSubscriber::onKernelResponse`

3. Vérifiez les headers dans la réponse (DevTools > Network)

### Solution

Le subscriber CORS est configuré pour autoriser toutes les origines en développement. Si vous avez toujours des problèmes :

1. Vérifiez que le fichier `src/EventSubscriber/CorsSubscriber.php` existe
2. Videz le cache Symfony :
   ```bash
   php bin/console cache:clear
   ```

---

## Problème : Erreur 401 lors de la connexion

### Symptômes
- Erreur 401 Unauthorized lors de `POST /api/login`
- Message "Identifiants invalides" ou "Invalid credentials"

### Causes possibles

1. **Utilisateur n'existe pas** : Vous devez d'abord créer un compte avec `/api/register`
2. **Email ou mot de passe incorrect** : Vérifiez les identifiants
3. **Clés JWT manquantes** : Les clés JWT n'existent pas ou sont invalides
4. **Format de requête incorrect** : Vérifiez le format JSON

### Solutions

#### Solution 1 : Créer un compte d'abord

```bash
# 1. Inscription
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'

# 2. Connexion (utilisez les MÊMES identifiants)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

#### Solution 2 : Vérifier les clés JWT

```bash
# Vérifier que les clés existent
ls -la config/jwt/

# Si elles n'existent pas, les générer
php bin/console lexik:jwt:generate-keypair
```

#### Solution 3 : Vérifier le format de la requête

**Format correct :**
```json
{
  "email": "test@example.com",
  "password": "test123"
}
```

**Vérifications :**
- ✅ Utilisez `email` (pas `username`)
- ✅ Utilisez `password` (pas `pass`)
- ✅ Header `Content-Type: application/json` présent
- ✅ Pas d'espaces avant/après les valeurs

#### Solution 4 : Vérifier dans la base de données

```bash
docker compose exec database mysql -u symfony -psymfony123 crypto_wallet -e "SELECT id, email FROM user;"
```

### Guide complet

Consultez **`GUIDE_CONNEXION.md`** pour un guide détaillé de résolution des problèmes de connexion.

## Problème : Token JWT invalide (après connexion)

### Symptômes
- Erreur 401 Unauthorized sur les routes protégées
- Message "Invalid JWT Token" ou "Token expired"

### Solution

1. Vérifiez que le token est bien envoyé dans le header :
   ```
   Authorization: Bearer {votre_token}
   ```

2. Le token peut avoir expiré, reconnectez-vous :
   ```bash
   curl -X POST http://localhost:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"test123"}'
   ```

3. Vérifiez que les clés JWT existent :
   ```bash
   ls -la config/jwt/
   ```
   Vous devriez voir `private.pem` et `public.pem`

4. Si les fichiers n'existent pas, générez-les :
   ```bash
   php bin/console lexik:jwt:generate-keypair
   ```

---

## Problème : Docker ne démarre pas

### Symptômes
- Erreur "permission denied" avec Docker
- Conteneurs ne démarrent pas

### Solution

1. Vérifiez que Docker Desktop est démarré
2. Vérifiez les permissions :
   ```bash
   docker info
   ```
3. Sur macOS, vous devrez peut-être redémarrer Docker Desktop

---

## Vérification générale

Pour vérifier que tout fonctionne :

```bash
# 1. Vérifier Docker
docker compose ps

# 2. Vérifier les routes
php bin/console debug:router

# 3. Vérifier la connexion à la base de données
php bin/console doctrine:schema:validate

# 4. Tester une route
curl http://localhost:8000/api/me
```

---

## Logs utiles

```bash
# Logs MySQL
docker compose logs database

# Logs de tous les services
docker compose logs

# Logs en temps réel
docker compose logs -f database
```
