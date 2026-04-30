import type React from 'react';
import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { SuratKeluar, SuratKeluarForm } from './UmumSurat';
import { renderWithQueryClient } from '../test/render';

const {
  listMock,
  yearsMock,
  datesMock,
  formOptionsMock,
  getMock,
  createMock,
  insertMock,
  updateMock,
  deleteMock,
  navigateMock,
  locationMock,
} = vi.hoisted(() => ({
  listMock: vi.fn(),
  yearsMock: vi.fn(),
  datesMock: vi.fn(),
  formOptionsMock: vi.fn(),
  getMock: vi.fn(),
  createMock: vi.fn(),
  insertMock: vi.fn(),
  updateMock: vi.fn(),
  deleteMock: vi.fn(),
  navigateMock: vi.fn(),
  locationMock: vi.fn(() => ({ searchStr: '' })),
}));

vi.mock('@tanstack/react-router', () => ({
  Link: ({ children, to, search, ...props }: { children: React.ReactNode; to: string; search?: Record<string, string> }) => (
    <a href={`${to}${search ? `?${new URLSearchParams(search).toString()}` : ''}`} {...props}>
      {children}
    </a>
  ),
  useNavigate: () => navigateMock,
  useLocation: () => locationMock(),
}));

vi.mock('../lib/api-services', () => ({
  suratKeluarService: {
    list: listMock,
    years: yearsMock,
    dates: datesMock,
    formOptions: formOptionsMock,
    get: getMock,
    create: createMock,
    insert: insertMock,
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

vi.mock('../components/DatePicker', () => ({
  DatePicker: ({ value, onChange }: { value: string; onChange: (value: string) => void }) => (
    <input aria-label="Tanggal" value={value} onChange={(event) => onChange(event.target.value)} />
  ),
}));

vi.mock('motion/react', () => ({
  motion: {
    div: ({ children, ...props }: React.HTMLAttributes<HTMLDivElement>) => <div {...props}>{children}</div>,
  },
}));

const suratKeluarItem = {
  id: 1,
  bln: '04',
  thn: '2026',
  nomor: '0001',
  no_sisip: null,
  tanggal: '2026-04-24',
  tanggal_indo: '24 April 2026',
  no_surat: 'B-0001/32150/KA.220/2026',
  dari: 'Badan Pusat Statistik Kabupaten Karawang',
  tujuan: 'Dinas Pertanian',
  perihal: 'Undangan rapat koordinasi',
  isi_surat: '<p>Dengan hormat, isi surat.</p>',
  lampiran: 0,
  file: null,
  tembusan: null,
  sifat: 'Biasa',
  created_by: 1,
  created_at: '',
  updated_at: '',
};

function setupDefaultMocks() {
  listMock.mockResolvedValue({
    success: true,
    message: 'ok',
    data: [suratKeluarItem],
    meta: { per_page: 15, has_more: true, count: 1 },
    links: { next_cursor: 'cursor-next', prev_cursor: null },
    pagination_info: { total_page: 2, total_records: 16 },
  });
  yearsMock.mockResolvedValue(['2026', '2025']);
  datesMock.mockResolvedValue({ success: true, message: 'ok', data: ['2026-04-24'] });
  formOptionsMock.mockResolvedValue({
    success: true,
    message: 'ok',
    data: {
      nomor_baru: 2,
      settings: [
        { id: 1, tahun: '2026', key: 'NAMA_KANTOR', value: 'Badan Pusat Statistik Kabupaten Karawang', grup: 1 },
        { id: 2, tahun: '2026', key: 'ALAMAT_KANTOR', value: 'Jl. Cakradireja No 36 Nagasari Karawang', grup: 1 },
        { id: 3, tahun: '2026', key: 'KEPALA_KANTOR', value: 'Kepala BPS', grup: 2 },
        { id: 4, tahun: '2026', key: 'NIP_KEPALA', value: '19710101 199211 1001', grup: 2 },
        { id: 5, tahun: '2026', key: 'FORMAT_SURAT_KELUAR', value: 'B-{nomor}/32150/KA.220/{tahun}', grup: 4 },
      ],
    },
  });
  getMock.mockResolvedValue({ success: true, message: 'ok', data: suratKeluarItem });
  createMock.mockResolvedValue({ success: true, message: 'created', data: { ...suratKeluarItem, id: 2 } });
  insertMock.mockResolvedValue({ success: true, message: 'inserted', data: { ...suratKeluarItem, id: 3, no_sisip: '1' } });
  updateMock.mockResolvedValue({ success: true, message: 'updated', data: suratKeluarItem });
  deleteMock.mockResolvedValue({ success: true, message: 'deleted', data: null });
}

describe('SuratKeluar workflow', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    locationMock.mockReturnValue({ searchStr: '' });
    setupDefaultMocks();
  });

  it('loads the API-backed list', async () => {
    renderWithQueryClient(<SuratKeluar />);

    expect(await screen.findByText('B-0001/32150/KA.220/2026')).toBeInTheDocument();
    expect(screen.getByText('Dinas Pertanian')).toBeInTheDocument();
    expect(screen.getByText(/Halaman 1 dari 2/)).toBeInTheDocument();
  });

  it('uses cursor pagination on next page', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<SuratKeluar />);

    await screen.findByText('B-0001/32150/KA.220/2026');
    await user.click(screen.getByRole('button', { name: 'Berikutnya' }));

    await waitFor(() => {
      expect(listMock).toHaveBeenLastCalledWith(expect.objectContaining({ cursor: 'cursor-next' }));
    });
  });

  it('deletes a surat keluar from the row action menu', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<SuratKeluar />);

    await screen.findByText('B-0001/32150/KA.220/2026');
    await user.click(screen.getByLabelText('Menu aksi B-0001/32150/KA.220/2026'));
    await user.click(screen.getByRole('button', { name: 'Hapus Surat' }));
    await user.click(screen.getByRole('button', { name: 'Hapus' }));

    await waitFor(() => {
      expect(deleteMock).toHaveBeenCalledWith(1);
    });
  });

  it('creates a surat keluar from the separate form route', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<SuratKeluarForm />);

    await screen.findByDisplayValue('0002');
    await user.type(screen.getByLabelText('Tanggal'), '2026-04-24');
    await user.type(screen.getByLabelText(/Tujuan/), 'Dinas Kominfo');
    await user.type(screen.getByLabelText(/Perihal/), 'Permintaan data');
    await user.click(screen.getByRole('button', { name: 'Simpan' }));

    await waitFor(() => {
      expect(createMock).toHaveBeenCalledWith(expect.objectContaining({
        tanggal: '2026-04-24',
        tujuan: 'Dinas Kominfo',
        perihal: 'Permintaan data',
      }));
    });
  });

  it('updates an existing surat keluar when id is present', async () => {
    const user = userEvent.setup();
    locationMock.mockReturnValue({ searchStr: '?id=1' });
    renderWithQueryClient(<SuratKeluarForm />);

    await screen.findByDisplayValue('Dinas Pertanian');
    await user.clear(screen.getByLabelText(/Perihal/));
    await user.type(screen.getByLabelText(/Perihal/), 'Perihal diperbarui');
    await user.click(screen.getByRole('button', { name: 'Simpan' }));

    await waitFor(() => {
      expect(updateMock).toHaveBeenCalledWith(1, expect.objectContaining({ perihal: 'Perihal diperbarui' }));
    });
  });

  it('inserts a surat keluar when sisip search param is present', async () => {
    const user = userEvent.setup();
    locationMock.mockReturnValue({ searchStr: '?id=1&sisip=true' });
    renderWithQueryClient(<SuratKeluarForm />);

    await screen.findByDisplayValue('Dinas Pertanian');
    await user.click(screen.getByRole('button', { name: 'Simpan' }));

    await waitFor(() => {
      expect(insertMock).toHaveBeenCalledWith(expect.objectContaining({
        id: 1,
        tahun: '2026',
        no_sisip: '0001',
        nomor_sisip: '0001',
      }));
      expect(updateMock).not.toHaveBeenCalled();
    });
  });
});
