import type React from 'react';
import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { SuratTugasWorkflow } from './SuratTugasWorkflow';
import { renderWithQueryClient } from '../test/render';

const {
  listMock,
  getMock,
  formOptionsMock,
  yearsMock,
  datesMock,
  createMock,
  insertMock,
  updateMock,
  deleteHeaderMock,
  getBySurtugMock,
  createDetailMock,
  updateDetailMock,
  bulkMitraMock,
  deleteDetailMock,
  pegawaiOptionsMock,
  kegiatanOptionsMock,
  mitraOptionsMock,
  mitraPenugasanOptionsMock,
  downloadDocxMock,
} = vi.hoisted(() => ({
  listMock: vi.fn(),
  getMock: vi.fn(),
  formOptionsMock: vi.fn(),
  yearsMock: vi.fn(),
  datesMock: vi.fn(),
  createMock: vi.fn(),
  insertMock: vi.fn(),
  updateMock: vi.fn(),
  deleteHeaderMock: vi.fn(),
  getBySurtugMock: vi.fn(),
  createDetailMock: vi.fn(),
  updateDetailMock: vi.fn(),
  bulkMitraMock: vi.fn(),
  deleteDetailMock: vi.fn(),
  pegawaiOptionsMock: vi.fn(),
  kegiatanOptionsMock: vi.fn(),
  mitraOptionsMock: vi.fn(),
  mitraPenugasanOptionsMock: vi.fn(),
  downloadDocxMock: vi.fn(),
}));

vi.mock('../lib/api-services', () => ({
  suratTugasService: {
    list: listMock,
    get: getMock,
    formOptions: formOptionsMock,
    years: yearsMock,
    dates: datesMock,
    create: createMock,
    insert: insertMock,
    update: updateMock,
    delete: deleteHeaderMock,
    downloadDocx: downloadDocxMock,
  },
  surtugDetilService: {
    getBySurtug: getBySurtugMock,
    create: createDetailMock,
    update: updateDetailMock,
    bulkMitra: bulkMitraMock,
    delete: deleteDetailMock,
    pegawaiOptions: pegawaiOptionsMock,
    kegiatanOptions: kegiatanOptionsMock,
    mitraOptions: mitraOptionsMock,
    mitraPenugasanOptions: mitraPenugasanOptionsMock,
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

vi.mock('../components/DatePicker', () => ({
  DatePicker: ({
    value,
    onChange,
    placeholder = 'Pilih tanggal',
  }: {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
  }) => (
    <input
      aria-label={placeholder}
      value={value}
      onChange={(event) => onChange(event.target.value)}
    />
  ),
}));

vi.mock('@/components/ui/searchable-select', () => ({
  SearchableSelect: ({
    value,
    onChange,
    options,
    placeholder = 'Pilih opsi',
  }: {
    value: string;
    onChange: (value: string) => void;
    options: { value: string; label: string }[];
    placeholder?: string;
  }) => (
    <select aria-label={placeholder} value={value} onChange={(event) => onChange(event.target.value)}>
      <option value="">{placeholder}</option>
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  ),
}));

const header = {
  id: 1,
  tahun: '2026',
  tanggal: '2026-04-24',
  nomor: '0001',
  no_sisip: null,
  no_mix: '0001',
  no_surat: '0001/ST-100/2026',
  tanggal_indo: '24 April 2026',
  kode_klas: 'KP.650',
  kepada: 'Tim Survei',
  menimbang: '',
  uraian: 'Melaksanakan survei',
  file: null,
  created_by: 1,
  created_at: '',
  updated_at: '',
};

const organikDetail = {
  id: 10,
  surtug_id: 1,
  pegawai_id: 7,
  mitra_id: null,
  grup_mitra: null,
  grup_pegawai: null,
  penugasan_id: null,
  dasar: 'Dasar',
  nama_kegiatan: 'Survei',
  tugas_sebagai: 'Petugas',
  hari: 2,
  wilayah_kerja: 'Karawang',
  tgl_mulai: '2026-04-25',
  jenis_kendaraan: 'Motor',
  no_dipa: 'DIPA-001',
  isOrganik: true,
  sppd: true,
  created_at: '',
  updated_at: '',
  pegawai: { id: 7, nama: 'Budi Pegawai', nip: '123', jabatan: 'Statistisi' },
  mitra: null,
};

function setupDefaultMocks() {
  listMock.mockResolvedValue({
    success: true,
    message: 'ok',
    data: {
      data: [header],
      pagination_info: { total_page: 1, total_records: 1 },
      meta: { current_page: 1, last_page: 1, total: 1 },
    },
  });
  getMock.mockResolvedValue({ success: true, message: 'ok', data: header });
  formOptionsMock.mockResolvedValue({
    success: true,
    message: 'ok',
    data: {
      nomor_baru: '0002',
      klasifikasi: [{ kode: 'KP.650', keterangan: 'Surat perintah dinas/ surat tugas' }],
    },
  });
  yearsMock.mockResolvedValue({ success: true, message: 'ok', data: ['2026'] });
  datesMock.mockResolvedValue({ success: true, message: 'ok', data: ['2026-04-24'] });
  getBySurtugMock.mockResolvedValue({ success: true, message: 'ok', data: [] });
  pegawaiOptionsMock.mockResolvedValue({
    success: true,
    message: 'ok',
    data: [{ id: 7, nama: 'Budi Pegawai', nip: '123', jabatan: 'Statistisi' }],
  });
  kegiatanOptionsMock.mockResolvedValue({
    success: true,
    message: 'ok',
    data: [{ id: 3, tahun: '2026', nama: 'Survei Ubinan' }],
  });
  mitraOptionsMock.mockResolvedValue({
    success: true,
    message: 'ok',
    data: [{ id: 8, nama_lengkap: 'Mitra A', keca: 'Karawang' }],
  });
  mitraPenugasanOptionsMock.mockResolvedValue({
    success: true,
    message: 'ok',
    data: [{ id: 30, mitra_id: 8, mitra: { id: 8, nama_lengkap: 'Mitra A', keca: 'Karawang' } }],
  });
  createMock.mockResolvedValue({ success: true, message: 'created', data: { ...header, id: 2, no_surat: '0002/ST-100/2026' } });
  insertMock.mockResolvedValue({ success: true, message: 'inserted', data: { ...header, id: 3, no_sisip: '1', no_mix: '0001.1' } });
  updateMock.mockResolvedValue({ success: true, message: 'updated', data: header });
  deleteHeaderMock.mockResolvedValue({ success: true, message: 'deleted', data: null });
  createDetailMock.mockResolvedValue({ success: true, message: 'created', data: organikDetail });
  updateDetailMock.mockResolvedValue({ success: true, message: 'updated', data: organikDetail });
  bulkMitraMock.mockResolvedValue({
    success: true,
    message: 'bulk created',
    data: { created: 1, failed: 0, details: [], failed_details: [] },
  });
  deleteDetailMock.mockResolvedValue({ success: true, message: 'deleted', data: null });
  downloadDocxMock.mockResolvedValue(new Blob(['docx']));
}

describe('SuratTugasWorkflow', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    setupDefaultMocks();
  });

  it('loads the API-backed list and keeps DOCX disabled without details', async () => {
    renderWithQueryClient(<SuratTugasWorkflow />);

    expect(await screen.findByText('0001/ST-100/2026')).toBeInTheDocument();
    expect(screen.getAllByText('Tim Survei').length).toBeGreaterThan(0);
    expect(await screen.findByRole('button', { name: 'DOCX' })).toBeDisabled();
  });

  it('renders the flat paginated list response returned by Laravel', async () => {
    listMock.mockResolvedValueOnce({
      success: true,
      message: 'ok',
      data: [header],
      meta: { per_page: 10, has_more: false, count: 1 },
      pagination_info: { total_page: 1, total_records: 1 },
    });

    renderWithQueryClient(<SuratTugasWorkflow />);

    expect(await screen.findByText('0001/ST-100/2026')).toBeInTheDocument();
    expect(screen.queryByText('Belum ada surat tugas.')).not.toBeInTheDocument();
  });

  it('toggles menimbang and uraian details from the first column', async () => {
    const user = userEvent.setup();
    listMock.mockResolvedValueOnce({
      success: true,
      message: 'ok',
      data: {
        data: [
          {
            ...header,
            menimbang: 'Dasar pertimbangan lapangan',
            uraian: 'Rincian tugas pendataan lapangan',
          },
        ],
        pagination_info: { total_page: 1, total_records: 1 },
        meta: { current_page: 1, last_page: 1, total: 1 },
      },
    });

    renderWithQueryClient(<SuratTugasWorkflow />);

    await screen.findByText('0001/ST-100/2026');
    expect(screen.queryByText('Dasar pertimbangan lapangan')).not.toBeInTheDocument();

    await user.click(screen.getByRole('button', { name: /Tampilkan info 0001\/ST-100\/2026/i }));

    expect(await screen.findByText('Dasar pertimbangan lapangan')).toBeInTheDocument();
    expect(screen.getByText('Rincian tugas pendataan lapangan')).toBeInTheDocument();

    await user.click(screen.getByRole('button', { name: /Sembunyikan info 0001\/ST-100\/2026/i }));

    await waitFor(() => {
      expect(screen.queryByText('Dasar pertimbangan lapangan')).not.toBeInTheDocument();
    });
  });

  it('creates a Surat Tugas header', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<SuratTugasWorkflow />);

    await screen.findByText('0001/ST-100/2026');
    await user.click(screen.getByRole('button', { name: /Buat Surat/i }));
    await user.clear(screen.getByPlaceholderText('2026'));
    await user.type(screen.getByPlaceholderText('2026'), '2026');
    await user.selectOptions(screen.getByLabelText('Pilih klasifikasi'), 'KP.650');
    await user.type(screen.getByPlaceholderText('Tim Survei'), 'Petugas Lapangan');
    await user.type(screen.getByPlaceholderText('Uraian tugas'), 'Melaksanakan pendataan lapangan');
    await user.click(screen.getByRole('button', { name: 'Simpan' }));

    await waitFor(() => {
      expect(createMock).toHaveBeenCalledWith(
        expect.objectContaining({
          tahun: '2026',
          kode_klas: 'KP.650',
          kepada: 'Petugas Lapangan',
          uraian: 'Melaksanakan pendataan lapangan',
        }),
      );
    });
  });

  it('creates a sisip header with the selected nomor', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<SuratTugasWorkflow />);

    await screen.findByText('0001/ST-100/2026');
    await user.click(screen.getByRole('button', { name: /Menu aksi 0001\/ST-100\/2026/i }));
    await user.click(screen.getByRole('button', { name: /Sisip 0001\/ST-100\/2026/i }));
    await user.click(screen.getByRole('button', { name: 'Simpan' }));

    await waitFor(() => {
      expect(insertMock).toHaveBeenCalledWith(expect.objectContaining({ nomor: '0001' }));
    });
  });

  it('adds an organik detail record', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<SuratTugasWorkflow />);

    await screen.findByText('0001/ST-100/2026');
    await user.click(screen.getByRole('button', { name: 'Organik' }));
    await user.selectOptions(screen.getByLabelText('Pilih pegawai'), '7');
    await user.type(screen.getByLabelText('Dasar'), 'Surat dasar');
    await user.type(screen.getByLabelText('Nama Kegiatan'), 'Survei Ubinan');
    await user.type(screen.getByLabelText('Tugas Sebagai'), 'PCL');
    await user.clear(screen.getByLabelText('Jumlah Hari'));
    await user.type(screen.getByLabelText('Jumlah Hari'), '2');
    await user.type(screen.getByLabelText('Wilayah Kerja'), 'Karawang');
    await user.clear(screen.getByLabelText('Tanggal mulai'));
    await user.type(screen.getByLabelText('Tanggal mulai'), '2026-04-25');
    await user.type(screen.getByLabelText('No DIPA'), 'DIPA-001');
    await user.click(screen.getByRole('button', { name: 'Simpan' }));

    await waitFor(() => {
      expect(createDetailMock).toHaveBeenCalledWith(
        expect.objectContaining({
          surtug_id: 1,
          pegawai_id: 7,
          isOrganik: true,
          nama_kegiatan: 'Survei Ubinan',
        }),
      );
    });
  });

  it('bulk creates mitra detail records', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<SuratTugasWorkflow />);

    await screen.findByText('0001/ST-100/2026');
    await user.click(screen.getByRole('button', { name: /Bulk Mitra/i }));
    await user.selectOptions(screen.getByLabelText('Pilih kegiatan'), '3');
    expect(await screen.findByText('Mitra A')).toBeInTheDocument();
    await user.click(screen.getByLabelText('Mitra A'));
    await user.type(screen.getByLabelText('Dasar'), 'Surat dasar');
    await user.clear(screen.getByLabelText('Nama Kegiatan'));
    await user.type(screen.getByLabelText('Nama Kegiatan'), 'Survei Ubinan');
    await user.type(screen.getByLabelText('Tugas Sebagai'), 'PCL');
    await user.clear(screen.getByLabelText('Jumlah Hari'));
    await user.type(screen.getByLabelText('Jumlah Hari'), '2');
    await user.type(screen.getByLabelText('Wilayah Kerja'), 'Karawang');
    await user.clear(screen.getByLabelText('Tanggal mulai'));
    await user.type(screen.getByLabelText('Tanggal mulai'), '2026-04-25');
    await user.type(screen.getByLabelText('No DIPA'), 'DIPA-001');
    await user.click(screen.getByRole('button', { name: 'Simpan' }));

    await waitFor(() => {
      expect(bulkMitraMock).toHaveBeenCalledWith(
        expect.objectContaining({
          surtug_id: 1,
          kegiatan_id: 3,
          mitra_ids: [8],
        }),
      );
    });
  });

  it('deletes a detail record', async () => {
    getBySurtugMock.mockResolvedValue({ success: true, message: 'ok', data: [organikDetail] });
    const user = userEvent.setup();
    renderWithQueryClient(<SuratTugasWorkflow />);

    expect(await screen.findByText('Budi Pegawai')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: 'Hapus detail' }));
    await user.click(screen.getByRole('button', { name: 'Hapus' }));

    await waitFor(() => {
      expect(deleteDetailMock).toHaveBeenCalledWith(10);
    });
  });

  it('deletes a header record', async () => {
    const user = userEvent.setup();
    renderWithQueryClient(<SuratTugasWorkflow />);

    await screen.findByText('0001/ST-100/2026');
    await user.click(screen.getByRole('button', { name: /Menu aksi 0001\/ST-100\/2026/i }));
    await user.click(screen.getByRole('button', { name: /Hapus 0001\/ST-100\/2026/i }));
    await user.click(screen.getByRole('button', { name: 'Hapus' }));

    await waitFor(() => {
      expect(deleteHeaderMock).toHaveBeenCalledWith(1);
    });
  });
});
