import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../context/useAuth';
import { can } from '../utils/permissions';

const initialFilters = {
  q: '',
  franquicia: '',
  categoria: '',
  stock: '',
};

export default function ProductosListPage() {
  const { user } = useAuth();
  const [productos, setProductos] = useState([]);
  const [catalogos, setCatalogos] = useState({ franquicias: [], categorias: [] });
  const [filters, setFilters] = useState(initialFilters);
  const [error, setError] = useState(null);
  const [flash, setFlash] = useState(null);
  const canWriteProducts = can(user?.rol, 'productosWrite');

  const load = useCallback(() => {
    setError(null);
    api.get('/api/productos', { params: activeParams(filters) })
      .then((res) => setProductos(res.data.productos))
      .catch(() => setError('No fue posible cargar el listado de productos.'));
  }, [filters]);

  useEffect(() => { load(); }, [load]);

  useEffect(() => {
    api.get('/api/catalogos')
      .then((res) => setCatalogos({
        franquicias: res.data.franquicias || [],
        categorias: res.data.categorias || [],
      }))
      .catch(() => setCatalogos({ franquicias: [], categorias: [] }));
  }, []);

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

  function updateFilter(name, value) {
    setFilters((current) => ({ ...current, [name]: value }));
  }

  function clearFilters() {
    setFilters(initialFilters);
  }

  return (
    <section className="panel">
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
        <div>
          <h2 style={{ marginBottom: 4 }}>Productos</h2>
          <p className="lead">Listado con stock total, categorías y filtros de catálogo.</p>
        </div>
        {canWriteProducts && (
          <Link to="/productos/nuevo" className="btn btn-primary">+ Crear producto</Link>
        )}
      </div>

      <div className="grid cols-4" style={{ marginTop: 16 }}>
        <div className="form-field">
          <label htmlFor="producto-q">Buscar</label>
          <input
            id="producto-q"
            placeholder="Nombre, franquicia o categoría"
            value={filters.q}
            onChange={(e) => updateFilter('q', e.target.value)}
          />
        </div>
        <div className="form-field">
          <label htmlFor="producto-franquicia">Franquicia</label>
          <select
            id="producto-franquicia"
            value={filters.franquicia}
            onChange={(e) => updateFilter('franquicia', e.target.value)}
          >
            <option value="">Todas</option>
            {catalogos.franquicias.map((f) => (
              <option key={f.id_franquicia} value={String(f.id_franquicia)}>{f.nombre_franquicia}</option>
            ))}
          </select>
        </div>
        <div className="form-field">
          <label htmlFor="producto-categoria">Categoría</label>
          <select
            id="producto-categoria"
            value={filters.categoria}
            onChange={(e) => updateFilter('categoria', e.target.value)}
          >
            <option value="">Todas</option>
            {catalogos.categorias.map((c) => (
              <option key={c.id_categoria} value={String(c.id_categoria)}>{c.nombre_categoria}</option>
            ))}
          </select>
        </div>
        <div className="form-field">
          <label htmlFor="producto-stock">Stock</label>
          <select
            id="producto-stock"
            value={filters.stock}
            onChange={(e) => updateFilter('stock', e.target.value)}
          >
            <option value="">Todos</option>
            <option value="sin_stock">Sin stock</option>
            <option value="bajo">Bajo (1-10)</option>
            <option value="disponible">Disponible (&gt;10)</option>
          </select>
        </div>
      </div>
      <button type="button" className="btn btn-sm" onClick={clearFilters}>Limpiar filtros</button>

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
            <tr><td colSpan="7">No hay productos para mostrar con esos filtros.</td></tr>
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

function activeParams(filters) {
  return Object.fromEntries(
    Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined)
  );
}
