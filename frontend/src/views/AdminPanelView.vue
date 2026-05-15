<script setup>
import { ref, onMounted } from 'vue'

const pedidos = ref([])
const cargando = ref(true)

const cargarPedidos = async () => {
    const token = localStorage.getItem('auth_token')
    try {
        const res = await fetch('http://127.0.0.1:8000/api/admin/pedidos', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        pedidos.value = await res.json()
    } catch (e) {
        console.error("Error al cargar pedidos admin");
    } finally {
        cargando.value = false
    }
}

const cambiarEstado = async (id, nuevoEstado) => {
    const token = localStorage.getItem('auth_token')
    await fetch(`http://127.0.0.1:8000/api/admin/pedidos/${id}/estado`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    cargarPedidos() // Refrescar lista
}

onMounted(cargarPedidos)
</script>

<template>
    <div class="admin-panel">
        <header class="admin-header">
            <h1>Panel de Gestión <span>Josetech</span></h1>
            <p>Control de reparaciones y órdenes activas</p>
        </header>

        <div v-if="cargando" class="spinner"></div>

        <div v-else class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Dispositivo</th>
                        <th>Total</th>
                        <th>Estado Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in pedidos" :key="p.id">
                        <td>#{{ p.id }}</td>
                        <td>{{ p.usuario.nombre }}</td>
                        <td>{{ p.lineas_pedido[0]?.averia.dispositivo.modelo }}</td>
                        <td>{{ p.total }}€</td>
                        <td>
                            <span class="status-badge" :class="p.estado.toLowerCase()">{{ p.estado }}</span>
                        </td>
                        <td>
                            <select @change="cambiarEstado(p.id, $event.target.value)">
                                <option value="">Cambiar estado...</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En Taller">En Taller</option>
                                <option value="Reparado">Reparado</option>
                                <option value="Entregado">Entregado</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.admin-panel {
    padding: 40px;
    max-width: 1200px;
    margin: 0 auto;
}

.admin-header h1 span {
    color: var(--main-color);
}

.table-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-top: 30px;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.admin-table th {
    background: #f8fafc;
    padding: 20px;
    font-weight: bold;
    border-bottom: 2px solid #eee;
}

.admin-table td {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.status-badge.pendiente {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge.reparado {
    background: #dcfce7;
    color: #166534;
}

select {
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #ddd;
}
</style>