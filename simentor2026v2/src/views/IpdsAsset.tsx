import React, { useState, useMemo, useCallback, useEffect } from 'react';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  Plus,
  Search,
  Monitor,
  HardDrive,
  Printer,
  Cpu,
  MapPin,
  Pencil,
  Trash2,
  AlertCircle,
  ChevronDown,
  ChevronRight,
  Calendar,
  Wrench,
  Users,
  Filter,
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { useQueryClient } from '@tanstack/react-query';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { usePagination } from '../hooks/usePagination';
import { useDebounce } from '../hooks/useDebounce';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal, type FormField } from '../components/EntityFormModal';
import { assetService, maintenanceScheduleService } from '../lib/api-services';
import type { Asset as ApiAsset } from '../types/api';

type AssetRow = {
  id: number;
  kode_asset: string;
  nama: string;
  type: string;
  category: string;
  brand: string;
  model: string;
  serial_number: string;
  location: string;
  status: string;
  assigned_to: string | null;
  purchase_date: string | null;
  warranty_expiry: string | null;
  delivery_date: string | null;
  ip_address: string | null;
  license_key: string | null;
  device: string | null;
  purchase_value?: number;
  depreciation_value?: number;
  value_depreciation?: number;
  maintenance_schedules: Array<{ id: number; next_maintenance: string; responsible_team: string }>;
};

type MaintenanceRow = {
  id: number;
  asset_id: number;
  next_maintenance: string;
  responsible_team: string;
  asset?: { kode_asset: string; brand: string; model: string };
};

const categoryIcons: Record<string, React.ReactNode> = {
  Server: <HardDrive className="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />,
  Laptop: <Monitor className="h-3.5 w-3.5 text-purple-600 dark:text-purple-400" />,
  Printer: <Printer className="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />,
  Router: <HardDrive className="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />,
  Desktop: <Cpu className="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />,
  Monitor: <Monitor className="h-3.5 w-3.5 text-cyan-600 dark:text-cyan-400" />,
};

const statusStyles: Record<string, string> = {
  active: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
  'in-use': 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400',
  maintenance: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400',
  retired: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400',
  expired: 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400',
};

const ASSET_FIELDS: FormField[] = [
  { name: 'kode_asset', label: 'Kode Asset', type: 'text', required: true, placeholder: 'AS-001' },
  { name: 'type', label: 'Tipe', type: 'select', required: true, options: [
    { value: 'hardware', label: 'Hardware' },
    { value: 'software', label: 'Software' },
    { value: 'network', label: 'Network' },
  ]},
  { name: 'category', label: 'Kategori', type: 'select', required: true, options: [
    { value: 'Server', label: 'Server' },
    { value: 'Laptop', label: 'Laptop' },
    { value: 'Desktop', label: 'Desktop' },
    { value: 'Monitor', label: 'Monitor' },
    { value: 'Printer', label: 'Printer' },
    { value: 'Router', label: 'Router' },
  ]},
  { name: 'brand', label: 'Brand', type: 'text', required: true, placeholder: 'Dell, Lenovo, dll' },
  { name: 'model', label: 'Model', type: 'text', required: true, placeholder: 'PowerEdge R740' },
  { name: 'serial_number', label: 'Serial Number', type: 'text', placeholder: 'SN123456789' },
  { name: 'location', label: 'Lokasi', type: 'text', required: true, placeholder: 'Data Center Jakarta' },
  { name: 'status', label: 'Status', type: 'select', required: true, options: [
    { value: 'active', label: 'Active' },
    { value: 'in-use', label: 'In Use' },
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'retired', label: 'Retired' },
    { value: 'expired', label: 'Expired' },
  ]},
  { name: 'assigned_to', label: 'Pengguna', type: 'text', placeholder: 'Nama pengguna / tim' },
  { name: 'purchase_date', label: 'Tanggal Pembelian', type: 'date' },
  { name: 'warranty_expiry', label: 'Garansi Berakhir', type: 'date' },
  { name: 'delivery_date', label: 'Tanggal Pengiriman', type: 'date' },
  { name: 'ip_address', label: 'IP Address', type: 'text', placeholder: '192.168.1.1' },
  { name: 'license_key', label: 'License Key', type: 'text', placeholder: 'XXXXX-XXXXX' },
  { name: 'device', label: 'Perangkat', type: 'text', placeholder: 'Nama perangkat' },
];

const MAINTENANCE_FIELDS: FormField[] = [
  { name: 'asset_id', label: 'Asset', type: 'number', required: true },
  { name: 'next_maintenance', label: 'Jadwal Maintenance', type: 'date', required: true },
  { name: 'responsible_team', label: 'Tim Bertanggung Jawab', type: 'text', required: true, placeholder: 'Infrastructure Team' },
];

export function IpdsAsset() {
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 300);
  const queryClient = useQueryClient();

  // Asset modal state
  const [modalOpen, setModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
  const [selectedAsset, setSelectedAsset] = useState<AssetRow | null>(null);
  const [deleteConfirm, setDeleteConfirm] = useState<number | null>(null);

  // Filter state
  const [filterType, setFilterType] = useState('');
  const [filterCategory, setFilterCategory] = useState('');
  const [filterStatus, setFilterStatus] = useState('');
  const [filterSort, setFilterSort] = useState('-created_at');
  const [showFilters, setShowFilters] = useState(false);

  // Expanded row (detail view)
  const [expandedRow, setExpandedRow] = useState<number | null>(null);

  // Maintenance schedule modal state
  const [msModalOpen, setMsModalOpen] = useState(false);
  const [msMode, setMsMode] = useState<'create' | 'edit'>('create');
  const [selectedMs, setSelectedMs] = useState<MaintenanceRow | null>(null);

  const pagination = usePagination(15);
  const [currentPage, setCurrentPage] = useState(1); // Keep for display sync if needed, though pagination.page is primary

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['ipds-assets', filterType, filterCategory, filterStatus, pagination.params],
    () => assetService.list({
      ...pagination.params,
      'filter[type]': filterType || undefined,
      'filter[category]': filterCategory || undefined,
      'filter[status]': filterStatus || undefined,
    }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  useEffect(() => {
    setCurrentPage(pagination.page);
  }, [pagination.page]);

  const { data: statsResponse, isLoading: statsLoading } = useApiQuery(
    ['ipds-assets-statistics'],
    () => assetService.statistics(),
  );

  // Fetch filter options
  const { data: filterOptions } = useApiQuery(
    ['ipds-assets-filters'],
    () => assetService.filters(),
  );

  // Asset mutations
  const createMutation = useApiMutation(
    (data: Record<string, unknown>) => assetService.create(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-assets'] });
        queryClient.invalidateQueries({ queryKey: ['ipds-assets-statistics'] });
        setModalOpen(false);
      },
    },
  );

  const updateMutation = useApiMutation(
    (data: Record<string, unknown> & { _id: number }) => assetService.update(data._id, data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-assets'] });
        queryClient.invalidateQueries({ queryKey: ['ipds-assets-statistics'] });
        setModalOpen(false);
      },
    },
  );

  const deleteMutation = useApiMutation(
    (id: number) => assetService.delete(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-assets'] });
        queryClient.invalidateQueries({ queryKey: ['ipds-assets-statistics'] });
        setDeleteConfirm(null);
      },
    },
  );

  // Maintenance schedule mutations
  const createMsMutation = useApiMutation(
    (data: Record<string, unknown>) => maintenanceScheduleService.create(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-assets'] });
        setMsModalOpen(false);
      },
    },
  );

  const updateMsMutation = useApiMutation(
    (data: Record<string, unknown> & { _id: number }) => maintenanceScheduleService.update(data._id, data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-assets'] });
        setMsModalOpen(false);
      },
    },
  );

  const deleteMsMutation = useApiMutation(
    (id: number) => maintenanceScheduleService.delete(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-assets'] });
      },
    },
  );

  const assetData: AssetRow[] = useMemo(() => {
    const rawItems = Array.isArray(response?.data) ? response.data : (response?.data as any)?.data;
    if (!rawItems) return [];
    return (rawItems as ApiAsset[]).map((a: ApiAsset) => ({
      id: a.id,
      kode_asset: a.kode_asset,
      nama: a.name || `${a.brand} ${a.model}`,
      type: a.type,
      category: a.category,
      brand: a.brand,
      model: a.model,
      serial_number: a.serial_number,
      location: a.location,
      status: a.status,
      assigned_to: a.assigned_to,
      purchase_date: a.purchase_date,
      warranty_expiry: a.warranty_expiry,
      delivery_date: a.delivery_date,
      ip_address: a.ip_address,
      license_key: a.license_key,
      device: a.device,
      purchase_value: a.purchase_value,
      depreciation_value: a.depreciation_value,
      value_depreciation: a.value_depreciation,
      maintenance_schedules: a.maintenance_schedules || [],
    }));
  }, [response]);

  const filteredData = useMemo(() => {
    let result = assetData;

    // Text search only (server handles type, category, and status filters)
    if (debouncedSearch) {
      const q = debouncedSearch.toLowerCase();
      result = result.filter((a) =>
        a.kode_asset.toLowerCase().includes(q) ||
        a.brand.toLowerCase().includes(q) ||
        a.model.toLowerCase().includes(q) ||
        a.location.toLowerCase().includes(q) ||
        (a.assigned_to || '').toLowerCase().includes(q) ||
        a.category.toLowerCase().includes(q) ||
        a.status.toLowerCase().includes(q)
      );
    }

    // Sort (client-side)
    result = [...result].sort((a, b) => {
      if (filterSort === '-created_at') {
        return (b.purchase_date || '').localeCompare(a.purchase_date || '');
      } else if (filterSort === 'created_at') {
        return (a.purchase_date || '').localeCompare(b.purchase_date || '');
      } else if (filterSort === 'kode_asset') {
        return a.kode_asset.localeCompare(b.kode_asset);
      } else if (filterSort === 'status') {
        return a.status.localeCompare(b.status);
      }
      return 0;
    });

    return result;
  }, [assetData, debouncedSearch, filterSort]);

  const hasActiveFilters = filterType || filterCategory || filterStatus || filterSort !== '-created_at';

  const filterData = filterOptions?.data as Record<string, string[]> | undefined;

  const typeOptions = useMemo(() => {
    if (filterData?.type) return filterData.type;
    return ['hardware', 'software', 'network'];
  }, [filterData]);

  const categoryOptions = useMemo(() => {
    if (filterData?.category) return filterData.category;
    return ['Server', 'Laptop', 'Desktop', 'Monitor', 'Printer', 'Router'];
  }, [filterData]);

  const statusOptions = useMemo(() => {
    if (filterData?.status) return filterData.status;
    return ['active', 'in-use', 'maintenance', 'retired', 'expired'];
  }, [filterData]);

  const resetFilters = useCallback(() => {
    setSearch('');
    setFilterType('');
    setFilterCategory('');
    setFilterStatus('');
    setFilterSort('-created_at');
    pagination.reset();
  }, [pagination]);

  const stats = useMemo(() => {
    const d = statsResponse?.data as any;
    return {
      total: d?.total_aset ?? 0,
      baik: d?.status_counts?.baik ?? 0,
      rusakRingan: d?.status_counts?.['rusak ringan'] ?? 0,
      rusakBerat: d?.status_counts?.['rusak berat'] ?? 0,
    };
  }, [statsResponse]);

  // Handlers
  const handleCreate = useCallback(() => {
    setModalMode('create');
    setSelectedAsset(null);
    setModalOpen(true);
  }, []);

  const handleEdit = useCallback((row: AssetRow) => {
    setModalMode('edit');
    setSelectedAsset(row);
    setModalOpen(true);
  }, []);

  const handleSubmit = useCallback((data: Record<string, unknown>) => {
    if (modalMode === 'create') {
      createMutation.mutate(data);
    } else {
      updateMutation.mutate({ ...data, _id: selectedAsset!.id });
    }
  }, [modalMode, selectedAsset, createMutation, updateMutation]);

  const handleDelete = useCallback((id: number) => {
    setDeleteConfirm(id);
  }, []);

  const confirmDelete = useCallback(() => {
    if (deleteConfirm) deleteMutation.mutate(deleteConfirm);
  }, [deleteConfirm, deleteMutation]);

  const toggleExpand = useCallback((id: number) => {
    setExpandedRow((prev) => (prev === id ? null : id));
  }, []);

  // Maintenance schedule handlers
  const handleAddMaintenance = useCallback((assetId: number) => {
    setMsMode('create');
    setSelectedMs({ id: 0, asset_id: assetId, next_maintenance: '', responsible_team: '' });
    setMsModalOpen(true);
  }, []);

  const handleEditMaintenance = useCallback((ms: MaintenanceRow) => {
    setMsMode('edit');
    setSelectedMs(ms);
    setMsModalOpen(true);
  }, []);

  const handleMsSubmit = useCallback((data: Record<string, unknown>) => {
    if (msMode === 'create') {
      createMsMutation.mutate(data);
    } else {
      updateMsMutation.mutate({ ...data, _id: selectedMs!.id });
    }
  }, [msMode, selectedMs, createMsMutation, updateMsMutation]);

  const handleDeleteMaintenance = useCallback((id: number) => {
    deleteMsMutation.mutate(id);
  }, [deleteMsMutation]);

  // Column helper with actions
  const columnHelper = createColumnHelper<AssetRow>();

  const columns = useMemo(() => [
    columnHelper.display({
      id: 'expand',
      header: '',
      cell: (info) => (
        <button
          onClick={() => toggleExpand(info.row.original.id)}
          className="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
        >
          {expandedRow === info.row.original.id
            ? <ChevronDown className="h-4 w-4" />
            : <ChevronRight className="h-4 w-4" />}
        </button>
      ),
    }),
    columnHelper.accessor('kode_asset', {
      header: 'Kode',
      cell: (info) => (
        <span className="font-bold text-gray-900 dark:text-white text-[13px] whitespace-nowrap">{info.getValue()}</span>
      ),
    }),
    columnHelper.accessor('model', {
      header: 'Perangkat',
      cell: (info) => (
        <div className="flex flex-col gap-0.5 max-w-[220px]">
          <span className="font-medium text-gray-900 dark:text-white text-[13px] leading-tight truncate">
            {info.row.original.brand} {info.getValue()}
          </span>
          <span className="text-[11px] text-gray-500">{info.row.original.type} · SN: {info.row.original.serial_number}</span>
        </div>
      ),
    }),
    columnHelper.accessor('category', {
      header: 'Kategori',
      cell: (info) => {
        const cat = info.getValue();
        return (
          <div className="flex items-center gap-2">
            <div className="h-7 w-7 rounded-lg bg-gray-100 dark:bg-white/10 flex items-center justify-center">
              {categoryIcons[cat] || <Cpu className="h-3.5 w-3.5 text-gray-600 dark:text-gray-400" />}
            </div>
            <span className="text-[13px] font-medium text-gray-700 dark:text-gray-300">{cat}</span>
          </div>
        );
      },
    }),
    columnHelper.accessor('location', {
      header: 'Lokasi',
      cell: (info) => (
        <div className="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
          <MapPin className="h-3.5 w-3.5 opacity-50" />
          <span className="text-[13px]">{info.getValue()}</span>
        </div>
      ),
    }),
    columnHelper.accessor('assigned_to', {
      header: 'Pengguna',
      cell: (info) => <span className="text-[13px] text-gray-700 dark:text-gray-300">{info.getValue() || '-'}</span>,
    }),
    columnHelper.accessor('status', {
      header: 'Status',
      cell: (info) => (
        <span className={`px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider whitespace-nowrap ${statusStyles[info.getValue()] || 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400'}`}>
          {info.getValue()}
        </span>
      ),
    }),
    columnHelper.accessor('purchase_date', {
      header: 'Tahun',
      cell: (info) => {
        const purchaseDate = info.getValue();
        const row = info.row.original;
        const realValue = (row.purchase_value || 0) - (row.depreciation_value || 0);
        return (
          <div className="flex flex-col gap-0.5">
            <span className="text-[13px] font-bold text-gray-600 dark:text-gray-400">
              {purchaseDate ? new Date(purchaseDate).getFullYear() : '-'}
            </span>
            <span className="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
              {new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR', 
                maximumFractionDigits: 0 
              }).format(realValue)}
            </span>
          </div>
        );
      },
    }),
    columnHelper.display({
      id: 'actions',
      header: '',
      cell: (info) => (
        <div className="flex items-center gap-1">
          <button
            data-scan="tombol edit asset"
            onClick={() => handleEdit(info.row.original)}
            className="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition"
            title="Edit asset"
          >
            <Pencil className="h-3.5 w-3.5" />
          </button>
          <button
            data-scan="tombol hapus asset"
            onClick={() => handleDelete(info.row.original.id)}
            className="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition"
            title="Hapus asset"
          >
            <Trash2 className="h-3.5 w-3.5" />
          </button>
        </div>
      ),
    }),
  ], [expandedRow, toggleExpand, handleEdit, handleDelete]);

  const table = useReactTable({
    data: filteredData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    rowCount: response?.pagination_info?.total_records || 0,
    state: {
      pagination: {
        pageIndex: currentPage - 1,
        pageSize: 15,
      }
    }
  });

  const handleNextPage = () => {
    const nextRaw = response?.links?.next_cursor || response?.links?.next || response?.links?.next_page_url;
    pagination.handleNext(nextRaw);
  };

  const handlePrevPage = () => {
    pagination.handlePrev();
  };

  const isMutating = createMutation.isPending || updateMutation.isPending;
  const isMsMutating = createMsMutation.isPending || updateMsMutation.isPending;

  const expandedAsset = expandedRow !== null ? assetData.find((a) => a.id === expandedRow) : null;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Asset TI"
        description="Inventaris perangkat teknologi informasi kantor."
        actions={
          <button
            data-scan="tombol tambah asset"
            onClick={handleCreate}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Tambah Asset
          </button>
        }
      />

      {/* Stats */}
      <div 
        data-scan="ringkasan statistik"
        className="grid grid-cols-2 lg:grid-cols-4 gap-4"
      >
        {[
          { label: 'Total Aset', value: stats.total, color: 'text-orange-600 dark:text-orange-400', icon: <HardDrive className="h-5 w-5" /> },
          { label: 'Baik', value: stats.baik, color: 'text-emerald-600 dark:text-emerald-400', icon: <Monitor className="h-5 w-5" /> },
          { label: 'Rusak Ringan', value: stats.rusakRingan, color: 'text-amber-600 dark:text-amber-400', icon: <Wrench className="h-5 w-5" /> },
          { label: 'Rusak Berat', value: stats.rusakBerat, color: 'text-red-600 dark:text-red-400', icon: <Cpu className="h-5 w-5" /> },
        ].map((s, i) => (
          <motion.div
            key={i}
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.08 }}
            className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-5 space-y-2"
          >
            <div className="flex items-center justify-between">
              <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{s.label}</p>
              <span className="opacity-30">{s.icon}</span>
            </div>
            <p className={`text-2xl font-black ${s.color}`}>{statsLoading ? '...' : s.value}</p>
          </motion.div>
        ))}
      </div>

      {/* Search + Filters */}
      <div 
        data-scan="pencarian dan filter"
        className="bg-white/40 dark:bg-white/5 glass rounded-2xl border border-white/40 dark:border-white/10 overflow-hidden"
      >
        <div className="flex items-center gap-3 p-2">
          <div className="relative flex-1 group">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-orange-500 transition-colors" />
            <input
              data-scan="input cari asset"
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari asset..."
              className="w-full bg-transparent border-none focus:ring-0 focus:outline-none pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400"
            />
          </div>
          <button
            data-scan="tombol toggle filter"
            onClick={() => setShowFilters(!showFilters)}
            className={`flex items-center gap-2 px-4 py-2 rounded-xl transition text-[13px] font-medium shrink-0 ${
              showFilters
                ? 'bg-orange-500 text-white shadow-sm'
                : 'bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 shadow-sm'
            }`}
          >
            <Filter className="h-4 w-4" />
            Filter
            {hasActiveFilters && (
              <span className="w-2 h-2 rounded-full bg-orange-500" />
            )}
          </button>
        </div>
        {showFilters && (
          <div className="px-4 pb-3 pt-1 border-t border-white/30 dark:border-white/10 space-y-3">
            <div className="flex flex-wrap items-end gap-3">
              <div className="flex w-full max-w-xs flex-col gap-2">
                <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tipe</label>
                <select
                  data-scan="filter tipe"
                  value={filterType}
                  onChange={(e) => { setFilterType(e.target.value); pagination.reset(); }}
                  className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
                >
                  <option value="">Semua</option>
                  {typeOptions.map((t) => <option key={t} value={t}>{t}</option>)}
                </select>
              </div>
              <div className="flex w-full max-w-xs flex-col gap-2">
                <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Kategori</label>
                <select
                  data-scan="filter kategori"
                  value={filterCategory}
                  onChange={(e) => { setFilterCategory(e.target.value); pagination.reset(); }}
                  className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
                >
                  <option value="">Semua</option>
                  {categoryOptions.map((c) => <option key={c} value={c}>{c}</option>)}
                </select>
              </div>
              <div className="flex w-full max-w-xs flex-col gap-2">
                <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Status</label>
                <select
                  data-scan="filter status"
                  value={filterStatus}
                  onChange={(e) => { setFilterStatus(e.target.value); pagination.reset(); }}
                  className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
                >
                  <option value="">Semua</option>
                  {statusOptions.map((s) => <option key={s} value={s}>{s}</option>)}
                </select>
              </div>
              <div className="flex flex-col gap-2">
                <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Urutkan</label>
                <select
                  data-scan="filter urutkan"
                  value={filterSort}
                  onChange={(e) => setFilterSort(e.target.value)}
                  className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
                >
                  <option value="-created_at">Terbaru</option>
                  <option value="created_at">Terlama</option>
                  <option value="kode_asset">Kode Asset</option>
                  <option value="status">Status</option>
                </select>
              </div>
              <div className="flex items-end">
                <button
                  data-scan="tombol reset"
                  onClick={resetFilters}
                  className="h-9 px-4 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition shadow-sm"
                >
                  Reset
                </button>
              </div>
            </div>
            <div>
              <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                Menampilkan {filteredData.length} dari {assetData.length} data
              </p>
            </div>
          </div>
        )}
      </div>

      {/* Table */}
      <div 
        data-scan="tabel asset"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data asset..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Asset" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    {table.getHeaderGroups().map((hg) => (
                      <React.Fragment key={hg.id}>
                        {hg.headers.map((h) => (
                          <th key={h.id} className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
                            {flexRender(h.column.columnDef.header, h.getContext())}
                          </th>
                        ))}
                      </React.Fragment>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                  {table.getRowModel().rows.map((row) => (
                    <React.Fragment key={row.id}>
                      <tr className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                        {row.getVisibleCells().map((cell) => (
                          <td key={cell.id} className="px-6 py-4">
                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                          </td>
                        ))}
                      </tr>
                      {/* Expanded Detail Row */}
                      <AnimatePresence>
                        {expandedRow === row.original.id && expandedAsset && (
                          <motion.tr
                            initial={{ opacity: 0, height: 0 }}
                            animate={{ opacity: 1, height: 'auto' }}
                            exit={{ opacity: 0, height: 0 }}
                            className="bg-gray-50/50 dark:bg-white/[0.02]"
                          >
                            <td colSpan={columns.length} className="px-6 py-5">
                              <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                                {[
                                  { label: 'Kode Asset', value: expandedAsset.kode_asset },
                                  { label: 'Tipe', value: expandedAsset.type },
                                  { label: 'Kategori', value: expandedAsset.category },
                                  { label: 'Brand / Model', value: `${expandedAsset.brand} ${expandedAsset.model}` },
                                  { label: 'Serial Number', value: expandedAsset.serial_number || '-' },
                                  { label: 'IP Address', value: expandedAsset.ip_address || '-' },
                                  { label: 'Device', value: expandedAsset.device || '-' },
                                  { label: 'License Key', value: expandedAsset.license_key || '-' },
                                  { label: 'Lokasi', value: expandedAsset.location },
                                  { label: 'Pengguna', value: expandedAsset.assigned_to || '-' },
                                  { label: 'Tanggal Pembelian', value: expandedAsset.purchase_date ? new Date(expandedAsset.purchase_date).toLocaleDateString('id-ID') : '-' },
                                  { label: 'Garansi Berakhir', value: expandedAsset.warranty_expiry ? new Date(expandedAsset.warranty_expiry).toLocaleDateString('id-ID') : '-' },
                                ].map((item, i) => (
                                  <div key={i} className="space-y-0.5">
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-gray-400">{item.label}</p>
                                    <p className="text-[13px] font-medium text-gray-900 dark:text-white">{item.value}</p>
                                  </div>
                                ))}
                              </div>

                              {/* Maintenance Schedules */}
                              <div className="border-t border-gray-200/60 dark:border-white/10 pt-4">
                                <div className="flex items-center justify-between mb-3">
                                  <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4 text-amber-500" />
                                    <span className="text-[12px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                      Jadwal Maintenance
                                    </span>
                                    <span className="px-1.5 py-0.5 rounded-md bg-amber-100 dark:bg-amber-500/10 text-[10px] font-bold text-amber-700 dark:text-amber-400">
                                      {expandedAsset.maintenance_schedules?.length || 0}
                                    </span>
                                  </div>
                                  <button
                                    data-scan="tombol tambah maintenance"
                                    onClick={() => handleAddMaintenance(expandedAsset.id)}
                                    className="flex items-center gap-1 px-3 py-1.5 text-[11px] font-bold rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-500/20 transition"
                                  >
                                    <Plus className="h-3 w-3" />
                                    Tambah
                                  </button>
                                </div>

                                {expandedAsset.maintenance_schedules && expandedAsset.maintenance_schedules.length > 0 ? (
                                  <div className="space-y-2">
                                    {expandedAsset.maintenance_schedules.map((ms) => (
                                      <div
                                        key={ms.id}
                                        className="flex items-center justify-between px-4 py-3 rounded-xl bg-white dark:bg-white/5 border border-gray-200/60 dark:border-white/5"
                                      >
                                        <div className="flex items-center gap-3">
                                          <div className="h-8 w-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center">
                                            <Wrench className="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                          </div>
                                          <div>
                                            <p className="text-[13px] font-medium text-gray-900 dark:text-white">
                                              {new Date(ms.next_maintenance).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })}
                                            </p>
                                            <div className="flex items-center gap-1 text-[11px] text-gray-500">
                                              <Users className="h-3 w-3" />
                                              {ms.responsible_team}
                                            </div>
                                          </div>
                                        </div>
                                        <div className="flex items-center gap-1">
                                          <button
                                            data-scan="tombol edit maintenance"
                                            onClick={() => handleEditMaintenance({ id: ms.id, asset_id: expandedAsset.id, next_maintenance: ms.next_maintenance, responsible_team: ms.responsible_team })}
                                            className="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition"
                                            title="Edit jadwal"
                                          >
                                            <Pencil className="h-3 w-3" />
                                          </button>
                                          <button
                                            data-scan="tombol hapus maintenance"
                                            onClick={() => handleDeleteMaintenance(ms.id)}
                                            className="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition"
                                            title="Hapus jadwal"
                                          >
                                            <Trash2 className="h-3 w-3" />
                                          </button>
                                        </div>
                                      </div>
                                    ))}
                                  </div>
                                ) : (
                                  <p className="text-[12px] text-gray-400 py-3 text-center">Belum ada jadwal maintenance.</p>
                                )}
                              </div>
                            </td>
                          </motion.tr>
                        )}
                      </AnimatePresence>
                    </React.Fragment>
                  ))}
                  {table.getRowModel().rows.length === 0 && (
                    <tr>
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data asset.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div 
              data-scan="navigasi halaman"
              className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5"
            >
              <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                Halaman <span className="text-gray-900 dark:text-white font-bold">{currentPage}</span> dari{' '}
                <span className="text-gray-900 dark:text-white font-bold">{response?.pagination_info?.total_page || response?.meta?.last_page || 1}</span>
              </p>
              <div className="flex gap-2">
                <button onClick={handlePrevPage} disabled={!pagination.canGoPrev || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm">
                  Sebelumnya
                </button>
                <button onClick={handleNextPage} disabled={!pagination.canGoNext || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm">
                  Berikutnya
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      {/* Asset Create/Edit Modal */}
      <EntityFormModal
        data-scan="modal form asset"
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        onSubmit={handleSubmit}
        title={modalMode === 'create' ? 'Tambah Asset Baru' : 'Edit Asset'}
        fields={ASSET_FIELDS}
        initialData={selectedAsset}
        isLoading={isMutating}
        mode={modalMode}
      />

      {/* Maintenance Schedule Create/Edit Modal */}
      <EntityFormModal
        data-scan="modal form maintenance"
        open={msModalOpen}
        onClose={() => setMsModalOpen(false)}
        onSubmit={handleMsSubmit}
        title={msMode === 'create' ? 'Tambah Jadwal Maintenance' : 'Edit Jadwal Maintenance'}
        fields={MAINTENANCE_FIELDS}
        initialData={selectedMs}
        isLoading={isMsMutating}
        mode={msMode}
      />

      {/* Delete Confirmation */}
      {deleteConfirm !== null && (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
          <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={() => setDeleteConfirm(null)} />
          <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-sm mx-4 p-6 space-y-4">
            <div className="flex items-center gap-3">
              <div className="h-10 w-10 rounded-full bg-red-100 dark:bg-red-500/10 flex items-center justify-center">
                <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400" />
              </div>
              <div>
                <h3 className="text-sm font-bold text-gray-900 dark:text-white">Hapus Asset?</h3>
                <p className="text-[12px] text-gray-500 dark:text-gray-400">Asset akan dihapus permanen beserta jadwal maintenancenya.</p>
              </div>
            </div>
            <div className="flex justify-end gap-3">
              <button
                onClick={() => setDeleteConfirm(null)}
                className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm"
              >
                Batal
              </button>
              <button
                onClick={confirmDelete}
                disabled={deleteMutation.isPending}
                className="px-4 py-2 text-[12px] font-bold rounded-full bg-red-600 hover:bg-red-700 text-white shadow-sm transition-all disabled:opacity-50"
              >
                {deleteMutation.isPending ? 'Menghapus...' : 'Hapus'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}