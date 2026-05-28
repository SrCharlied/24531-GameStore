import { useCallback, useEffect, useMemo, useReducer, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api/client';
import { carritoReducer, initialState as carritoInitial } from '../reducers/carritoReducer';
import { validateCompra } from '../utils/validation';

export default function CompraNuevaPage() {
  const navigate = useNavigate();
  const [catalogos, setCatalogos] = useState(null);
  const [loadingCat, setLoadingCat] = useState(true);
  const [form, setForm] = useState({ cliente: '', empleado: '', local: '', metodo: '' });
  const [state, dispatch] = useReducer(carritoReducer, carritoInitial);
  const [errors, setErrors] = useState({});
  const [serverError, setServerError] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    api.get('/api/catalogos')
      .then((res) => setCatalogos(res.data))
      .catch(() => setServerError('No se pudieron cargar los catálogos.'))
      .finally(() => setLoadingCat(false));
  }, []);

  // useMemo: calcula el total derivado del carrito en cada render solo si cambian las líneas.
  const total = useMemo(
    () => state.lineas.reduce(
      (sum, l) => sum + (Number(l.cantidad) || 0) * (Number(l.precio) || 0),
      0,
    ),
    [state.lineas],
  );

  // useCallback: estabiliza las refs de los handlers que se pasan a hijos.
  const updateLine = useCallback((id, changes) => {
    dispatch({ type: 'UPDATE_LINE', id, changes });
  }, []);

  const removeLine = useCallback((id) => {
    dispatch({ type: 'REMOVE_LINE', id });
  }, []);

  const productoPriceMap = useMemo(() => {
    if (!catalogos) return new Map();
    return new Map(catalogos.productos.map((p) => [String(p.id_producto), Number(p.precio_actual)]));
  }, [catalogos]);

  function handleSelectProducto(lineId, value) {
    const precioSugerido = productoPriceMap.get(value);
    const currentLine = state.lineas.find((l) => l.id === lineId);
    const changes = { producto_id: value };
    if (precioSugerido !== undefined && !currentLine.precio) {
      changes.precio = precioSugerido.toFixed(2);
    }
    updateLine(lineId, changes);
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setServerError(null);

    const v = validateCompra({ ...form, lineas: state.lineas });
    setErrors(v);
    if (Object.keys(v).length > 0) return;

    const payload = {
      cliente: Number(form.cliente),
      empleado: Number(form.empleado),
      local: Number(form.local),
      metodo: Number(form.metodo),
      productos: state.lineas.map((l) => ({
        id: Number(l.producto_id),
        cantidad: Number(l.cantidad),
        precio: Number(l.precio),
      })),
    };

    setSubmitting(true);
    try {
      const res = await api.post('/api/compras', payload);
      dispatch({ type: 'RESET' });
      navigate('/compras', { state: { flash: { type: 'success', text: res.data.message } } });
    } catch (err) {
      setServerError(err.response?.data?.message || 'No se pudo registrar la compra.');
    } finally {
      setSubmitting(false);
    }
  }

  if (loadingCat) return <div className="loading">Cargando catálogos…</div>;
  if (!catalogos) return <div className="alert">{serverError}</div>;

  return (
    <section className="panel">
      <h2>Registrar nueva compra</h2>
      <p className="lead">
        La compra se registra dentro de una transacción (función <code>registrar_compra()</code>):
        inserta en <code>COMPRA</code>, agrega líneas y descuenta inventario. Todo o nada.
      </p>

      {serverError && <div className="alert" style={{ marginTop: 12 }}>{serverError}</div>}

      <form onSubmit={handleSubmit} style={{ marginTop: 16 }} noValidate>
        <div className="grid cols-3">
          <Select
            id="cliente" label="Cliente"
            value={form.cliente} onChange={(v) => setForm({ ...form, cliente: v })}
            options={catalogos.clientes.map((c) => [c.id_cliente, c.nombre_cliente])}
            error={errors.cliente}
          />
          <Select
            id="empleado" label="Empleado"
            value={form.empleado} onChange={(v) => setForm({ ...form, empleado: v })}
            options={catalogos.empleados.map((e) => [e.id_empleado, e.nombre_empleado])}
            error={errors.empleado}
          />
          <Select
            id="local" label="Local"
            value={form.local} onChange={(v) => setForm({ ...form, local: v })}
            options={catalogos.locales.map((l) => [l.id_local, l.nombre])}
            error={errors.local}
          />
        </div>

        <Select
          id="metodo" label="Método de pago"
          value={form.metodo} onChange={(v) => setForm({ ...form, metodo: v })}
          options={catalogos.metodos_pago.map((m) => [m.id_metodo, m.nombre])}
          error={errors.metodo}
        />

        <div className="form-field">
          <label>Productos</label>
          {state.lineas.map((l) => (
            <CompraLine
              key={l.id}
              line={l}
              productos={catalogos.productos}
              onSelectProducto={handleSelectProducto}
              onChange={updateLine}
              onRemove={removeLine}
              disabled={submitting}
            />
          ))}
          <button
            type="button"
            className="btn btn-sm"
            style={{ marginTop: 8 }}
            disabled={submitting}
            onClick={() => dispatch({ type: 'ADD_LINE' })}
          >
            + Agregar línea
          </button>
          {errors.lineas && <p className="form-error">{errors.lineas}</p>}
          {state.lineas.length > 0 && (
            <p style={{ marginTop: 12, textAlign: 'right', fontFamily: 'var(--font-display)', color: 'var(--cyan)' }}>
              Total: ${total.toFixed(2)}
            </p>
          )}
        </div>

        <div className="form-actions">
          <button type="submit" className="btn btn-primary" disabled={submitting}>
            {submitting ? 'Registrando…' : 'Registrar compra'}
          </button>
          <button type="button" className="btn" onClick={() => navigate('/compras')} disabled={submitting}>
            Cancelar
          </button>
        </div>
      </form>
    </section>
  );
}

function Select({ id, label, value, onChange, options, error }) {
  return (
    <div className="form-field">
      <label htmlFor={id}>{label}</label>
      <select id={id} value={value} onChange={(e) => onChange(e.target.value)}>
        <option value="">Seleccione…</option>
        {options.map(([v, label]) => (
          <option key={v} value={String(v)}>{label}</option>
        ))}
      </select>
      {error && <p className="form-error">{error}</p>}
    </div>
  );
}

function CompraLine({ line, productos, onSelectProducto, onChange, onRemove, disabled = false }) {
  return (
    <div className="compra-line">
      <div className="form-field">
        <label>Producto</label>
        <select value={line.producto_id} disabled={disabled} onChange={(e) => onSelectProducto(line.id, e.target.value)}>
          <option value="">Seleccione un producto</option>
          {productos.map((p) => (
            <option key={p.id_producto} value={String(p.id_producto)}>{p.nombre}</option>
          ))}
        </select>
      </div>
      <div className="form-field">
        <label>Cantidad</label>
        <input
          type="number" min="1" step="1"
          disabled={disabled}
          value={line.cantidad}
          onChange={(e) => onChange(line.id, { cantidad: e.target.value })}
        />
      </div>
      <div className="form-field">
        <label>Precio</label>
        <input
          type="number" step="0.01" min="0.01"
          disabled={disabled}
          value={line.precio}
          onChange={(e) => onChange(line.id, { precio: e.target.value })}
        />
      </div>
      <button type="button" className="btn btn-sm btn-danger" disabled={disabled} onClick={() => onRemove(line.id)} title="Quitar">
        ×
      </button>
    </div>
  );
}
