import { beforeEach, describe, expect, it, vi } from 'vitest';
import { hasToken, getSavedUser, clearSession } from './auth';
import type { AuthUser } from './auth.types';

describe('auth localStorage helpers', () => {
  beforeEach(() => {
    localStorage.clear();
    sessionStorage.clear();
    vi.restoreAllMocks();
  });

  it('hasToken returns false when no token stored', () => {
    expect(hasToken()).toBe(false);
  });

  it('hasToken returns true when token is in localStorage', () => {
    localStorage.setItem('auth_token', 'test-token');
    expect(hasToken()).toBe(true);
  });

  it('hasToken returns true when token is in sessionStorage', () => {
    sessionStorage.setItem('auth_token', 'test-token');
    expect(hasToken()).toBe(true);
  });

  it('getSavedUser returns null when no user stored', () => {
    expect(getSavedUser()).toBeNull();
  });

  it('getSavedUser parses user from localStorage', () => {
    const user: AuthUser = {
      id: 1,
      name: 'Test',
      email: 'test@example.com',
      email_verified_at: null,
      roles: ['admin'],
      permissions: [],
    };
    localStorage.setItem('auth_user', JSON.stringify(user));
    expect(getSavedUser()).toEqual(user);
  });

  it('getSavedUser returns null for invalid JSON', () => {
    localStorage.setItem('auth_user', 'not-json');
    expect(getSavedUser()).toBeNull();
  });

  it('clearSession removes all auth keys from both storages', () => {
    localStorage.setItem('auth_token', 'tok');
    localStorage.setItem('auth_user', '{}');
    sessionStorage.setItem('auth_token', 'tok2');
    sessionStorage.setItem('auth_user', '{}');

    clearSession();

    expect(localStorage.getItem('auth_token')).toBeNull();
    expect(localStorage.getItem('auth_user')).toBeNull();
    expect(sessionStorage.getItem('auth_token')).toBeNull();
    expect(sessionStorage.getItem('auth_user')).toBeNull();
  });
});
