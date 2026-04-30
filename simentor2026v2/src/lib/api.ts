const API_BASE_URL = `${import.meta.env.VITE_API_URL || 'http://127.0.0.1:9001'}/api`;
const APP_BASE_URL = API_BASE_URL.replace(/\/api$/, '');

export type ApiResponse<T> = {
  success: boolean;
  message: string;
  data: T;
  meta?: {
    current_page?: number;
    from?: number;
    last_page?: number;
    path?: string;
    per_page?: number;
    to?: number;
    total?: number;
    count?: number;
    has_more?: boolean;
  };
  links?: {
    first?: string | null;
    last?: string | null;
    prev?: string | null;
    next?: string | null;
    path?: string | null;
    next_cursor?: string | null;
    prev_cursor?: string | null;
    next_page_url?: string | null;
    prev_page_url?: string | null;
  };
  per_page?: number;
  pagination_info?: {
    total_page: number;
    total_records: number;
  };
};

class ApiError extends Error {
  status: number;
  data: unknown;

  constructor(status: number, message: string, data?: unknown) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
  }
}

const TOKEN_KEY = 'auth_token';

function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY) ?? sessionStorage.getItem(TOKEN_KEY);
}

function setToken(token: string | null, remember = false) {
  if (!token) {
    localStorage.removeItem(TOKEN_KEY);
    sessionStorage.removeItem(TOKEN_KEY);
    return;
  }

  if (remember) {
    localStorage.setItem(TOKEN_KEY, token);
    sessionStorage.removeItem(TOKEN_KEY);
  } else {
    sessionStorage.setItem(TOKEN_KEY, token);
    localStorage.removeItem(TOKEN_KEY);
  }
}

async function request<T>(
  path: string,
  options: RequestInit = {}
): Promise<ApiResponse<T>> {
  const token = getToken();
  const isFormData = options.body instanceof FormData;
  const headers: Record<string, string> = {
    'Accept': 'application/json',
    ...(options.headers as Record<string, string>),
  };

  if (!isFormData && options.body && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json';
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers,
  });

  const text = await response.text();
  const json = text ? JSON.parse(text) : {};

  if (!response.ok) {
    throw new ApiError(
      response.status,
      json.message || 'Request failed',
      json
    );
  }

  return json;
}

export function apiGet<T>(path: string, params?: Record<string, string | number | boolean | undefined>): Promise<ApiResponse<T>> {
  let url = path;
  if (params) {
    const searchParams = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== '') {
        searchParams.append(key, String(value));
      }
    });
    const qs = searchParams.toString();
    if (qs) url += `?${qs}`;
  }
  return request<T>(url, { method: 'GET' });
}

export function apiPost<T>(path: string, body?: unknown): Promise<ApiResponse<T>> {
  return request<T>(path, {
    method: 'POST',
    body: body ? JSON.stringify(body) : undefined,
  });
}

export function apiPostForm<T>(path: string, body: FormData): Promise<ApiResponse<T>> {
  return request<T>(path, {
    method: 'POST',
    body,
  });
}

export function apiPatch<T>(path: string, body?: unknown): Promise<ApiResponse<T>> {
  return request<T>(path, {
    method: 'PATCH',
    body: body ? JSON.stringify(body) : undefined,
  });
}

export function apiPut<T>(path: string, body?: unknown): Promise<ApiResponse<T>> {
  return request<T>(path, {
    method: 'PUT',
    body: body ? JSON.stringify(body) : undefined,
  });
}

export function apiPutForm<T>(path: string, body: FormData): Promise<ApiResponse<T>> {
  return request<T>(path, {
    method: 'PUT',
    body,
  });
}

export function apiDelete<T>(path: string): Promise<ApiResponse<T>> {
  return request<T>(path, { method: 'DELETE' });
}

export { setToken, getToken, ApiError, API_BASE_URL, APP_BASE_URL };
