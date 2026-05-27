import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import Layout from './components/Layout';
import RequireAuth from './components/RequireAuth';
import { AuthProvider } from './context/AuthContext';
import LoginPage from './pages/LoginPage';
import DashboardPage from './pages/DashboardPage';
import ProductosListPage from './pages/ProductosListPage';
import ProductoFormPage from './pages/ProductoFormPage';
import ComprasListPage from './pages/ComprasListPage';
import CompraNuevaPage from './pages/CompraNuevaPage';
import ReportesPage from './pages/ReportesPage';
import { rolesFor } from './utils/permissions';

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<LoginPage />} />

          <Route element={<RequireAuth />}>
            <Route element={<Layout />}>
              <Route element={<RequireAuth roles={rolesFor('comprasRead')} fallback="/productos" />}>
                <Route path="/compras" element={<ComprasListPage />} />
              </Route>

              <Route element={<RequireAuth roles={rolesFor('comprasWrite')} fallback="/compras" />}>
                <Route path="/compras/nueva" element={<CompraNuevaPage />} />
              </Route>

              <Route element={<RequireAuth roles={rolesFor('dashboard')} fallback="/compras" />}>
                <Route path="/" element={<DashboardPage />} />
              </Route>

              <Route element={<RequireAuth roles={rolesFor('productosRead')} fallback="/compras" />}>
                <Route path="/productos" element={<ProductosListPage />} />
              </Route>

              <Route element={<RequireAuth roles={rolesFor('productosWrite')} fallback="/productos" />}>
                <Route path="/productos/nuevo" element={<ProductoFormPage />} />
                <Route path="/productos/:id/editar" element={<ProductoFormPage />} />
              </Route>

              <Route element={<RequireAuth roles={rolesFor('reportes')} fallback="/compras" />}>
                <Route path="/reportes" element={<ReportesPage />} />
              </Route>
            </Route>
          </Route>

          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}
