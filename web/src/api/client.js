import axios from 'axios';

// Por defecto, requests relativos: Vite proxea /api/* y /sanctum/* hacia el backend.
// Si VITE_API_URL viene definido (deploy en otro host), se usa como baseURL absoluto.
const baseURL = import.meta.env.VITE_API_URL ?? '';

const api = axios.create({
  baseURL,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

let csrfInitialized = false;

export async function ensureCsrfCookie() {
  if (csrfInitialized) return;
  await api.get('/sanctum/csrf-cookie');
  csrfInitialized = true;
}

api.interceptors.request.use(async (config) => {
  const method = (config.method || 'get').toLowerCase();
  if (['post', 'put', 'patch', 'delete'].includes(method)) {
    await ensureCsrfCookie();
  }
  return config;
});

export default api;
