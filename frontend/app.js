/**
 * Gestión de Gastos - Aplicación JavaScript
 */

// Configuración
const API_BASE_URL = '/proyectos/gestion_gastos/Gestion-Gastos/backend';
const USUARIO_ID = 1;

// Estado de la aplicación
let mesActual = new Date().getMonth() + 1;
let anioActual = new Date().getFullYear();
let presupuestoActual = null;
let movimientos = [];
let estadisticas = {};
let tipoSeleccionado = 'gasto';
let filtroActual = 'todos';

// Nombres de meses
const MESES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const DIAS_SEMANA = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

// Iconos de categorías
const ICONOS_CATEGORIAS = {
    'comida': '🍔',
    'transporte': '🚗',
    'ocio': '🎮',
    'salud': '💊',
    'educacion': '📚',
    'vivienda': '🏠',
    'servicios': '💡',
    'compras': '🛍️',
    'trabajo': '💼',
    'otros': '📦'
};

// ============================================================================
// INICIALIZACIÓN
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
    inicializarEventos();
    cargarDatos();
});

function inicializarEventos() {
    // Controles de mes
    document.getElementById('btn-mes-anterior').addEventListener('click', () => cambiarMes(-1));
    document.getElementById('btn-mes-siguiente').addEventListener('click', () => cambiarMes(1));
    document.getElementById('btn-hoy').addEventListener('click', irAHoy);
    
    // Modales
    document.getElementById('btn-presupuesto').addEventListener('click', () => abrirModalPresupuesto());
    document.getElementById('btn-nuevo-movimiento').addEventListener('click', () => abrirModalMovimiento());
    
    document.getElementById('close-modal-presupuesto').addEventListener('click', cerrarModalPresupuesto);
    document.getElementById('cancel-modal-presupuesto').addEventListener('click', cerrarModalPresupuesto);
    
    document.getElementById('close-modal-movimiento').addEventListener('click', cerrarModalMovimiento);
    document.getElementById('cancel-modal-movimiento').addEventListener('click', cerrarModalMovimiento);
    
    // Formularios
    document.getElementById('form-presupuesto').addEventListener('submit', guardarPresupuesto);
    document.getElementById('form-movimiento').addEventListener('submit', guardarMovimiento);
    
    // Botones de tipo de movimiento
    document.querySelectorAll('.tipo-btn').forEach(btn => {
        btn.addEventListener('click', (e) => seleccionarTipo(e.target.closest('.tipo-btn')));
    });
    
    // Filtros
    document.getElementById('filtro-todos').addEventListener('click', () => aplicarFiltro('todos'));
    document.getElementById('filtro-gastos').addEventListener('click', () => aplicarFiltro('gastos'));
    document.getElementById('filtro-ingresos').addEventListener('click', () => aplicarFiltro('ingresos'));
    
    // Cerrar modales al hacer clic fuera
    document.getElementById('modal-presupuesto').addEventListener('click', (e) => {
        if (e.target.id === 'modal-presupuesto') cerrarModalPresupuesto();
    });
    
    document.getElementById('modal-movimiento').addEventListener('click', (e) => {
        if (e.target.id === 'modal-movimiento') cerrarModalMovimiento();
    });
}

// ============================================================================
// CARGA DE DATOS
// ============================================================================

async function cargarDatos() {
    mostrarCargando();
    try {
        await Promise.all([
            cargarPresupuesto(),
            cargarMovimientos(),
            cargarEstadisticas()
        ]);
        
        actualizarUI();
    } catch (error) {
        console.error('Error al cargar datos:', error);
        console.error('Detalles del error:', error.message);
        mostrarToast('Error al cargar los datos. Verifica que el servidor esté funcionando.', 'error');
    }
}

async function cargarPresupuesto() {
    try {
        const response = await fetch(`${API_BASE_URL}/presupuesto.php?usuario_id=${USUARIO_ID}&mes=${mesActual}&anio=${anioActual}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            presupuestoActual = data.data;
        } else {
            presupuestoActual = null;
        }
    } catch (error) {
        console.error('Error al cargar presupuesto:', error);
        console.error('URL:', `${API_BASE_URL}/presupuesto.php?usuario_id=${USUARIO_ID}&mes=${mesActual}&anio=${anioActual}`);
        throw error;
    }
}

async function cargarMovimientos() {
    try {
        const response = await fetch(`${API_BASE_URL}/movimientos.php?usuario_id=${USUARIO_ID}&mes=${mesActual}&anio=${anioActual}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            movimientos = data.data || [];
        } else {
            movimientos = [];
        }
    } catch (error) {
        console.error('Error al cargar movimientos:', error);
        console.error('URL:', `${API_BASE_URL}/movimientos.php?usuario_id=${USUARIO_ID}&mes=${mesActual}&anio=${anioActual}`);
        throw error;
    }
}

async function cargarEstadisticas() {
    try {
        const response = await fetch(`${API_BASE_URL}/estadisticas.php?usuario_id=${USUARIO_ID}&mes=${mesActual}&anio=${anioActual}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            estadisticas = data.data;
        } else {
            estadisticas = {};
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
        console.error('URL:', `${API_BASE_URL}/estadisticas.php?usuario_id=${USUARIO_ID}&mes=${mesActual}&anio=${anioActual}`);
        throw error;
    }
}

// ============================================================================
// ACTUALIZACIÓN DE UI
// ============================================================================

function actualizarUI() {
    actualizarTituloMes();
    actualizarResumenPresupuesto();
    actualizarBarraProgreso();
    actualizarCalendario();
    actualizarListaMovimientos();
    actualizarCategoriasChart();
}

function actualizarTituloMes() {
    const titulo = `${MESES[mesActual - 1]} ${anioActual}`;
    document.getElementById('mes-actual').textContent = titulo;
}

function actualizarResumenPresupuesto() {
    const container = document.getElementById('resumen-presupuesto');
    
    if (!presupuestoActual) {
        container.innerHTML = `
            <div class="md:col-span-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-2xl p-6 text-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-2"></i>
                <p class="text-lg font-semibold text-gray-700">No hay presupuesto configurado</p>
                <p class="text-sm text-gray-600 mt-1">Configura tu presupuesto mensual para comenzar a gestionar tus gastos</p>
                <button onclick="abrirModalPresupuesto()" class="mt-4 px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                    <i class="fas fa-plus mr-2"></i>Configurar Presupuesto
                </button>
            </div>
        `;
        return;
    }
    
    const totalGastos = presupuestoActual.total_gastos || 0;
    const totalIngresos = presupuestoActual.total_ingresos || 0;
    const saldoRestante = presupuestoActual.saldo_restante || 0;
    const presupuestoTotal = presupuestoActual.monto_total || 0;
    
    container.innerHTML = `
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-blue-600">Presupuesto Total</span>
                <i class="fas fa-wallet text-blue-400 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">$${formatearNumero(presupuestoTotal)}</p>
            <p class="text-xs text-gray-500 mt-1">$${formatearNumero(presupuestoActual.presupuesto_diario || 0)} / día</p>
        </div>
        
        <div class="bg-gradient-to-br from-red-50 to-pink-50 border border-red-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-red-600">Total Gastado</span>
                <i class="fas fa-arrow-down text-red-400 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">$${formatearNumero(totalGastos)}</p>
            <p class="text-xs text-gray-500 mt-1">${presupuestoActual.porcentaje_gastado || 0}% del presupuesto</p>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-green-600">Total Ingresos</span>
                <i class="fas fa-arrow-up text-green-400 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">$${formatearNumero(totalIngresos)}</p>
            <p class="text-xs text-gray-500 mt-1">${movimientos.filter(m => m.tipo === 'ingreso').length} ingresos</p>
        </div>
        
        <div class="bg-gradient-to-br ${saldoRestante >= 0 ? 'from-purple-50 to-indigo-50 border-purple-100' : 'from-red-50 to-pink-50 border-red-100'} border rounded-2xl p-6 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium ${saldoRestante >= 0 ? 'text-purple-600' : 'text-red-600'}">Saldo Disponible</span>
                <i class="fas fa-balance-scale ${saldoRestante >= 0 ? 'text-purple-400' : 'text-red-400'} text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">$${formatearNumero(saldoRestante)}</p>
            <p class="text-xs text-gray-500 mt-1">
                ${saldoRestante >= 0 ? 'Saldo positivo' : 'Sobregiro'}
            </p>
        </div>
    `;
}

function actualizarBarraProgreso() {
    const container = document.getElementById('barra-progreso-container');
    
    if (!presupuestoActual) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    
    const porcentaje = Math.min(presupuestoActual.porcentaje_gastado || 0, 100);
    const diferencia = presupuestoActual.diferencia || 0;
    
    let colorBarra = 'bg-green-500';
    let mensaje = 'Vas muy bien con tu presupuesto';
    
    if (porcentaje > 90) {
        colorBarra = 'bg-red-500';
        mensaje = '¡Alerta! Has gastado casi todo tu presupuesto';
    } else if (porcentaje > 70) {
        colorBarra = 'bg-yellow-500';
        mensaje = 'Ten cuidado, ya gastaste la mayor parte de tu presupuesto';
    }
    
    container.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-lg font-semibold text-gray-900">Progreso del Presupuesto</h4>
            <span class="text-sm font-medium ${diferencia >= 0 ? 'text-green-600' : 'text-red-600'}">
                ${diferencia >= 0 ? '▼' : '▲'} $${formatearNumero(Math.abs(diferencia))} ${diferencia >= 0 ? 'bajo' : 'sobre'} lo esperado
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div class="progress-bar ${colorBarra} h-4 rounded-full transition-all duration-500 flex items-center justify-end pr-2" style="width: ${porcentaje}%">
                ${porcentaje > 10 ? `<span class="text-xs font-bold text-white">${porcentaje.toFixed(1)}%</span>` : ''}
            </div>
        </div>
        <p class="text-sm text-gray-600 mt-2">
            <i class="fas fa-info-circle mr-1"></i>${mensaje}
        </p>
    `;
}

function actualizarCalendario() {
    const calendario = document.getElementById('calendario');
    const diasEnMes = new Date(anioActual, mesActual, 0).getDate();
    const primerDia = new Date(anioActual, mesActual - 1, 1).getDay();
    
    let html = '';
    
    // Encabezados de días de la semana
    DIAS_SEMANA.forEach(dia => {
        html += `<div class="text-center font-semibold text-gray-600 text-sm py-2">${dia}</div>`;
    });
    
    // Días vacíos antes del primer día
    for (let i = 0; i < primerDia; i++) {
        html += `<div class="p-2"></div>`;
    }
    
    // Calcular presupuesto diario
    const presupuestoDiario = presupuestoActual ? presupuestoActual.presupuesto_diario : 0;
    
    // Días del mes
    for (let dia = 1; dia <= diasEnMes; dia++) {
        const fecha = `${anioActual}-${String(mesActual).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        const movimientosDia = movimientos.filter(m => m.fecha === fecha);
        
        let gastosDia = 0;
        let ingresosDia = 0;
        movimientosDia.forEach(m => {
            if (m.tipo === 'gasto') {
                gastosDia += parseFloat(m.monto);
            } else {
                ingresosDia += parseFloat(m.monto);
            }
        });
        
        const saldoDia = presupuestoDiario + ingresosDia - gastosDia;
        const esHoy = dia === new Date().getDate() && mesActual === new Date().getMonth() + 1 && anioActual === new Date().getFullYear();
        
        let colorClase = 'border-gray-200';
        if (movimientosDia.length > 0) {
            if (saldoDia >= presupuestoDiario * 0.5) {
                colorClase = 'border-green-300 bg-green-50';
            } else if (saldoDia >= 0) {
                colorClase = 'border-yellow-300 bg-yellow-50';
            } else {
                colorClase = 'border-red-300 bg-red-50';
            }
        }
        
        html += `
            <div class="calendar-day bg-white border-2 ${colorClase} ${esHoy ? 'ring-2 ring-primary' : ''} rounded-xl p-3 cursor-pointer hover:shadow-md transition" 
                 onclick="mostrarDetallesDia('${fecha}')">
                <div class="text-right">
                    <span class="text-sm font-bold ${esHoy ? 'text-primary' : 'text-gray-700'}">${dia}</span>
                </div>
                ${movimientosDia.length > 0 ? `
                    <div class="mt-1 space-y-1">
                        ${gastosDia > 0 ? `<div class="text-xs text-red-600 font-medium">-$${formatearNumero(gastosDia)}</div>` : ''}
                        ${ingresosDia > 0 ? `<div class="text-xs text-green-600 font-medium">+$${formatearNumero(ingresosDia)}</div>` : ''}
                        <div class="text-xs ${saldoDia >= 0 ? 'text-blue-600' : 'text-red-600'} font-bold">
                            $${formatearNumero(saldoDia)}
                        </div>
                    </div>
                ` : `
                    <div class="mt-1">
                        <div class="text-xs text-gray-400">Sin mov.</div>
                    </div>
                `}
            </div>
        `;
    }
    
    calendario.innerHTML = html;
}

function actualizarListaMovimientos() {
    const lista = document.getElementById('lista-movimientos');
    
    let movimientosFiltrados = movimientos;
    
    if (filtroActual === 'gastos') {
        movimientosFiltrados = movimientos.filter(m => m.tipo === 'gasto');
    } else if (filtroActual === 'ingresos') {
        movimientosFiltrados = movimientos.filter(m => m.tipo === 'ingreso');
    }
    
    if (movimientosFiltrados.length === 0) {
        lista.innerHTML = `
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-inbox text-5xl mb-3"></i>
                <p>No hay movimientos registrados</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    movimientosFiltrados.forEach(mov => {
        const icono = ICONOS_CATEGORIAS[mov.categoria] || '📦';
        const esGasto = mov.tipo === 'gasto';
        
        html += `
            <div class="bg-gray-50 hover:bg-gray-100 rounded-xl p-4 transition cursor-pointer border border-gray-200"
                 onclick="editarMovimiento(${mov.id})">
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3 flex-1">
                        <div class="text-2xl">${icono}</div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">${mov.descripcion}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-calendar-alt mr-1"></i>${formatearFecha(mov.fecha)}
                            </p>
                            <span class="inline-block mt-2 px-2 py-1 bg-white rounded text-xs font-medium text-gray-600 border border-gray-200">
                                ${mov.categoria}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold ${esGasto ? 'text-red-600' : 'text-green-600'}">
                            ${esGasto ? '-' : '+'}$${formatearNumero(mov.monto)}
                        </p>
                        <button onclick="event.stopPropagation(); eliminarMovimiento(${mov.id})" 
                                class="mt-2 text-gray-400 hover:text-red-500 transition">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    lista.innerHTML = html;
}

function actualizarCategoriasChart() {
    const container = document.getElementById('categorias-container');
    
    if (!estadisticas.categorias || estadisticas.categorias.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-gray-400">
                <i class="fas fa-chart-pie text-5xl mb-3"></i>
                <p>No hay datos de categorías disponibles</p>
            </div>
        `;
        return;
    }
    
    const categoriasGastos = estadisticas.categorias.filter(c => c.tipo === 'gasto');
    const totalGastos = categoriasGastos.reduce((sum, c) => sum + parseFloat(c.total), 0);
    
    if (categoriasGastos.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-gray-400">
                <p>No hay gastos registrados por categoría</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    categoriasGastos.forEach(cat => {
        const porcentaje = totalGastos > 0 ? (parseFloat(cat.total) / totalGastos * 100) : 0;
        const icono = ICONOS_CATEGORIAS[cat.categoria] || '📦';
        
        html += `
            <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl">${icono}</span>
                        <span class="font-semibold text-gray-800 capitalize">${cat.categoria}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-500">${cat.cantidad} mov.</span>
                </div>
                <div class="mb-2">
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">Total</span>
                        <span class="font-bold text-gray-900">$${formatearNumero(cat.total)}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary to-secondary h-2 rounded-full" style="width: ${porcentaje}%"></div>
                    </div>
                </div>
                <p class="text-xs text-gray-500 text-right">${porcentaje.toFixed(1)}% del total</p>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ============================================================================
// NAVEGACIÓN DE MES
// ============================================================================

async function cambiarMes(direccion) {
    mesActual += direccion;
    
    if (mesActual > 12) {
        mesActual = 1;
        anioActual++;
    } else if (mesActual < 1) {
        mesActual = 12;
        anioActual--;
    }
    
    await cargarDatos();
}

async function irAHoy() {
    const hoy = new Date();
    mesActual = hoy.getMonth() + 1;
    anioActual = hoy.getFullYear();
    await cargarDatos();
}

// ============================================================================
// MODALES
// ============================================================================

function abrirModalPresupuesto() {
    const modal = document.getElementById('modal-presupuesto');
    const input = document.getElementById('input-presupuesto');
    
    if (presupuestoActual) {
        input.value = presupuestoActual.monto_total;
    } else {
        input.value = '';
    }
    
    modal.classList.remove('hidden');
    input.focus();
}

function cerrarModalPresupuesto() {
    document.getElementById('modal-presupuesto').classList.add('hidden');
    document.getElementById('form-presupuesto').reset();
}

function abrirModalMovimiento(movimiento = null) {
    const modal = document.getElementById('modal-movimiento');
    const titulo = document.getElementById('modal-movimiento-titulo');
    const form = document.getElementById('form-movimiento');
    const categoriaContainer = document.getElementById('categoria-container');
    const categoriaInput = document.getElementById('input-categoria');
    
    if (movimiento) {
        titulo.textContent = 'Editar Movimiento';
        document.getElementById('movimiento-id').value = movimiento.id;
        document.getElementById('input-monto').value = movimiento.monto;
        document.getElementById('input-fecha').value = movimiento.fecha;
        document.getElementById('input-descripcion').value = movimiento.descripcion;
        document.getElementById('input-categoria').value = movimiento.categoria;
        
        tipoSeleccionado = movimiento.tipo;
        document.querySelectorAll('.tipo-btn').forEach(btn => {
            btn.classList.remove('bg-danger', 'bg-success', 'text-white', 'border-danger', 'border-success');
            btn.classList.add('border-gray-300', 'text-gray-600');
        });
        
        const btnSeleccionado = document.querySelector(`[data-tipo="${movimiento.tipo}"]`);
        if (movimiento.tipo === 'gasto') {
            btnSeleccionado.classList.add('bg-danger', 'text-white', 'border-danger');
            btnSeleccionado.classList.remove('border-gray-300', 'text-gray-600');
            categoriaContainer.style.display = 'block';
            categoriaInput.setAttribute('required', 'required');
        } else {
            btnSeleccionado.classList.add('bg-success', 'text-white', 'border-success');
            btnSeleccionado.classList.remove('border-gray-300', 'text-gray-600');
            categoriaContainer.style.display = 'none';
            categoriaInput.removeAttribute('required');
        }
    } else {
        titulo.textContent = 'Nuevo Movimiento';
        form.reset();
        document.getElementById('movimiento-id').value = '';
        document.getElementById('input-fecha').value = new Date().toISOString().split('T')[0];
        
        // Seleccionar gasto por defecto
        tipoSeleccionado = 'gasto';
        document.querySelectorAll('.tipo-btn').forEach(btn => {
            if (btn.dataset.tipo === 'gasto') {
                btn.classList.add('bg-danger', 'text-white', 'border-danger');
                btn.classList.remove('border-gray-300', 'text-gray-600');
            } else {
                btn.classList.remove('bg-danger', 'bg-success', 'text-white', 'border-danger', 'border-success');
                btn.classList.add('border-gray-300', 'text-gray-600');
            }
        });
        // Mostrar categoría por defecto (gasto)
        categoriaContainer.style.display = 'block';
        categoriaInput.setAttribute('required', 'required');
    }
    
    modal.classList.remove('hidden');
}

function cerrarModalMovimiento() {
    document.getElementById('modal-movimiento').classList.add('hidden');
    document.getElementById('form-movimiento').reset();
}

function seleccionarTipo(btn) {
    const tipo = btn.dataset.tipo;
    tipoSeleccionado = tipo;
    
    document.querySelectorAll('.tipo-btn').forEach(b => {
        b.classList.remove('bg-danger', 'bg-success', 'text-white', 'border-danger', 'border-success');
        b.classList.add('border-gray-300', 'text-gray-600');
    });
    
    if (tipo === 'gasto') {
        btn.classList.add('bg-danger', 'text-white', 'border-danger');
        btn.classList.remove('border-gray-300', 'text-gray-600');
        // Mostrar categoría para gastos
        document.getElementById('categoria-container').style.display = 'block';
        document.getElementById('input-categoria').setAttribute('required', 'required');
    } else {
        btn.classList.add('bg-success', 'text-white', 'border-success');
        btn.classList.remove('border-gray-300', 'text-gray-600');
        // Ocultar categoría para ingresos
        document.getElementById('categoria-container').style.display = 'none';
        document.getElementById('input-categoria').removeAttribute('required');
    }
}

// ============================================================================
// GUARDAR DATOS
// ============================================================================

async function guardarPresupuesto(e) {
    e.preventDefault();
    
    const monto = parseFloat(document.getElementById('input-presupuesto').value);
    
    if (monto < 0) {
        mostrarToast('El presupuesto no puede ser negativo', 'error');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/presupuesto.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                usuario_id: USUARIO_ID,
                mes: mesActual,
                anio: anioActual,
                monto_total: monto
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarToast('Presupuesto guardado exitosamente', 'success');
            cerrarModalPresupuesto();
            await cargarDatos();
        } else {
            mostrarToast(data.message || 'Error al guardar el presupuesto', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al guardar el presupuesto', 'error');
    }
}

async function guardarMovimiento(e) {
    e.preventDefault();
    
    const id = document.getElementById('movimiento-id').value;
    const monto = parseFloat(document.getElementById('input-monto').value);
    const fecha = document.getElementById('input-fecha').value;
    const descripcion = document.getElementById('input-descripcion').value.trim();
    const categoria = document.getElementById('input-categoria').value;
    
    if (monto <= 0) {
        mostrarToast('El monto debe ser mayor a 0', 'error');
        return;
    }
    
    if (!descripcion) {
        mostrarToast('La descripción es requerida', 'error');
        return;
    }
    
    const datos = {
        tipo: tipoSeleccionado,
        monto: monto,
        fecha: fecha,
        descripcion: descripcion,
        // Solo incluir categoría si es un gasto
        categoria: tipoSeleccionado === 'gasto' ? categoria : 'otros',
        usuario_id: USUARIO_ID
    };
    
    try {
        let response;
        if (id) {
            // Actualizar
            datos.id = parseInt(id);
            response = await fetch(`${API_BASE_URL}/movimientos.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            });
        } else {
            // Crear
            response = await fetch(`${API_BASE_URL}/movimientos.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            });
        }
        
        const data = await response.json();
        
        if (data.success) {
            mostrarToast(id ? 'Movimiento actualizado' : 'Movimiento creado', 'success');
            cerrarModalMovimiento();
            await cargarDatos();
        } else {
            mostrarToast(data.message || 'Error al guardar el movimiento', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al guardar el movimiento', 'error');
    }
}

async function eliminarMovimiento(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este movimiento?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/movimientos.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarToast('Movimiento eliminado', 'success');
            await cargarDatos();
        } else {
            mostrarToast(data.message || 'Error al eliminar el movimiento', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al eliminar el movimiento', 'error');
    }
}

function editarMovimiento(id) {
    const movimiento = movimientos.find(m => m.id === id);
    if (movimiento) {
        abrirModalMovimiento(movimiento);
    }
}

// ============================================================================
// FILTROS
// ============================================================================

function aplicarFiltro(filtro) {
    filtroActual = filtro;
    
    // Actualizar botones
    document.getElementById('filtro-todos').classList.remove('bg-primary', 'text-white');
    document.getElementById('filtro-todos').classList.add('bg-gray-100', 'text-gray-700');
    
    document.getElementById('filtro-gastos').classList.remove('bg-primary', 'text-white');
    document.getElementById('filtro-gastos').classList.add('bg-gray-100', 'text-gray-700');
    
    document.getElementById('filtro-ingresos').classList.remove('bg-primary', 'text-white');
    document.getElementById('filtro-ingresos').classList.add('bg-gray-100', 'text-gray-700');
    
    const btnActivo = document.getElementById(`filtro-${filtro}`);
    btnActivo.classList.add('bg-primary', 'text-white');
    btnActivo.classList.remove('bg-gray-100', 'text-gray-700');
    
    actualizarListaMovimientos();
}

// ============================================================================
// DETALLES DE DÍA
// ============================================================================

function mostrarDetallesDia(fecha) {
    const movimientosDia = movimientos.filter(m => m.fecha === fecha);
    
    if (movimientosDia.length === 0) {
        mostrarToast('No hay movimientos en este día', 'info');
        return;
    }
    
    // Por ahora solo mostramos un toast, pero podrías crear un modal más detallado
    let detalles = `Movimientos del ${formatearFecha(fecha)}:\n`;
    movimientosDia.forEach(m => {
        detalles += `\n${m.tipo === 'gasto' ? '-' : '+'}$${formatearNumero(m.monto)} - ${m.descripcion}`;
    });
    
    alert(detalles);
}

// ============================================================================
// UTILIDADES
// ============================================================================

function formatearNumero(numero) {
    return parseFloat(numero).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const partes = fecha.split('-');
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function mostrarCargando() {
    // Podrías agregar un spinner aquí
}

function mostrarToast(mensaje, tipo = 'info') {
    const container = document.getElementById('toast-container');
    
    const iconos = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const colores = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast ${colores[tipo]} text-white px-6 py-4 rounded-xl shadow-lg flex items-center space-x-3 min-w-[300px]`;
    toast.innerHTML = `
        <i class="fas ${iconos[tipo]} text-xl"></i>
        <span class="font-medium">${mensaje}</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
