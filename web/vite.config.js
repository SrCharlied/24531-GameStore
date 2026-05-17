import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    port: 5173,
    strictPort: true,
    watch: {
      // En Docker (Windows bind mount) los eventos inotify no llegan; polling sí
      usePolling: true,
      interval: 500,
    },
    // Proxy: el frontend en :5173 reenvía las llamadas /api/* y /sanctum/*
    // al contenedor `api`. Así el navegador ve todo como mismo origen y se
    // evitan problemas de CORS y de cookies cross-port.
    proxy: {
      '/api':     { target: 'http://api:8000', changeOrigin: false },
      '/sanctum': { target: 'http://api:8000', changeOrigin: false },
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: ['./src/test/setup.js'],
  },
});
