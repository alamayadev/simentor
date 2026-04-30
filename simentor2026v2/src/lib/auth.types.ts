import type { User, Role, Permission } from '../types/api';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  current_team_id?: number | null;
  profile_photo_path?: string | null;
  created_at?: string;
  updated_at?: string;
  roles: Role[] | string[];
  permissions: Permission[] | string[];
}

export interface MeUser {
  id: number;
  name: string;
  email: string;
  roles: string[];
  permissions: string[];
}

export interface LoginResponse {
  user: User;
  roles: Role[];
  permissions: Permission[];
  token: string;
}

export interface AuthState {
  user: AuthUser | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}
