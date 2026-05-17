import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/useAuth';

export default function Layout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  async function handleLogout() {
    await logout();
    navigate('/login');
  }

  const isAdmin = user?.rol === 'admin';

  return (
    <div className="wrapper">
      <header className="topbar">
        <div className="brand">
          <h1>GameStore</h1>
          <p>Sistema simple de inventario, compras y reportes.</p>
        </div>
        <nav className="nav">
          {isAdmin && <NavLink to="/" end>Dashboard</NavLink>}
          {isAdmin && <NavLink to="/productos">Productos</NavLink>}
          <NavLink to="/compras">Compras</NavLink>
          {isAdmin && <NavLink to="/reportes">Reportes</NavLink>}

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
