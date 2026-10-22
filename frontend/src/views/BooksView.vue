<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api, { clearToken } from '../api/axios'

const router = useRouter()

const STATUSES = [
  { value: 'to_read', label: 'Ingin dibaca' },
  { value: 'reading', label: 'Sedang dibaca' },
  { value: 'finished', label: 'Selesai' },
]

const books = ref([])
const loading = ref(false)
const message = ref('')
const errors = ref({})
const editingId = ref(null)
const form = reactive({ title: '', author: '', year: '', status: 'to_read' })

function statusLabel(value) {
  return STATUSES.find((s) => s.value === value)?.label ?? value
}

function resetForm() {
  editingId.value = null
  errors.value = {}
  form.title = ''
  form.author = ''
  form.year = ''
  form.status = 'to_read'
}

async function loadBooks() {
  loading.value = true
  try {
    const { data } = await api.get('/books')
    books.value = data
  } catch {
    message.value = 'Gagal memuat data buku.'
  } finally {
    loading.value = false
  }
}

async function save() {
  errors.value = {}
  message.value = ''
  const payload = {
    title: form.title,
    author: form.author,
    year: form.year === '' ? null : Number(form.year),
    status: form.status,
  }
  try {
    if (editingId.value) {
      await api.put(`/books/${editingId.value}`, payload)
      message.value = 'Buku diperbarui.'
    } else {
      await api.post('/books', payload)
      message.value = 'Buku ditambahkan.'
    }
    resetForm()
    await loadBooks()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}
    else message.value = 'Gagal menyimpan data.'
  }
}

function startEdit(book) {
  editingId.value = book.id
  errors.value = {}
  form.title = book.title
  form.author = book.author
  form.year = book.year
  form.status = book.status
}

async function remove(book) {
  if (!confirm(`Hapus "${book.title}"?`)) return
  try {
    await api.delete(`/books/${book.id}`)
    message.value = 'Buku dihapus.'
    if (editingId.value === book.id) resetForm()
    await loadBooks()
  } catch {
    message.value = 'Gagal menghapus data.'
  }
}

async function logout() {
  try {
    await api.post('/logout')
  } finally {
    clearToken()
    router.push({ name: 'login' })
  }
}

onMounted(loadBooks)
</script>

<template>
  <section>
    <header class="bar">
      <h1>Books Tracker</h1>
      <button @click="logout">Logout</button>
    </header>

    <p v-if="message" class="info">{{ message }}</p>

    <form class="card" @submit.prevent="save">
      <h2>{{ editingId ? 'Ubah buku' : 'Tambah buku' }}</h2>
      <label>
        Judul
        <input v-model="form.title" type="text" />
        <small v-if="errors.title" class="error">{{ errors.title[0] }}</small>
      </label>
      <label>
        Penulis
        <input v-model="form.author" type="text" />
        <small v-if="errors.author" class="error">{{ errors.author[0] }}</small>
      </label>
      <label>
        Tahun
        <input v-model="form.year" type="number" />
        <small v-if="errors.year" class="error">{{ errors.year[0] }}</small>
      </label>
      <label>
        Status
        <select v-model="form.status">
          <option v-for="s in STATUSES" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
        <small v-if="errors.status" class="error">{{ errors.status[0] }}</small>
      </label>
      <button type="submit">{{ editingId ? 'Simpan perubahan' : 'Tambah' }}</button>
      <button v-if="editingId" type="button" @click="resetForm">Batal</button>
    </form>

    <p v-if="loading">Memuat...</p>
    <table v-else-if="books.length" class="card">
      <thead>
        <tr><th>Judul</th><th>Penulis</th><th>Tahun</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <tr v-for="book in books" :key="book.id">
          <td>{{ book.title }}</td>
          <td>{{ book.author }}</td>
          <td>{{ book.year }}</td>
          <td>{{ statusLabel(book.status) }}</td>
          <td class="actions">
            <button @click="startEdit(book)">Ubah</button>
            <button @click="remove(book)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else>Belum ada buku.</p>
  </section>
</template>

<style scoped>
.bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
  padding: 1rem 1.25rem;
  margin: 1rem 0;
}
label {
  display: block;
  margin-bottom: 0.75rem;
}
input,
select {
  display: block;
  width: 100%;
  box-sizing: border-box;
  padding: 0.5rem;
  margin-top: 0.25rem;
}
button {
  padding: 0.4rem 0.9rem;
  margin-right: 0.4rem;
  cursor: pointer;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th,
td {
  text-align: left;
  padding: 0.5rem;
  border-bottom: 1px solid #eee;
}
.actions {
  white-space: nowrap;
}
.error {
  color: #c62828;
}
.info {
  color: #2e7d32;
}
</style>