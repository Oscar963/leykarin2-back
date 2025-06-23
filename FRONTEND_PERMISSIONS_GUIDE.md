# 🎯 GUÍA DE IMPLEMENTACIÓN DE PERMISOS - FRONTEND

## 📋 ÍNDICE
1. [Configuración de Roles y Permisos](#configuración-de-roles-y-permisos)
2. [Guards de Rutas](#guards-de-rutas)
3. [Componentes Condicionales](#componentes-condicionales)
4. [Servicios de Autenticación](#servicios-de-autenticación)
5. [Directivas Personalizadas](#directivas-personalizadas)
6. [Ejemplos de Implementación](#ejemplos-de-implementación)
7. [Matriz de Permisos Final](#matriz-de-permisos-final)

---

## 🔧 CONFIGURACIÓN DE ROLES Y PERMISOS

### **1. Constantes de Roles**
```typescript
// constants/roles.ts
export const ROLES = {
  ADMIN_SISTEMA: 'Administrador del Sistema',
  ADMIN_MUNICIPAL: 'Administrador Municipal',
  VISADOR: 'Visador o de Administrador Municipal',
  ENCARGADO_PRESUPUESTOS: 'Encargado de Presupuestos',
  SUBROGANTE_ENCARGADO: 'Subrogante de Encargado de Presupuestos',
  DIRECTOR: 'Director',
  SUBROGANTE_DIRECTOR: 'Subrogante de Director',
  JEFATURA: 'Jefatura',
  SUBROGANTE_JEFATURA: 'Subrogante de Jefatura'
} as const;

export type UserRole = typeof ROLES[keyof typeof ROLES];
```

### **2. Configuración de Módulos por Rol**
```typescript
// constants/modules.ts
export const MODULE_ACCESS = {
  [ROLES.ADMIN_SISTEMA]: [
    'dashboard', 'purchase_plans', 'projects', 'form_f1', 
    'users', 'files', 'directions', 'reports', 'audit'
  ],
  [ROLES.ADMIN_MUNICIPAL]: [
    'dashboard', 'purchase_plans', 'projects', 'form_f1', 'users', 'directions'
  ],
  [ROLES.VISADOR]: [
    'dashboard', 'purchase_plans', 'projects', 'form_f1'
  ],
  [ROLES.ENCARGADO_PRESUPUESTOS]: [
    'form_f1'
  ],
  [ROLES.SUBROGANTE_ENCARGADO]: [
    'form_f1'
  ],
  [ROLES.DIRECTOR]: [
    'dashboard', 'purchase_plans', 'projects', 'form_f1', 'files'
  ],
  [ROLES.SUBROGANTE_DIRECTOR]: [
    'dashboard', 'purchase_plans', 'projects', 'form_f1', 'files'
  ],
  [ROLES.JEFATURA]: [
    'dashboard', 'purchase_plans', 'projects', 'form_f1', 'files'
  ],
  [ROLES.SUBROGANTE_JEFATURA]: [
    'dashboard', 'purchase_plans', 'projects', 'form_f1', 'files'
  ]
} as const;
```

### **3. Permisos Granulares por Módulo**
```typescript
// constants/permissions.ts
export const MODULE_PERMISSIONS = {
  [ROLES.ADMIN_SISTEMA]: {
    dashboard: ['view'],
    purchase_plans: ['create', 'edit', 'delete', 'approve', 'reject', 'send', 'view', 'export'],
    projects: ['create', 'edit', 'delete', 'view', 'verify'],
    form_f1: ['create', 'edit', 'delete', 'view', 'upload', 'download', 'remove'],
    users: ['create', 'edit', 'delete', 'view', 'reset_password'],
    files: ['create', 'edit', 'delete', 'view', 'upload', 'download'],
    directions: ['create', 'edit', 'delete', 'view'],
    reports: ['view', 'export'],
    audit: ['view', 'logs']
  },
  [ROLES.ADMIN_MUNICIPAL]: {
    dashboard: ['view'],
    purchase_plans: ['create', 'edit', 'delete', 'visar', 'approve', 'reject', 'send', 'view', 'export'],
    projects: ['create', 'edit', 'delete', 'view'],
    users: [],
    directions: ['create', 'edit', 'delete', 'view'],
    form_f1: ['view', 'download'],
    files: [],
  },
  [ROLES.VISADOR]: {
    dashboard: ['view'],
    purchase_plans: ['view', 'visar', 'reject', 'export'],
    projects: ['create', 'edit', 'delete', 'view'],
    form_f1: ['view', 'download'],
    users: [],
    files: [],
  },
  [ROLES.ENCARGADO_PRESUPUESTOS]: {
    form_f1: ['create', 'edit', 'delete', 'view', 'upload', 'download', 'remove'],
    users: [],
    files: [],
  },
  [ROLES.SUBROGANTE_ENCARGADO]: {
    form_f1: ['create', 'edit', 'delete', 'view', 'upload', 'download', 'remove'],
    users: [],
    files: [],
  },
  [ROLES.DIRECTOR]: {
    dashboard: ['view'],
    purchase_plans: ['create', 'edit', 'view', 'send', 'export'],
    projects: ['create', 'edit', 'delete', 'view', 'verify'],
    form_f1: ['view', 'download'],
    files: [],
    users: []
  },
  [ROLES.SUBROGANTE_DIRECTOR]: {
    dashboard: ['view'],
    purchase_plans: ['create', 'edit', 'view', 'send', 'export'],
    projects: ['create', 'edit', 'view', 'verify'],
    form_f1: ['view', 'download'],
    files: [],
    users: []
  },
  [ROLES.JEFATURA]: {
    dashboard: ['view'],
    purchase_plans: ['view'],
    projects: ['create', 'edit', 'view', 'verify'],
    form_f1: [],
    files: [],
    users: []
  },
  [ROLES.SUBROGANTE_JEFATURA]: {
    dashboard: ['view'],
    purchase_plans: ['view'],
    projects: ['create', 'edit', 'view', 'verify'],
    form_f1: [],
    files: [],
    users: []
  }
} as const;
```

---

## 🛡️ GUARDS DE RUTAS

### **1. Guard de Autenticación**
```typescript
// guards/auth.guard.ts
import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(): boolean {
    if (this.authService.isAuthenticated()) {
      return true;
    }
    
    this.router.navigate(['/login']);
    return false;
  }
}
```

### **2. Guard de Roles**
```typescript
// guards/role.guard.ts
import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { MODULE_ACCESS } from '../constants/modules';

@Injectable({
  providedIn: 'root'
})
export class RoleGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  canActivate(route: ActivatedRouteSnapshot): boolean {
    const userRole = this.authService.getUserRole();
    const requiredModule = route.data['module'];
    
    if (!requiredModule) {
      return true;
    }

    const hasAccess = this.hasModuleAccess(userRole, requiredModule);
    
    if (!hasAccess) {
      this.router.navigate(['/unauthorized']);
      return false;
    }

    return true;
  }

  private hasModuleAccess(role: string, module: string): boolean {
    const allowedModules = MODULE_ACCESS[role] || [];
    return allowedModules.includes(module);
  }
}
```

### **3. Configuración de Rutas**
```typescript
// app-routing.module.ts
import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from './guards/auth.guard';
import { RoleGuard } from './guards/role.guard';

const routes: Routes = [
  {
    path: 'dashboard',
    component: DashboardComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { module: 'dashboard' }
  },
  {
    path: 'purchase-plans',
    component: PurchasePlansComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { module: 'purchase_plans' }
  },
  {
    path: 'projects',
    component: ProjectsComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { module: 'projects' }
  },
  {
    path: 'form-f1',
    component: FormF1Component,
    canActivate: [AuthGuard, RoleGuard],
    data: { module: 'form_f1' }
  },
  {
    path: 'users',
    component: UsersComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { module: 'users' }
  },
  {
    path: 'files',
    component: FilesComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { module: 'files' }
  },
  {
    path: 'directions',
    component: DirectionsComponent,
    canActivate: [AuthGuard, RoleGuard],
    data: { module: 'directions' }
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
```

---

## 🧩 COMPONENTES CONDICIONALES

### **1. Servicio de Permisos**
```typescript
// services/permissions.service.ts
import { Injectable } from '@angular/core';
import { AuthService } from './auth.service';
import { MODULE_PERMISSIONS } from '../constants/permissions';

@Injectable({
  providedIn: 'root'
})
export class PermissionsService {
  constructor(private authService: AuthService) {}

  hasPermission(module: string, permission: string): boolean {
    const userRole = this.authService.getUserRole();
    const modulePermissions = MODULE_PERMISSIONS[userRole];
    
    if (!modulePermissions || !modulePermissions[module]) {
      return false;
    }

    return modulePermissions[module].includes(permission);
  }

  hasModuleAccess(module: string): boolean {
    const userRole = this.authService.getUserRole();
    const modulePermissions = MODULE_PERMISSIONS[userRole];
    
    return !!modulePermissions && !!modulePermissions[module];
  }

  getModulePermissions(module: string): string[] {
    const userRole = this.authService.getUserRole();
    const modulePermissions = MODULE_PERMISSIONS[userRole];
    
    return modulePermissions?.[module] || [];
  }
}
```

### **2. Directiva de Permisos**
```typescript
// directives/permission.directive.ts
import { Directive, Input, TemplateRef, ViewContainerRef } from '@angular/core';
import { PermissionsService } from '../services/permissions.service';

@Directive({
  selector: '[appPermission]'
})
export class PermissionDirective {
  private hasView = false;

  @Input() set appPermission(permission: string) {
    const hasPermission = this.permissionsService.hasPermission(
      this.getModuleFromRoute(), 
      permission
    );

    if (hasPermission && !this.hasView) {
      this.viewContainer.createEmbeddedView(this.templateRef);
      this.hasView = true;
    } else if (!hasPermission && this.hasView) {
      this.viewContainer.clear();
      this.hasView = false;
    }
  }

  constructor(
    private templateRef: TemplateRef<any>,
    private viewContainer: ViewContainerRef,
    private permissionsService: PermissionsService
  ) {}

  private getModuleFromRoute(): string {
    // Implementar lógica para obtener el módulo actual
    return 'purchase_plans'; // Ejemplo
  }
}
```

### **3. Componente de Navegación**
```typescript
// components/navigation/navigation.component.ts
import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth.service';
import { PermissionsService } from '../../services/permissions.service';
import { MODULE_ACCESS } from '../../constants/modules';

@Component({
  selector: 'app-navigation',
  template: `
    <nav>
      <ul>
        <li *ngFor="let module of availableModules">
          <a [routerLink]="['/' + module.route]" 
             [routerLinkActive]="'active'">
            {{ module.label }}
          </a>
        </li>
      </ul>
    </nav>
  `
})
export class NavigationComponent implements OnInit {
  availableModules: any[] = [];

  constructor(
    private authService: AuthService,
    private permissionsService: PermissionsService
  ) {}

  ngOnInit() {
    this.loadAvailableModules();
  }

  private loadAvailableModules() {
    const userRole = this.authService.getUserRole();
    const modules = MODULE_ACCESS[userRole] || [];

    const moduleConfig = {
      dashboard: { route: 'dashboard', label: 'Dashboard' },
      purchase_plans: { route: 'purchase-plans', label: 'Planes de Compra' },
      projects: { route: 'projects', label: 'Proyectos' },
      form_f1: { route: 'form-f1', label: 'Formulario F1' },
      users: { route: 'users', label: 'Usuarios' },
      files: { route: 'files', label: 'Archivos' },
      directions: { route: 'directions', label: 'Direcciones' }
    };

    this.availableModules = modules
      .filter(module => moduleConfig[module])
      .map(module => moduleConfig[module]);
  }
}
```

---

## 🔐 SERVICIOS DE AUTENTICACIÓN

### **1. Servicio de Autenticación**
```typescript
// services/auth.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../environments/environment';

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  permissions: string[];
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject: BehaviorSubject<User | null>;
  public currentUser: Observable<User | null>;

  constructor(private http: HttpClient) {
    this.currentUserSubject = new BehaviorSubject<User | null>(
      JSON.parse(localStorage.getItem('currentUser') || 'null')
    );
    this.currentUser = this.currentUserSubject.asObservable();
  }

  public get currentUserValue(): User | null {
    return this.currentUserSubject.value;
  }

  login(email: string, password: string): Observable<User> {
    return this.http.post<any>(`${environment.apiUrl}/auth/login`, { email, password })
      .pipe(map(response => {
        const user = response.user;
        localStorage.setItem('currentUser', JSON.stringify(user));
        localStorage.setItem('token', response.token);
        this.currentUserSubject.next(user);
        return user;
      }));
  }

  logout() {
    localStorage.removeItem('currentUser');
    localStorage.removeItem('token');
    this.currentUserSubject.next(null);
  }

  isAuthenticated(): boolean {
    return !!this.currentUserValue;
  }

  getUserRole(): string {
    return this.currentUserValue?.role || '';
  }

  getUserPermissions(): string[] {
    return this.currentUserValue?.permissions || [];
  }

  hasPermission(permission: string): boolean {
    const permissions = this.getUserPermissions();
    return permissions.includes(permission);
  }
}
```

### **2. Interceptor HTTP**
```typescript
// interceptors/auth.interceptor.ts
import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = localStorage.getItem('token');
    
    if (token) {
      request = request.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`
        }
      });
    }

    return next.handle(request);
  }
}
```

---

## 🎨 DIRECTIVAS PERSONALIZADAS

### **1. Directiva de Módulo**
```typescript
// directives/module.directive.ts
import { Directive, Input, TemplateRef, ViewContainerRef } from '@angular/core';
import { PermissionsService } from '../services/permissions.service';

@Directive({
  selector: '[appModule]'
})
export class ModuleDirective {
  private hasView = false;

  @Input() set appModule(module: string) {
    const hasAccess = this.permissionsService.hasModuleAccess(module);

    if (hasAccess && !this.hasView) {
      this.viewContainer.createEmbeddedView(this.templateRef);
      this.hasView = true;
    } else if (!hasAccess && this.hasView) {
      this.viewContainer.clear();
      this.hasView = false;
    }
  }

  constructor(
    private templateRef: TemplateRef<any>,
    private viewContainer: ViewContainerRef,
    private permissionsService: PermissionsService
  ) {}
}
```