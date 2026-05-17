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

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<LoginPage />} />

          <Route element={<RequireAuth />}>
            <Route element={<Layout />}>
              {/* Empleado y admin */}
              <Route path="/compras" element={<ComprasListPage />} />
              <Route path="/compras/nueva" element={<CompraNuevaPage />} />

              {/* Solo admin */}
              <Route element={<RequireAuth role="admin" />}>
                <Route path="/" element={<DashboardPage />} />
                <Route path="/productos" element={<ProductosListPage />} />
                <Route path="/productos/nuevo" element={<ProductoFormPage />} />
                <Route path="/productos/:id/editar" element={<ProductoFormPage />} />
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
