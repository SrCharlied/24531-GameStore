import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '../context/useAuth';

export default function RequireAuth({ roles, fallback = '/compras' }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) return <div className="loading">Cargando…</div>;
  if (!user) return <Navigate to="/login" state={{ from: location }} replace />;

  const allowedRoles = roles ? (Array.isArray(roles) ? roles : [roles]) : null;
  if (allowedRoles && !allowedRoles.includes(user.rol)) {
    return <Navigate to={fallback} replace />;
  }

  return <Outlet />;
}
