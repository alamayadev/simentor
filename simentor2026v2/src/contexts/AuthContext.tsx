import React, { createContext, useCallback, useEffect, useState } from 'react';
import type { AuthUser } from '../lib/auth.types';
import * as authService from '../lib/auth';

interface AuthContextValue {
  user: AuthUser | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string, remember?: boolean) => Promise<AuthUser>;
  logout: () => Promise<void>;
  updateUser: (user: AuthUser) => void;
}

export const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(authService.getSavedUser());
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (!authService.hasToken()) {
      setIsLoading(false);
      return;
    }

    authService
      .fetchMe()
      .then((me) => {
        if (me) {
          setUser((prev) =>
            prev
              ? { ...prev, roles: me.roles, permissions: me.permissions, email: me.email }
              : { ...me, name: me.email.split('@')[0], email_verified_at: null }
          );
        } else {
          setUser(null);
        }
      })
      .finally(() => setIsLoading(false));
  }, []);

  const login = useCallback(async (email: string, password: string, remember = false): Promise<AuthUser> => {
    const { user: authUser } = await authService.login(email, password, remember);
    setUser(authUser);
    return authUser;
  }, []);

  const logout = useCallback(async () => {
    await authService.logout();
    setUser(null);
  }, []);

  const updateUser = useCallback((updated: AuthUser) => {
    setUser(updated);
    localStorage.setItem('auth_user', JSON.stringify(updated));
  }, []);

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        isLoading,
        login,
        logout,
        updateUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}
