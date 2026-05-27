import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../context/useAuth';
import { can } from '../utils/permissions';

export default function ProductosListPage() {
  const { user } = useAuth();
  const [productos, setProductos] = useState([]);
  const [error, setError] = useState(null);
  const [flash, setFlash] = useState(null);
  const canWriteProducts = can(user?.rol, 'productosWrite');

  const load = useCallback(() => {
    api.get('/api/productos')
      .then((res) => setProductos(res.data.productos))
      .catch(() => setError('No fue posible cargar el listado de productos.'));
  }, []);

  useEffect(() => { load(); }, [load]);

  async function handleDelete(id) {
    if (!confirm('¿Eliminar este producto?')) return;
    try {
      await api.delete(`/api/productos/${id}`);
      setFlash({ type: 'success', text: 'Producto eliminado.' });
      load();
    } catch (e) {
      setFlash({ type: 'error', text: e.response?.data?.message || 'Error al eliminar.' });
    }
  }

  return (
    <section className="panel">
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
        <div>
          <h2 style={{ marginBottom: 4 }}>Productos</h2>
          <p className="lead">Listado con stock total y categorías.</p>
        </div>
        {canWriteProducts && (
          <Link to="/productos/nuevo" className="btn btn-primary">+ Crear producto</Link>
        )}
      </div>

      {error && <div className="alert" style={{ marginTop: 12 }}>{error}</div>}
      {flash && (
        <div className={flash.type === 'success' ? 'alert alert-success' : 'alert'} style={{ marginTop: 12 }}>
          {flash.text}
        </div>
      )}

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Franquicia</th>
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>{canWriteProducts ? 'Acciones' : 'Modo'}</th>
          </tr>
        </thead>
        <tbody>
          {productos.length === 0 ? (
            <tr><td colSpan="7">No hay productos para mostrar.</td></tr>
          ) : productos.map((p) => (
            <tr key={p.id_producto}>
              <td>#{p.id_producto}</td>
              <td>{p.nombre}</td>
              <td>{p.franquicia}</td>
              <td><span className="tag">{p.categoria}</span></td>
              <td>${Number(p.precio_actual).toFixed(2)}</td>
              <td>{p.stock_total}</td>
              <td>
                {canWriteProducts ? (
                  <div className="row-actions">
                    <Link to={`/productos/${p.id_producto}/editar`} className="btn btn-sm">Editar</Link>
                    <button type="button" className="btn btn-sm btn-danger" onClick={() => handleDelete(p.id_producto)}>
                      Eliminar
                    </button>
                  </div>
                ) : (
                  <span className="tag">Solo lectura</span>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </section>
  );
}
