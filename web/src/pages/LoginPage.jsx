import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/useAuth';

export default function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [form, setForm] = useState({ username: '', password: '' });
  const [errors, setErrors] = useState({});
  const [serverError, setServerError] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  function update(field) {
    return (e) => setForm({ ...form, [field]: e.target.value });
  }

  function validate() {
    const e = {};
    if (!form.username.trim()) e.username = 'El usuario es obligatorio.';
    if (!form.password) e.password = 'La contraseña es obligatoria.';
    return e;
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setServerError(null);
    const v = validate();
    setErrors(v);
    if (Object.keys(v).length > 0) return;

    setSubmitting(true);
    try {
      const user = await login(form.username, form.password);
      navigate(user.rol === 'admin' ? '/' : '/compras');
    } catch (err) {
      const msg = err.response?.data?.message || 'No se pudo iniciar sesión.';
      setServerError(msg);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="wrapper">
      <div className="panel" style={{ maxWidth: 420, margin: '40px auto' }}>
        <h2>Iniciar sesión</h2>
        <p className="lead">Ingresa con tu usuario para acceder al sistema.</p>

        {serverError && <div className="alert" style={{ marginTop: 12 }}>{serverError}</div>}

        <form onSubmit={handleSubmit} style={{ marginTop: 16 }} noValidate>
          <div className="form-field">
            <label htmlFor="username">Usuario</label>
            <input
              id="username"
              type="text"
              value={form.username}
              onChange={update('username')}
              autoFocus
            />
            {errors.username && <p className="form-error">{errors.username}</p>}
          </div>

          <div className="form-field">
            <label htmlFor="password">Contraseña</label>
            <input
              id="password"
              type="password"
              value={form.password}
              onChange={update('password')}
            />
            {errors.password && <p className="form-error">{errors.password}</p>}
          </div>

          <div className="form-actions">
            <button type="submit" className="btn btn-primary" disabled={submitting}>
              {submitting ? 'Entrando…' : 'Entrar'}
            </button>
          </div>
        </form>

        <div className="lead" style={{ marginTop: 16, fontSize: '0.85rem' }}>
          <strong>Usuarios de prueba:</strong>
          <ul style={{ margin: '8px 0 0', paddingLeft: 18, lineHeight: 1.7 }}>
            <li><code>admin / admin123</code> — acceso completo</li>
            <li><code>gerente / gerente123</code> — dashboard, reportes, lectura</li>
            <li><code>vendedor / vendedor123</code> — registrar y anular compras</li>
            <li><code>bodega / bodega123</code> — productos e inventario</li>
            <li><code>auditor / auditor123</code> — solo lectura + auditoría</li>
          </ul>
        </div>
      </div>
    </div>
  );
}
