import { beforeEach, describe, expect, it, vi } from 'vitest';
import { apiGet, apiPost } from './api';

describe('api helpers', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
    localStorage.clear();
  });

  it('adds auth header and query params for GET requests', async () => {
    localStorage.setItem('auth_token', 'test-token');

    const fetchMock = vi.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      text: async () => JSON.stringify({ success: true, message: 'ok', data: [] }),
    } as Response);

    await apiGet('/admin/permissions/grouped-prefixes', {
      per_page: 6,
      page: 2,
      'filter[search]': 'raw-data',
    });

    expect(fetchMock).toHaveBeenCalledWith(
      'http://127.0.0.1:9001/api/admin/permissions/grouped-prefixes?per_page=6&page=2&filter%5Bsearch%5D=raw-data',
      expect.objectContaining({
        method: 'GET',
        headers: expect.objectContaining({
          Accept: 'application/json',
          Authorization: 'Bearer test-token',
        }),
      })
    );
  });

  it('sends JSON body for POST requests', async () => {
    const fetchMock = vi.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      text: async () => JSON.stringify({ success: true, message: 'created', data: { id: 1 } }),
    } as Response);

    await apiPost('/admin/permissions/bulk-create', { prefix: 'raw-data' });

    expect(fetchMock).toHaveBeenCalledWith(
      'http://127.0.0.1:9001/api/admin/permissions/bulk-create',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({ prefix: 'raw-data' }),
        headers: expect.objectContaining({
          Accept: 'application/json',
          'Content-Type': 'application/json',
        }),
      })
    );
  });
});
