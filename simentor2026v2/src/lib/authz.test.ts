import { beforeEach, describe, expect, it } from 'vitest';
import { getUserRoleNames, hasRequiredRole, canAccessPath } from './authz';
import type { AuthUser } from './auth.types';

function makeUser(roles: (string | { name: string })[]): AuthUser {
  return {
    id: 1,
    name: 'Test',
    email: 'test@example.com',
    email_verified_at: null,
    roles,
    permissions: [],
  };
}

describe('getUserRoleNames', () => {
  it('returns empty array for null user', () => {
    expect(getUserRoleNames(null)).toEqual([]);
  });

  it('returns empty array for undefined user', () => {
    expect(getUserRoleNames(undefined)).toEqual([]);
  });

  it('returns empty array for user with no roles', () => {
    expect(getUserRoleNames(makeUser([]))).toEqual([]);
  });

  it('extracts names from string roles', () => {
    expect(getUserRoleNames(makeUser(['admin', 'editor']))).toEqual(['admin', 'editor']);
  });

  it('extracts names from object roles', () => {
    expect(getUserRoleNames(makeUser([{ name: 'admin' }, { name: 'editor' }]))).toEqual(['admin', 'editor']);
  });

  it('filters out falsy role names', () => {
    expect(getUserRoleNames(makeUser(['admin', null as unknown as string, { name: '' }]))).toEqual(['admin']);
  });
});

describe('hasRequiredRole', () => {
  it('super-admin bypasses all role checks', () => {
    const user = makeUser(['super-admin']);
    expect(hasRequiredRole(user, 'katim')).toBe(true);
    expect(hasRequiredRole(user, ['admin', 'katim'])).toBe(true);
  });

  it('returns true when user has the required role', () => {
    const user = makeUser(['katim']);
    expect(hasRequiredRole(user, 'katim')).toBe(true);
  });

  it('returns true when user has one of the required roles', () => {
    const user = makeUser(['editor']);
    expect(hasRequiredRole(user, ['admin', 'editor'])).toBe(true);
  });

  it('returns false when user lacks the required role', () => {
    const user = makeUser(['editor']);
    expect(hasRequiredRole(user, 'katim')).toBe(false);
  });

  it('returns false for null user', () => {
    expect(hasRequiredRole(null, 'katim')).toBe(false);
  });
});

describe('canAccessPath', () => {
  beforeEach(() => {
    localStorage.clear();
    sessionStorage.clear();
  });

  it('returns true for unguarded paths', () => {
    expect(canAccessPath(makeUser(['editor']), '/skp/dashboard')).toBe(true);
  });

  it('returns true when user has required role for path', () => {
    expect(canAccessPath(makeUser(['super-admin']), '/admin/pengguna')).toBe(true);
  });

  it('returns false when user lacks required role for path', () => {
    expect(canAccessPath(makeUser(['editor']), '/admin/pengguna')).toBe(false);
  });

  it('returns true for null user on unguarded path', () => {
    expect(canAccessPath(null, '/skp/dashboard')).toBe(true);
  });

  it('returns false for null user on guarded path', () => {
    expect(canAccessPath(null, '/admin/pengguna')).toBe(false);
  });
});
