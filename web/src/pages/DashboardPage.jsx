import { useEffect, useState } from 'react';
import api from '../api/client';

export default function DashboardPage() {
  const [summary, setSummary] = useState(null);
  const [activity, setActivity] = useState([]);
  const [stockAlerts, setStockAlerts] = useState([]);
  const [error, setError] = useState(null);

  useEffect(() => {
    api.get('/api/dashboard')
      .then((res) => {
        setSummary(res.data.summary);
        setActivity(res.data.activity);
        setStockAlerts(res.data.stock_alerts || []);
      })
      .catch(() => setError('No fue posible cargar el dashboard.'));
  }, []);

  const stats = summary && [
    { label: 'Productos activos', value: summary.productos_activos },
    { label: 'Compras registradas', value: summary.compras_registradas },
    { label: 'Locales monitoreados', value: summary.locales_monitoreados },
    { label: 'Unidades en stock', value: summary.unidades_stock },
    { label: 'Sin stock', value: summary.productos_sin_stock, danger: Number(summary.productos_sin_stock) > 0 },
    { label: 'Stock bajo', value: summary.productos_stock_bajo, danger: Number(summary.productos_stock_bajo) > 0 },
    { label: 'Ingresos del mes', value: `$${Number(summary.ingresos_mes).toFixed(2)}` },
  ];

  return (
    <>
      <section className="panel">
        <h2>Dashboard</h2>
        <p className="lead">Métricas globales desde PostgreSQL con alertas operativas de inventario.</p>

        {error && <div className="alert" style={{ marginTop: 12 }}>{error}</div>}

        {stats && (
          <div className="grid auto-cards" style={{ marginTop: 16 }}>
            {stats.map((s) => (
              <div key={s.label} className={s.danger ? 'card card-danger' : 'card'}>
                <p className="eyebrow">{s.label}</p>
                <p className={s.danger ? 'metric metric-danger' : 'metric'}>{s.value}</p>
              </div>
            ))}
          </div>
        )}
      </section>

      <section className="panel">
        <h3 style={{ marginTop: 0 }}>Alertas de inventario</h3>
        <p className="lead">Productos sin stock o con 10 unidades o menos, ordenados por urgencia.</p>
        <table>
          <thead>
            <tr>
              <th>Producto</th>
              <th>Franquicia</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            {stockAlerts.length === 0 ? (
              <tr><td colSpan="6">Sin alertas de inventario por ahora.</td></tr>
            ) : stockAlerts.map((p) => {
              const stock = Number(p.stock_total);
              return (
                <tr key={p.id_producto}>
                  <td>{p.nombre}</td>
                  <td>{p.franquicia}</td>
                  <td>{p.categoria}</td>
                  <td>${Number(p.precio_actual).toFixed(2)}</td>
                  <td>{stock}</td>
                  <td>
                    <span className={stock === 0 ? 'tag tag-danger' : 'tag tag-warning'}>
                      {stock === 0 ? 'Sin stock' : 'Stock bajo'}
                    </span>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </section>

      <section className="panel">
        <h3 style={{ marginTop: 0 }}>Compras recientes</h3>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Local</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            {activity.length === 0 ? (
              <tr><td colSpan="4">Sin actividad reciente.</td></tr>
            ) : activity.map((c) => (
              <tr key={c.id_compra}>
                <td>#{c.id_compra}</td>
                <td>{c.nombre_cliente}</td>
                <td>{c.local_nombre}</td>
                <td>{new Date(c.fecha_compra).toLocaleString()}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
    </>
  );
}
