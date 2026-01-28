# 🏪 Guide d'Implémentation - Stores Angular pour Cache/État

## 🎯 Problème

Quand vous changez de route dans Angular, le composant est détruit et recréé. Si vous chargez les données dans `ngOnInit()`, cela fait un nouvel appel API à chaque navigation → impression de "rechargement" constant.

## ✅ Solution : Stores avec Cache

Implémentez des **stores/services** qui gardent l'état entre les navigations et ne rechargent que si nécessaire.

---

## 📦 Architecture Recommandée

### Structure des Stores

```
src/
├── services/
│   ├── stores/
│   │   ├── portfolio.store.ts      # Store pour le portefeuille
│   │   ├── stats.store.ts           # Store pour les statistiques
│   │   └── dashboard.store.ts       # Store pour le dashboard
│   └── api/
│       └── crypto-wallet-api.service.ts  # Service API (déjà créé)
```

---

## 💼 1. PortfolioStore

### `src/services/stores/portfolio.store.ts`

```typescript
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';
import { CryptoWalletAPI } from '../api/crypto-wallet-api.service';

export interface PortfolioState {
  assets: any[];
  loading: boolean;
  error: string | null;
  lastLoadedAt: Date | null;
}

@Injectable({
  providedIn: 'root'
})
export class PortfolioStore {
  private readonly CACHE_TTL = 30000; // 30 secondes
  
  private state$ = new BehaviorSubject<PortfolioState>({
    assets: [],
    loading: false,
    error: null,
    lastLoadedAt: null
  });

  constructor(private api: CryptoWalletAPI) {}

  // Observable public (read-only)
  get state(): Observable<PortfolioState> {
    return this.state$.asObservable();
  }

  get assets$(): Observable<any[]> {
    return this.state$.pipe(
      map(state => state.assets)
    );
  }

  get loading$(): Observable<boolean> {
    return this.state$.pipe(
      map(state => state.loading)
    );
  }

  get error$(): Observable<string | null> {
    return this.state$.pipe(
      map(state => state.error)
    );
  }

  /**
   * Charge le portefeuille
   * @param force Force le rechargement même si le cache est valide
   */
  load(force: boolean = false): Observable<any[]> {
    const currentState = this.state$.value;
    
    // Vérifier si le cache est encore valide
    if (!force && this.isCacheValid(currentState.lastLoadedAt)) {
      return of(currentState.assets);
    }

    // Mettre à jour l'état de chargement
    this.updateState({ loading: true, error: null });

    return this.api.getPortfolio().pipe(
      tap(data => {
        this.updateState({
          assets: data.assets || [],
          loading: false,
          error: null,
          lastLoadedAt: new Date()
        });
      }),
      catchError(error => {
        this.updateState({
          loading: false,
          error: error.message || 'Erreur lors du chargement du portefeuille'
        });
        throw error;
      })
    );
  }

  /**
   * Ajoute un actif et met à jour le cache
   */
  addAsset(asset: any): Observable<any> {
    return this.api.addAsset(asset).pipe(
      tap(newAsset => {
        const currentState = this.state$.value;
        this.updateState({
          assets: [...currentState.assets, newAsset.asset],
          lastLoadedAt: new Date()
        });
      })
    );
  }

  /**
   * Met à jour un actif et rafraîchit le cache
   */
  updateAsset(id: number, updates: any): Observable<any> {
    return this.api.updateAsset(id, updates).pipe(
      tap(updatedAsset => {
        const currentState = this.state$.value;
        const assets = currentState.assets.map(asset =>
          asset.id === id ? updatedAsset.asset : asset
        );
        this.updateState({
          assets,
          lastLoadedAt: new Date()
        });
      })
    );
  }

  /**
   * Supprime un actif et met à jour le cache
   */
  deleteAsset(id: number): Observable<void> {
    return this.api.deleteAsset(id).pipe(
      tap(() => {
        const currentState = this.state$.value;
        const assets = currentState.assets.filter(asset => asset.id !== id);
        this.updateState({
          assets,
          lastLoadedAt: new Date()
        });
      })
    );
  }

  /**
   * Force le rafraîchissement
   */
  refresh(): Observable<any[]> {
    return this.load(true);
  }

  /**
   * Vérifie si le cache est encore valide
   */
  private isCacheValid(lastLoadedAt: Date | null): boolean {
    if (!lastLoadedAt) return false;
    
    const now = new Date();
    const diff = now.getTime() - lastLoadedAt.getTime();
    return diff < this.CACHE_TTL;
  }

  /**
   * Met à jour l'état
   */
  private updateState(partial: Partial<PortfolioState>): void {
    this.state$.next({
      ...this.state$.value,
      ...partial
    });
  }
}
```

---

## 📊 2. StatsStore

### `src/services/stores/stats.store.ts`

```typescript
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { tap, catchError, map } from 'rxjs/operators';
import { CryptoWalletAPI } from '../api/crypto-wallet-api.service';

export interface StatsState {
  value: any | null;
  summary: any | null;
  history: any[] | null;
  distribution: any[] | null;
  loading: boolean;
  error: string | null;
  lastLoadedAt: Date | null;
}

@Injectable({
  providedIn: 'root'
})
export class StatsStore {
  private readonly CACHE_TTL = 30000; // 30 secondes
  
  private state$ = new BehaviorSubject<StatsState>({
    value: null,
    summary: null,
    history: null,
    distribution: null,
    loading: false,
    error: null,
    lastLoadedAt: null
  });

  constructor(private api: CryptoWalletAPI) {}

  get state(): Observable<StatsState> {
    return this.state$.asObservable();
  }

  get value$(): Observable<any> {
    return this.state$.pipe(map(s => s.value));
  }

  get summary$(): Observable<any> {
    return this.state$.pipe(map(s => s.summary));
  }

  get history$(): Observable<any[]> {
    return this.state$.pipe(map(s => s.history || []));
  }

  get distribution$(): Observable<any[]> {
    return this.state$.pipe(map(s => s.distribution || []));
  }

  get loading$(): Observable<boolean> {
    return this.state$.pipe(map(s => s.loading));
  }

  /**
   * Charge toutes les statistiques
   */
  loadAll(force: boolean = false): Observable<void> {
    const currentState = this.state$.value;
    
    if (!force && this.isCacheValid(currentState.lastLoadedAt)) {
      return of(void 0);
    }

    this.updateState({ loading: true, error: null });

    return forkJoin({
      value: this.api.getPortfolioValue(),
      summary: this.api.getPortfolioSummary(),
      history: this.api.getPortfolioHistory(),
      distribution: this.api.getPortfolioDistribution()
    }).pipe(
      tap(data => {
        this.updateState({
          ...data,
          loading: false,
          error: null,
          lastLoadedAt: new Date()
        });
      }),
      catchError(error => {
        this.updateState({
          loading: false,
          error: error.message
        });
        throw error;
      }),
      map(() => void 0)
    );
  }

  /**
   * Charge uniquement la valeur totale
   */
  loadValue(force: boolean = false): Observable<any> {
    const currentState = this.state$.value;
    
    if (!force && currentState.value && this.isCacheValid(currentState.lastLoadedAt)) {
      return of(currentState.value);
    }

    return this.api.getPortfolioValue().pipe(
      tap(value => {
        this.updateState({
          value,
          lastLoadedAt: new Date()
        });
      })
    );
  }

  /**
   * Rafraîchit toutes les stats
   */
  refresh(): Observable<void> {
    return this.loadAll(true);
  }

  private isCacheValid(lastLoadedAt: Date | null): boolean {
    if (!lastLoadedAt) return false;
    const now = new Date();
    return (now.getTime() - lastLoadedAt.getTime()) < this.CACHE_TTL;
  }

  private updateState(partial: Partial<StatsState>): void {
    this.state$.next({
      ...this.state$.value,
      ...partial
    });
  }
}
```

---

## 🎨 3. Utilisation dans les Composants

### Exemple : PortfolioComponent

```typescript
import { Component, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
import { PortfolioStore } from '../services/stores/portfolio.store';

@Component({
  selector: 'app-portfolio',
  template: `
    <div *ngIf="loading$ | async">Chargement...</div>
    <div *ngIf="error$ | async as error">{{ error }}</div>
    
    <button (click)="refresh()">🔄 Rafraîchir</button>
    
    <div *ngFor="let asset of assets$ | async">
      {{ asset.symbol }} - {{ asset.quantity }}
    </div>
  `
})
export class PortfolioComponent implements OnInit {
  assets$: Observable<any[]>;
  loading$: Observable<boolean>;
  error$: Observable<string | null>;

  constructor(private portfolioStore: PortfolioStore) {
    // Les observables sont déjà disponibles, pas besoin de recharger
    this.assets$ = this.portfolioStore.assets$;
    this.loading$ = this.portfolioStore.loading$;
    this.error$ = this.portfolioStore.error$;
  }

  ngOnInit(): void {
    // Charge seulement si le cache n'est pas valide
    this.portfolioStore.load();
  }

  refresh(): void {
    // Force le rechargement
    this.portfolioStore.refresh().subscribe();
  }

  addAsset(asset: any): void {
    // Ajoute et met à jour automatiquement le cache
    this.portfolioStore.addAsset(asset).subscribe({
      next: () => console.log('Actif ajouté'),
      error: (err) => console.error('Erreur:', err)
    });
  }
}
```

---

## 🔄 4. Rafraîchissement Automatique après Actions

### Dans PortfolioComponent

```typescript
deleteAsset(id: number): void {
  this.portfolioStore.deleteAsset(id).subscribe({
    next: () => {
      // Le store met déjà à jour le cache automatiquement
      // Pas besoin de recharger manuellement !
    }
  });
}

updateAsset(id: number, updates: any): void {
  this.portfolioStore.updateAsset(id, updates).subscribe({
    next: () => {
      // Cache mis à jour automatiquement
    }
  });
}
```

---

## 🎯 5. Dashboard Store (Optionnel)

Si vous avez un dashboard qui combine portfolio + stats :

```typescript
@Injectable({ providedIn: 'root' })
export class DashboardStore {
  constructor(
    private portfolioStore: PortfolioStore,
    private statsStore: StatsStore
  ) {}

  loadAll(force: boolean = false): Observable<void> {
    return forkJoin({
      portfolio: this.portfolioStore.load(force),
      stats: this.statsStore.loadAll(force)
    }).pipe(map(() => void 0));
  }

  refresh(): Observable<void> {
    return this.loadAll(true);
  }
}
```

---

## ⚙️ 6. Configuration du TTL

Vous pouvez rendre le TTL configurable :

```typescript
@Injectable({ providedIn: 'root' })
export class PortfolioStore {
  private cacheTTL = 30000; // 30s par défaut

  setCacheTTL(ttl: number): void {
    this.cacheTTL = ttl;
  }

  private isCacheValid(lastLoadedAt: Date | null): boolean {
    if (!lastLoadedAt) return false;
    const now = new Date();
    return (now.getTime() - lastLoadedAt.getTime()) < this.cacheTTL;
  }
}
```

---

## 🚀 7. Alternative : RouteReuseStrategy (Moins Recommandé)

Si vous préférez une solution plus simple mais moins contrôlable :

```typescript
import { RouteReuseStrategy, ActivatedRouteSnapshot, DetachedRouteHandle } from '@angular/router';

@Injectable()
export class CustomRouteReuseStrategy implements RouteReuseStrategy {
  private storedRoutes = new Map<string, DetachedRouteHandle>();

  shouldDetach(route: ActivatedRouteSnapshot): boolean {
    return route.data['reuse'] === true;
  }

  store(route: ActivatedRouteSnapshot, handle: DetachedRouteHandle): void {
    this.storedRoutes.set(this.getRouteKey(route), handle);
  }

  shouldAttach(route: ActivatedRouteSnapshot): boolean {
    return this.storedRoutes.has(this.getRouteKey(route));
  }

  retrieve(route: ActivatedRouteSnapshot): DetachedRouteHandle | null {
    return this.storedRoutes.get(this.getRouteKey(route)) || null;
  }

  shouldReuseRoute(future: ActivatedRouteSnapshot, curr: ActivatedRouteSnapshot): boolean {
    return future.routeConfig === curr.routeConfig;
  }

  private getRouteKey(route: ActivatedRouteSnapshot): string {
    return route.routeConfig?.path || '';
  }
}
```

**Dans app.config.ts :**
```typescript
providers: [
  { provide: RouteReuseStrategy, useClass: CustomRouteReuseStrategy }
]
```

**Dans vos routes :**
```typescript
{
  path: 'portfolio',
  component: PortfolioComponent,
  data: { reuse: true }
}
```

---

## ✅ Avantages de la Solution Store

1. ✅ **État conservé** entre les navigations
2. ✅ **Pas de rechargement inutile** (TTL respecté)
3. ✅ **Rafraîchissement automatique** après add/update/delete
4. ✅ **Bouton refresh** pour forcer le rechargement
5. ✅ **Gestion d'erreur centralisée**
6. ✅ **Loading states** gérés automatiquement
7. ✅ **Réutilisable** dans plusieurs composants

---

## 📝 Checklist d'Implémentation

- [ ] Créer `PortfolioStore` avec cache TTL
- [ ] Créer `StatsStore` avec cache TTL
- [ ] Modifier les composants pour utiliser les stores
- [ ] Ajouter bouton "Refresh" dans l'UI
- [ ] Tester la navigation entre les routes
- [ ] Vérifier que le cache fonctionne (pas de rechargement)
- [ ] Vérifier le refresh après add/update/delete

---

## 🎉 Résultat Attendu

- ✅ Navigation fluide sans rechargement
- ✅ Données conservées entre les routes
- ✅ Rechargement uniquement si nécessaire (TTL, action, refresh)
- ✅ Expérience utilisateur améliorée (comportement SaaS)
