# 📊 Gestión de Gastos Personales

Una aplicación web moderna y minimalista para gestionar tus finanzas personales, con seguimiento de gastos e ingresos, presupuesto mensual y visualización en calendario.

## 🚀 Características

### ✨ Funcionalidades Principales

- **Dashboard Interactivo**: Visualización completa de tu situación financiera
- **Calendario Mensual**: Ve tus gastos e ingresos día por día
- **Gestión de Presupuesto**: Configura y monitorea tu presupuesto mensual
- **Registro de Movimientos**: Añade gastos e ingresos con descripción y categoría
- **Análisis por Categorías**: Visualiza tus gastos organizados por categoría
- **Barra de Progreso**: Monitorea en tiempo real el uso de tu presupuesto
- **Filtros Inteligentes**: Filtra por tipo de movimiento (gastos/ingresos)
- **Notificaciones Toast**: Feedback visual para todas las acciones
- **Totalmente Responsive**: Funciona perfectamente en móviles, tablets y desktop

### 🎨 Diseño

- Estilo minimalista y moderno
- Paleta de colores suaves y profesional
- Animaciones sutiles y transiciones fluidas
- Iconos Font Awesome para mejor UX
- Tipografía Inter para máxima legibilidad

## 🛠️ Tecnologías Utilizadas

### Frontend
- **HTML5**: Estructura semántica
- **TailwindCSS**: Framework CSS utility-first
- **JavaScript**: Vanilla JS (sin frameworks)
- **Font Awesome**: Iconografía

### Backend
- **PHP 7+**: PHP nativo sin frameworks
- **MySQL**: Base de datos relacional

## 📁 Estructura del Proyecto

```
gestion_gastos/
│
├── backend/
│   ├── config.php           # Configuración de BD y funciones globales
│   ├── movimientos.php      # API CRUD de movimientos
│   ├── presupuesto.php      # API gestión de presupuesto
│   └── estadisticas.php     # API estadísticas y análisis
│
├── frontend/
│   ├── index.html           # Página principal
│   └── app.js               # Lógica de la aplicación
│
├── database/
│   └── schema.sql           # Script de creación de BD
│
├── assets/                  # Recursos estáticos (futuro)
│
└── README.md               # Este archivo
```

## 🔧 Instalación

### Requisitos Previos

- XAMPP (o cualquier stack LAMP/WAMP)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Navegador web moderno

### Pasos de Instalación

1. **Clonar o descargar el proyecto** en la carpeta de XAMPP:
   ```
   C:\xampp\htdocs\gestion_gastos\
   ```

2. **Iniciar XAMPP**:
   - Iniciar Apache
   - Iniciar MySQL

3. **Crear la base de datos**:
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Crear nueva base de datos: `gestion_gastos`
   - Importar el archivo: `database/schema.sql`
   
   O ejecutar desde línea de comandos:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

4. **Configurar la conexión** (si es necesario):
   - Editar `backend/config.php`
   - Ajustar credenciales de MySQL:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'gestion_gastos');
     ```

5. **Acceder a la aplicación**:
   ```
   http://localhost/gestion_gastos/frontend/
   ```

## 📖 Guía de Uso

### 1. Configurar Presupuesto Mensual

- Haz clic en "Configurar Presupuesto"
- Ingresa el monto total para el mes
- El sistema calculará automáticamente el presupuesto diario

### 2. Registrar Movimientos

- Haz clic en "Nuevo Movimiento"
- Selecciona el tipo (Gasto o Ingreso)
- Ingresa:
  - Monto
  - Fecha
  - Categoría
  - Descripción
- Guarda el movimiento

### 3. Visualizar el Calendario

- El calendario muestra cada día del mes
- Cada día indica:
  - Gastos del día (en rojo)
  - Ingresos del día (en verde)
  - Saldo disponible (en azul)
- Los días con movimientos se resaltan con colores:
  - Verde: Buen saldo
  - Amarillo: Saldo moderado
  - Rojo: Sobregasto

### 4. Analizar Gastos

- Visualiza el resumen en las tarjetas superiores
- Revisa la barra de progreso del presupuesto
- Consulta gastos por categoría en la sección inferior
- Filtra movimientos recientes por tipo

### 5. Editar o Eliminar Movimientos

- Haz clic en un movimiento para editarlo
- Usa el ícono de papelera para eliminarlo
- Los cambios se reflejan inmediatamente en el dashboard

## 🎯 Características Avanzadas

### Cálculo Inteligente de Presupuesto

El sistema calcula automáticamente:
- Presupuesto diario = Presupuesto total / Días del mes
- Presupuesto esperado gastado = Presupuesto diario × Días transcurridos
- Diferencia = Presupuesto esperado - Gastos reales

### Indicadores Visuales

- **Barra de Progreso**: 
  - Verde: < 70% gastado
  - Amarillo: 70-90% gastado
  - Rojo: > 90% gastado

- **Calendario**:
  - Borde verde: Saldo saludable
  - Borde amarillo: Saldo moderado
  - Borde rojo: Sobregasto

### Estadísticas por Categoría

- Visualiza qué categorías consumen más presupuesto
- Porcentaje del total gastado por categoría
- Número de movimientos por categoría

## 🔌 API Endpoints

### Presupuesto

```
GET  /backend/presupuesto.php?usuario_id=1&mes=10&anio=2025
POST /backend/presupuesto.php
```

### Movimientos

```
GET    /backend/movimientos.php?usuario_id=1&mes=10&anio=2025
POST   /backend/movimientos.php
PUT    /backend/movimientos.php
DELETE /backend/movimientos.php
```

### Estadísticas

```
GET /backend/estadisticas.php?usuario_id=1&mes=10&anio=2025
```

## 🎨 Personalización

### Cambiar Colores

Edita las clases de Tailwind en `frontend/index.html`:

```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#6366f1',    // Color primario
                secondary: '#8b5cf6',   // Color secundario
                success: '#10b981',     // Verde
                danger: '#ef4444',      // Rojo
                warning: '#f59e0b',     // Amarillo
            }
        }
    }
}
```

### Agregar Categorías

Edita las opciones en el select de categorías en `index.html` y actualiza los iconos en `app.js`:

```javascript
const ICONOS_CATEGORIAS = {
    'comida': '🍔',
    'nueva_categoria': '🎯',  // Nueva categoría
    // ... más categorías
};
```

## 🐛 Solución de Problemas

### Error de conexión a la base de datos

- Verifica que MySQL esté corriendo
- Revisa las credenciales en `backend/config.php`
- Asegúrate de que la base de datos existe

### Las páginas no cargan

- Verifica que Apache esté corriendo en XAMPP
- Comprueba la URL: `http://localhost/gestion_gastos/frontend/`
- Revisa los logs de error de Apache

### Los datos no se guardan

- Abre la consola del navegador (F12) para ver errores
- Verifica que las rutas de las APIs sean correctas
- Comprueba permisos de archivos y carpetas

## 🚀 Mejoras Futuras

- [ ] Sistema de autenticación de usuarios
- [ ] Exportación de reportes a PDF/Excel
- [ ] Gráficos más avanzados (Charts.js)
- [ ] Comparación entre meses
- [ ] Modo oscuro completo
- [ ] Notificaciones por email
- [ ] Metas de ahorro
- [ ] Sincronización en la nube
- [ ] App móvil (PWA)

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la Licencia MIT.

## 👤 Autor

Desarrollado con ❤️ para ayudarte a controlar tus finanzas personales.

---

¿Tienes preguntas o sugerencias? ¡Abre un issue o contribuye al proyecto!
