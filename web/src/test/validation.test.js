import { describe, expect, it } from 'vitest';
import { validateCompra, validateDateRange, validateProducto } from '../utils/validation';

describe('validateProducto', () => {
  const valido = {
    nombre: 'Figura X',
    precio_actual: '149.99',
    franquicia: '3',
    categorias: ['1', '2'],
  };

  it('un formulario válido no devuelve errores', () => {
    expect(validateProducto(valido)).toEqual({});
  });

  it('detecta campos faltantes', () => {
    const errors = validateProducto({ nombre: '', precio_actual: '', franquicia: '', categorias: [] });
    expect(errors.nombre).toBeTruthy();
    expect(errors.precio_actual).toBeTruthy();
    expect(errors.franquicia).toBeTruthy();
    expect(errors.categorias).toBeTruthy();
  });

  it('rechaza precios <= 0', () => {
    const errors = validateProducto({ ...valido, precio_actual: '0' });
    expect(errors.precio_actual).toMatch(/mayor a 0/);
  });
});

describe('validateCompra', () => {
  const baseCompra = {
    cliente: '1', empleado: '1', local: '1', metodo: '1',
    lineas: [{ id: 'a', producto_id: '5', cantidad: 2, precio: '10.00' }],
  };

  it('una compra válida no devuelve errores', () => {
    expect(validateCompra(baseCompra)).toEqual({});
  });

  it('exige al menos una línea', () => {
    const errors = validateCompra({ ...baseCompra, lineas: [] });
    expect(errors.lineas).toMatch(/al menos una línea/);
  });

  it('rechaza una línea sin producto o con cantidad menor a 1', () => {
    const errors = validateCompra({
      ...baseCompra,
      lineas: [{ id: 'a', producto_id: '', cantidad: 0, precio: '10' }],
    });
    expect(errors.lineas).toBeTruthy();
  });

  it('rechaza productos duplicados en el carrito', () => {
    const errors = validateCompra({
      ...baseCompra,
      lineas: [
        { id: 'a', producto_id: '5', cantidad: 1, precio: '10' },
        { id: 'b', producto_id: '5', cantidad: 2, precio: '10' },
      ],
    });
    expect(errors.lineas).toMatch(/No repitas productos/);
  });
});

describe('validateDateRange', () => {
  it('permite rangos vacíos o cronológicos', () => {
    expect(validateDateRange({ desde: '', hasta: '2026-05-01' })).toBeNull();
    expect(validateDateRange({ desde: '2026-05-01', hasta: '2026-05-02' })).toBeNull();
  });

  it('rechaza rangos invertidos', () => {
    expect(validateDateRange({ desde: '2026-05-03', hasta: '2026-05-02' })).toMatch(/posterior/);
  });
});
