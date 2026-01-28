# 🚀 Guide Cache - Backend et Frontend

## 📋 Vue d'ensemble

Ce guide explique comment optimiser les performances en évitant les rechargements inutiles de données lors de la navigation dans votre application Angular.

## 🎯 Problème Résolu

**Avant :** À chaque changement de route → composant détruit/recréé → `ngOnInit()` → nouvel appel API → rechargement constant

**Après :** Stores avec cache → état conservé → rechargement uniquement si nécessaire (TTL, action, refresh)

---

## 🔧 Optimisations Backend

### Headers HTTP de Cache

Le backend envoie automatiquement des headers pour faciliter la mise en cache côté frontend :

#### Headers envoyés pour les routes GET

```
Cache-Control: private, max-age=30, must-revalidate
X-Cache-TTL: 30
ETag: "abc123..."
```

**Signification :**
- `Cache-Control: private` : Cache uniquement côté client (pas de proxy)
- `max-age=30` : Cache valide pendant 30 secondes
- `must-revalidate` : Vérifier avec le serveur après expiration
- `X-Cache-TTL` : TTL en secondes (pour le frontend)
- `ETag` : Permet la validation conditionnelle (304 Not Modified)

#### Validation Conditionnelle (ETag)

Le backend supporte les requêtes `If-None-Match` :

```http
GET /api/portfolio
If-None-Match: "abc123..."
```

Si le contenu n'a pas changé, le backend répond :
```http
304 Not Modified
```

Le frontend peut utiliser cette réponse pour éviter de recharger les données.

---

## 💻 Implémentation Frontend (Angular)

### Solution Recommandée : Stores avec Cache

Consultez **`GUIDE_ANGULAR_STORES.md`** pour l'implémentation complète.

#### Résumé de l'Architecture

```
PortfolioStore
├── Cache TTL: 30 secondes
├── État conservé entre navigations
├── Rechargement si :
│   ├── Cache expiré (TTL)
│   ├── Action effectuée (add/update/delete)
│   └── Refresh explicite
└── Mise à jour automatique après actions
```

#### Exemple d'Utilisation

```typescript
// Dans votre composant
constructor(private portfolioStore: PortfolioStore) {}

ngOnInit(): void {
  // Charge seulement si le cache n'est pas valide
  this.portfolioStore.load();
  
  // Les données sont déjà disponibles via l'observable
  this.assets$ = this.portfolioStore.assets$;
}

refresh(): void {
  // Force le rechargement
  this.portfolioStore.refresh().subscribe();
}

addAsset(asset: any): void {
  // Ajoute et met à jour automatiquement le cache
  this.portfolioStore.addAsset(asset).subscribe();
}
```

---

## 🔄 Flux de Données

### Scénario 1 : Navigation Normale

```
1. Utilisateur va sur /portfolio
2. PortfolioStore.load() vérifie le cache
3. Si cache valide (< 30s) → retourne les données en cache
4. Si cache expiré → appel API → mise à jour du cache
5. Composant affiche les données
```

### Scénario 2 : Après une Action

```
1. Utilisateur ajoute un actif
2. portfolioStore.addAsset() → appel API
3. Store met à jour automatiquement le cache
4. Pas besoin de recharger manuellement !
```

### Scénario 3 : Refresh Explicite

```
1. Utilisateur clique sur "Rafraîchir"
2. portfolioStore.refresh() → force le rechargement
3. Appel API → mise à jour du cache
```

---

## ⚙️ Configuration

### Modifier le TTL du Cache

**Côté Backend :** Modifiez `src/EventSubscriber/CacheSubscriber.php`
```php
$response->headers->set('Cache-Control', 'private, max-age=60, must-revalidate');
$response->headers->set('X-Cache-TTL', '60');
```

**Côté Frontend :** Dans vos stores Angular
```typescript
private readonly CACHE_TTL = 60000; // 60 secondes
```

### Désactiver le Cache pour une Route

**Côté Backend :** Ajoutez une condition dans `CacheSubscriber.php`
```php
if (str_starts_with($path, '/api/stats/portfolio/value')) {
    // Pas de cache pour cette route
    return;
}
```

---

## 📊 Monitoring

### Vérifier les Headers de Cache

```bash
curl -I http://localhost:8000/api/portfolio \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Réponse attendue :**
```
HTTP/1.1 200 OK
Cache-Control: private, max-age=30, must-revalidate
X-Cache-TTL: 30
ETag: "abc123..."
```

### Tester la Validation Conditionnelle

```bash
# Première requête
curl -I http://localhost:8000/api/portfolio \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "If-None-Match: \"abc123...\""
```

Si le contenu n'a pas changé :
```
HTTP/1.1 304 Not Modified
```

---

## ✅ Checklist d'Implémentation

### Backend (Déjà fait ✅)

- [x] CacheSubscriber créé
- [x] Headers Cache-Control ajoutés
- [x] Headers ETag ajoutés
- [x] Support 304 Not Modified

### Frontend (À faire)

- [ ] Créer PortfolioStore avec cache
- [ ] Créer StatsStore avec cache
- [ ] Modifier les composants pour utiliser les stores
- [ ] Ajouter bouton "Refresh"
- [ ] Tester la navigation
- [ ] Vérifier que le cache fonctionne

---

## 🎉 Résultat Attendu

- ✅ **Navigation fluide** : Pas de rechargement à chaque changement de route
- ✅ **Performance optimisée** : Moins d'appels API inutiles
- ✅ **Expérience utilisateur améliorée** : Comportement SaaS moderne
- ✅ **État conservé** : Scroll, données, etc. conservés entre les routes

---

## 📚 Documentation Complémentaire

- **`GUIDE_ANGULAR_STORES.md`** : Guide complet d'implémentation des stores Angular
- **`README_FRONTEND.md`** : Documentation générale de l'API
- **`API_ROUTES.md`** : Documentation détaillée des routes
