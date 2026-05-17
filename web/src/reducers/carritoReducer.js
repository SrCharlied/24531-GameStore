// Reducer para el carrito de líneas en CompraNuevaPage.
// Acciones soportadas:
//   { type: 'ADD_LINE' }                                      -> agrega una línea vacía
//   { type: 'REMOVE_LINE', id }                                -> elimina una línea
//   { type: 'UPDATE_LINE', id, changes: {producto_id|cantidad|precio} } -> actualiza campos
//   { type: 'RESET' }                                          -> limpia el carrito

export const initialState = { lineas: [] };

export function carritoReducer(state, action) {
  switch (action.type) {
    case 'ADD_LINE': {
      const id = (globalThis.crypto?.randomUUID?.() ?? `tmp-${Date.now()}-${Math.random()}`);
      return {
        lineas: [
          ...state.lineas,
          { id, producto_id: '', cantidad: 1, precio: '' },
        ],
      };
    }
    case 'REMOVE_LINE':
      return { lineas: state.lineas.filter((l) => l.id !== action.id) };

    case 'UPDATE_LINE':
      return {
        lineas: state.lineas.map((l) =>
          l.id === action.id ? { ...l, ...action.changes } : l
        ),
      };

    case 'RESET':
      return initialState;

    default:
      return state;
  }
}
