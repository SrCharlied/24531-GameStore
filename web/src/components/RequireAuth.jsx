import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '../context/useAuth';

export default function RequireAuth({ role }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) return <div className="loading">Cargando…</div>;
  if (!user) return <Navigate to="/login" state={{ from: location }} replace />;
  if (role && user.rol !== role) return <Navigate to="/compras" replace />;

  return <Outlet />;
}
