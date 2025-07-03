# 🧩 Componentes de Interfaz - Sistema de Modificaciones

## 📋 Componentes Principales

### 1. **ModificationListComponent**

```typescript
// modification-list.component.ts
@Component({
  selector: 'app-modification-list',
  template: `
    <div class="modification-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>📋 Modificaciones</h1>
        <button class="btn-primary" (click)="createModification()">
          ➕ Nueva Modificación
        </button>
      </div>

      <!-- Estadísticas -->
      <div class="stats-grid">
        <div class="stat-card total">
          <span class="stat-number">{{stats.total}}</span>
          <span class="stat-label">Total</span>
        </div>
        <div class="stat-card pending">
          <span class="stat-number">{{stats.pending}}</span>
          <span class="stat-label">Pendientes</span>
        </div>
        <div class="stat-card approved">
          <span class="stat-number">{{stats.approved}}</span>
          <span class="stat-label">Aprobadas</span>
        </div>
        <div class="stat-card rejected">
          <span class="stat-number">{{stats.rejected}}</span>
          <span class="stat-label">Rechazadas</span>
        </div>
      </div>

      <!-- Filtros -->
      <div class="filters-section">
        <div class="search-box">
          <input type="text" placeholder="🔍 Buscar modificaciones..." 
                 [(ngModel)]="searchTerm" (input)="onSearch()">
        </div>
        <div class="filter-controls">
          <select [(ngModel)]="selectedType" (change)="onFilterChange()">
            <option value="">Todos los tipos</option>
            <option *ngFor="let type of modificationTypes" [value]="type.id">
              {{type.name}}
            </option>
          </select>
          <select [(ngModel)]="selectedStatus" (change)="onFilterChange()">
            <option value="">Todos los estados</option>
            <option value="pending">⏳ Pendiente</option>
            <option value="approved">✅ Aprobada</option>
            <option value="rejected">❌ Rechazada</option>
          </select>
        </div>
      </div>

      <!-- Tabla -->
      <div class="table-container">
        <table class="modification-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Tipo</th>
              <th>Motivo</th>
              <th>Plan</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Impacto</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr *ngFor="let modification of modifications">
              <td>{{modification.modification_number}}</td>
              <td>
                <span class="type-badge" [class]="modification.type_class">
                  {{modification.type_name}}
                </span>
              </td>
              <td>{{modification.reason}}</td>
              <td>{{modification.purchase_plan_name}}</td>
              <td>
                <span class="status-badge" [class]="modification.status">
                  {{modification.status_label}}
                </span>
              </td>
              <td>{{modification.date | date:'dd/MM'}}</td>
              <td class="budget-impact" [class]="modification.impact_class">
                {{modification.budget_impact | currency}}
              </td>
              <td class="actions">
                <button class="btn-icon" (click)="viewModification(modification.id)">
                  👁️
                </button>
                <button class="btn-icon" (click)="editModification(modification.id)" 
                        *ngIf="canEdit(modification)">
                  📝
                </button>
                <button class="btn-icon" (click)="deleteModification(modification.id)"
                        *ngIf="canDelete(modification)">
                  🗑️
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="pagination">
        <button [disabled]="currentPage === 1" (click)="previousPage()">◀️</button>
        <span *ngFor="let page of pages" 
              [class.active]="page === currentPage"
              (click)="goToPage(page)">
          {{page}}
        </span>
        <button [disabled]="currentPage === totalPages" (click)="nextPage()">▶️</button>
      </div>
    </div>
  `
})
export class ModificationListComponent {
  modifications: Modification[] = [];
  stats = { total: 0, pending: 0, approved: 0, rejected: 0 };
  currentPage = 1;
  totalPages = 1;
  searchTerm = '';
  selectedType = '';
  selectedStatus = '';

  constructor(private modificationService: ModificationService) {}

  ngOnInit() {
    this.loadModifications();
    this.loadStats();
  }

  loadModifications() {
    this.modificationService.getAllModifications(
      this.currentPage, 
      this.searchTerm, 
      this.selectedType, 
      this.selectedStatus
    ).subscribe(response => {
      this.modifications = response.data;
      this.totalPages = response.last_page;
    });
  }

  onSearch() {
    this.currentPage = 1;
    this.loadModifications();
  }

  onFilterChange() {
    this.currentPage = 1;
    this.loadModifications();
  }
}
```

### 2. **ModificationFormComponent**

```typescript
// modification-form.component.ts
@Component({
  selector: 'app-modification-form',
  template: `
    <div class="modification-form">
      <div class="form-header">
        <h2>{{isEditing ? '📝 Editar' : '➕ Crear'}} Modificación</h2>
      </div>

      <form [formGroup]="modificationForm" (ngSubmit)="onSubmit()">
        <!-- Información Básica -->
        <div class="form-section">
          <h3>📋 Información Básica</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label>Plan de Compra *</label>
              <select formControlName="purchase_plan_id" 
                      [class.error]="hasError('purchase_plan_id')">
                <option value="">Seleccionar plan...</option>
                <option *ngFor="let plan of purchasePlans" [value]="plan.id">
                  {{plan.name}} - {{plan.direction.name}}
                </option>
              </select>
              <span class="error-message" *ngIf="hasError('purchase_plan_id')">
                {{getErrorMessage('purchase_plan_id')}}
              </span>
            </div>

            <div class="form-group">
              <label>Tipo de Modificación *</label>
              <select formControlName="modification_type_id"
                      [class.error]="hasError('modification_type_id')">
                <option value="">Seleccionar tipo...</option>
                <option *ngFor="let type of modificationTypes" [value]="type.id">
                  {{type.name}}
                </option>
              </select>
              <span class="error-message" *ngIf="hasError('modification_type_id')">
                {{getErrorMessage('modification_type_id')}}
              </span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Fecha *</label>
              <input type="date" formControlName="date"
                     [class.error]="hasError('date')">
              <span class="error-message" *ngIf="hasError('date')">
                {{getErrorMessage('date')}}
              </span>
            </div>

            <div class="form-group">
              <label>Número de Modificación</label>
              <input type="text" formControlName="modification_number" readonly>
              <small>Auto-generado</small>
            </div>
          </div>
        </div>

        <!-- Detalles -->
        <div class="form-section">
          <h3>📝 Detalles de la Modificación</h3>
          
          <div class="form-group">
            <label>Motivo Principal *</label>
            <textarea formControlName="reason" rows="3" 
                      placeholder="Describa el motivo principal de la modificación..."
                      [class.error]="hasError('reason')"></textarea>
            <span class="error-message" *ngIf="hasError('reason')">
              {{getErrorMessage('reason')}}
            </span>
          </div>

          <div class="form-group">
            <label>Descripción Detallada *</label>
            <textarea formControlName="description" rows="4"
                      placeholder="Describa detalladamente los cambios..."
                      [class.error]="hasError('description')"></textarea>
            <span class="error-message" *ngIf="hasError('description')">
              {{getErrorMessage('description')}}
            </span>
          </div>

          <div class="form-group">
            <label>Justificación Técnica *</label>
            <textarea formControlName="justification" rows="4"
                      placeholder="Justifique técnicamente la necesidad de la modificación..."
                      [class.error]="hasError('justification')"></textarea>
            <span class="error-message" *ngIf="hasError('justification')">
              {{getErrorMessage('justification')}}
            </span>
          </div>
        </div>

        <!-- Impacto Presupuestario -->
        <div class="form-section">
          <h3>💰 Impacto Presupuestario</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label>Impacto ($)</label>
              <input type="number" formControlName="budget_impact" 
                     step="0.01" placeholder="0.00"
                     [class.error]="hasError('budget_impact')">
              <span class="error-message" *ngIf="hasError('budget_impact')">
                {{getErrorMessage('budget_impact')}}
              </span>
            </div>

            <div class="form-group">
              <label>Tipo de Impacto</label>
              <select formControlName="impact_type">
                <option value="increase">➕ Incremento</option>
                <option value="decrease">➖ Decremento</option>
                <option value="none">🔄 Sin cambio</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Archivos -->
        <div class="form-section">
          <h3>📎 Documentos de Respaldo</h3>
          
          <div class="file-upload-area" 
               (dragover)="onDragOver($event)" 
               (drop)="onDrop($event)"
               (click)="fileInput.click()">
            <div class="upload-content">
              <span class="upload-icon">📁</span>
              <p>Haga clic o arrastre archivos aquí</p>
              <small>PDF, DOC, XLS, JPG, PNG (máx. 10MB cada uno)</small>
            </div>
            <input #fileInput type="file" multiple 
                   (change)="onFileSelect($event)" 
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" 
                   style="display: none;">
          </div>

          <div class="file-list" *ngIf="selectedFiles.length > 0">
            <div class="file-item" *ngFor="let file of selectedFiles; let i = index">
              <span class="file-icon">📄</span>
              <span class="file-name">{{file.name}}</span>
              <span class="file-size">{{file.size | fileSize}}</span>
              <button type="button" class="btn-remove" (click)="removeFile(i)">
                🗑️
              </button>
            </div>
          </div>
        </div>

        <!-- Botones -->
        <div class="form-actions">
          <button type="button" class="btn-secondary" (click)="onCancel()">
            ❌ Cancelar
          </button>
          <button type="button" class="btn-secondary" (click)="saveDraft()" 
                  [disabled]="modificationForm.invalid">
            💾 Guardar Borrador
          </button>
          <button type="submit" class="btn-primary" 
                  [disabled]="modificationForm.invalid || isSubmitting">
            {{isSubmitting ? '⏳ Enviando...' : '📤 Enviar para Aprobación'}}
          </button>
        </div>
      </form>
    </div>
  `
})
export class ModificationFormComponent {
  modificationForm: FormGroup;
  isEditing = false;
  isSubmitting = false;
  selectedFiles: File[] = [];

  constructor(
    private fb: FormBuilder,
    private modificationService: ModificationService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.createForm();
  }

  createForm() {
    this.modificationForm = this.fb.group({
      purchase_plan_id: ['', Validators.required],
      modification_type_id: ['', Validators.required],
      date: ['', Validators.required],
      modification_number: [''],
      reason: ['', [Validators.required, Validators.minLength(10)]],
      description: ['', [Validators.required, Validators.minLength(20)]],
      justification: ['', [Validators.required, Validators.minLength(20)]],
      budget_impact: [0, [Validators.required, Validators.min(0)]],
      impact_type: ['increase']
    });
  }

  onSubmit() {
    if (this.modificationForm.valid) {
      this.isSubmitting = true;
      const formData = this.modificationForm.value;
      
      // Agregar archivos al FormData
      const submitData = new FormData();
      Object.keys(formData).forEach(key => {
        submitData.append(key, formData[key]);
      });
      
      this.selectedFiles.forEach(file => {
        submitData.append('files[]', file);
      });

      const request = this.isEditing 
        ? this.modificationService.updateModification(this.modificationId, submitData)
        : this.modificationService.createModification(submitData);

      request.subscribe({
        next: (response) => {
          this.showSuccess('Modificación guardada exitosamente');
          this.router.navigate(['/modifications', response.id]);
        },
        error: (error) => {
          this.showError('Error al guardar la modificación');
          this.isSubmitting = false;
        }
      });
    }
  }
}
```

### 3. **ModificationDetailComponent**

```typescript
// modification-detail.component.ts
@Component({
  selector: 'app-modification-detail',
  template: `
    <div class="modification-detail" *ngIf="modification">
      <!-- Header -->
      <div class="detail-header">
        <div class="header-info">
          <h1>👁️ Modificación #{{modification.modification_number}}</h1>
          <p class="plan-info">{{modification.purchase_plan_name}}</p>
        </div>
        <div class="header-actions">
          <button class="btn-secondary" (click)="printModification()">
            📄 Imprimir
          </button>
          <button class="btn-primary" (click)="editModification()" 
                  *ngIf="canEdit()">
            📝 Editar
          </button>
        </div>
      </div>

      <!-- Estado y Información General -->
      <div class="status-section">
        <div class="status-badge" [class]="modification.status">
          {{modification.status_label}}
        </div>
        <div class="status-info">
          <p><strong>Creada:</strong> {{modification.created_at | date:'dd/MM/yyyy HH:mm'}}</p>
          <p><strong>Por:</strong> {{modification.created_by_name}}</p>
          <p *ngIf="modification.approved_at">
            <strong>Aprobada:</strong> {{modification.approved_at | date:'dd/MM/yyyy HH:mm'}}
          </p>
          <p *ngIf="modification.rejected_at">
            <strong>Rechazada:</strong> {{modification.rejected_at | date:'dd/MM/yyyy HH:mm'}}
          </p>
        </div>
      </div>

      <!-- Detalles -->
      <div class="detail-grid">
        <div class="detail-card">
          <h3>📋 Información General</h3>
          <div class="info-list">
            <div class="info-item">
              <span class="label">Tipo:</span>
              <span class="value type-badge" [class]="modification.type_class">
                {{modification.type_name}}
              </span>
            </div>
            <div class="info-item">
              <span class="label">Fecha:</span>
              <span class="value">{{modification.date | date:'dd/MM/yyyy'}}</span>
            </div>
            <div class="info-item">
              <span class="label">Plan de Compra:</span>
              <span class="value">{{modification.purchase_plan_name}}</span>
            </div>
            <div class="info-item">
              <span class="label">Dirección:</span>
              <span class="value">{{modification.direction_name}}</span>
            </div>
          </div>
        </div>

        <div class="detail-card">
          <h3>💰 Impacto Presupuestario</h3>
          <div class="budget-info">
            <div class="budget-item">
              <span class="label">Monto:</span>
              <span class="value budget-impact" [class]="modification.impact_class">
                {{modification.budget_impact | currency}}
              </span>
            </div>
            <div class="budget-item">
              <span class="label">Presupuesto Original:</span>
              <span class="value">{{modification.original_budget | currency}}</span>
            </div>
            <div class="budget-item">
              <span class="label">Nuevo Presupuesto:</span>
              <span class="value">{{modification.new_budget | currency}}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido -->
      <div class="content-section">
        <div class="content-card">
          <h3>📝 Detalles de la Modificación</h3>
          <div class="content-item">
            <h4>Motivo Principal</h4>
            <p>{{modification.reason}}</p>
          </div>
          <div class="content-item">
            <h4>Descripción Detallada</h4>
            <p>{{modification.description}}</p>
          </div>
          <div class="content-item">
            <h4>Justificación Técnica</h4>
            <p>{{modification.justification}}</p>
          </div>
        </div>
      </div>

      <!-- Archivos -->
      <div class="files-section" *ngIf="modification.files?.length">
        <div class="content-card">
          <h3>📎 Documentos Adjuntos</h3>
          <div class="files-grid">
            <div class="file-card" *ngFor="let file of modification.files">
              <div class="file-icon">📄</div>
              <div class="file-info">
                <h4>{{file.name}}</h4>
                <p>{{file.size | fileSize}} • {{file.uploaded_at | date:'dd/MM/yyyy'}}</p>
              </div>
              <div class="file-actions">
                <button class="btn-icon" (click)="downloadFile(file)">
                  ⬇️
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Historial -->
      <div class="history-section">
        <div class="content-card">
          <h3>📜 Historial de Acciones</h3>
          <div class="timeline">
            <div class="timeline-item" *ngFor="let action of modification.history">
              <div class="timeline-icon" [class]="action.action_class">
                {{action.action_icon}}
              </div>
              <div class="timeline-content">
                <h4>{{action.description}}</h4>
                <p>{{action.user_name}} • {{action.date | date:'dd/MM/yyyy HH:mm'}}</p>
                <p *ngIf="action.details" class="action-details">
                  {{action.details}}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Acciones -->
      <div class="actions-section" *ngIf="canPerformActions()">
        <div class="action-buttons">
          <button class="btn-success" (click)="approveModification()" 
                  *ngIf="canApprove()">
            ✅ Aprobar
          </button>
          <button class="btn-danger" (click)="rejectModification()" 
                  *ngIf="canReject()">
            ❌ Rechazar
          </button>
        </div>
      </div>
    </div>
  `
})
export class ModificationDetailComponent {
  modification: Modification | null = null;
  modificationId: number;

  constructor(
    private modificationService: ModificationService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    this.modificationId = +this.route.snapshot.paramMap.get('id')!;
  }

  ngOnInit() {
    this.loadModification();
  }

  loadModification() {
    this.modificationService.getModificationById(this.modificationId)
      .subscribe({
        next: (modification) => {
          this.modification = modification;
        },
        error: (error) => {
          this.showError('Error al cargar la modificación');
          this.router.navigate(['/modifications']);
        }
      });
  }

  canEdit(): boolean {
    return this.modification?.status === 'pending' && 
           this.hasPermission('modifications.edit');
  }

  canApprove(): boolean {
    return this.modification?.status === 'pending' && 
           this.hasPermission('modifications.approve');
  }

  canReject(): boolean {
    return this.modification?.status === 'pending' && 
           this.hasPermission('modifications.reject');
  }

  approveModification() {
    this.modificationService.approveModification(this.modificationId)
      .subscribe({
        next: () => {
          this.showSuccess('Modificación aprobada exitosamente');
          this.loadModification();
        },
        error: (error) => {
          this.showError('Error al aprobar la modificación');
        }
      });
  }

  rejectModification() {
    // Abrir modal de rechazo
    this.openRejectModal();
  }
}
```

## 🎨 Estilos CSS

### **Variables CSS**
```css
:root {
  /* Colores principales */
  --primary-color: #1e40af;
  --primary-light: #3b82f6;
  --primary-dark: #1e3a8a;
  
  /* Estados */
  --success-color: #059669;
  --success-light: #10b981;
  --danger-color: #dc2626;
  --danger-light: #ef4444;
  --warning-color: #d97706;
  --warning-light: #f59e0b;
  --info-color: #3b82f6;
  
  /* Grises */
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  
  /* Tipografía */
  --font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-size-xs: 0.75rem;
  --font-size-sm: 0.875rem;
  --font-size-base: 1rem;
  --font-size-lg: 1.125rem;
  --font-size-xl: 1.25rem;
  --font-size-2xl: 1.5rem;
  
  /* Espaciado */
  --spacing-1: 0.25rem;
  --spacing-2: 0.5rem;
  --spacing-3: 0.75rem;
  --spacing-4: 1rem;
  --spacing-5: 1.25rem;
  --spacing-6: 1.5rem;
  --spacing-8: 2rem;
  --spacing-10: 2.5rem;
  --spacing-12: 3rem;
  
  /* Bordes */
  --border-radius: 0.375rem;
  --border-radius-lg: 0.5rem;
  --border-radius-xl: 0.75rem;
  
  /* Sombras */
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}
```

### **Componentes Base**
```css
/* Botones */
.btn-primary {
  background-color: var(--primary-color);
  color: white;
  padding: var(--spacing-3) var(--spacing-6);
  border: none;
  border-radius: var(--border-radius);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-secondary {
  background-color: var(--gray-100);
  color: var(--gray-700);
  border: 1px solid var(--gray-300);
  padding: var(--spacing-3) var(--spacing-6);
  border-radius: var(--border-radius);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background-color: var(--gray-200);
}

/* Badges */
.status-badge {
  padding: var(--spacing-1) var(--spacing-3);
  border-radius: var(--border-radius);
  font-size: var(--font-size-sm);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-badge.pending {
  background-color: var(--warning-light);
  color: var(--warning-color);
}

.status-badge.approved {
  background-color: var(--success-light);
  color: var(--success-color);
}

.status-badge.rejected {
  background-color: var(--danger-light);
  color: var(--danger-color);
}

/* Cards */
.card {
  background-color: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow);
  padding: var(--spacing-6);
  border: 1px solid var(--gray-200);
}

.card-header {
  border-bottom: 1px solid var(--gray-200);
  padding-bottom: var(--spacing-4);
  margin-bottom: var(--spacing-6);
}

.card-title {
  font-size: var(--font-size-xl);
  font-weight: 600;
  color: var(--gray-900);
  margin: 0;
}

/* Formularios */
.form-group {
  margin-bottom: var(--spacing-6);
}

.form-label {
  display: block;
  font-weight: 500;
  color: var(--gray-700);
  margin-bottom: var(--spacing-2);
}

.form-input {
  width: 100%;
  padding: var(--spacing-3);
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  font-size: var(--font-size-base);
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgb(59 130 246 / 0.1);
}

.form-input.error {
  border-color: var(--danger-color);
}

.error-message {
  color: var(--danger-color);
  font-size: var(--font-size-sm);
  margin-top: var(--spacing-1);
}

/* Tablas */
.table {
  width: 100%;
  border-collapse: collapse;
  background-color: white;
  border-radius: var(--border-radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow);
}

.table th {
  background-color: var(--gray-50);
  padding: var(--spacing-4);
  text-align: left;
  font-weight: 600;
  color: var(--gray-700);
  border-bottom: 1px solid var(--gray-200);
}

.table td {
  padding: var(--spacing-4);
  border-bottom: 1px solid var(--gray-100);
  color: var(--gray-600);
}

.table tr:hover {
  background-color: var(--gray-50);
}
```

Esta documentación proporciona una base sólida para implementar la interfaz de modificaciones con componentes reutilizables, estilos consistentes y una experiencia de usuario profesional. 