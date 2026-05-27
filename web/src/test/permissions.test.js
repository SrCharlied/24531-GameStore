import { describe, expect, it } from 'vitest';
import { can, canAny, rolesFor } from '../utils/permissions';

describe('permissions', () => {
  it('permite a bodega administrar productos sin abrir compras', () => {
    expect(can('bodega', 'productosRead')).toBe(true);
    expect(can('bodega', 'productosWrite')).toBe(true);
    expect(can('bodega', 'comprasRead')).toBe(false);
    expect(can('bodega', 'comprasWrite')).toBe(false);
  });

  it('permite a vendedor registrar compras sin administrar productos', () => {
    expect(can('vendedor', 'comprasRead')).toBe(true);
    expect(can('vendedor', 'comprasWrite')).toBe(true);
    expect(can('vendedor', 'productosWrite')).toBe(false);
  });

  it('deja a gerente y auditor en modo lectura/reportes', () => {
    expect(can('gerente', 'dashboard')).toBe(true);
    expect(can('gerente', 'reportes')).toBe(true);
    expect(can('gerente', 'comprasWrite')).toBe(false);
    expect(can('auditor', 'productosRead')).toBe(true);
    expect(can('auditor', 'productosWrite')).toBe(false);
  });

  it('resuelve listas de roles para rutas protegidas', () => {
    expect(rolesFor('productosWrite')).toEqual(['admin', 'bodega']);
    expect(rolesFor('comprasWrite')).toEqual(['admin', 'vendedor']);
    expect(canAny('gerente', ['dashboard', 'productosWrite'])).toBe(true);
  });
});
