import type { AuthUser } from './auth.types';

export const ROLE_GUARDS: Record<string, string[]> = {
  '/admin/pengguna': ['super-admin'],
  '/admin/peran': ['super-admin'],
  '/admin/izin': ['super-admin'],
  '/admin/metadata': ['super-admin'],
  '/bank-data/raw': ['katim'],
  '/umum/rkk-dipa/revisi-import': ['katim'],
};

function normalizeRoleName(role: string | { name?: string } | null | undefined) {
  if (!role) return '';
  if (typeof role === 'string') return role;
  return role.name || '';
}

export function getUserRoleNames(user: AuthUser | null | undefined): string[] {
  if (!user?.roles) return [];
  return user.roles
    .map((role) => normalizeRoleName(role as string | { name?: string }))
    .filter(Boolean);
}

export function hasRequiredRole(user: AuthUser | null | undefined, requiredRoles: string | string[]) {
  const allowed = Array.isArray(requiredRoles) ? requiredRoles : [requiredRoles];
  const roleNames = getUserRoleNames(user);
  if (roleNames.includes('super-admin')) return true;
  return allowed.some((role) => roleNames.includes(role));
}

export function canAccessPath(user: AuthUser | null | undefined, pathname: string) {
  const requiredRoles = ROLE_GUARDS[pathname];
  if (!requiredRoles) return true;
  return hasRequiredRole(user, requiredRoles);
}

export function getStoredUser(): AuthUser | null {
  const raw = localStorage.getItem('auth_user') ?? sessionStorage.getItem('auth_user');
  if (!raw) return null;

  try {
    return JSON.parse(raw) as AuthUser;
  } catch {
    return null;
  }
}
