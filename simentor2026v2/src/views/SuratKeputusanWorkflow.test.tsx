import React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { SuratKeputusanWorkflow } from './SuratKeputusanWorkflow';
import { suratKeputusanService, skDetilService } from '../lib/api-services';
import type { SuratKeputusan } from '../types/api';

vi.mock('@tanstack/react-router', () => ({
  useLocation: () => ({ pathname: '/umum/surat/keputusan' }),
  Link: ({ children }: any) => <a>{children}</a>,
}));

// Mock dependencies
vi.mock('../lib/api-services', () => ({
  suratKeputusanService: {
    list: vi.fn(),
    years: vi.fn(),
    dates: vi.fn(),
    formOptions: vi.fn(),
  },
  skDetilService: {
    pegawaiOptions: vi.fn(),
    kegiatanOptions: vi.fn(),
    mitraOptions: vi.fn(),
  },
}));

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: false,
    },
  },
});

describe('SuratKeputusanWorkflow Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    vi.mocked(suratKeputusanService.list).mockResolvedValue({
      success: true,
      message: 'OK',
      data: {
        data: [] as SuratKeputusan[],
        meta: { total: 0, last_page: 1, has_more: false },
      },
    });

    vi.mocked(suratKeputusanService.years).mockResolvedValue({
      success: true,
      message: 'OK',
      data: ['2026', '2025'],
    });

    vi.mocked(suratKeputusanService.dates).mockResolvedValue({
      success: true,
      message: 'OK',
      data: [],
    });

    vi.mocked(suratKeputusanService.formOptions).mockResolvedValue({
      success: true,
      message: 'OK',
      data: {
        klasifikasi: [{ kode: 'A', keterangan: 'Klas A' }],
        nomor_baru_sk: 'SK-001',
        pejabat: [],
      },
    });

    vi.mocked(skDetilService.pegawaiOptions).mockResolvedValue({
      success: true,
      message: 'OK',
      data: [],
    });

    vi.mocked(skDetilService.kegiatanOptions).mockResolvedValue({
      success: true,
      message: 'OK',
      data: [],
    });

    vi.mocked(skDetilService.mitraOptions).mockResolvedValue({
      success: true,
      message: 'OK',
      data: [],
    });
  });

  const renderComponent = () => {
    return render(
      <QueryClientProvider client={queryClient}>
        <SuratKeputusanWorkflow />
      </QueryClientProvider>
    );
  };

  it('renders without crashing', async () => {
    renderComponent();
    await waitFor(() => {
      expect(screen.getByText(/Belum ada surat keputusan/i)).toBeInTheDocument();
    });
  });

  it('fetches and displays initial data', async () => {
    vi.mocked(suratKeputusanService.list).mockResolvedValue({
      success: true,
      message: 'OK',
      data: {
        data: [
          {
            id: 1,
            no_surat: 'SK-123',
            kepada: 'Tim BPS',
            tanggal: '2026-04-01',
          } as unknown as SuratKeputusan,
        ],
        meta: { total: 1, last_page: 1, has_more: false },
      },
    });

    renderComponent();

    await waitFor(() => {
      expect(screen.getByText('SK-123')).toBeInTheDocument();
      expect(screen.getByText('Tim BPS')).toBeInTheDocument();
    });
  });
});
