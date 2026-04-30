import { describe, expect, it } from 'vitest';
import { getSkPersonnelKind, selectSuratKeputusanDocxEndpoint } from './surat-keputusan';
import type { SkDetil } from '../types/api';

function detail(id: number, isOrganik: boolean, penugasan_id: number | null = null): SkDetil {
  return {
    id,
    sk_id: 5,
    pegawai_id: isOrganik ? id : null,
    mitra_id: isOrganik ? null : id,
    penugasan_id,
    isOrganik,
    created_at: '',
    updated_at: '',
  };
}

describe('getSkPersonnelKind', () => {
  it('returns empty for empty array', () => {
    expect(getSkPersonnelKind([])).toBe('empty');
  });

  it('returns organik when all details are organik', () => {
    expect(getSkPersonnelKind([detail(1, true), detail(2, true)])).toBe('organik');
  });

  it('returns mitra_penugasan when all mitra have penugasan_id', () => {
    expect(getSkPersonnelKind([detail(10, false, 100), detail(11, false, 101)])).toBe('mitra_penugasan');
  });

  it('returns mitra_manual when all mitra have no penugasan_id', () => {
    expect(getSkPersonnelKind([detail(10, false, null), detail(11, false, null)])).toBe('mitra_manual');
  });

  it('returns mixed when organik and mitra are combined', () => {
    expect(getSkPersonnelKind([detail(1, true), detail(10, false, 100)])).toBe('mixed');
  });

  it('returns mixed when mitra_penugasan and mitra_manual are combined', () => {
    expect(getSkPersonnelKind([detail(10, false, 100), detail(11, false, null)])).toBe('mixed');
  });
});

describe('selectSuratKeputusanDocxEndpoint', () => {
  it('returns null for empty details', () => {
    expect(selectSuratKeputusanDocxEndpoint(5, [])).toBeNull();
  });

  it('returns mitra endpoint for mitra_penugasan', () => {
    expect(selectSuratKeputusanDocxEndpoint(5, [detail(10, false, 100)])).toBe(
      '/surat/surat-keputusan/kpa/generate-docx/mitra/5',
    );
  });

  it('returns mitra manual endpoint for mitra_manual', () => {
    expect(selectSuratKeputusanDocxEndpoint(5, [detail(10, false, null)])).toBe(
      '/surat/surat-keputusan/kpa/generate-docx/mitra/manual/5',
    );
  });

  it('returns organik endpoint for organik', () => {
    expect(selectSuratKeputusanDocxEndpoint(5, [detail(1, true)])).toBe(
      '/surat/surat-keputusan/kepala/generate-docx/organik/5',
    );
  });

  it('returns null for mixed personnel', () => {
    expect(selectSuratKeputusanDocxEndpoint(5, [detail(1, true), detail(10, false, 100)])).toBeNull();
  });
});
