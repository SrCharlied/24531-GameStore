import { useEffect, useState } from 'react';
import api from '../api/client';

export default function DashboardPage() {
  const [summary, setSummary] = useState(null);
  const [activity, setActivity] = useState([]);
  const [error, setError] = useState(null);

  useEffect(() => {
    api.get('/api/dashboard')
      .then((res) => {
        setSummary(res.data.summary);
        setActivity(res.data.activity);
      })
      .catch(() => setError('No fue posible cargar el dashboard.'));
  }, []);

  const stats = summary && [
    { label: 'Productos activos', value: summary.productos_activos },
    { label: 'Compras registradas', value: summary.compras_registradas },
    { label: 'Locales monitoreados', value: summary.locales_monitoreados },
    { label: 'Unidades en stock', value: summary.unidades_stock },
  ];

  return (
    <section className="panel">
      <h2>Dashboard</h2>
      <p className="lead">Métricas globales desde PostgreSQL.</p>

      {error && <div className="alert" style={{ marginTop: 12 }}>{error}</div>}

      {stats && (
        <div className="grid cols-4" style={{ marginTop: 16 }}>
          {stats.map((s) => (
            <div key={s.label} className="card">
              <p className="eyebrow">{s.label}</p>
              <p className="metric">{s.value}</p>
            </div>
          ))}
        </div>
      )}

      <h3 style={{ marginTop: 24 }}>Compras recientes</h3>
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
  );
}
