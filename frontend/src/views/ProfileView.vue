<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

// Estado de la UI
const pestanaActual = ref('info')
const cargando = ref(true)
const enviando = ref(false)

// Datos del Perfil
const profileForm = ref({
    nombre: '',
    email: '',
    telefono: '',
    direccion: '',
    avatar_url: ''
})

// Seguridad
const passwordForm = ref({
    actual: '',
    nueva: '',
    confirmar: ''
})

// Pedidos del Usuario
const misPedidos = ref([])

onMounted(async () => {
    if (!authStore.usuario) await authStore.verificarSesion()

    if (authStore.usuario) {
        profileForm.value = { ...authStore.usuario }
        await cargarMisPedidos()
    }
    cargando.value = false
})

const cargarMisPedidos = async () => {
    const token = localStorage.getItem('auth_token')
    try {
        const res = await fetch('http://127.0.0.1:8000/api/mis-pedidos', {
            headers: { 'Authorization': `Bearer ${token}` }
        })
        if (res.ok) misPedidos.value = await res.json()
    } catch (e) { console.error(e) }
}

const actualizarPerfil = async () => {
    enviando.value = true
    const token = localStorage.getItem('auth_token')
    try {
        const res = await fetch('http://127.0.0.1:8000/api/user/update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(profileForm.value)
        })
        if (res.ok) {
            alert('Perfil actualizado con éxito')
            await authStore.verificarSesion() // Refrescamos el usuario global
        }
    } catch (e) { console.error(e) }
    finally { enviando.value = false }
}
</script>

<template>
    <main class="profile-container fade-in">
        <div v-if="cargando" class="loading">Cargando tu cuenta...</div>

        <div v-else class="profile-layout">
            <aside class="profile-sidebar">
                <div class="user-card">
                    <div class="avatar-wrapper">
                        <img :src="profileForm.avatar_url || 'https://ui-avatars.com/api/?name=' + profileForm.nombre"
                            alt="Avatar">
                    </div>
                    <h3>{{ authStore.usuario?.nombre }}</h3>
                    <span class="badge">{{ authStore.usuario?.rol }}</span>
                </div>

                <nav class="profile-nav">
                    <button @click="pestanaActual = 'info'" :class="{ active: pestanaActual === 'info' }">
                        <i class="fa-solid fa-user-gear"></i> Mi Información
                    </button>
                    <button @click="pestanaActual = 'pedidos'" :class="{ active: pestanaActual === 'pedidos' }">
                        <i class="fa-solid fa-box-open"></i> Mis Pedidos
                    </button>
                    <button @click="pestanaActual = 'seguridad'" :class="{ active: pestanaActual === 'seguridad' }">
                        <i class="fa-solid fa-shield-halved"></i> Seguridad
                    </button>
                </nav>
            </aside>

            <section class="profile-content">

                <div v-if="pestanaActual === 'info'" class="tab-card">
                    <h2>Información Personal</h2>
                    <form @submit.prevent="actualizarPerfil" class="profile-form">
                        <div class="form-grid">
                            <div class="input-group">
                                <label>Nombre Completo</label>
                                <input type="text" v-model="profileForm.nombre">
                            </div>
                            <div class="input-group">
                                <label>Correo Electrónico</label>
                                <input type="email" v-model="profileForm.email" disabled>
                            </div>
                            <div class="input-group">
                                <label>Teléfono</label>
                                <input type="text" v-model="profileForm.telefono">
                            </div>
                            <div class="input-group">
                                <label>URL de Foto de Perfil</label>
                                <input type="url" v-model="profileForm.avatar_url"
                                    placeholder="https://tu-imagen.com/foto.jpg">
                            </div>
                            <div class="input-group full">
                                <label>Dirección de Recogida habitual</label>
                                <input type="text" v-model="profileForm.direccion">
                            </div>
                        </div>
                        <button type="submit" class="btn-save" :disabled="enviando">
                            {{ enviando ? 'Guardando...' : 'Actualizar Perfil' }}
                        </button>
                    </form>
                </div>

                <div v-if="pestanaActual === 'pedidos'" class="tab-card">
                    <h2>Historial de Reparaciones</h2>
                    <div v-if="misPedidos.length === 0" class="empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>Aún no has solicitado ninguna reparación.</p>
                    </div>
                    <div v-else class="orders-list">
                        <div v-for="p in misPedidos" :key="p.id" class="order-item">
                            <div class="order-main">
                                <strong>{{ p.dispositivo?.marca }} {{ p.dispositivo?.modelo }}</strong>
                                <span class="order-code">{{ p.codigo_seguimiento }}</span>
                            </div>
                            <div class="order-status">
                                <span class="status-dot" :class="p.estado.toLowerCase()"></span>
                                {{ p.estado }}
                            </div>
                            <router-link :to="'/consulta?codigo=' + p.codigo_seguimiento" class="btn-view">Ver
                                Detalles</router-link>
                        </div>
                    </div>
                </div>

                <div v-if="pestanaActual === 'seguridad'" class="tab-card">
                    <h2>Cambiar Contraseña</h2>
                    <form @submit.prevent="" class="profile-form">
                        <div class="input-group">
                            <label>Contraseña Actual</label>
                            <input type="password" v-model="passwordForm.actual">
                        </div>
                        <div class="input-group">
                            <label>Nueva Contraseña</label>
                            <input type="password" v-model="passwordForm.nueva">
                        </div>
                        <button type="button" class="btn-save danger">Actualizar Clave de Acceso</button>
                    </form>
                </div>

            </section>
        </div>
    </main>
</template>

<style scoped lang="scss">
.profile-container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
    min-height: 80vh;
}

.profile-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
}

/* SIDEBAR */
.profile-sidebar {
    background: white;
    border-radius: 20px;
    padding: 30px;
    border: 1px solid #e2e8f0;
    height: fit-content;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);

    .user-card {
        text-align: center;
        margin-bottom: 30px;

        .avatar-wrapper {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            overflow: hidden;
            border: 3px solid var(--main-color);

            img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
        }

        h3 {
            margin: 0 0 5px 0;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .badge {
            font-size: 0.7rem;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--main-color);
        }
    }
}

.profile-nav {
    display: flex;
    flex-direction: column;
    gap: 10px;

    button {
        background: transparent;
        border: none;
        padding: 12px 15px;
        border-radius: 12px;
        text-align: left;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;

        &:hover {
            background: #f8fafc;
            color: var(--main-color);
        }

        &.active {
            background: var(--main-color);
            color: white;
        }
    }
}

/* CONTENIDO */
.tab-card {
    background: white;
    border-radius: 24px;
    padding: 35px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);

    h2 {
        margin: 0 0 25px 0;
        font-size: 1.6rem;
        font-weight: 800;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 15px;
    }
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;

    &.full {
        grid-column: span 2;
    }

    label {
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
    }

    input {
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        font-family: inherit;
        font-size: 1rem;

        &:focus {
            border-color: var(--main-color);
            outline: none;
        }

        &:disabled {
            background: #f8fafc;
            color: #94a3b8;
        }
    }
}

.btn-save {
    background: var(--main-color);
    color: white;
    border: none;
    padding: 14px 25px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;

    &:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }

    &.danger {
        background: #ef4444;
    }
}

/* LISTA DE PEDIDOS */
.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border: 1px solid #f1f5f9;
    border-radius: 16px;
    margin-bottom: 15px;
    background: #fcfcfd;

    .order-main {
        strong {
            display: block;
            font-size: 1.1rem;
        }

        .order-code {
            font-size: 0.85rem;
            color: var(--main-color);
            font-weight: 800;
        }
    }

    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 5px;

        &.pendiente {
            background: #f59e0b;
        }

        &.reparado {
            background: #10b981;
        }
    }
}

@media (max-width: 850px) {
    .profile-layout {
        grid-template-columns: 1fr;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .input-group.full {
        grid-column: span 1;
    }
}
</style>