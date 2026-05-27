import { useCallback, useEffect, useMemo, useState } from 'react';
import api from '../api/client';

const initialFilters = {
  q: '',
  desde: '',
  hasta: '',
};

export default function AuditoriaPreciosPage() {
  const [logs, setLogs] = useState([]);
  const [filters, setFilters] = useState(initialFilters);
  const [error, setError] = useState(null);

  const params = useMemo(() => activeParams(filters), [filters]);

  const load = useCallback(() => {
    setError(null);
    api.get('/api/auditoria/precios', { params })
      .then((res) => setLogs(res.data.logs))
      .catch(() => setError('No fue posible cargar la auditoría de precios.'));
  }, [params]);

  useEffect(() => { load(); }, [load]);

  function updateFilter(name, value) {
    setFilters((current) => ({ ...current, [name]: value }));
  }

  function clearFilters() {
    setFilters(initialFilters);
  }

  return (
    <section className="panel">
      <div>
        <h2 style={{ marginBottom: 4 }}>Auditoría de precios</h2>
        <p className="lead">
          Cambios registrados automáticamente por el trigger <code>audit_precio_producto</code>.
        </p>
      </div>

      <div className="grid cols-3" style={{ marginTop: 16 }}>
        <div className="form-field">
          <label htmlFor="audit-q">Producto</label>
          <input
            id="audit-q"
            placeholder="Buscar por producto"
            value={filters.q}
            onChange={(e) => updateFilter('q', e.target.value)}
          />
        </div>
        <div className="form-field">
          <label htmlFor="audit-desde">Desde</label>
          <input
            id="audit-desde"
            type="date"
            value={filters.desde}
            onChange={(e) => updateFilter('desde', e.target.value)}
          />
        </div>
        <div className="form-field">
          <label htmlFor="audit-hasta">Hasta</label>
          <input
            id="audit-hasta"
            type="date"
            value={filters.hasta}
            onChange={(e) => updateFilter('hasta', e.target.value)}
          />
        </div>
      </div>
      <button type="button" className="btn btn-sm" onClick={clearFilters}>Limpiar filtros</button>

      {error && <div className="alert" style={{ marginTop: 12 }}>{error}</div>}

      <table>
        <thead>
          <tr>
            <th>ID log</th>
            <th>Producto</th>
            <th>Precio anterior</th>
            <th>Precio nuevo</th>
            <th>Diferencia</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          {logs.length === 0 ? (
            <tr><td colSpan="6">No hay cambios de precio para mostrar.</td></tr>
          ) : logs.map((log) => {
            const anterior = Number(log.precio_anterior);
            const nuevo = Number(log.precio_nuevo);
            const diferencia = nuevo - anterior;
            return (
              <tr key={log.id_log}>
                <td>#{log.id_log}</td>
                <td>{log.producto} <span className="tag">#{log.id_producto}</span></td>
                <td>${anterior.toFixed(2)}</td>
                <td>${nuevo.toFixed(2)}</td>
                <td className={diferencia >= 0 ? 'text-success' : 'text-danger'}>
                  {diferencia >= 0 ? '+' : ''}${diferencia.toFixed(2)}
                </td>
                <td>{new Date(log.fecha_cambio).toLocaleString()}</td>
              </tr>
            );
          })}
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
