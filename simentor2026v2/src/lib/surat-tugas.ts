import type { SurtugDetil } from '../types/api';

export type SurtugPersonnelKind = 'organik' | 'mitra' | 'empty' | 'mixed';

export function getSurtugPersonnelKind(details: SurtugDetil[]): SurtugPersonnelKind {
  const hasOrganik = details.some((detail) => detail.isOrganik === true);
  const hasMitra = details.some((detail) => detail.isOrganik === false);

  if (!hasOrganik && !hasMitra) return 'empty';
  if (hasOrganik && hasMitra) return 'mixed';
  return hasOrganik ? 'organik' : 'mitra';
}

export function selectSuratTugasDocxEndpoint(surtugId: number, details: SurtugDetil[]): string | null {
  if (details.length === 0) return null;

  const kind = getSurtugPersonnelKind(details);
  if (kind === 'mixed' || kind === 'empty') return null;

  if (kind === 'mitra') {
    return `/surat/surat-tugas/generate-docx/gab/mitra/${surtugId}`;
  }

  if (details.length === 1) {
    return `/surat/surat-tugas/generate-docx/satu/organik/${details[0].id}`;
  }

  return `/surat/surat-tugas/generate-docx/organik/${surtugId}`;
}
