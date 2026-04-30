import React, { useEffect, useMemo, useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { motion } from 'motion/react';
import {
  Calendar,
  ChevronDown,
  Download,
  Ellipsis,
  FileText,
  Loader2,
  Pencil,
  Plus,
  RefreshCw,
  Search,
  Trash2,
  Users,
  X,
} from 'lucide-react';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal, type FormField } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { DatePicker } from '../components/DatePicker';
import { useApiMutation, useApiQuery } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { suratTugasService, surtugDetilService } from '../lib/api-services';
import { getSurtugPersonnelKind, selectSuratTugasDocxEndpoint } from '../lib/surat-tugas';
import type {
  PaginatedResponse,
  SuratTugas,
  SurtugDetil,
  SurtugKegiatanOption,
  SurtugMitraOption,
  SurtugMitraPenugasanOption,
  SurtugPegawaiOption,
} from '../types/api';
import { SearchableSelect } from '@/components/ui/searchable-select';

type NoticeState = {
  tone: 'success' | 'error';
  message: string;
} | null;

type HeaderModalMode = 'create' | 'edit' | 'insert' | null;
type DetailModalState = {
  mode: 'create' | 'edit';
  type: 'organik' | 'mitra';
  bulk: boolean;
  item?: SurtugDetil;
} | null;

type DetailFormData = {
  pegawai_id: string;
  mitra_id: string;
  penugasan_id: string;
  kegiatan_id: string;
  mitra_ids: string[];
  dasar: string;
  nama_kegiatan: string;
  tugas_sebagai: string;
  hari: string;
  wilayah_kerja: string;
  tgl_mulai: string;
  jenis_kendaraan: string;
  no_dipa: string;
  sppd: boolean;
};

type DeleteTarget =
  | { type: 'header'; item: SuratTugas }
  | { type: 'detail'; item: SurtugDetil }
  | null;

const pageSize = 10;

type ListPayload<T> = PaginatedResponse<T> | T[];

function extractRows<T>(payload?: ListPayload<T>): T[] {
  if (!payload) return [];
  if (Array.isArray(payload)) return payload;
  return Array.isArray(payload.data) ? payload.data : [];
}

function formatDate(value?: string | null) {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(parsed);
}

function toText(value: unknown) {
  return String(value ?? '').trim();
}

function toNullableNumber(value: string) {
  const parsed = Number(value);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
}

function createEmptyDetailForm(selected?: SuratTugas | null): DetailFormData {
  return {
    pegawai_id: '',
    mitra_id: '',
    penugasan_id: '',
    kegiatan_id: '',
    mitra_ids: [],
    dasar: '',
    nama_kegiatan: '',
    tugas_sebagai: '',
    hari: '1',
    wilayah_kerja: '',
    tgl_mulai: selected?.tanggal ?? '',
    jenis_kendaraan: '',
    no_dipa: '',
    sppd: false,
  };
}

function detailToForm(item: SurtugDetil): DetailFormData {
  return {
    pegawai_id: item.pegawai_id ? String(item.pegawai_id) : '',
    mitra_id: item.mitra_id ? String(item.mitra_id) : '',
    penugasan_id: item.penugasan_id ? String(item.penugasan_id) : '',
    kegiatan_id: '',
    mitra_ids: [],
    dasar: item.dasar ?? '',
    nama_kegiatan: item.nama_kegiatan ?? '',
    tugas_sebagai: item.tugas_sebagai ?? '',
    hari: String(item.hari ?? 1),
    wilayah_kerja: item.wilayah_kerja ?? '',
    tgl_mulai: item.tgl_mulai ?? '',
    jenis_kendaraan: item.jenis_kendaraan ?? '',
    no_dipa: item.no_dipa ?? '',
    sppd: Boolean(item.sppd),
  };
}

function downloadBlob(blob: Blob, filename: string) {
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  link.remove();
  window.URL.revokeObjectURL(url);
}

function sanitizeFilename(value: string) {
  return value.replace(/[\\/:*?"<>|]+/g, '_');
}

export function SuratTugasWorkflow() {
  const queryClient = useQueryClient();
  const currentYear = new Date().getFullYear();

  const [searchInput, setSearchInput] = useState('');
  const [filterYear, setFilterYear] = useState(String(currentYear));
  const [filterDate, setFilterDate] = useState('');
  const [page, setPage] = useState(1);
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [notice, setNotice] = useState<NoticeState>(null);
  const [headerModalMode, setHeaderModalMode] = useState<HeaderModalMode>(null);
  const [detailModal, setDetailModal] = useState<DetailModalState>(null);
  const [detailForm, setDetailForm] = useState<DetailFormData>(() => createEmptyDetailForm());
  const [deleteTarget, setDeleteTarget] = useState<DeleteTarget>(null);
  const [isDownloading, setIsDownloading] = useState(false);
  const [openActionMenuId, setOpenActionMenuId] = useState<number | null>(null);
  const [expandedInfoIds, setExpandedInfoIds] = useState<number[]>([]);

  const debouncedSearch = useDebounce(searchInput, 350);

  const { data: formOptionsResponse } = useApiQuery(
    ['surat-tugas-form-options'],
    () => suratTugasService.formOptions(),
  );

  const { data: yearsResponse } = useApiQuery(
    ['surat-tugas-years'],
    () => suratTugasService.years(),
  );

  const { data: datesResponse } = useApiQuery(
    ['surat-tugas-dates', filterYear],
    () => suratTugasService.dates({ tahun: filterYear || undefined }),
  );

  const {
    data: listResponse,
    isLoading,
    error,
    refetch,
  } = useApiQuery(
    ['surat-tugas', debouncedSearch, filterYear, filterDate, page],
    () =>
      suratTugasService.list({
        per_page: pageSize,
        page,
        'filter[search]': debouncedSearch || undefined,
        'filter[tahun]': filterYear || undefined,
        'filter[tanggal]': filterDate || undefined,
        sort: '-nomor',
      }),
  );

  const { data: selectedHeaderResponse } = useApiQuery(
    ['surat-tugas-detail', selectedId],
    () => suratTugasService.get(selectedId as number),
    { enabled: selectedId !== null },
  );

  const {
    data: detailResponse,
    isLoading: isLoadingDetails,
    error: detailsError,
    refetch: refetchDetails,
  } = useApiQuery(
    ['surtug-details', selectedId],
    () => surtugDetilService.getBySurtug(selectedId as number),
    { enabled: selectedId !== null },
  );

  const { data: pegawaiOptionsResponse } = useApiQuery(
    ['surtug-pegawai-options'],
    () => surtugDetilService.pegawaiOptions(),
  );

  const { data: kegiatanOptionsResponse } = useApiQuery(
    ['surtug-kegiatan-options', filterYear],
    () => surtugDetilService.kegiatanOptions({ tahun: filterYear || String(currentYear) }),
  );

  const { data: mitraOptionsResponse } = useApiQuery(
    ['surtug-mitra-options'],
    () => surtugDetilService.mitraOptions(),
  );

  const selectedKegiatanId = Number(detailForm.kegiatan_id || 0);
  const { data: mitraPenugasanResponse, isLoading: isLoadingMitraPenugasan } = useApiQuery(
    ['surtug-mitra-penugasan-options', selectedKegiatanId],
    () => surtugDetilService.mitraPenugasanOptions(selectedKegiatanId),
    { enabled: Boolean(detailModal && detailModal.type === 'mitra' && selectedKegiatanId > 0) },
  );

  const listPayload = listResponse?.data;
  const nestedListPayload = Array.isArray(listPayload) ? undefined : listPayload;
  const rows = useMemo(() => extractRows(listPayload), [listPayload]);
  const selectedFromList = rows.find((item) => item.id === selectedId) ?? null;
  const selectedHeader = selectedHeaderResponse?.data ?? selectedFromList;
  const details = detailResponse?.data ?? [];
  const personnelKind = getSurtugPersonnelKind(details);
  const docxEndpoint = selectedId ? selectSuratTugasDocxEndpoint(selectedId, details) : null;

  const totalRecords =
    listResponse?.pagination_info?.total_records ??
    nestedListPayload?.pagination_info?.total_records ??
    listResponse?.meta?.total ??
    nestedListPayload?.meta?.total ??
    rows.length;
  const totalPages = Math.max(
    1,
    listResponse?.pagination_info?.total_page ??
      nestedListPayload?.pagination_info?.total_page ??
      listResponse?.meta?.last_page ??
      nestedListPayload?.meta?.last_page ??
      page,
  );
  const canNextPage = listResponse?.meta?.has_more ?? nestedListPayload?.meta?.has_more ?? page < totalPages;

  const klasifikasiOptions = useMemo(
    () =>
      (formOptionsResponse?.data?.klasifikasi ?? []).map((item) => ({
        value: item.kode,
        label: `${item.kode} - ${item.keterangan}`,
      })),
    [formOptionsResponse],
  );

  const yearOptions = useMemo(() => {
    const years = yearsResponse?.data ?? [];
    if (years.length) return years;
    return Array.from({ length: 5 }, (_, index) => String(currentYear - 3 + index));
  }, [currentYear, yearsResponse]);

  const dateOptions = datesResponse?.data ?? [];

  const pegawaiOptions = useMemo(
    () =>
      (pegawaiOptionsResponse?.data ?? []).map((item: SurtugPegawaiOption) => ({
        value: String(item.id),
        label: [item.nama, item.nip, item.jabatan].filter(Boolean).join(' - '),
      })),
    [pegawaiOptionsResponse],
  );

  const kegiatanOptions = useMemo(
    () =>
      (kegiatanOptionsResponse?.data ?? []).map((item: SurtugKegiatanOption) => ({
        value: String(item.id),
        label: `${item.tahun} - ${item.nama}`,
      })),
    [kegiatanOptionsResponse],
  );

  const mitraOptions = useMemo(
    () =>
      (mitraOptionsResponse?.data ?? []).map((item: SurtugMitraOption) => ({
        value: String(item.id),
        label: [item.nama_lengkap, item.keca].filter(Boolean).join(' - '),
      })),
    [mitraOptionsResponse],
  );

  const mitraPenugasanOptions = mitraPenugasanResponse?.data ?? [];

  const stats = useMemo(
    () => ({
      total: totalRecords,
      detail: details.length,
      tipe:
        personnelKind === 'organik'
          ? 'Organik'
          : personnelKind === 'mitra'
            ? 'Mitra'
            : personnelKind === 'mixed'
              ? 'Campuran'
              : '-',
      nomorBaru: formOptionsResponse?.data?.nomor_baru ?? '-',
    }),
    [details.length, formOptionsResponse, personnelKind, totalRecords],
  );

  useEffect(() => {
    if (!rows.length) {
      setSelectedId(null);
      return;
    }

    if (!selectedId || !rows.some((item) => item.id === selectedId)) {
      setSelectedId(rows[0].id);
    }
  }, [rows, selectedId]);

  useEffect(() => {
    setPage(1);
  }, [debouncedSearch, filterYear, filterDate]);

  useEffect(() => {
    if (page > totalPages) {
      setPage(totalPages);
    }
  }, [page, totalPages]);

  useEffect(() => {
    if (!detailModal || detailModal.type !== 'mitra' || !detailForm.kegiatan_id) return;
    const selected = kegiatanOptionsResponse?.data?.find((item) => String(item.id) === detailForm.kegiatan_id);
    if (!selected) return;

    setDetailForm((previous) => {
      if (previous.nama_kegiatan && detailModal.mode === 'edit') return previous;
      if (previous.nama_kegiatan === selected.nama) return previous;
      return { ...previous, nama_kegiatan: selected.nama };
    });
  }, [detailForm.kegiatan_id, detailModal, kegiatanOptionsResponse]);

  const createHeaderMutation = useApiMutation(
    (payload: Record<string, unknown>) => suratTugasService.create(payload),
    {
      invalidateKeys: [['surat-tugas'], ['surat-tugas-form-options'], ['surat-tugas-years'], ['surat-tugas-dates']],
      onSuccess: (response) => {
        setSelectedId(response.data.id);
        setHeaderModalMode(null);
        setNotice({ tone: 'success', message: response.message || 'Surat tugas berhasil dibuat.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal membuat surat tugas.' });
      },
    },
  );

  const insertHeaderMutation = useApiMutation(
    (payload: Record<string, unknown>) => suratTugasService.insert(payload),
    {
      invalidateKeys: [['surat-tugas'], ['surat-tugas-form-options'], ['surat-tugas-years'], ['surat-tugas-dates']],
      onSuccess: (response) => {
        setSelectedId(response.data.id);
        setHeaderModalMode(null);
        setNotice({ tone: 'success', message: response.message || 'Surat tugas sisip berhasil dibuat.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal membuat surat tugas sisip.' });
      },
    },
  );

  const updateHeaderMutation = useApiMutation(
    ({ id, payload }: { id: number; payload: FormData | Record<string, unknown> }) =>
      suratTugasService.update(id, payload),
    {
      invalidateKeys: [['surat-tugas'], ['surat-tugas-detail'], ['surat-tugas-dates']],
      onSuccess: (response) => {
        setHeaderModalMode(null);
        setNotice({ tone: 'success', message: response.message || 'Surat tugas berhasil diperbarui.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal memperbarui surat tugas.' });
      },
    },
  );

  const deleteHeaderMutation = useApiMutation(
    (id: number) => suratTugasService.delete(id),
    {
      invalidateKeys: [['surat-tugas'], ['surat-tugas-form-options'], ['surat-tugas-years'], ['surat-tugas-dates']],
      onSuccess: () => {
        setDeleteTarget(null);
        setSelectedId(null);
        setNotice({ tone: 'success', message: 'Surat tugas berhasil dihapus.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menghapus surat tugas.' });
      },
    },
  );

  const createDetailMutation = useApiMutation(
    (payload: Record<string, unknown>) => surtugDetilService.create(payload),
    {
      invalidateKeys: [['surtug-details', selectedId]],
      onSuccess: () => {
        setDetailModal(null);
        setNotice({ tone: 'success', message: 'Detail surat tugas berhasil ditambahkan.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menambahkan detail.' });
      },
    },
  );

  const updateDetailMutation = useApiMutation(
    ({ id, payload }: { id: number; payload: Record<string, unknown> }) => surtugDetilService.update(id, payload),
    {
      invalidateKeys: [['surtug-details', selectedId]],
      onSuccess: () => {
        setDetailModal(null);
        setNotice({ tone: 'success', message: 'Detail surat tugas berhasil diperbarui.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal memperbarui detail.' });
      },
    },
  );

  const bulkMitraMutation = useApiMutation(
    (payload: Record<string, unknown>) => surtugDetilService.bulkMitra(payload),
    {
      invalidateKeys: [['surtug-details', selectedId]],
      onSuccess: (response) => {
        setDetailModal(null);
        const failed = response.data.failed ? ` ${response.data.failed} gagal.` : '';
        setNotice({ tone: response.data.failed ? 'error' : 'success', message: `${response.data.created} mitra ditambahkan.${failed}` });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menambahkan mitra.' });
      },
    },
  );

  const deleteDetailMutation = useApiMutation(
    (id: number) => surtugDetilService.delete(id),
    {
      invalidateKeys: [['surtug-details', selectedId]],
      onSuccess: () => {
        setDeleteTarget(null);
        setNotice({ tone: 'success', message: 'Detail surat tugas berhasil dihapus.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menghapus detail.' });
      },
    },
  );

  const headerFields = useMemo<FormField[]>(() => {
    const includeNomor = headerModalMode === 'edit' || headerModalMode === 'insert';
    const includeFile = headerModalMode === 'edit';
    return [
      { name: 'tahun', label: 'Tahun', type: 'text', required: true, placeholder: String(currentYear) },
      { name: 'tanggal', label: 'Tanggal Surat', type: 'date', required: true },
      {
        name: 'kode_klas',
        label: 'Klasifikasi',
        type: 'select',
        required: true,
        searchable: true,
        wrapLabel: true,
        options: klasifikasiOptions,
        placeholder: 'Pilih klasifikasi',
        colSpan: 2 as const,
      },
      ...(includeNomor
        ? [{ name: 'nomor', label: 'Nomor Induk', type: 'text' as const, required: true, placeholder: '0001' }]
        : []),
      { name: 'kepada', label: 'Kepada', type: 'text', required: true, placeholder: 'Tim Survei', colSpan: 2 as const },
      { name: 'menimbang', label: 'Menimbang', type: 'textarea', placeholder: 'Dasar pertimbangan opsional', colSpan: 2 as const },
      { name: 'uraian', label: 'Uraian', type: 'textarea', required: true, placeholder: 'Uraian tugas', colSpan: 2 as const },
      ...(includeFile
        ? [{ name: 'file', label: 'Lampiran PDF', type: 'file' as const, accept: 'application/pdf', colSpan: 2 as const }]
        : []),
    ];
  }, [currentYear, headerModalMode, klasifikasiOptions]);

  const headerInitialData = useMemo(() => {
    if (!headerModalMode) return null;
    const base = {
      tahun: selectedHeader?.tahun ?? filterYear ?? String(currentYear),
      tanggal: selectedHeader?.tanggal ?? new Date().toISOString().slice(0, 10),
      nomor: selectedHeader?.nomor ?? formOptionsResponse?.data?.nomor_baru ?? '',
      kode_klas: selectedHeader?.kode_klas ?? '',
      kepada: selectedHeader?.kepada ?? '',
      menimbang: selectedHeader?.menimbang ?? '',
      uraian: selectedHeader?.uraian ?? '',
    };

    if (headerModalMode === 'create') {
      return {
        tahun: filterYear || String(currentYear),
        tanggal: new Date().toISOString().slice(0, 10),
        kode_klas: '',
        kepada: '',
        menimbang: '',
        uraian: '',
      };
    }

    return base;
  }, [currentYear, filterYear, formOptionsResponse, headerModalMode, selectedHeader]);

  const isHeaderSaving =
    createHeaderMutation.isPending || insertHeaderMutation.isPending || updateHeaderMutation.isPending;
  const isDetailSaving = createDetailMutation.isPending || updateDetailMutation.isPending || bulkMitraMutation.isPending;
  const canAddOrganik = selectedId !== null && (personnelKind === 'empty' || personnelKind === 'organik');
  const canAddMitra = selectedId !== null && (personnelKind === 'empty' || personnelKind === 'mitra');

  const openDetailModal = (type: 'organik' | 'mitra', item?: SurtugDetil, bulk = false) => {
    setNotice(null);
    setDetailModal({
      mode: item ? 'edit' : 'create',
      type,
      bulk,
      item,
    });
    setDetailForm(item ? detailToForm(item) : createEmptyDetailForm(selectedHeader));
  };

  const handleHeaderSubmit = async (raw: Record<string, unknown>) => {
    const payload: Record<string, unknown> = {
      tahun: toText(raw.tahun),
      tanggal: toText(raw.tanggal),
      kode_klas: toText(raw.kode_klas),
      kepada: toText(raw.kepada),
      menimbang: toText(raw.menimbang),
      uraian: toText(raw.uraian),
    };

    if (!payload.tahun || !payload.tanggal || !payload.kode_klas || !payload.kepada || !payload.uraian) {
      setNotice({ tone: 'error', message: 'Tahun, tanggal, klasifikasi, kepada, dan uraian wajib diisi.' });
      return;
    }

    setNotice(null);

    if (headerModalMode === 'create') {
      await createHeaderMutation.mutateAsync(payload);
      return;
    }

    payload.nomor = toText(raw.nomor);
    if (!payload.nomor) {
      setNotice({ tone: 'error', message: 'Nomor induk wajib diisi untuk edit atau sisip.' });
      return;
    }

    if (headerModalMode === 'insert') {
      await insertHeaderMutation.mutateAsync(payload);
      return;
    }

    if (!selectedHeader) return;
    if (selectedHeader.no_sisip) payload.no_sisip = selectedHeader.no_sisip;

    const maybeFile = raw.file;
    if (typeof File !== 'undefined' && maybeFile instanceof File) {
      const formData = new FormData();
      Object.entries(payload).forEach(([key, value]) => formData.append(key, String(value ?? '')));
      formData.append('file', maybeFile);
      await updateHeaderMutation.mutateAsync({ id: selectedHeader.id, payload: formData });
      return;
    }

    await updateHeaderMutation.mutateAsync({ id: selectedHeader.id, payload });
  };

  const buildDetailPayload = () => {
    if (!selectedId) return null;
    const hari = Number(detailForm.hari);
    const common = {
      surtug_id: selectedId,
      dasar: toText(detailForm.dasar),
      nama_kegiatan: toText(detailForm.nama_kegiatan),
      tugas_sebagai: toText(detailForm.tugas_sebagai),
      hari,
      wilayah_kerja: toText(detailForm.wilayah_kerja),
      tgl_mulai: toText(detailForm.tgl_mulai),
      no_dipa: toText(detailForm.no_dipa),
    };

    if (!common.dasar || !common.nama_kegiatan || !common.hari || !common.wilayah_kerja || !common.tgl_mulai || !common.no_dipa) {
      setNotice({ tone: 'error', message: 'Dasar, kegiatan, hari, wilayah kerja, tanggal mulai, dan DIPA wajib diisi.' });
      return null;
    }

    if (!Number.isFinite(hari) || hari < 1) {
      setNotice({ tone: 'error', message: 'Jumlah hari harus lebih dari 0.' });
      return null;
    }

    if (detailModal?.bulk) {
      if (!detailForm.kegiatan_id || detailForm.mitra_ids.length === 0) {
        setNotice({ tone: 'error', message: 'Pilih kegiatan dan minimal satu mitra untuk bulk mitra.' });
        return null;
      }

      return {
        ...common,
        kegiatan_id: Number(detailForm.kegiatan_id),
        mitra_ids: detailForm.mitra_ids.map(Number),
      };
    }

    if (detailModal?.type === 'organik') {
      const pegawaiId = toNullableNumber(detailForm.pegawai_id);
      if (!pegawaiId) {
        setNotice({ tone: 'error', message: 'Pegawai wajib dipilih.' });
        return null;
      }

      return {
        ...common,
        pegawai_id: pegawaiId,
        mitra_id: null,
        grup_mitra: null,
        grup_pegawai: null,
        penugasan_id: null,
        jenis_kendaraan: toText(detailForm.jenis_kendaraan),
        isOrganik: true,
        sppd: Boolean(detailForm.sppd),
      };
    }

    const mitraId = toNullableNumber(detailForm.mitra_id);
    if (!mitraId) {
      setNotice({ tone: 'error', message: 'Mitra wajib dipilih.' });
      return null;
    }

    return {
      ...common,
      pegawai_id: null,
      mitra_id: mitraId,
      grup_mitra: null,
      grup_pegawai: null,
      penugasan_id: toNullableNumber(detailForm.penugasan_id),
      jenis_kendaraan: '',
      isOrganik: false,
      sppd: false,
    };
  };

  const handleDetailSubmit = async () => {
    const payload = buildDetailPayload();
    if (!payload || !detailModal) return;

    setNotice(null);

    if (detailModal.bulk) {
      await bulkMitraMutation.mutateAsync(payload);
      return;
    }

    if (detailModal.mode === 'edit' && detailModal.item) {
      await updateDetailMutation.mutateAsync({ id: detailModal.item.id, payload });
      return;
    }

    await createDetailMutation.mutateAsync(payload);
  };

  const handleRefresh = async () => {
    setNotice(null);
    await queryClient.invalidateQueries({ queryKey: ['surat-tugas'] });
    await queryClient.invalidateQueries({ queryKey: ['surtug-details'] });
    await refetch();
    if (selectedId) await refetchDetails();
  };

  const handleDownloadDocx = async () => {
    if (!docxEndpoint || !selectedHeader) return;
    setNotice(null);
    setIsDownloading(true);
    try {
      const blob = await suratTugasService.downloadDocx(docxEndpoint);
      downloadBlob(blob, `${sanitizeFilename(selectedHeader.no_surat || 'surat-tugas')}.docx`);
      setNotice({ tone: 'success', message: 'DOCX surat tugas berhasil diunduh.' });
    } catch (downloadError) {
      const message = downloadError instanceof Error ? downloadError.message : 'Gagal mengunduh DOCX.';
      setNotice({ tone: 'error', message });
    } finally {
      setIsDownloading(false);
    }
  };

  const confirmDelete = async () => {
    if (!deleteTarget) return;
    if (deleteTarget.type === 'header') {
      await deleteHeaderMutation.mutateAsync(deleteTarget.item.id);
      return;
    }
    await deleteDetailMutation.mutateAsync(deleteTarget.item.id);
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Surat Tugas"
        description="Kelola nomor, penugasan personel, dan dokumen Surat Tugas."
        actions={
          <button
            onClick={() => {
              setHeaderModalMode('create');
              setNotice(null);
            }}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Buat Surat
          </button>
        }
      />

      {notice && (
        <div
          className={`rounded-[20px] border px-4 py-3 text-sm ${
            notice.tone === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
              : 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300'
          }`}
        >
          {notice.message}
        </div>
      )}

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {[
          { label: 'Total Surat', value: stats.total, icon: FileText, color: 'text-orange-600 dark:text-orange-400' },
          { label: 'Detail Aktif', value: selectedId ? stats.detail : '-', icon: Users, color: 'text-emerald-600 dark:text-emerald-400' },
          { label: 'Tipe Petugas', value: stats.tipe, icon: Users, color: 'text-blue-600 dark:text-blue-400' },
          { label: 'Nomor Baru', value: stats.nomorBaru, icon: Calendar, color: 'text-amber-600 dark:text-amber-400' },
        ].map((item, index) => {
          const Icon = item.icon;
          return (
            <motion.div
              key={item.label}
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.04 }}
              className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-5 space-y-2"
            >
              <div className="flex items-center justify-between gap-3">
                <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{item.label}</p>
                <Icon className="h-4 w-4 text-gray-400 dark:text-gray-500" />
              </div>
              <p className={`text-2xl font-black truncate ${item.color}`}>{isLoading ? '...' : item.value}</p>
            </motion.div>
          );
        })}
      </div>

      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-sm">
        <div className="flex flex-wrap items-end gap-3">
          <div className="flex w-full max-w-sm flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Pencarian</label>
            <div className="relative group">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
              <input
                type="text"
                placeholder="Cari nomor, kepada, atau uraian..."
                value={searchInput}
                onChange={(event) => setSearchInput(event.target.value)}
                className="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 pl-9 pr-4 text-xs text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
              />
            </div>
          </div>

          <div className="flex flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tahun</label>
            <select
              value={filterYear}
              onChange={(event) => setFilterYear(event.target.value)}
              className="h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none transition focus:border-amber-500 dark:[color-scheme:dark]"
            >
              <option value="">Semua Tahun</option>
              {yearOptions.map((year) => (
                <option key={year} value={year}>
                  {year}
                </option>
              ))}
            </select>
          </div>

          <div className="flex flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tanggal</label>
            <select
              value={filterDate}
              onChange={(event) => setFilterDate(event.target.value)}
              className="h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none transition focus:border-amber-500 dark:[color-scheme:dark]"
            >
              <option value="">Semua Tanggal</option>
              {dateOptions.map((date) => (
                <option key={date} value={date}>
                  {formatDate(date)}
                </option>
              ))}
            </select>
          </div>

          <button
            onClick={() => {
              setSearchInput('');
              setFilterYear(String(currentYear));
              setFilterDate('');
              setNotice(null);
            }}
            className="h-10 px-5 rounded-full bg-white/70 dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-[11px] font-bold shadow-sm hover:bg-white transition-all"
          >
            Reset
          </button>

          <button
            onClick={() => void handleRefresh()}
            className="h-10 px-5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-bold shadow-sm transition-all flex items-center gap-2"
          >
            <RefreshCw className="h-3.5 w-3.5" />
            Muat Ulang
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-[minmax(0,1.1fr)_minmax(380px,0.9fr)] gap-5">
        <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
          {isLoading ? (
            <LoadingSkeleton variant="table" message="Memuat surat tugas..." />
          ) : error ? (
            <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Surat Tugas" />
          ) : rows.length === 0 ? (
            <EmptyState title="Belum ada surat tugas." description="Ubah filter atau buat surat tugas baru." />
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="w-full text-[13px]">
                  <thead>
                    <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                      {['Nomor Surat', 'Tanggal', 'Kepada', 'Aksi'].map((header) => (
                        <th key={header} className="px-5 py-4 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
                          {header}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                    {rows.map((item) => {
                      const selected = selectedId === item.id;
                      const expanded = expandedInfoIds.includes(item.id);
                      const menimbang = toText(item.menimbang) || '-';
                      const uraian = toText(item.uraian) || '-';
                      return (
                        <React.Fragment key={item.id}>
                          <tr
                            onClick={() => setSelectedId(item.id)}
                            className={`cursor-pointer transition-colors ${
                              selected
                                ? 'bg-amber-50/80 dark:bg-amber-500/10'
                                : 'hover:bg-white/40 dark:hover:bg-white/[0.02]'
                            }`}
                          >
                            <td className="px-5 py-4">
                              <div className="flex items-start gap-3">
                                <button
                                  type="button"
                                  aria-label={`${expanded ? 'Sembunyikan' : 'Tampilkan'} info ${item.no_surat}`}
                                  aria-expanded={expanded}
                                  onClick={(event) => {
                                    event.stopPropagation();
                                    setExpandedInfoIds((previous) =>
                                      previous.includes(item.id)
                                        ? previous.filter((id) => id !== item.id)
                                        : [...previous, item.id],
                                    );
                                  }}
                                  className="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-white/10 dark:hover:text-amber-400"
                                >
                                  <ChevronDown className={`h-4 w-4 transition-transform ${expanded ? 'rotate-180' : ''}`} />
                                </button>
                                <div className="flex min-w-0 flex-col gap-0.5">
                                  <span className="font-bold text-gray-900 dark:text-white text-[13px] leading-tight">{item.no_surat}</span>
                                  <div className="flex flex-wrap items-center gap-2 text-[11px] text-gray-500">
                                    <span>#{item.id} / {item.no_mix}</span>
                                    {item.kode_klas && (
                                      <span className="rounded-md bg-slate-100 px-2 py-0.5 font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                        {item.kode_klas}
                                      </span>
                                    )}
                                  </div>
                                </div>
                              </div>
                            </td>
                            <td className="px-5 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">{formatDate(item.tanggal)}</td>
                            <td className="px-5 py-4">
                              <span className="line-clamp-2 text-gray-700 dark:text-gray-300">{item.kepada}</span>
                            </td>
                            <td className="px-5 py-4">
                              <HeaderActionMenu
                                item={item}
                                isOpen={openActionMenuId === item.id}
                                onToggle={() => setOpenActionMenuId(openActionMenuId === item.id ? null : item.id)}
                                onEdit={() => {
                                  setSelectedId(item.id);
                                  setHeaderModalMode('edit');
                                  setNotice(null);
                                  setOpenActionMenuId(null);
                                }}
                                onInsert={() => {
                                  setSelectedId(item.id);
                                  setHeaderModalMode('insert');
                                  setNotice(null);
                                  setOpenActionMenuId(null);
                                }}
                                onDelete={() => {
                                  setDeleteTarget({ type: 'header', item });
                                  setNotice(null);
                                  setOpenActionMenuId(null);
                                }}
                              />
                            </td>
                          </tr>
                          {expanded && (
                            <tr className={selected ? 'bg-amber-50/60 dark:bg-amber-500/10' : 'bg-white/30 dark:bg-white/[0.02]'}>
                              <td colSpan={4} className="px-5 pb-5 pt-0">
                                <div className="ml-10 grid gap-3 rounded-xl border border-gray-200/70 bg-white/70 p-4 dark:border-white/10 dark:bg-white/[0.04] md:grid-cols-2">
                                  <div className="min-w-0">
                                    <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Menimbang</p>
                                    <p className="mt-1 whitespace-pre-wrap text-[12px] leading-5 text-gray-700 dark:text-gray-300">{menimbang}</p>
                                  </div>
                                  <div className="min-w-0">
                                    <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Uraian</p>
                                    <p className="mt-1 whitespace-pre-wrap text-[12px] leading-5 text-gray-700 dark:text-gray-300">{uraian}</p>
                                  </div>
                                </div>
                              </td>
                            </tr>
                          )}
                        </React.Fragment>
                      );
                    })}
                  </tbody>
                </table>
              </div>

              <div className="flex items-center justify-between px-5 py-4 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5">
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Halaman {page} dari {totalPages} | {totalRecords} surat
                </p>
                <div className="flex gap-2">
                  <button
                    onClick={() => setPage((prev) => Math.max(1, prev - 1))}
                    disabled={page === 1}
                    className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                  >
                    Sebelumnya
                  </button>
                  <button
                    onClick={() => setPage((prev) => prev + 1)}
                    disabled={!canNextPage}
                    className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                  >
                    Berikutnya
                  </button>
                </div>
              </div>
            </>
          )}
        </div>

        <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
          <div className="flex items-start justify-between gap-4 border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
            <div className="min-w-0">
              <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Detail Surat</p>
              <h2 className="mt-1 truncate text-base font-bold text-gray-900 dark:text-white">
                {selectedHeader?.no_surat ?? 'Pilih surat tugas'}
              </h2>
              {selectedHeader ? (
                <p className="mt-1 line-clamp-2 text-[12px] text-gray-500 dark:text-gray-400">{selectedHeader.kepada}</p>
              ) : null}
            </div>
            <button
              onClick={() => void handleDownloadDocx()}
              disabled={!docxEndpoint || isDownloading || personnelKind === 'mixed'}
              className="flex shrink-0 items-center gap-1.5 rounded-full bg-slate-900 px-4 py-2 text-[11px] font-bold text-white shadow-sm transition-all hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-white dark:text-slate-900"
            >
              {isDownloading ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Download className="h-3.5 w-3.5" />}
              DOCX
            </button>
          </div>

          {!selectedId ? (
            <EmptyState title="Tidak ada surat terpilih." description="Pilih surat dari tabel untuk mengelola detail." compact />
          ) : detailsError ? (
            <ApiErrorBoundary error={detailsError} onRetry={() => refetchDetails()} title="Gagal Memuat Detail" />
          ) : (
            <>
              <div className="flex flex-wrap gap-2 px-5 py-4 border-b border-gray-200/60 dark:border-white/10">
                {canAddOrganik ? (
                  <button
                    onClick={() => openDetailModal('organik')}
                    className="flex items-center gap-1.5 px-3 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-[11px] font-bold shadow-sm transition"
                  >
                    <Plus className="h-3.5 w-3.5" />
                    Organik
                  </button>
                ) : null}
                {canAddMitra ? (
                  <>
                    <button
                      onClick={() => openDetailModal('mitra')}
                      className="flex items-center gap-1.5 px-3 py-2 rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-200 text-[11px] font-bold shadow-sm transition"
                    >
                      <Plus className="h-3.5 w-3.5" />
                      Mitra
                    </button>
                    <button
                      onClick={() => openDetailModal('mitra', undefined, true)}
                      className="flex items-center gap-1.5 px-3 py-2 rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-200 text-[11px] font-bold shadow-sm transition"
                    >
                      <Users className="h-3.5 w-3.5" />
                      Bulk Mitra
                    </button>
                  </>
                ) : null}
                {personnelKind === 'mixed' ? (
                  <span className="rounded-full bg-red-100 px-3 py-2 text-[11px] font-bold text-red-700 dark:bg-red-500/10 dark:text-red-300">
                    Detail campuran tidak didukung generator.
                  </span>
                ) : null}
              </div>

              <div className="max-h-[560px] overflow-y-auto p-5">
                {isLoadingDetails ? (
                  <LoadingSkeleton variant="table" rows={3} message="Memuat detail..." />
                ) : details.length === 0 ? (
                  <EmptyState title="Belum ada detail." description="Tambahkan petugas organik atau mitra untuk mengaktifkan unduhan DOCX." compact />
                ) : (
                  <div className="space-y-3">
                    {details.map((detail) => (
                      <div
                        key={detail.id}
                        className="rounded-[18px] border border-gray-100/80 dark:border-white/5 bg-white/80 dark:bg-white/[0.02] p-4"
                      >
                        <div className="flex items-start justify-between gap-3">
                          <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                              <p className="truncate text-[13px] font-bold text-gray-900 dark:text-white">
                                {detail.isOrganik ? detail.pegawai?.nama : detail.mitra?.nama_lengkap}
                              </p>
                              <span className="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                {detail.isOrganik ? 'Organik' : 'Mitra'}
                              </span>
                            </div>
                            <p className="mt-1 line-clamp-2 text-[12px] text-gray-600 dark:text-gray-400">
                              {detail.nama_kegiatan} | {detail.wilayah_kerja}
                            </p>
                            <p className="mt-1 text-[11px] text-gray-500 dark:text-gray-500">
                              {formatDate(detail.tgl_mulai)} | {detail.hari} hari | DIPA {detail.no_dipa}
                            </p>
                          </div>
                          <div className="flex shrink-0 items-center gap-1">
                            <IconButton
                              label="Edit detail"
                              onClick={() => openDetailModal(detail.isOrganik ? 'organik' : 'mitra', detail)}
                            >
                              <Pencil className="h-3.5 w-3.5" />
                            </IconButton>
                            <IconButton
                              label="Hapus detail"
                              danger
                              onClick={() => {
                                setDeleteTarget({ type: 'detail', item: detail });
                                setNotice(null);
                              }}
                            >
                              <Trash2 className="h-3.5 w-3.5" />
                            </IconButton>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      </div>

      <EntityFormModal
        open={headerModalMode !== null}
        onClose={() => setHeaderModalMode(null)}
        onSubmit={(data) => void handleHeaderSubmit(data)}
        title={
          headerModalMode === 'edit'
            ? 'Edit Surat Tugas'
            : headerModalMode === 'insert'
              ? 'Sisip Surat Tugas'
              : 'Buat Surat Tugas'
        }
        description="Nomor surat akan dihitung oleh backend sesuai format yang aktif."
        fields={headerFields}
        initialData={headerInitialData}
        isLoading={isHeaderSaving}
        mode={headerModalMode === 'edit' ? 'edit' : 'create'}
      />

      <DetailFormModal
        state={detailModal}
        form={detailForm}
        setForm={setDetailForm}
        canUseOrganik={canAddOrganik || detailModal?.type === 'organik'}
        canUseMitra={canAddMitra || detailModal?.type === 'mitra'}
        pegawaiOptions={pegawaiOptions}
        kegiatanOptions={kegiatanOptions}
        mitraOptions={mitraOptions}
        mitraPenugasanOptions={mitraPenugasanOptions}
        isLoadingMitraPenugasan={isLoadingMitraPenugasan}
        isLoading={isDetailSaving}
        onClose={() => setDetailModal(null)}
        onSubmit={() => void handleDetailSubmit()}
        onTypeChange={(type) => {
          setDetailModal((previous) => (previous ? { ...previous, type, bulk: type === 'mitra' ? previous.bulk : false } : previous));
          setDetailForm((previous) => ({
            ...previous,
            pegawai_id: '',
            mitra_id: '',
            penugasan_id: '',
            kegiatan_id: '',
            mitra_ids: [],
            jenis_kendaraan: '',
            sppd: false,
          }));
        }}
      />

      <ConfirmDialog
        open={deleteTarget !== null}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => void confirmDelete()}
        title={deleteTarget?.type === 'header' ? 'Hapus Surat Tugas' : 'Hapus Detail Surat Tugas'}
        message={
          deleteTarget?.type === 'header'
            ? `Surat ${deleteTarget.item.no_surat} akan dihapus.`
            : 'Detail petugas ini akan dihapus dari surat tugas.'
        }
        confirmLabel="Hapus"
        isLoading={deleteHeaderMutation.isPending || deleteDetailMutation.isPending}
      />
    </div>
  );
}

function HeaderActionMenu({
  item,
  isOpen,
  onToggle,
  onEdit,
  onInsert,
  onDelete,
}: {
  item: SuratTugas;
  isOpen: boolean;
  onToggle: () => void;
  onEdit: () => void;
  onInsert: () => void;
  onDelete: () => void;
}) {
  return (
    <div className="relative flex justify-end" onClick={(event) => event.stopPropagation()}>
      <button
        type="button"
        aria-label={`Menu aksi ${item.no_surat}`}
        title={`Menu aksi ${item.no_surat}`}
        onClick={onToggle}
        className="inline-flex size-9 items-center justify-center rounded-lg text-gray-500 outline-none transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-white/10 dark:hover:text-amber-400"
      >
        <Ellipsis className="h-4 w-4" />
      </button>

      {isOpen && (
        <div className="absolute right-0 top-full z-50 mt-1 min-w-[160px] overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-gray-950">
          <button
            type="button"
            aria-label={`Edit ${item.no_surat}`}
            onClick={onEdit}
            className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
          >
            <Pencil className="h-3.5 w-3.5 text-amber-500" />
            Edit Surat
          </button>
          <button
            type="button"
            aria-label={`Sisip ${item.no_surat}`}
            onClick={onInsert}
            className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
          >
            <Plus className="h-3.5 w-3.5 text-blue-500" />
            Sisip Surat
          </button>
          <div className="mx-2 h-px bg-gray-100 dark:bg-white/5" />
          <button
            type="button"
            aria-label={`Hapus ${item.no_surat}`}
            onClick={onDelete}
            className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
          >
            <Trash2 className="h-3.5 w-3.5" />
            Hapus Surat
          </button>
        </div>
      )}
    </div>
  );
}

function IconButton({
  label,
  children,
  danger = false,
  onClick,
}: {
  label: string;
  children: React.ReactNode;
  danger?: boolean;
  onClick: () => void;
}) {
  return (
    <button
      type="button"
      aria-label={label}
      title={label}
      onClick={onClick}
      className={`p-2 rounded-lg transition ${
        danger
          ? 'hover:bg-red-50 dark:hover:bg-red-500/10 text-gray-500 hover:text-red-600 dark:hover:text-red-400'
          : 'hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 hover:text-amber-600 dark:hover:text-amber-400'
      }`}
    >
      {children}
    </button>
  );
}

function EmptyState({ title, description, compact = false }: { title: string; description: string; compact?: boolean }) {
  return (
    <div className={`flex flex-col items-center justify-center text-center ${compact ? 'py-10' : 'py-16'}`}>
      <div className="h-14 w-14 rounded-2xl bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center mb-4">
        <FileText className="h-7 w-7 text-amber-600 dark:text-amber-400" />
      </div>
      <p className="text-sm font-semibold text-gray-800 dark:text-gray-200">{title}</p>
      <p className="mt-1 max-w-sm text-xs text-gray-500 dark:text-gray-400">{description}</p>
    </div>
  );
}

function FieldLabel({ children, required = false }: { children: React.ReactNode; required?: boolean }) {
  return (
    <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
      {children}
      {required ? <span className="ml-0.5 text-red-500">*</span> : null}
    </label>
  );
}

function DetailFormModal({
  state,
  form,
  setForm,
  canUseOrganik,
  canUseMitra,
  pegawaiOptions,
  kegiatanOptions,
  mitraOptions,
  mitraPenugasanOptions,
  isLoadingMitraPenugasan,
  isLoading,
  onClose,
  onSubmit,
  onTypeChange,
}: {
  state: DetailModalState;
  form: DetailFormData;
  setForm: React.Dispatch<React.SetStateAction<DetailFormData>>;
  canUseOrganik: boolean;
  canUseMitra: boolean;
  pegawaiOptions: { value: string; label: string }[];
  kegiatanOptions: { value: string; label: string }[];
  mitraOptions: { value: string; label: string }[];
  mitraPenugasanOptions: SurtugMitraPenugasanOption[];
  isLoadingMitraPenugasan: boolean;
  isLoading: boolean;
  onClose: () => void;
  onSubmit: () => void;
  onTypeChange: (type: 'organik' | 'mitra') => void;
}) {
  if (!state) return null;

  const change = (name: keyof DetailFormData, value: DetailFormData[keyof DetailFormData]) => {
    setForm((previous) => ({ ...previous, [name]: value }));
  };

  const penugasanSelectOptions = mitraPenugasanOptions.map((item) => ({
    value: String(item.id),
    label: item.mitra ? [item.mitra.nama_lengkap, item.mitra.keca].filter(Boolean).join(' - ') : `Penugasan #${item.id}`,
  }));

  const selectedMitraIds = new Set(form.mitra_ids);
  const isMitra = state.type === 'mitra';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div className="relative mx-4 flex max-h-[88vh] w-full max-w-3xl flex-col rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">
        <div className="flex items-center justify-between border-b border-gray-200/60 px-6 py-4 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">
              {state.mode === 'edit' ? 'Edit Detail Surat Tugas' : state.bulk ? 'Bulk Mitra Surat Tugas' : 'Tambah Detail Surat Tugas'}
            </h2>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Isi personel dan rincian pelaksanaan untuk dokumen Surat Tugas.
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <form
          onSubmit={(event) => {
            event.preventDefault();
            onSubmit();
          }}
          className="flex-1 overflow-y-auto px-6 py-4"
        >
          {state.mode === 'create' ? (
            <div className="mb-5 flex flex-wrap gap-2">
              <button
                type="button"
                disabled={!canUseOrganik}
                onClick={() => onTypeChange('organik')}
                className={`rounded-full px-4 py-2 text-[12px] font-bold transition disabled:cursor-not-allowed disabled:opacity-40 ${
                  state.type === 'organik'
                    ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                    : 'bg-white/70 text-gray-700 border border-gray-200 hover:bg-white dark:border-white/10 dark:bg-white/10 dark:text-gray-200'
                }`}
              >
                Organik
              </button>
              <button
                type="button"
                disabled={!canUseMitra}
                onClick={() => onTypeChange('mitra')}
                className={`rounded-full px-4 py-2 text-[12px] font-bold transition disabled:cursor-not-allowed disabled:opacity-40 ${
                  state.type === 'mitra'
                    ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                    : 'bg-white/70 text-gray-700 border border-gray-200 hover:bg-white dark:border-white/10 dark:bg-white/10 dark:text-gray-200'
                }`}
              >
                Mitra
              </button>
              {isMitra ? (
                <button
                  type="button"
                  onClick={() => setForm((previous) => ({ ...previous, mitra_ids: [], mitra_id: '', penugasan_id: '' }))}
                  className="rounded-full border border-gray-200 bg-white/70 px-4 py-2 text-[12px] font-bold text-gray-700 transition hover:bg-white dark:border-white/10 dark:bg-white/10 dark:text-gray-200"
                >
                  {state.bulk ? 'Mode Bulk' : 'Mode Single'}
                </button>
              ) : null}
            </div>
          ) : null}

          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            {state.type === 'organik' ? (
              <div className="md:col-span-2">
                <FieldLabel required>Pegawai</FieldLabel>
                <SearchableSelect
                  value={form.pegawai_id}
                  onChange={(value) => change('pegawai_id', value)}
                  options={pegawaiOptions}
                  placeholder="Pilih pegawai"
                  searchPlaceholder="Cari pegawai..."
                  emptyLabel="Pegawai tidak ditemukan."
                  wrapLabel
                />
              </div>
            ) : (
              <>
                <div className="md:col-span-2">
                  <FieldLabel required={state.bulk}>Kegiatan</FieldLabel>
                  <SearchableSelect
                    value={form.kegiatan_id}
                    onChange={(value) => {
                      change('kegiatan_id', value);
                      change('penugasan_id', '');
                      change('mitra_id', '');
                      change('mitra_ids', []);
                    }}
                    options={kegiatanOptions}
                    placeholder={state.bulk ? 'Pilih kegiatan' : 'Opsional untuk mengambil penugasan'}
                    searchPlaceholder="Cari kegiatan..."
                    emptyLabel="Kegiatan tidak ditemukan."
                    wrapLabel
                  />
                </div>

                {state.bulk ? (
                  <div className="md:col-span-2">
                    <FieldLabel required>Mitra Penugasan</FieldLabel>
                    <div className="max-h-48 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50/50 p-3 dark:border-white/10 dark:bg-white/5">
                      {isLoadingMitraPenugasan ? (
                        <div className="flex items-center gap-2 py-3 text-xs text-gray-500">
                          <Loader2 className="h-3.5 w-3.5 animate-spin" />
                          Memuat mitra...
                        </div>
                      ) : mitraPenugasanOptions.length === 0 ? (
                        <p className="py-3 text-xs text-gray-500">Pilih kegiatan yang memiliki penugasan mitra.</p>
                      ) : (
                        <div className="grid gap-2 sm:grid-cols-2">
                          {mitraPenugasanOptions.map((item) => {
                            const value = String(item.mitra_id);
                            return (
                              <label key={item.id} className="flex items-start gap-2 rounded-lg bg-white/70 p-2 text-xs text-gray-700 dark:bg-white/5 dark:text-gray-200">
                                <input
                                  type="checkbox"
                                  checked={selectedMitraIds.has(value)}
                                  onChange={(event) => {
                                    const next = new Set(selectedMitraIds);
                                    if (event.target.checked) next.add(value);
                                    else next.delete(value);
                                    change('mitra_ids', Array.from(next));
                                  }}
                                  className="mt-0.5"
                                />
                                <span>{item.mitra?.nama_lengkap ?? `Mitra #${item.mitra_id}`}</span>
                              </label>
                            );
                          })}
                        </div>
                      )}
                    </div>
                  </div>
                ) : form.kegiatan_id ? (
                  <div className="md:col-span-2">
                    <FieldLabel required>Mitra Penugasan</FieldLabel>
                    <SearchableSelect
                      value={form.penugasan_id}
                      onChange={(value) => {
                        const selected = mitraPenugasanOptions.find((item) => String(item.id) === value);
                        change('penugasan_id', value);
                        change('mitra_id', selected ? String(selected.mitra_id) : '');
                      }}
                      options={penugasanSelectOptions}
                      placeholder="Pilih mitra dari penugasan"
                      searchPlaceholder="Cari mitra..."
                      emptyLabel="Mitra penugasan tidak ditemukan."
                      wrapLabel
                    />
                  </div>
                ) : (
                  <div className="md:col-span-2">
                    <FieldLabel required>Mitra</FieldLabel>
                    <SearchableSelect
                      value={form.mitra_id}
                      onChange={(value) => change('mitra_id', value)}
                      options={mitraOptions}
                      placeholder="Pilih mitra"
                      searchPlaceholder="Cari mitra..."
                      emptyLabel="Mitra tidak ditemukan."
                      wrapLabel
                    />
                  </div>
                )}
              </>
            )}

            <TextAreaField label="Dasar" required value={form.dasar} onChange={(value) => change('dasar', value)} className="md:col-span-2" />
            <TextField label="Nama Kegiatan" required value={form.nama_kegiatan} onChange={(value) => change('nama_kegiatan', value)} className="md:col-span-2" />
            <TextField label="Tugas Sebagai" value={form.tugas_sebagai} onChange={(value) => change('tugas_sebagai', value)} />
            <TextField label="Jumlah Hari" required type="number" value={form.hari} onChange={(value) => change('hari', value)} />
            <TextField label="Wilayah Kerja" required value={form.wilayah_kerja} onChange={(value) => change('wilayah_kerja', value)} />
            <div>
              <FieldLabel required>Tanggal Mulai</FieldLabel>
              <DatePicker value={form.tgl_mulai} onChange={(value) => change('tgl_mulai', value)} placeholder="Tanggal mulai" />
            </div>
            {state.type === 'organik' ? (
              <>
                <TextField label="Jenis Kendaraan" value={form.jenis_kendaraan} onChange={(value) => change('jenis_kendaraan', value)} />
                <label className="flex h-[70px] items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/50 px-4 text-sm text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                  <input
                    type="checkbox"
                    checked={form.sppd}
                    onChange={(event) => change('sppd', event.target.checked)}
                  />
                  SPPD
                </label>
              </>
            ) : null}
            <TextField label="No DIPA" required value={form.no_dipa} onChange={(value) => change('no_dipa', value)} className={state.type === 'organik' ? '' : 'md:col-span-2'} />
          </div>
        </form>

        <div className="flex items-center justify-end gap-3 border-t border-gray-200/60 px-6 py-4 dark:border-white/10">
          <button
            type="button"
            onClick={onClose}
            disabled={isLoading}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm disabled:opacity-50"
          >
            Batal
          </button>
          <button
            onClick={onSubmit}
            disabled={isLoading}
            className="flex items-center gap-1.5 px-5 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all disabled:opacity-50"
          >
            {isLoading ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : null}
            {state.mode === 'edit' ? 'Perbarui' : 'Simpan'}
          </button>
        </div>
      </div>
    </div>
  );
}

function TextField({
  label,
  value,
  onChange,
  required = false,
  type = 'text',
  className = '',
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  required?: boolean;
  type?: 'text' | 'number';
  className?: string;
}) {
  return (
    <div className={className}>
      <FieldLabel required={required}>{label}</FieldLabel>
      <input
        aria-label={label}
        type={type}
        value={value}
        onChange={(event) => onChange(event.target.value)}
        className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
      />
    </div>
  );
}

function TextAreaField({
  label,
  value,
  onChange,
  required = false,
  className = '',
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  required?: boolean;
  className?: string;
}) {
  return (
    <div className={className}>
      <FieldLabel required={required}>{label}</FieldLabel>
      <textarea
        aria-label={label}
        value={value}
        onChange={(event) => onChange(event.target.value)}
        rows={3}
        className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
      />
    </div>
  );
}

export default SuratTugasWorkflow;
