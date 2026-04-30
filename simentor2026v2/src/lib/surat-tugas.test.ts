import { describe, expect, it } from 'vitest';
import { getSurtugPersonnelKind, selectSuratTugasDocxEndpoint } from './surat-tugas';
import type { SurtugDetil } from '../types/api';

function detail(id: number, isOrganik: boolean): SurtugDetil {
  return {
    id,
    surtug_id: 9,
    pegawai_id: isOrganik ? id : null,
    mitra_id: isOrganik ? null : id,
    grup_mitra: null,
    grup_pegawai: null,
    penugasan_id: null,
    dasar: 'Dasar',
    nama_kegiatan: 'Kegiatan',
    tugas_sebagai: 'Petugas',
    hari: 1,
    wilayah_kerja: 'Kecamatan',
    tgl_mulai: '2026-04-24',
    jenis_kendaraan: '',
    no_dipa: 'DIPA',
    isOrganik,
    sppd: false,
    created_at: '',
    updated_at: '',
  };
}

describe('surat tugas docx selector', () => {
  it('selects the single-organik endpoint by detail id', () => {
    expect(selectSuratTugasDocxEndpoint(9, [detail(4, true)])).toBe(
      '/surat/surat-tugas/generate-docx/satu/organik/4',
    );
  });

  it('selects the multi-organik endpoint by surat id', () => {
    expect(selectSuratTugasDocxEndpoint(9, [detail(4, true), detail(5, true)])).toBe(
      '/surat/surat-tugas/generate-docx/organik/9',
    );
  });

  it('selects the mitra gabungan endpoint for any mitra details', () => {
    expect(selectSuratTugasDocxEndpoint(9, [detail(7, false)])).toBe(
      '/surat/surat-tugas/generate-docx/gab/mitra/9',
    );
  });

  it('rejects empty and mixed detail sets', () => {
    expect(getSurtugPersonnelKind([])).toBe('empty');
    expect(selectSuratTugasDocxEndpoint(9, [])).toBeNull();
    expect(getSurtugPersonnelKind([detail(4, true), detail(7, false)])).toBe('mixed');
    expect(selectSuratTugasDocxEndpoint(9, [detail(4, true), detail(7, false)])).toBeNull();
  });
});
