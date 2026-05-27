import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../context/useAuth';
import { can } from '../utils/permissions';

export default function ComprasListPage() {
  const { user } = useAuth();
  const [compras, setCompras] = useState([]);
  const [error, setError] = useState(null);
  const [flash, setFlash] = useState(null);
  const canWriteCompras = can(user?.rol, 'comprasWrite');

  const load = useCallback(() => {
    api.get('/api/compras')
      .then((res) => setCompras(res.data.compras))
      .catch(() => setError('No fue posible cargar el historial de compras.'));
  }, []);

  useEffect(() => { load(); }, [load]);

  async function handleAnular(id) {
    if (!confirm(`¿Anular la compra #${id}? Se devolverá el inventario.`)) return;
    try {
      await api.delete(`/api/compras/${id}`);
      setFlash({ type: 'success', text: `Compra #${id} anulada.` });
      load();
    } catch (e) {
      setFlash({ type: 'error', text: e.response?.data?.message || 'Error al anular.' });
    }
  }

  const csvUrl = `${api.defaults.baseURL}/api/compras/export.csv`;

  return (
    <section className="panel">
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
        <div>
          <h2 style={{ marginBottom: 4 }}>Compras</h2>
          <p className="lead">Historial real calculado desde PostgreSQL.</p>
        </div>
        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          <a href={csvUrl} className="btn">Exportar CSV</a>
          {canWriteCompras && (
            <Link to="/compras/nueva" className="btn btn-primary">+ Registrar compra</Link>
          )}
        </div>
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
            <tr><td colSpan="7">No hay compras para mostrar.</td></tr>
          ) : compras.map((c) => (
            <tr key={c.id_compra}>
              <td>#{c.id_compra}</td>
              <td>{c.nombre_cliente}</td>
              <td>{c.nombre_empleado}</td>
              <td>{c.local_nombre}</td>
              <td>{new Date(c.fecha_compra).toLocaleString()}</td>
              <td>${Number(c.total_compra).toFixed(2)}</td>
              <td>
                {canWriteCompras ? (
                  <button type="button" className="btn btn-sm btn-danger" onClick={() => handleAnular(c.id_compra)}>
                    Anular
                  </button>
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
