import { useEffect, useState } from 'react';
import api from '../api/client';

export default function ReportesPage() {
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    api.get('/api/reportes')
      .then((res) => setData(res.data))
      .catch(() => setError('No fue posible cargar los reportes.'));
  }, []);

  if (error) return <div className="panel"><div className="alert">{error}</div></div>;
  if (!data) return <div className="loading">Cargando reportes…</div>;

  return (
    <>
      <section className="panel">
        <h2>Top 5 locales por ingreso</h2>
        <p className="lead">Agregación con GROUP BY sobre <code>COMPRA + COMPRA_PRODUCTOS</code>.</p>
        <table>
          <thead>
            <tr><th>Local</th><th>Compras</th><th>Ingreso total</th></tr>
          </thead>
          <tbody>
            {data.ventas_por_local.map((r) => (
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
            {data.top_productos.map((r) => (
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
        <p className="lead">Clientes que gastan por encima del promedio (subquery anidada con <code>AVG</code>).</p>
        <table>
          <thead>
            <tr><th>Cliente</th><th>Compras</th><th>Total gastado</th></tr>
          </thead>
          <tbody>
            {data.clientes_destacados.map((r) => (
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
