<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Navegación
const pestanaActual = ref('pedidos')
const cargando = ref(false)

// === ESTADOS DE LAS PESTAÑAS ===
const pedidos = ref([])
const usuarios = ref([])
const dispositivos = ref([])
const servicios = ref([])
const contabilidad = ref([])

// Formularios
const nuevoDispositivo = ref({ marca: 'Apple', modelo: '', tipo: 'Smartphone', imagen_url: '' })
const dispositivoEditando = ref({ id: null, marca: '', modelo: '', tipo: '', imagen_url: '' })
const mostrarModalEditar = ref(false)

// NUEVO: Formulario para rellenar la contabilidad
const nuevaContabilidad = ref({
    pedido_id: '',
    piezas_cambiadas: '',
    coste_piezas: 0,
    total_cobrado: 0,
    metodo_pago: 'Efectivo'
})

// === OPTIMIZACIÓN: BUSCADOR Y PAGINACIÓN ===
const busquedaDispositivo = ref('')
const paginaActualDisp = ref(1)
const itemsPorPaginaDisp = 10

watch(busquedaDispositivo, () => { paginaActualDisp.value = 1 })

const dispositivosFiltrados = computed(() => {
    if (!busquedaDispositivo.value) return dispositivos.value
    const busqueda = busquedaDispositivo.value.toLowerCase()
    return dispositivos.value.filter(d =>
        d.modelo.toLowerCase().includes(busqueda) || d.marca.toLowerCase().includes(busqueda)
    )
})
const totalPaginasDisp = computed(() => Math.ceil(dispositivosFiltrados.value.length / itemsPorPaginaDisp) || 1)
const dispositivosPaginados = computed(() => {
    const inicio = (paginaActualDisp.value - 1) * itemsPorPaginaDisp
    return dispositivosFiltrados.value.slice(inicio, inicio + itemsPorPaginaDisp)
})
const filasVaciasDisp = computed(() => {
    const cantidadActual = dispositivosPaginados.value.length
    return (cantidadActual > 0 && cantidadActual < itemsPorPaginaDisp) ? itemsPorPaginaDisp - cantidadActual : 0
})
const cambiarPagina = (delta) => {
    const nuevaPagina = paginaActualDisp.value + delta
    if (nuevaPagina >= 1 && nuevaPagina <= totalPaginasDisp.value) paginaActualDisp.value = nuevaPagina
}

// === VERIFICACIÓN DE SEGURIDAD ===
onMounted(async () => {
    if (!authStore.usuario) await authStore.verificarSesion()
    if (!authStore.usuario || authStore.usuario.rol !== 'admin') {
        router.push('/')
        return
    }
    await cargarPedidos()
})

watch(pestanaActual, async (nuevaPestana) => {
    if (nuevaPestana === 'usuarios' && usuarios.value.length === 0) await cargarUsuarios()
    if ((nuevaPestana === 'del_dispositivo' || nuevaPestana === 'add_dispositivo') && dispositivos.value.length === 0) await cargarDispositivos()
    if (nuevaPestana === 'precios' && servicios.value.length === 0) await cargarServicios()
    if (nuevaPestana === 'contabilidad' && contabilidad.value.length === 0) await cargarContabilidad()
})

const getHeaders = () => ({
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
})

// === MÉTODOS CRUD BÁSICOS (Pedidos, Usuarios, Dispositivos) ===
const cargarPedidos = async () => { /* ... (Se mantiene igual) ... */
    cargando.value = true; try { const res = await fetch('http://127.0.0.1:8000/api/admin/pedidos', { headers: getHeaders() }); if (res.ok) pedidos.value = await res.json() } catch (error) { console.error(error) } finally { cargando.value = false }
}
const actualizarEstado = async (id, nuevoEstado) => {
    try { const res = await fetch(`http://127.0.0.1:8000/api/admin/pedidos/${id}/estado`, { method: 'PUT', headers: getHeaders(), body: JSON.stringify({ estado: nuevoEstado }) }); if (res.ok) { const pedido = pedidos.value.find(p => p.id === id); if (pedido) pedido.estado = nuevoEstado } } catch (error) { console.error(error) }
}
const cargarUsuarios = async () => {
    cargando.value = true; try { const res = await fetch('http://127.0.0.1:8000/api/admin/usuarios', { headers: getHeaders() }); if (res.ok) { const data = await res.json(); usuarios.value = data.map(u => ({ ...u, nueva_password: '' })) } } catch (error) { console.error(error) } finally { cargando.value = false }
}
const cambiarRol = async (id, nuevoRol) => {
    try { const res = await fetch(`http://127.0.0.1:8000/api/admin/usuarios/${id}/rol`, { method: 'PUT', headers: getHeaders(), body: JSON.stringify({ rol: nuevoRol }) }); if (res.ok) { const u = usuarios.value.find(u => u.id === id); if (u) u.rol = nuevoRol } } catch (error) { console.error(error) }
}
const actualizarPassword = async (id, nuevaClave) => {
    if (!nuevaClave || nuevaClave.length < 6) return alert('La contraseña debe tener al menos 6 caracteres.'); try { const res = await fetch(`http://127.0.0.1:8000/api/admin/usuarios/${id}/password`, { method: 'PUT', headers: getHeaders(), body: JSON.stringify({ password: nuevaClave }) }); if (res.ok) { alert('Contraseña actualizada correctamente.'); const u = usuarios.value.find(u => u.id === id); if (u) u.nueva_password = '' } else { alert('Hubo un error al actualizar la contraseña.') } } catch (error) { console.error(error) }
}
const cargarDispositivos = async () => {
    cargando.value = true; try { const res = await fetch('http://127.0.0.1:8000/api/dispositivos', { headers: getHeaders() }); if (res.ok) dispositivos.value = await res.json() } catch (error) { console.error(error) } finally { cargando.value = false }
}
const guardarDispositivo = async () => {
    if (!nuevoDispositivo.value.modelo || !nuevoDispositivo.value.imagen_url) return alert("El modelo y la URL de la imagen son obligatorios"); const existe = dispositivos.value.some(d => d.marca.toLowerCase() === nuevoDispositivo.value.marca.toLowerCase() && d.modelo.toLowerCase() === nuevoDispositivo.value.modelo.trim().toLowerCase()); if (existe) return alert(`El dispositivo ${nuevoDispositivo.value.marca} ${nuevoDispositivo.value.modelo} ya existe.`); try { const res = await fetch('http://127.0.0.1:8000/api/admin/dispositivos', { method: 'POST', headers: getHeaders(), body: JSON.stringify(nuevoDispositivo.value) }); if (res.ok) { alert('¡Dispositivo añadido al catálogo!'); nuevoDispositivo.value.modelo = ''; nuevoDispositivo.value.imagen_url = ''; await cargarDispositivos() } } catch (error) { console.error(error) }
}
const eliminarDispositivo = async (id) => {
    if (!confirm('¿Borrar este dispositivo del catálogo?')) return; try { const res = await fetch(`http://127.0.0.1:8000/api/admin/dispositivos/${id}`, { method: 'DELETE', headers: getHeaders() }); if (res.ok) { dispositivos.value = dispositivos.value.filter(d => d.id !== id); if (dispositivosPaginados.value.length === 0 && paginaActualDisp.value > 1) paginaActualDisp.value--; alert('Dispositivo eliminado') } } catch (error) { console.error(error) }
}
const abrirModalEditar = (disp) => { dispositivoEditando.value = { ...disp }; mostrarModalEditar.value = true }
const cerrarModal = () => { mostrarModalEditar.value = false }
const guardarEdicionDispositivo = async () => {
    if (!dispositivoEditando.value.modelo || !dispositivoEditando.value.imagen_url) return alert('Modelo y URL son obligatorios'); const existe = dispositivos.value.some(d => d.id !== dispositivoEditando.value.id && d.marca.toLowerCase() === dispositivoEditando.value.marca.toLowerCase() && d.modelo.toLowerCase() === dispositivoEditando.value.modelo.trim().toLowerCase()); if (existe) return alert(`Ya existe otro equipo con ese nombre.`); try { const res = await fetch(`http://127.0.0.1:8000/api/admin/dispositivos/${dispositivoEditando.value.id}`, { method: 'PUT', headers: getHeaders(), body: JSON.stringify(dispositivoEditando.value) }); if (res.ok) { alert('¡Dispositivo actualizado!'); await cargarDispositivos(); cerrarModal() } } catch (error) { console.error(error) }
}

// === LÓGICA DE PRECIOS (SOLO EDITAR) ===
const cargarServicios = async () => {
    cargando.value = true
    try {
        const res = await fetch('http://127.0.0.1:8000/api/servicios', { headers: getHeaders() })
        if (res.ok) servicios.value = await res.json()
    } catch (error) { console.error('Error cargando tarifas:', error) }
    finally { cargando.value = false }
}

const actualizarPrecio = async (id, precioNuevo) => {
    try {
        const res = await fetch(`http://127.0.0.1:8000/api/admin/servicios/${id}`, {
            method: 'PUT',
            headers: getHeaders(),
            body: JSON.stringify({ precio: precioNuevo })
        })
        if (res.ok) alert('Precio actualizado y guardado en la base de datos.')
    } catch (error) { console.error('Error modificando tarifa:', error) }
}


// === LÓGICA DE CONTABILIDAD (VER Y AÑADIR) ===
const cargarContabilidad = async () => {
    cargando.value = true
    try {
        const res = await fetch('http://127.0.0.1:8000/api/admin/contabilidad', { headers: getHeaders() })
        if (res.ok) contabilidad.value = await res.json()
    } catch (error) { console.error('Error cargando contabilidad:', error) }
    finally { cargando.value = false }
}

const guardarContabilidad = async () => {
    if (!nuevaContabilidad.value.pedido_id || nuevaContabilidad.value.total_cobrado <= 0) {
        return alert("Debes introducir un ID de pedido válido y un total cobrado.");
    }

    // Calculamos las ganancias netas (Total cobrado - Coste de las piezas que hemos comprado)
    const gananciasNetas = parseFloat(nuevaContabilidad.value.total_cobrado) - parseFloat(nuevaContabilidad.value.coste_piezas);

    try {
        const res = await fetch('http://127.0.0.1:8000/api/admin/contabilidad', {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({
                ...nuevaContabilidad.value,
                ganancias: gananciasNetas
            })
        })
        if (res.ok) {
            alert('¡Registro contable añadido con éxito!')
            // Limpiamos el formulario
            nuevaContabilidad.value = { pedido_id: '', piezas_cambiadas: '', coste_piezas: 0, total_cobrado: 0, metodo_pago: 'Efectivo' }
            await cargarContabilidad() // Recargamos la tabla
        } else {
            const data = await res.json();
            alert(data.message || 'El ID del pedido no existe o hay un error.');
        }
    } catch (error) { console.error('Error guardando contabilidad:', error) }
}

const totalCostesPiezas = computed(() => contabilidad.value.reduce((acc, item) => acc + parseFloat(item.coste_piezas || 0), 0))
const totalGananciasNetas = computed(() => contabilidad.value.reduce((acc, item) => acc + parseFloat(item.ganancias || 0), 0))
const cajaTotalAcumulada = computed(() => contabilidad.value.reduce((acc, item) => acc + parseFloat(item.total_cobrado || 0), 0))
</script>

<template>
    <main class="admin-container">
        <header class="admin-header">
            <div class="header-title">
                <h1>Centro de Mando <span>Josetech</span></h1>
                <p>Administración global del taller y base de datos</p>
            </div>

            <nav class="sub-navbar">
                <button @click="pestanaActual = 'pedidos'" :class="{ active: pestanaActual === 'pedidos' }">
                    <i class="fa-solid fa-clipboard-list"></i> Pedidos
                </button>
                <button @click="pestanaActual = 'usuarios'" :class="{ active: pestanaActual === 'usuarios' }">
                    <i class="fa-solid fa-users"></i> Usuarios
                </button>
                <button @click="pestanaActual = 'add_dispositivo'"
                    :class="{ active: pestanaActual === 'add_dispositivo' }">
                    <i class="fa-solid fa-plus-circle"></i> Añadir Disp.
                </button>
                <button @click="pestanaActual = 'del_dispositivo'"
                    :class="{ active: pestanaActual === 'del_dispositivo' }">
                    <i class="fa-solid fa-layer-group"></i> Catálogo
                </button>
                <button @click="pestanaActual = 'precios'" :class="{ active: pestanaActual === 'precios' }">
                    <i class="fa-solid fa-tags"></i> Precios
                </button>
                <button @click="pestanaActual = 'contabilidad'" :class="{ active: pestanaActual === 'contabilidad' }">
                    <i class="fa-solid fa-wallet"></i> Contabilidad
                </button>
            </nav>
        </header>

        <div v-if="cargando" class="loading-state">
            <span class="spinner"></span>
            <p>Sincronizando con la base de datos...</p>
        </div>

        <div v-else class="panel-content">

            <section v-if="pestanaActual === 'pedidos'" class="admin-card fade-in">
                <div class="card-header">
                    <h2>Órdenes de Reparación Activas</h2>
                </div>
                <div v-if="pedidos.length === 0" class="empty-data">
                    <i class="fa-solid fa-inbox"></i>
                    <h3>No hay órdenes registradas</h3>
                </div>
                <div v-else class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Track / ID</th>
                                <th>Cliente</th>
                                <th>Dispositivo</th>
                                <th>Averías</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pedido in pedidos" :key="pedido.id" class="table-row">
                                <td><span class="fw-bold">#{{ pedido.id }}</span><small class="d-block text-muted">{{
                                        pedido.codigo_seguimiento }}</small></td>
                                <td><span class="client-name">{{ pedido.usuario?.nombre || 'Borrado' }}</span></td>
                                <td><span class="device-name">{{ pedido.dispositivo?.marca }} {{
                                        pedido.dispositivo?.modelo }}</span></td>
                                <td><span class="repair-types">{{ pedido.tipo_reparacion }}</span></td>
                                <td><span class="status-pill" :class="pedido.estado.toLowerCase().replace(' ', '-')">{{
                                        pedido.estado }}</span></td>
                                <td>
                                    <select class="status-select" :value="pedido.estado"
                                        @change="actualizarEstado(pedido.id, $event.target.value)">
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="En Taller">En Taller</option>
                                        <option value="Reparado">Reparado</option>
                                        <option value="Entregado">Entregado</option>
                                        <option value="Cancelado">Cancelado</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-if="pestanaActual === 'usuarios'" class="admin-card fade-in">
                <div class="card-header">
                    <h2>Gestión de Clientes y Staff</h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol Actual</th>
                                <th>Cambiar Rol</th>
                                <th>Forzar Nueva Clave</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in usuarios" :key="user.id" class="table-row">
                                <td class="client-name">{{ user.nombre }}</td>
                                <td>{{ user.email }}</td>
                                <td><span class="status-pill"
                                        :class="user.rol === 'admin' ? 'reparado' : 'entregado'">{{
                                        user.rol.toUpperCase() }}</span></td>
                                <td>
                                    <select class="status-select" :value="user.rol"
                                        @change="cambiarRol(user.id, $event.target.value)">
                                        <option value="cliente">Cliente</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="password-action">
                                        <input type="text" v-model="user.nueva_password" placeholder="Nueva clave"
                                            class="mini-input">
                                        <button @click="actualizarPassword(user.id, user.nueva_password)"
                                            class="btn-save-mini" title="Guardar clave"><i
                                                class="fa-solid fa-floppy-disk"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-if="pestanaActual === 'add_dispositivo'" class="admin-card fade-in">
                <div class="card-header">
                    <h2>Dar de Alta Nuevo Dispositivo</h2>
                    <p class="subtitle-card">Los dispositivos añadidos aquí aparecerán instantáneamente en el buscador
                        principal.</p>
                </div>
                <form class="admin-form" @submit.prevent="guardarDispositivo">
                    <div class="form-grid">
                        <div class="input-group">
                            <label>Marca</label>
                            <select v-model="nuevoDispositivo.marca">
                                <option value="Apple">Apple</option>
                                <option value="Samsung">Samsung</option>
                                <option value="Xiaomi">Xiaomi</option>
                                <option value="Google">Google</option>
                                <option value="OnePlus">OnePlus</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Modelo</label>
                            <input type="text" v-model="nuevoDispositivo.modelo" required>
                        </div>
                        <div class="input-group">
                            <label>Familia / Tipo</label>
                            <select v-model="nuevoDispositivo.tipo">
                                <option value="Smartphone">Smartphone</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Portátil">Portátil</option>
                                <option value="Consola">Consola</option>
                                <option value="Ordenador">Ordenador</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>URL de la Imagen</label>
                            <input type="url" v-model="nuevoDispositivo.imagen_url" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary mt-4"><i class="fa-solid fa-save"></i> Guardar en Base de
                        Datos</button>
                </form>
            </section>

            <section v-if="pestanaActual === 'del_dispositivo'" class="admin-card fade-in">
                <div class="card-header flex-between">
                    <div>
                        <h2>Catálogo Activo</h2>
                        <p class="subtitle-card">Edita la información o borra los equipos.</p>
                    </div>
                    <div class="search-mini"><i class="fa-solid fa-search"></i><input type="text"
                            v-model="busquedaDispositivo" placeholder="Buscar modelo..."></div>
                </div>
                <div class="table-responsive table-fixed-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="disp in dispositivosPaginados" :key="disp.id" class="table-row">
                                <td class="fw-bold text-muted">#{{ disp.id }}</td>
                                <td><img v-if="disp.imagen_url" :src="disp.imagen_url" class="mini-thumb" alt="disp"><i
                                        v-else class="fa-solid fa-mobile-screen"></i></td>
                                <td class="device-name">{{ disp.marca }}</td>
                                <td>{{ disp.modelo }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button @click="abrirModalEditar(disp)" class="btn-edit"><i
                                                class="fa-solid fa-pen"></i> Editar</button>
                                        <button @click="eliminarDispositivo(disp.id)" class="btn-delete"><i
                                                class="fa-solid fa-trash"></i> Borrar</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-controls mt-4" v-if="totalPaginasDisp > 1">
                    <button @click="cambiarPagina(-1)" :disabled="paginaActualDisp === 1" class="btn-page"><i
                            class="fa-solid fa-chevron-left"></i> Anterior</button>
                    <span class="page-info">Página {{ paginaActualDisp }} de {{ totalPaginasDisp }}</span>
                    <button @click="cambiarPagina(1)" :disabled="paginaActualDisp === totalPaginasDisp"
                        class="btn-page">Siguiente <i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </section>

            <section v-if="pestanaActual === 'precios'" class="admin-card fade-in">
                <div class="card-header">
                    <h2>Modificar Tarifas</h2>
                    <p class="subtitle-card">Actualiza los precios base de las reparaciones que ya están registradas en
                        tu sistema.</p>
                </div>

                <div v-if="servicios.length === 0" class="text-center text-muted py-4">No hay tarifas dinámicas creadas
                    en la base de datos.</div>
                <div v-else class="prices-grid" style="max-width: 100%;">
                    <div v-for="serv in servicios" :key="serv.id" class="price-item"
                        style="max-width: 700px; margin-bottom: 5px;">
                        <div class="price-info">
                            <i :class="serv.icono"></i>
                            <div>
                                <span style="display:block; font-weight: 700;">{{ serv.nombre }}</span>
                                <small
                                    style="display:inline-block; font-size: 0.75rem; background: #e2e8f0; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; color: #475569; margin-top: 4px;">
                                    {{ serv.categoria }}
                                </small>
                            </div>
                        </div>
                        <div class="price-input">
                            <input type="number" v-model="serv.precio" step="0.01" style="width: 100px; padding: 8px;">
                            <span style="font-weight:700; margin-left:5px;">€</span>
                            <button @click="actualizarPrecio(serv.id, serv.precio)" class="btn-save-mini"
                                style="margin-left:15px; padding: 10px 15px;" title="Guardar cambios de precio">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Precio
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="pestanaActual === 'contabilidad'" class="admin-card fade-in">
                <div class="card-header">
                    <h2>Módulo Financiero y Contabilidad</h2>
                    <p class="subtitle-card">Rellena los datos cuando factures una reparación para calcular tus
                        ganancias.</p>
                </div>

                <form class="admin-form mb-4 pb-4 border-bottom" @submit.prevent="guardarContabilidad">
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 15px; color: #1e293b;">
                        <i class="fa-solid fa-file-invoice-dollar" style="color: var(--main-color);"></i> Registrar
                        Nuevo Asiento
                    </h3>
                    <div class="form-grid">
                        <div class="input-group">
                            <label>ID del Pedido Finalizado</label>
                            <input type="number" v-model="nuevaContabilidad.pedido_id" placeholder="Ej: 14" required>
                        </div>
                        <div class="input-group">
                            <label>Piezas Cambiadas (Opcional)</label>
                            <input type="text" v-model="nuevaContabilidad.piezas_cambiadas"
                                placeholder="Ej: Pantalla OLED + Adhesivo">
                        </div>
                        <div class="input-group">
                            <label>Coste de Compra Piezas (€)</label>
                            <input type="number" step="0.01" v-model="nuevaContabilidad.coste_piezas" required>
                        </div>
                        <div class="input-group">
                            <label>Total Cobrado al Cliente (€)</label>
                            <input type="number" step="0.01" v-model="nuevaContabilidad.total_cobrado" required>
                        </div>
                        <div class="input-group">
                            <label>Método de Pago</label>
                            <select v-model="nuevaContabilidad.metodo_pago">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta de Crédito</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary mt-4" style="background-color: #10b981;">
                        <i class="fa-solid fa-plus-circle"></i> Añadir a la tabla y Calcular Ganancias
                    </button>
                </form>

                <div class="finance-dashboard-grid">
                    <div class="finance-card cost">
                        <div class="fin-icon"><i class="fa-solid fa-layer-group"></i></div>
                        <div class="fin-data"><span>Coste de Piezas</span>
                            <h3>{{ totalCostesPiezas.toFixed(2) }} €</h3>
                        </div>
                    </div>
                    <div class="finance-card profit">
                        <div class="fin-icon"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="fin-data"><span>Ganancias Netas</span>
                            <h3>{{ totalGananciasNetas.toFixed(2) }} €</h3>
                        </div>
                    </div>
                    <div class="finance-card revenue">
                        <div class="fin-icon"><i class="fa-solid fa-vault"></i></div>
                        <div class="fin-data"><span>Caja Bruta Total</span>
                            <h3>{{ cajaTotalAcumulada.toFixed(2) }} €</h3>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="margin-top: 30px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Orden</th>
                                <th>Componentes / Piezas</th>
                                <th>Coste Material (€)</th>
                                <th>Margen Taller (€)</th>
                                <th>Total Facturado</th>
                                <th>Método de Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in contabilidad" :key="item.id" class="table-row">
                                <td class="fw-bold">#{{ item.pedido_id }}</td>
                                <td>
                                    <span style="color: #334155; font-weight: 600;">{{ item.piezas_cambiadas || 'Ninguna (Mano de obra)' }}</span>
                                </td>
                                <td style="color: #dc2626; font-weight: 700;">-{{
                                    parseFloat(item.coste_piezas).toFixed(2) }} €</td>
                                <td style="color: #2563eb; font-weight: 700;">+{{ parseFloat(item.ganancias).toFixed(2)
                                    }} €</td>
                                <td style="color: #166534; font-weight: 800; font-size: 1rem;">{{
                                    parseFloat(item.total_cobrado).toFixed(2) }} €</td>
                                <td>
                                    <span class="payment-method-tag" :class="item.metodo_pago.toLowerCase()">
                                        <i class="fa-solid"
                                            :class="item.metodo_pago.toLowerCase() === 'efectivo' ? 'fa-money-bill-wave' : 'fa-credit-card'"></i>
                                        {{ item.metodo_pago }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="contabilidad.length === 0">
                                <td colspan="6" class="text-center text-muted py-4">No hay asientos financieros
                                    registrados. Usa el formulario de arriba para añadir uno.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div v-if="mostrarModalEditar" class="modal-overlay" @click.self="cerrarModal">
        </div>
    </main>
</template>

<style scoped lang="scss">
/* Todos tus estilos se mantienen idénticos... */
.admin-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 40px 20px;
    color: var(--text-color);
}

.admin-header {
    margin-bottom: 30px;

    .header-title {
        margin-bottom: 25px;

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 5px 0;

            span {
                color: var(--main-color);
            }
        }

        p {
            color: #64748b;
            margin: 0;
            font-size: 1.05rem;
        }
    }
}

.sub-navbar {
    display: flex;
    gap: 10px;
    background-color: white;
    padding: 10px;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    border: 1px solid #e2e8f0;
    overflow-x: auto;

    button {
        background: transparent;
        border: none;
        padding: 12px 20px;
        font-size: 0.95rem;
        font-weight: 700;
        color: #64748b;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;

        &:hover {
            background-color: #f8fafc;
            color: #334155;
        }

        &.active {
            background-color: var(--main-color);
            color: white;
            box-shadow: 0 4px 10px rgba(var(--main-color-rgb, 217, 106, 26), 0.2);
        }
    }
}

.admin-card {
    background: white;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    padding: 25px;
}

.card-header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f5f9;

    h2 {
        font-size: 1.5rem;
        margin: 0;
        font-weight: 800;
        color: #0f172a;
    }

    .subtitle-card {
        color: #64748b;
        font-size: 0.9rem;
        margin: 5px 0 0 0;
    }
}

.border-top {
    border-top: 1px solid #f1f5f9;
}

.border-bottom {
    border-bottom: 1px solid #f1f5f9;
}

.pb-4 {
    padding-bottom: 25px;
}

.mb-3 {
    margin-bottom: 15px;
}

.mb-4 {
    margin-bottom: 25px;
}

.pt-4 {
    padding-top: 25px;
}

.text-center {
    text-align: center;
}

.py-4 {
    padding: 20px 0;
}

.text-2xl {
    font-size: 1.5rem;
}

.flex-between {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.search-mini {
    position: relative;

    i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    input {
        padding: 10px 10px 10px 35px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.9rem;
        outline: none;
        width: 250px;

        &:focus {
            border-color: var(--main-color);
        }
    }
}

.admin-form .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;

    label {
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
    }

    input,
    select {
        padding: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-family: inherit;
        font-size: 0.95rem;
        outline: none;

        &:focus {
            border-color: var(--main-color);
            box-shadow: 0 0 0 3px rgba(var(--main-color-rgb, 217, 106, 26), 0.1);
        }
    }
}

.mt-4 {
    margin-top: 25px;
}

.btn-primary {
    background: var(--main-color);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: 0.2s;

    &:hover {
        background: var(--secondary-color, #b55612);
        transform: translateY(-2px);
    }
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.btn-edit {
    background: #e0e7ff;
    color: #4f46e5;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;

    &:hover {
        background: #4f46e5;
        color: white;
    }
}

.btn-delete {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;

    &:hover {
        background: #dc2626;
        color: white;
    }
}

.btn-cancel {
    background: #f1f5f9;
    color: #475569;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;

    &:hover {
        background: #e2e8f0;
    }
}

.password-action {
    display: flex;
    gap: 5px;
    align-items: center;
}

.mini-input {
    padding: 8px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.85rem;
    outline: none;
    width: 120px;

    &:focus {
        border-color: var(--main-color);
    }
}

.btn-save-mini {
    background: #10b981;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.2s;

    &:hover {
        background: #059669;
    }
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    border-top: 1px solid #f1f5f9;
    padding-top: 20px;

    .btn-page {
        background: white;
        border: 1px solid #cbd5e1;
        padding: 8px 15px;
        border-radius: 8px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;

        &:hover:not(:disabled) {
            border-color: var(--main-color);
            color: var(--main-color);
        }

        &:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    }

    .page-info {
        font-size: 0.9rem;
        font-weight: 700;
        color: #64748b;
    }
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 0.9rem;

    th {
        background-color: #f8fafc;
        padding: 15px;
        color: #475569;
        font-weight: 700;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    td {
        padding: 10px 15px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    tr:hover:not(.empty-row) {
        background-color: #fcfcfd;
    }
}

.table-row {
    height: 75px;
}

.empty-row {
    background-color: transparent !important;
    border-bottom: 1px solid transparent !important;

    td {
        border: none !important;
    }
}

.fw-bold {
    font-weight: 700;
}

.text-muted {
    color: #94a3b8;
    font-size: 0.8rem;
}

.d-block {
    display: block;
}

.client-name {
    font-weight: 700;
    color: #1e293b;
}

.device-name {
    font-weight: 700;
    color: var(--main-color);
}

.repair-types {
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.4;
    display: block;
    max-width: 200px;
}

.mini-thumb {
    width: 50px;
    height: 50px;
    object-fit: contain;
    border-radius: 8px;
    background: #f8fafc;
    padding: 4px;
    border: 1px solid #e2e8f0;
}

.status-pill {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    white-space: nowrap;

    &.pendiente {
        background-color: #fef3c7;
        color: #d97706;
    }

    &.en-taller {
        background-color: #e0e7ff;
        color: #4f46e5;
    }

    &.reparado {
        background-color: #dcfce7;
        color: #166534;
    }

    &.entregado {
        background-color: #f1f5f9;
        color: #475569;
    }

    &.cancelado {
        background-color: #fee2e2;
        color: #991b1b;
    }
}

.status-select {
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background-color: white;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    outline: none;
    cursor: pointer;

    &:focus {
        border-color: var(--main-color);
    }
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;

        h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
        }
    }

    .btn-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #94a3b8;
        cursor: pointer;

        &:hover {
            color: #0f172a;
        }
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
    }
}

.prices-grid {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.price-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;

    .price-info {
        display: flex;
        align-items: center;
        gap: 15px;
        font-weight: 700;
        color: #334155;

        i {
            font-size: 1.3rem;
            color: #94a3b8;
            width: 25px;
            text-align: center;
        }
    }

    .price-input {
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: bold;

        input {
            width: 80px;
            padding: 8px;
            text-align: center;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-weight: bold;
            outline: none;

            &:focus {
                border-color: var(--main-color);
            }
        }
    }
}

.finance-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.finance-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);

    .fin-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .fin-data {
        span {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        h3 {
            font-size: 1.7rem;
            font-weight: 900;
            margin: 5px 0 0 0;
        }
    }

    &.cost {
        .fin-icon {
            background: #fee2e2;
            color: #dc2626;
        }

        .fin-data h3 {
            color: #b91c1c;
        }
    }

    &.profit {
        .fin-icon {
            background: #e0e7ff;
            color: #2563eb;
        }

        .fin-data h3 {
            color: #1d4ed8;
        }
    }

    &.revenue {
        .fin-icon {
            background: #dcfce7;
            color: #166534;
        }

        .fin-data h3 {
            color: #15803d;
        }
    }
}

.payment-method-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;

    &.efectivo {
        background: #fef3c7;
        color: #d97706;
    }

    &.tarjeta {
        background: #e0f2fe;
        color: #0369a1;
    }
}

.empty-data {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;

    i {
        font-size: 3.5rem;
        margin-bottom: 15px;
        color: #cbd5e1;
    }

    h3 {
        color: var(--text-color);
        font-size: 1.3rem;
        margin: 0 0 8px 0;
    }

    p {
        font-size: 0.95rem;
        margin: 0;
    }
}

.loading-state {
    text-align: center;
    padding: 80px 0;

    p {
        color: #64748b;
        font-weight: 600;
        margin-top: 15px;
    }
}

.spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid rgba(var(--main-color-rgb, 217, 106, 26), 0.2);
    border-top-color: var(--main-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>