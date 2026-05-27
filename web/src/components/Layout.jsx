import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { can } from '../utils/permissions';

export default function Layout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  async function handleLogout() {
    await logout();
    navigate('/login');
  }

  const role = user?.rol;

  return (
    <div className="wrapper">
      <header className="topbar">
        <div className="brand">
          <h1>GameStore</h1>
          <p>Sistema simple de inventario, compras y reportes.</p>
        </div>
        <nav className="nav">
          {can(role, 'dashboard') && <NavLink to="/" end>Dashboard</NavLink>}
          {can(role, 'productosRead') && <NavLink to="/productos">Productos</NavLink>}
          {can(role, 'comprasRead') && <NavLink to="/compras">Compras</NavLink>}
          {can(role, 'reportes') && <NavLink to="/reportes">Reportes</NavLink>}
          {can(role, 'auditoria') && <NavLink to="/auditoria/precios">Auditoría</NavLink>}

          {user && (
            <>
              <span className="user-pill">
                {user.username} · {user.rol}
              </span>
              <button type="button" className="btn btn-sm" onClick={handleLogout}>
                Cerrar sesión
              </button>
            </>
          )}
        </nav>
      </header>
      <main className="content">
        <Outlet />
      </main>
    </div>
  );
}
