import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../context/useAuth';
import { can } from '../utils/permissions';
import { validateDateRange } from '../utils/validation';

const initialFilters = {
  q: '',
  local: '',
  desde: '',
  hasta: '',
};

export default function ComprasListPage() {
  const { user } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const [compras, setCompras] = useState([]);
  const [locales, setLocales] = useState([]);
  const [filters, setFilters] = useState(initialFilters);
  const [error, setError] = useState(null);
  const [flash, setFlash] = useState(location.state?.flash || null);
  const [anulandoId, setAnulandoId] = useState(null);
  const canWriteCompras = can(user?.rol, 'comprasWrite');

  const dateError = validateDateRange(filters);
  const params = useMemo(() => activeParams(filters), [filters]);

  const load = useCallback(() => {
    if (dateError) return;
    setError(null);
    api.get('/api/compras', { params })
      .then((res) => setCompras(res.data.compras))
      .catch(() => setError('No fue posible cargar el historial de compras.'));
  }, [dateError, params]);

  useEffect(() => { load(); }, [load]);

  useEffect(() => {
    if (!location.state?.flash) return;
    navigate(location.pathname, { replace: true, state: {} });
  }, [location.pathname, location.state?.flash, navigate]);

  useEffect(() => {
    api.get('/api/catalogos')
      .then((res) => setLocales(res.data.locales || []))
      .catch(() => setLocales([]));
  }, []);

  async function handleAnular(id) {
    if (!confirm(`¿Anular la compra #${id}? Se devolverá el inventario y se eliminará el registro de venta.`)) return;
    setAnulandoId(id);
    setFlash(null);
    try {
      await api.delete(`/api/compras/${id}`);
      setFlash({ type: 'success', text: `Compra #${id} anulada.` });
      load();
    } catch (e) {
      setFlash({ type: 'error', text: e.response?.data?.message || 'Error al anular.' });
    } finally {
      setAnulandoId(null);
    }
  }

  function updateFilter(name, value) {
    setFilters((current) => ({ ...current, [name]: value }));
  }

  function clearFilters() {
    setFilters(initialFilters);
  }

  const csvQuery = new URLSearchParams(params).toString();
  const csvUrl = `${api.defaults.baseURL}/api/compras/export.csv${csvQuery ? `?${csvQuery}` : ''}`;

  return (
    <section className="panel">
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
        <div>
          <h2 style={{ marginBottom: 4 }}>Compras</h2>
          <p className="lead">Historial real calculado desde PostgreSQL con filtros exportables.</p>
        </div>
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          <a href={csvUrl} className="btn">Exportar CSV</a>
          {canWriteCompras && (
            <Link to="/compras/nueva" className="btn btn-primary">+ Registrar compra</Link>
          )}
        </div>
      </div>

      <div className="grid cols-4" style={{ marginTop: 16 }}>
        <div className="form-field">
          <label htmlFor="compra-q">Buscar</label>
          <input
            id="compra-q"
            placeholder="Cliente, empleado o local"
            value={filters.q}
            onChange={(e) => updateFilter('q', e.target.value)}
          />
        </div>
        <div className="form-field">
          <label htmlFor="compra-local">Local</label>
          <select
            id="compra-local"
            value={filters.local}
            onChange={(e) => updateFilter('local', e.target.value)}
          >
            <option value="">Todos</option>
            {locales.map((l) => (
              <option key={l.id_local} value={String(l.id_local)}>{l.nombre}</option>
            ))}
          </select>
        </div>
        <div className="form-field">
          <label htmlFor="compra-desde">Desde</label>
          <input
            id="compra-desde"
            type="date"
            value={filters.desde}
            onChange={(e) => updateFilter('desde', e.target.value)}
          />
        </div>
        <div className="form-field">
          <label htmlFor="compra-hasta">Hasta</label>
          <input
            id="compra-hasta"
            type="date"
            value={filters.hasta}
            onChange={(e) => updateFilter('hasta', e.target.value)}
          />
        </div>
      </div>
      <button type="button" className="btn btn-sm" onClick={clearFilters}>Limpiar filtros</button>

      {dateError && <div className="alert" style={{ marginTop: 12 }}>{dateError}</div>}
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
            <th>Cliente</th>
            <th>Empleado</th>
            <th>Local</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>{canWriteCompras ? 'Acciones' : 'Modo'}</th>
          </tr>
        </thead>
        <tbody>
          {compras.length === 0 ? (
            <tr><td colSpan="7">No hay compras para mostrar con esos filtros.</td></tr>
          ) : compras.map((c) => (
            <tr key={c.id_compra}>
              <td>#{c.id_compra}</td>
              <td>{c.nombre_cliente}</td>
              <td>{c.nombre_empleado}</td>
              <td>{c.local_nombre}</td>
              <td>{new Date(c.fecha_compra).toLocaleString()}</td>
              <td>${Number(c.total_compra).toFixed(2)}</td>
              <td>
                <div className="row-actions">
                  <Link to={`/compras/${c.id_compra}`} className="btn btn-sm">Ver detalle</Link>
                  {canWriteCompras ? (
                    <button
                      type="button"
                      className="btn btn-sm btn-danger"
                      disabled={anulandoId === c.id_compra}
                      onClick={() => handleAnular(c.id_compra)}
                    >
                      {anulandoId === c.id_compra ? 'Anulando…' : 'Anular'}
                    </button>
                  ) : (
                    <span className="tag">Solo lectura</span>
                  )}
                </div>
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
