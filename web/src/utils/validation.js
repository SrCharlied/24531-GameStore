export function validateProducto(form) {
  const errors = {};
  if (!form.nombre || !form.nombre.trim()) {
    errors.nombre = 'El nombre es obligatorio.';
  } else if (form.nombre.length > 150) {
    errors.nombre = 'Máximo 150 caracteres.';
  }
  const precio = Number(form.precio_actual);
  if (!form.precio_actual && form.precio_actual !== 0) {
    errors.precio_actual = 'El precio es obligatorio.';
  } else if (Number.isNaN(precio) || precio < 0.01) {
    errors.precio_actual = 'El precio debe ser mayor a 0.';
  }
  if (!form.franquicia) {
    errors.franquicia = 'Selecciona una franquicia.';
  }
  if (!Array.isArray(form.categorias) || form.categorias.length === 0) {
    errors.categorias = 'Selecciona al menos una categoría.';
  }
  return errors;
}

export function validateCompra(form) {
  const errors = {};
  if (!form.cliente)  errors.cliente  = 'Selecciona un cliente.';
  if (!form.empleado) errors.empleado = 'Selecciona un empleado.';
  if (!form.local)    errors.local    = 'Selecciona un local.';
  if (!form.metodo)   errors.metodo   = 'Selecciona un método de pago.';

  if (!Array.isArray(form.lineas) || form.lineas.length === 0) {
    errors.lineas = 'Agrega al menos una línea con producto, cantidad y precio.';
    return errors;
  }

  const lineasInvalidas = form.lineas.some((l) => {
    if (!l.producto_id) return true;
    const c = Number(l.cantidad);
    const p = Number(l.precio);
    return Number.isNaN(c) || c < 1 || Number.isNaN(p) || p < 0.01;
  });
  if (lineasInvalidas) {
    errors.lineas = 'Cada línea debe tener producto, cantidad ≥ 1 y precio > 0.';
  }
  return errors;
}
