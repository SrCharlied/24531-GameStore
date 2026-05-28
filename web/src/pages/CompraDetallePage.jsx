import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../api/client';

export default function CompraDetallePage() {
  const { id } = useParams();
  const [compra, setCompra] = useState(null);
  const [lineas, setLineas] = useState([]);
  const [error, setError] = useState(null);

  useEffect(() => {
    setError(null);
    api.get(`/api/compras/${id}`)
      .then((res) => {
        setCompra(res.data.compra);
        setLineas(res.data.lineas || []);
      })
      .catch((e) => setError(e.response?.data?.message || 'No fue posible cargar el detalle de la compra.'));
  }, [id]);

  if (error) {
    return (
      <section className="panel">
        <div className="alert">{error}</div>
        <Link to="/compras" className="btn">Volver a compras</Link>
      </section>
    );
  }

  if (!compra) return <div className="loading">Cargando detalle…</div>;

  return (
    <>
      <section className="panel">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'start', gap: 12, flexWrap: 'wrap' }}>
          <div>
            <h2 style={{ marginBottom: 4 }}>Compra #{compra.id_compra}</h2>
            <p className="lead">Detalle completo de la venta y productos asociados.</p>
          </div>
          <Link to="/compras" className="btn">Volver a compras</Link>
        </div>

        <div className="grid auto-cards" style={{ marginTop: 16 }}>
          <div className="card">
            <p className="eyebrow">Cliente</p>
            <p style={{ marginBottom: 0 }}>{compra.nombre_cliente}</p>
          </div>
          <div className="card">
            <p className="eyebrow">Empleado</p>
            <p style={{ marginBottom: 0 }}>{compra.nombre_empleado}</p>
          </div>
          <div className="card">
            <p className="eyebrow">Local</p>
            <p style={{ marginBottom: 0 }}>{compra.local_nombre}</p>
          </div>
          <div className="card">
            <p className="eyebrow">Método</p>
            <p style={{ marginBottom: 0 }}>{compra.metodo_pago}</p>
          </div>
          <div className="card">
            <p className="eyebrow">Fecha</p>
            <p style={{ marginBottom: 0 }}>{new Date(compra.fecha_compra).toLocaleString()}</p>
          </div>
          <div className="card">
            <p className="eyebrow">Total</p>
            <p className="metric">${Number(compra.total_compra).toFixed(2)}</p>
          </div>
        </div>
      </section>

      <section className="panel">
        <h3 style={{ marginTop: 0 }}>Productos vendidos</h3>
        <table>
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Precio venta</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            {lineas.length === 0 ? (
              <tr><td colSpan="4">Esta compra no tiene líneas registradas.</td></tr>
            ) : lineas.map((l) => (
              <tr key={l.id_producto}>
                <td>{l.producto}</td>
                <td>{l.cantidad}</td>
                <td>${Number(l.precio_venta).toFixed(2)}</td>
                <td>${Number(l.subtotal).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
    </>
  );
}
