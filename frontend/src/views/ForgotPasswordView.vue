<script setup>
import { ref } from 'vue'

const email = ref('')
const enviado = ref(false)

const enviarSolicitud = async () => {
    await fetch('https://proyectojosetech.onrender.com/api/password-forgot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email.value })
    })
    enviado.value = true
}
</script>

<template>
    <div class="forgot-container">
        <div class="auth-card" v-if="!enviado">
            <h2>¿Has olvidado tu clave?</h2>
            <p>Introduce tu email y avisaremos al administrador para restablecerla manualmente.</p>
            <input type="email" v-model="email" placeholder="tu@email.com" class="main-input">
            <button @click="enviarSolicitud" class="btn-submit">Avisar al Administrador</button>
        </div>
        <div class="auth-card success" v-else>
            <i class="fa-solid fa-circle-check"></i>
            <h3>Solicitud enviada</h3>
            <p>El administrador de Josetech ha recibido tu aviso. Pronto recibirás noticias en tu email.</p>
            <router-link to="/login">Volver al login</router-link>
        </div>
    </div>
</template>

<style scoped>
.forgot-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
}

.auth-card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
    max-width: 400px;
}

.main-input {
    width: 100%;
    padding: 15px;
    margin: 20px 0;
    border: 2px solid #eee;
    border-radius: 10px;
}

.btn-submit {
    width: 100%;
    background: var(--main-color);
    color: white;
    border: none;
    padding: 15px;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
}

.success i {
    font-size: 3rem;
    color: #10b981;
    margin-bottom: 15px;
}
</style>