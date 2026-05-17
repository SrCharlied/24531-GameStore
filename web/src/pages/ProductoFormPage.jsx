import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../api/client';
import { validateProducto } from '../utils/validation';

export default function ProductoFormPage() {
  const { id } = useParams();
  const isEdit = Boolean(id);
  const navigate = useNavigate();

  const [catalogos, setCatalogos] = useState(null);
  const [form, setForm] = useState({
    nombre: '',
    descripcion: '',
    precio_actual: '',
    franquicia: '',
    categorias: [],
    stock: {},
  });
  const [errors, setErrors] = useState({});
  const [serverError, setServerError] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    let cancelled = false;
    async function load() {
      try {
        const catRes = await api.get('/api/catalogos');
        if (cancelled) return;
        setCatalogos(catRes.data);

        if (isEdit) {
          const detRes = await api.get(`/api/productos/${id}`);
          if (cancelled) return;
          const p = detRes.data.producto;
          setForm({
            nombre: p.nombre || '',
            descripcion: p.descripcion || '',
            precio_actual: p.precio_actual || '',
            franquicia: String(p.franquicia_id || ''),
            categorias: detRes.data.categorias_ids.map(String),
            stock: Object.fromEntries(
              Object.entries(detRes.data.stock_por_local).map(([k, v]) => [k, String(v)])
            ),
          });
        }
      } catch {
        setServerError(isEdit ? 'No se pudo cargar el producto.' : 'No se pudieron cargar los catálogos.');
      } finally {
        if (!cancelled) setLoading(false);
      }
    }
    load();
    return () => { cancelled = true; };
  }, [id, isEdit]);

  function toggleCategoria(value) {
    setForm((f) => {
      const has = f.categorias.includes(value);
      return { ...f, categorias: has ? f.categorias.filter((c) => c !== value) : [...f.categorias, value] };
    });
  }

  function setStockFor(idLocal, value) {
    setForm((f) => ({ ...f, stock: { ...f.stock, [idLocal]: value } }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setServerError(null);
    const v = validateProducto(form);
    setErrors(v);
    if (Object.keys(v).length > 0) return;

    const payload = {
      nombre: form.nombre.trim(),
      descripcion: form.descripcion,
      precio_actual: Number(form.precio_actual),
      franquicia: Number(form.franquicia),
      categorias: form.categorias.map(Number),
      stock: Object.fromEntries(
        Object.entries(form.stock)
          .filter(([, val]) => val !== '' && val !== null && val !== undefined)
          .map(([k, v]) => [k, Number(v)])
      ),
    };

    setSubmitting(true);
    try {
      if (isEdit) {
        await api.put(`/api/productos/${id}`, payload);
      } else {
        await api.post('/api/productos', payload);
      }
      navigate('/productos');
    } catch (err) {
      const data = err.response?.data;
      if (data?.errors) setErrors(flattenLaravelErrors(data.errors));
      setServerError(data?.message || 'No se pudo guardar el producto.');
    } finally {
      setSubmitting(false);
    }
  }

  if (loading) return <div className="loading">Cargando…</div>;
  if (!catalogos) return <div className="alert">No fue posible cargar los catálogos.</div>;

  return (
    <section className="panel">
      <h2>{isEdit ? `Editar producto #${id}` : 'Crear nuevo producto'}</h2>
      <p className="lead">
        {isEdit
          ? 'Cualquier cambio de precio queda registrado en LOG_PRECIOS_PRODUCTO.'
          : 'Las categorías quedarán asociadas en PRODUCTO_CATEGORIA.'}
      </p>

      {serverError && <div className="alert" style={{ marginTop: 12 }}>{serverError}</div>}

      <form onSubmit={handleSubmit} style={{ marginTop: 16 }} noValidate>
        <div className="form-field">
          <label htmlFor="nombre">Nombre del producto</label>
          <input
            id="nombre"
            value={form.nombre}
            onChange={(e) => setForm({ ...form, nombre: e.target.value })}
          />
          {errors.nombre && <p className="form-error">{errors.nombre}</p>}
        </div>

        <div className="form-field">
          <label htmlFor="descripcion">Descripción</label>
          <textarea
            id="descripcion"
            rows="3"
            value={form.descripcion}
            onChange={(e) => setForm({ ...form, descripcion: e.target.value })}
          />
        </div>

        <div className="form-field">
          <label htmlFor="precio_actual">Precio</label>
          <input
            id="precio_actual"
            type="number"
            step="0.01"
            min="0.01"
            value={form.precio_actual}
            onChange={(e) => setForm({ ...form, precio_actual: e.target.value })}
          />
          {errors.precio_actual && <p className="form-error">{errors.precio_actual}</p>}
        </div>

        <div className="form-field">
          <label htmlFor="franquicia">Franquicia</label>
          <select
            id="franquicia"
            value={form.franquicia}
            onChange={(e) => setForm({ ...form, franquicia: e.target.value })}
          >
            <option value="">Seleccione una franquicia</option>
            {catalogos.franquicias.map((f) => (
              <option key={f.id_franquicia} value={String(f.id_franquicia)}>{f.nombre_franquicia}</option>
            ))}
          </select>
          {errors.franquicia && <p className="form-error">{errors.franquicia}</p>}
        </div>

        <div className="form-field">
          <label>Categorías</label>
          <div className="checkbox-grid">
            {catalogos.categorias.map((c) => (
              <label key={c.id_categoria}>
                <input
                  type="checkbox"
                  checked={form.categorias.includes(String(c.id_categoria))}
                  onChange={() => toggleCategoria(String(c.id_categoria))}
                />
                {c.nombre_categoria}
              </label>
            ))}
          </div>
          {errors.categorias && <p className="form-error">{errors.categorias}</p>}
        </div>

        <div className="form-field">
          <label>
            Stock por local{' '}
            <span style={{ color: 'var(--muted)', fontWeight: 'normal', fontSize: '0.85rem' }}>
              (deja vacío para no registrar inventario en ese local)
            </span>
          </label>
          <div className="grid cols-3" style={{ gap: 12 }}>
            {catalogos.locales.map((l) => (
              <div key={l.id_local}>
                <label style={{ fontWeight: 'normal', fontSize: '0.9rem', color: 'var(--muted)' }}>
                  {l.nombre} <small>({l.zona})</small>
                </label>
                <input
                  type="number"
                  min="0"
                  step="1"
                  placeholder="0"
                  value={form.stock[l.id_local] ?? ''}
                  onChange={(e) => setStockFor(l.id_local, e.target.value)}
                />
              </div>
            ))}
          </div>
        </div>

        <div className="form-actions">
          <button type="submit" className="btn btn-primary" disabled={submitting}>
            {submitting ? 'Guardando…' : isEdit ? 'Guardar cambios' : 'Guardar producto'}
          </button>
          <button type="button" className="btn" onClick={() => navigate('/productos')}>
            Cancelar
          </button>
        </div>
      </form>
    </section>
  );
}

function flattenLaravelErrors(errors) {
  const out = {};
  for (const [field, msgs] of Object.entries(errors)) {
    out[field] = Array.isArray(msgs) ? msgs[0] : msgs;
  }
  return out;
}
