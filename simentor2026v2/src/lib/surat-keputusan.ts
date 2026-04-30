import type { SkDetil } from '../types/api';

export type SkPersonnelKind = 'organik' | 'mitra_penugasan' | 'mitra_manual' | 'empty' | 'mixed';

export function getSkPersonnelKind(details: SkDetil[]): SkPersonnelKind {
  if (details.length === 0) return 'empty';

  let hasOrganik = false;
  let hasMitraPenugasan = false;
  let hasMitraManual = false;

  for (const detail of details) {
    if (detail.isOrganik) {
      hasOrganik = true;
    } else {
      if (detail.penugasan_id) {
        hasMitraPenugasan = true;
      } else {
        hasMitraManual = true;
      }
    }
  }

  const typesCount = [hasOrganik, hasMitraPenugasan, hasMitraManual].filter(Boolean).length;
  
  if (typesCount > 1) return 'mixed';
  
  if (hasOrganik) return 'organik';
  if (hasMitraPenugasan) return 'mitra_penugasan';
  if (hasMitraManual) return 'mitra_manual';
  
  return 'empty';
}

export function selectSuratKeputusanDocxEndpoint(skId: number, details: SkDetil[]): string | null {
  const kind = getSkPersonnelKind(details);
  
  switch (kind) {
    case 'mitra_penugasan':
      return `/surat/surat-keputusan/kpa/generate-docx/mitra/${skId}`;
    case 'mitra_manual':
      return `/surat/surat-keputusan/kpa/generate-docx/mitra/manual/${skId}`;
    case 'organik':
      return `/surat/surat-keputusan/kepala/generate-docx/organik/${skId}`;
    case 'mixed':
    case 'empty':
    default:
      return null;
  }
}
