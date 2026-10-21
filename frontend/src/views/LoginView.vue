<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api, { setToken } from '../api/axios'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/login', {
      email: email.value,
      password: password.value,
    })
    setToken(data.token)
    router.push({ name: 'books' })
  } catch (e) {
    if (e.response?.status === 401) error.value = 'Email atau password salah.'
    else if (e.response?.status === 422) error.value = 'Email dan password wajib diisi dengan benar.'
    else error.value = 'Tidak bisa terhubung ke server.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="card">
    <h1>Books Tracker</h1>
    <p>Login untuk melanjutkan.</p>
    <form @submit.prevent="submit">
      <label>
        Email
        <input v-model="email" type="email" autocomplete="username" required />
      </label>
      <label>
        Password
        <input v-model="password" type="password" autocomplete="current-password" required />
      </label>
      <p v-if="error" class="error">{{ error }}</p>
      <button type="submit" :disabled="loading">{{ loading ? 'Memproses...' : 'Login' }}</button>
    </form>
  </section>
</template>

<style scoped>
.card {
  max-width: 360px;
  margin: 4rem auto;
  padding: 1.5rem;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}
label {
  display: block;
  margin-bottom: 0.75rem;
}
input {
  display: block;
  width: 100%;
  box-sizing: border-box;
  padding: 0.5rem;
  margin-top: 0.25rem;
}
button {
  padding: 0.5rem 1rem;
  cursor: pointer;
}
.error {
  color: #c62828;
}
</style>