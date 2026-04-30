import type React from 'react';
import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AdminIzin } from './AdminIzin';
import { renderWithQueryClient } from '../test/render';

const {
  groupedListMock,
  bulkCreateMock,
  updateMock,
  deleteMock,
} = vi.hoisted(() => ({
  groupedListMock: vi.fn(),
  bulkCreateMock: vi.fn(),
  updateMock: vi.fn(),
  deleteMock: vi.fn(),
}));

vi.mock('../lib/api-services', () => ({
  adminPermissionsService: {
    groupedList: groupedListMock,
    bulkCreate: bulkCreateMock,
    update: updateMock,
    delete: deleteMock,
  },
}));

vi.mock('../components/PageHeader', () => ({
  PageHeader: ({ title, description, actions }: { title: string; description: string; actions?: React.ReactNode }) => (
    <div>
      <h1>{title}</h1>
      <p>{description}</p>
      {actions}
    </div>
  ),
}));

vi.mock('motion/react', () => ({
  motion: {
    div: ({ children, ...props }: React.HTMLAttributes<HTMLDivElement>) => <div {...props}>{children}</div>,
  },
}));

describe('AdminIzin', () => {
  beforeEach(() => {
    groupedListMock.mockReset();
    bulkCreateMock.mockReset();
    updateMock.mockReset();
    deleteMock.mockReset();

    groupedListMock.mockResolvedValue({
      success: true,
      message: 'ok',
      data: [
        {
          id: 1,
          prefix: 'raw-data',
          guard_name: 'sanctum',
          permissions_count: 2,
          permissions: [
            { id: 10, name: 'raw-data-view', guard_name: 'sanctum' },
            { id: 11, name: 'raw-data-create', guard_name: 'sanctum' },
          ],
        },
      ],
      meta: {
        current_page: 1,
        last_page: 1,
        total: 1,
      },
    });

    bulkCreateMock.mockResolvedValue({
      success: true,
      message: 'created',
      data: { created: [], existing: [] },
    });

    updateMock.mockResolvedValue({
      success: true,
      message: 'updated',
      data: { id: 10, name: 'raw-data-view', guard_name: 'sanctum' },
    });

    deleteMock.mockResolvedValue({
      success: true,
      message: 'deleted',
      data: null,
    });
  });

  it('renders grouped permission cards from the API', async () => {
    renderWithQueryClient(<AdminIzin />);

    expect(await screen.findByText('raw-data')).toBeInTheDocument();
    expect(screen.getByText('raw-data-view')).toBeInTheDocument();
    expect(screen.getByText('raw-data-create')).toBeInTheDocument();
    expect(groupedListMock).toHaveBeenCalledWith({
      sort: 'prefix',
      'filter[search]': undefined,
    });
  });

  it('submits prefix search to the grouped endpoint', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<AdminIzin />);

    await screen.findByText('raw-data');

    await user.type(screen.getByPlaceholderText('Cari prefix, contoh: raw-data'), 'admin');
    await user.click(screen.getByRole('button', { name: 'Terapkan' }));

    await waitFor(() => {
      expect(groupedListMock).toHaveBeenLastCalledWith({
        sort: 'prefix',
        'filter[search]': 'admin',
      });
    });
  });

  it('creates permissions via bulk create modal', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<AdminIzin />);

    await screen.findByText('raw-data');

    await user.click(screen.getByRole('button', { name: /Tambah Izin/i }));
    await user.type(screen.getByPlaceholderText('Contoh: raw-data'), 'pegawai');
    await user.click(screen.getByRole('button', { name: 'Simpan' }));

    await waitFor(() => {
      expect(bulkCreateMock).toHaveBeenCalledWith({ prefix: 'pegawai' });
    });

    expect(await screen.findByText('Izin akses berhasil dibuat.')).toBeInTheDocument();
  });
});
