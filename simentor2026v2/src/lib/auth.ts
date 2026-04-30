import { apiPost, apiGet, setToken, getToken } from './api';
import type { AuthUser, LoginResponse, MeUser } from './auth.types';

const TOKEN_KEY = 'auth_token';
const USER_KEY = 'auth_user';

function saveUser(user: AuthUser, remember = false) {
  if (remember) {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
    sessionStorage.removeItem(USER_KEY);
  } else {
    sessionStorage.setItem(USER_KEY, JSON.stringify(user));
    localStorage.removeItem(USER_KEY);
  }
}

function loadSavedUser(): AuthUser | null {
  const raw = localStorage.getItem(USER_KEY) ?? sessionStorage.getItem(USER_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

function clearAuth() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
  sessionStorage.removeItem(TOKEN_KEY);
  sessionStorage.removeItem(USER_KEY);
}

export async function login(email: string, password: string, remember = false): Promise<{ user: AuthUser; token: string }> {
  const res = await apiPost<LoginResponse>('/login', { email, password });

  const { user, token } = res.data;
  setToken(token, remember);
  saveUser(user, remember);

  return { user, token };
}

export async function logout(): Promise<void> {
  try {
    await apiPost<null>('/logout');
  } catch {
    // even if API fails, clear local state
  }
  clearAuth();
}

export async function fetchMe(): Promise<MeUser | null> {
  if (!getToken()) return null;
  try {
    const res = await apiGet<MeUser>('/profile');
    return res.data;
  } catch {
    clearAuth();
    return null;
  }
}

export function getSavedUser(): AuthUser | null {
  return loadSavedUser();
}

export function hasToken(): boolean {
  return !!getToken();
}

export function clearSession(): void {
  clearAuth();
}
