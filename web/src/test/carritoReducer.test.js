import { describe, expect, it } from 'vitest';
import { carritoReducer, initialState } from '../reducers/carritoReducer';

describe('carritoReducer', () => {
  it('ADD_LINE agrega una línea vacía con id único', () => {
    const s1 = carritoReducer(initialState, { type: 'ADD_LINE' });
    expect(s1.lineas).toHaveLength(1);
    expect(s1.lineas[0]).toMatchObject({ producto_id: '', cantidad: 1, precio: '' });
    expect(s1.lineas[0].id).toBeTruthy();

    const s2 = carritoReducer(s1, { type: 'ADD_LINE' });
    expect(s2.lineas).toHaveLength(2);
    expect(s2.lineas[0].id).not.toBe(s2.lineas[1].id);
  });

  it('UPDATE_LINE actualiza solo la línea con el id indicado', () => {
    const s1 = carritoReducer(initialState, { type: 'ADD_LINE' });
    const s2 = carritoReducer(s1, { type: 'ADD_LINE' });
    const targetId = s2.lineas[0].id;
    const otherId = s2.lineas[1].id;

    const s3 = carritoReducer(s2, {
      type: 'UPDATE_LINE',
      id: targetId,
      changes: { producto_id: '5', cantidad: 3, precio: '15.50' },
    });

    expect(s3.lineas.find((l) => l.id === targetId)).toMatchObject({
      producto_id: '5', cantidad: 3, precio: '15.50',
    });
    // La otra línea no cambia
    expect(s3.lineas.find((l) => l.id === otherId)).toMatchObject({
      producto_id: '', cantidad: 1, precio: '',
    });
  });

  it('REMOVE_LINE elimina por id, y RESET vacía el carrito', () => {
    let s = initialState;
    s = carritoReducer(s, { type: 'ADD_LINE' });
    s = carritoReducer(s, { type: 'ADD_LINE' });
    expect(s.lineas).toHaveLength(2);

    const idToRemove = s.lineas[0].id;
    s = carritoReducer(s, { type: 'REMOVE_LINE', id: idToRemove });
    expect(s.lineas).toHaveLength(1);
    expect(s.lineas.find((l) => l.id === idToRemove)).toBeUndefined();

    s = carritoReducer(s, { type: 'RESET' });
    expect(s).toEqual(initialState);
  });
});
