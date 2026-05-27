const ROLE_PERMISSIONS = {
  admin: {
    dashboard: true,
    reportes: true,
    auditoria: true,
    comprasRead: true,
    comprasWrite: true,
    productosRead: true,
    productosWrite: true,
  },
  gerente: {
    dashboard: true,
    reportes: true,
    auditoria: false,
    comprasRead: true,
    comprasWrite: false,
    productosRead: true,
    productosWrite: false,
  },
  vendedor: {
    dashboard: false,
    reportes: false,
    auditoria: false,
    comprasRead: true,
    comprasWrite: true,
    productosRead: false,
    productosWrite: false,
  },
  bodega: {
    dashboard: false,
    reportes: false,
    auditoria: false,
    comprasRead: false,
    comprasWrite: false,
    productosRead: true,
    productosWrite: true,
  },
  auditor: {
    dashboard: true,
    reportes: true,
    auditoria: true,
    comprasRead: true,
    comprasWrite: false,
    productosRead: true,
    productosWrite: false,
  },
};

export function can(role, permission) {
  return Boolean(ROLE_PERMISSIONS[role]?.[permission]);
}

export function canAny(role, permissions) {
  return permissions.some((permission) => can(role, permission));
}

export function rolesFor(permission) {
  return Object.entries(ROLE_PERMISSIONS)
    .filter(([, permissions]) => permissions[permission])
    .map(([role]) => role);
}

export { ROLE_PERMISSIONS };
