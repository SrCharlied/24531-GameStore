import { useCallback, useEffect, useMemo, useState } from 'react';
import api from '../api/client';

const initialFilters = {
  local: '',
  desde: '',
  hasta: '',
};

export default function ReportesPage() {
  const [data, setData] = useState(null);
  const [locales, setLocales] = useState([]);
  const [filters, setFilters] = useState(initialFilters);
  const [error, setError] = useState(null);

  const params = useMemo(() => activeParams(filters), [filters]);

  const load = useCallback(() => {
    setError(null);
    api.get('/api/reportes', { params })
      .then((res) => setData(res.data))
      .catch(() => setError('No fue posible cargar los reportes.'));
  }, [params]);

  useEffect(() => { load(); }, [load]);

  useEffect(() => {
    api.get('/api/catalogos')
      .then((res) => setLocales(res.data.locales || []))
      .catch(() => setLocales([]));
  }, []);

  function updateFilter(name, value) {
    setFilters((current) => ({ ...current, [name]: value }));
  }

  function clearFilters() {
    setFilters(initialFilters);
  }

  if (error) return <div className="panel"><div className="alert">{error}</div></div>;
  if (!data) return <div className="loading">Cargando reportes…</div>;

  return (
    <>
      <section className="panel">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start', gap: 12, flexWrap: 'wrap' }}>
          <div>
            <h2 style={{ marginBottom: 4 }}>Reportes</h2>
            <p className="lead">Indicadores comerciales filtrables por local y rango de fechas.</p>
          </div>
          <button type="button" className="btn btn-sm" onClick={clearFilters}>Limpiar filtros</button>
        </div>

        <div className="grid cols-3" style={{ marginTop: 16 }}>
          <div className="form-field">
            <label htmlFor="reporte-local">Local</label>
            <select
              id="reporte-local"
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
            <label htmlFor="reporte-desde">Desde</label>
            <input
              id="reporte-desde"
              type="date"
              value={filters.desde}
              onChange={(e) => updateFilter('desde', e.target.value)}
            />
          </div>
          <div className="form-field">
            <label htmlFor="reporte-hasta">Hasta</label>
            <input
              id="reporte-hasta"
              type="date"
              value={filters.hasta}
              onChange={(e) => updateFilter('hasta', e.target.value)}
            />
          </div>
        </div>
      </section>

      <section className="panel">
        <h2>Top 5 locales por ingreso</h2>
        <p className="lead">Agregación con GROUP BY sobre <code>COMPRA + COMPRA_PRODUCTOS</code>.</p>
        <table>
          <thead>
            <tr><th>Local</th><th>Compras</th><th>Ingreso total</th></tr>
          </thead>
          <tbody>
            {data.ventas_por_local.length === 0 ? (
              <tr><td colSpan="3">Sin ventas en el rango seleccionado.</td></tr>
            ) : data.ventas_por_local.map((r) => (
              <tr key={r.local_nombre}>
                <td>{r.local_nombre}</td>
                <td>{r.total_compras}</td>
                <td>${Number(r.ingreso_total).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>

      <section className="panel">
        <h2>Top 5 productos más vendidos</h2>
        <p className="lead">Usa una CTE (<code>WITH</code>) para precalcular ventas por producto.</p>
        <table>
          <thead>
            <tr><th>Producto</th><th>Unidades vendidas</th><th>Ingreso generado</th></tr>
          </thead>
          <tbody>
            {data.top_productos.length === 0 ? (
              <tr><td colSpan="3">Sin productos vendidos en el rango seleccionado.</td></tr>
            ) : data.top_productos.map((r) => (
              <tr key={r.id_producto}>
                <td>{r.nombre}</td>
                <td>{r.unidades_vendidas}</td>
                <td>${Number(r.ingreso_generado).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>

      <section className="panel">
        <h2>Clientes destacados</h2>
        <p className="lead">Clientes que gastan por encima del promedio del rango filtrado.</p>
        <table>
          <thead>
            <tr><th>Cliente</th><th>Compras</th><th>Total gastado</th></tr>
          </thead>
          <tbody>
            {data.clientes_destacados.length === 0 ? (
              <tr><td colSpan="3">Sin clientes destacados para estos filtros.</td></tr>
            ) : data.clientes_destacados.map((r) => (
              <tr key={r.nombre_cliente}>
                <td>{r.nombre_cliente}</td>
                <td>{r.total_compras}</td>
                <td>${Number(r.total_gastado).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
    </>
  );
}

function activeParams(filters) {
  return Object.fromEntries(
    Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined)
  );
}
