import React, { useState, useMemo, useCallback } from 'react';
import { 
  ExternalLink, 
  MoreHorizontal, 
  MoreVertical, 
  Plus, 
  Search, 
  Filter, 
  Link as LinkIcon,
  ChevronRight,
  ChevronDown,
  Trash2,
  Pencil
} from 'lucide-react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { usePagination } from '../hooks/usePagination';
import { linkService } from '../lib/api-services';
import type { Link } from '../types/api';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal, type FormField } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';

import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

// ============================================================
// Component: SubLinkItem
// ============================================================
function SubLinkItem({ 
  child, 
  onEdit, 
  onDelete 
}: { 
  child: Link; 
  onEdit: (link: Link) => void; 
  onDelete: (link: Link) => void;
}) {
  return (
    <div className="flex items-center justify-between gap-2 border border-slate-200 bg-white/70 px-3 py-2 text-xs text-slate-600 transition-colors hover:border-amber-300/50 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:text-white group">
      <div className="flex items-center gap-2 min-w-0">
        <div className="flex-1 min-w-0">
          <div className="font-medium truncate">{child.nama}</div>
          {child.link && (
            <a
              href={child.link}
              target="_blank"
              rel="noreferrer"
              className="text-[10px] text-amber-600 dark:text-amber-400 hover:underline truncate block"
            >
              {child.link}
            </a>
          )}
        </div>
      </div>
      <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button
          onClick={() => onEdit(child)}
          className="p-1 rounded hover:bg-slate-100 dark:hover:bg-white/10 text-slate-500 transition"
        >
          <Pencil className="size-3" />
        </button>
        <button
          onClick={() => onDelete(child)}
          className="p-1 rounded hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500 transition"
        >
          <Trash2 className="size-3" />
        </button>
        {child.link && (
          <a
            href={child.link}
            target="_blank"
            rel="noreferrer"
            className="p-1 rounded hover:bg-slate-100 dark:hover:bg-white/10 text-slate-500 transition"
          >
            <ExternalLink className="size-3" />
          </a>
        )}
      </div>
    </div>
  );
}

// ============================================================
// Component: LinkCard
// ============================================================
function LinkCard({
  item,
  onEdit,
  onDelete,
}: {
  item: Link;
  onEdit: (link: Link) => void;
  onDelete: (link: Link) => void;
}) {
  const [isExpanded, setIsExpanded] = useState(false);
  const children = Array.isArray(item.children) ? item.children : [];
  const recursiveChildren = Array.isArray(item.children_recursive) ? item.children_recursive : [];
  const hasChildren = children.length > 0 || recursiveChildren.length > 0;

  return (
    <div className="bg-white/70 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all group">
      <div className="p-4 space-y-3">
        <div className="flex items-start justify-between gap-3">
          <div className="space-y-1 flex-1 min-w-0">
            <div className="flex items-center gap-2">
              <h3 className="text-sm font-bold text-slate-900 dark:text-white truncate">
                {item.nama}
              </h3>
              {item.kategori && (
                <span className={cn(
                  "px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider",
                  item.kategori === 'internal' 
                    ? "bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400"
                    : "bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
                )}>
                  {item.kategori}
                </span>
              )}
            </div>
            {item.deskripsi && (
              <p className="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                {item.deskripsi}
              </p>
            )}
          </div>
          <div className="flex items-center gap-1 shrink-0">
            <button
              onClick={() => onEdit(item)}
              className="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 text-slate-500 transition"
            >
              <Pencil className="size-3.5" />
            </button>
            <button
              onClick={() => onDelete(item)}
              className="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500 transition"
            >
              <Trash2 className="size-3.5" />
            </button>
          </div>
        </div>

        <div className="flex items-center justify-between gap-2">
          {item.link ? (
            <a
              href={item.link}
              target="_blank"
              rel="noreferrer"
              className="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline"
            >
              <ExternalLink className="size-3" />
              Buka Tautan
            </a>
          ) : (
            <div className="h-4" />
          )}

          {hasChildren && (
            <button
              onClick={() => setIsExpanded(!isExpanded)}
              className="flex items-center gap-1 text-[11px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition"
            >
              {isExpanded ? 'Tutup' : 'Sub-Link'}
              {isExpanded ? <ChevronDown className="size-3" /> : <ChevronRight className="size-3" />}
            </button>
          )}
        </div>
      </div>

      {isExpanded && hasChildren && (
        <div className="bg-slate-50/50 dark:bg-black/20 border-t border-slate-100 dark:border-white/5 p-3 space-y-3">
          {/* Recursive Children as badges/tags (matches original style) */}
          {recursiveChildren.length > 0 && (
            <div className="flex flex-wrap gap-2">
              {recursiveChildren.map((child: Link) => (
                <div
                  key={child.id}
                  className="inline-flex items-center gap-1 bg-white dark:bg-white/5 border border-slate-200 dark:border-white/10 px-2 py-1 rounded text-[10px] group/tag"
                >
                  {child.link ? (
                    <a
                      href={child.link}
                      target="_blank"
                      rel="noreferrer"
                      className="text-slate-700 dark:text-slate-200 hover:text-amber-600 dark:hover:text-amber-400"
                    >
                      {child.nama}
                    </a>
                  ) : (
                    <span className="text-slate-700 dark:text-slate-200">{child.nama}</span>
                  )}
                  <div className="flex items-center gap-0.5 opacity-0 group-hover/tag:opacity-100 transition-opacity">
                    <button onClick={() => onEdit(child)} className="hover:text-amber-600"><Pencil className="size-2.5" /></button>
                    <button onClick={() => onDelete(child)} className="hover:text-red-500"><Trash2 className="size-2.5" /></button>
                  </div>
                </div>
              ))}
            </div>
          )}

          {/* Direct Children as list (matches original style) */}
          {children.length > 0 && (
            <div className="space-y-1.5">
              <div className="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">Daftar Sub</div>
              {children.map((child: Link) => (
                <SubLinkItem 
                  key={child.id} 
                  child={child} 
                  onEdit={onEdit} 
                  onDelete={onDelete} 
                />
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}

// ============================================================
// Main View: Polink
// ============================================================
export function Polink() {
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 300);
  const pagination = usePagination(9);
  
  // Reset pagination when search changes
  React.useEffect(() => {
    pagination.reset();
  }, [debouncedSearch]);
  
  // Modal states
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingItem, setEditingItem] = useState<Link | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Link | null>(null);

  // ----- Data fetching -----
  const { 
    data: response, 
    isLoading, 
    error, 
    refetch 
  } = useApiQuery(
    ['links', debouncedSearch, pagination.params],
    () => linkService.list({
      ...pagination.params,
      'filter[nama]': debouncedSearch || undefined,
    }),
    { 
      staleTime: 5000,
      enabled: true 
    }
  );

  React.useEffect(() => {
    pagination.sync(response);
  }, [response]);

  // Pagination Handlers
  const handleNextPage = () => {
    const nextRaw = (response as any)?.links?.next_cursor || (response as any)?.links?.next || (response as any)?.links?.next_page_url;
    pagination.handleNext(nextRaw);
  };

  const handlePrevPage = () => {
    pagination.handlePrev();
  };

  const links = useMemo(() => {
    const rawData = response?.data;
    // If response.data is directly the array (flat structure)
    if (Array.isArray(rawData)) return rawData;
    // If response.data holds the object containing the array (nested structure)
    if (rawData && typeof rawData === 'object' && 'data' in rawData && Array.isArray(rawData.data)) {
      return (rawData as any).data;
    }
    return [];
  }, [response]);
  
  // Only show top-level for the main grid
  const topLevelLinks = useMemo(() => {
    // If searching, show all matches. If not, only show root links.
    if (debouncedSearch) return links;
    return links.filter((l: Link) => !l.parent_id);
  }, [links, debouncedSearch]);

  // ----- Mutations -----
  const createMutation = useApiMutation(
    (data: any) => linkService.create(data),
    {
      invalidateKeys: [['links']],
      onSuccess: () => setShowFormModal(false),
    }
  );

  const updateMutation = useApiMutation(
    (data: any) => linkService.update(data.id, data),
    {
      invalidateKeys: [['links']],
      onSuccess: () => {
        setShowFormModal(false);
        setEditingItem(null);
      },
    }
  );

  const deleteMutation = useApiMutation(
    (id: number) => linkService.delete(id),
    {
      invalidateKeys: [['links']],
      onSuccess: () => setDeleteTarget(null),
    }
  );

  // ----- Form Config -----
  const parentOptions = useMemo(() => {
    const options: { value: string; label: string }[] = [];
    const walk = (item: Link, depth: number) => {
      if (item.id === editingItem?.id) return;
      const prefix = depth > 0 ? '-- '.repeat(depth) : '';
      options.push({ value: String(item.id), label: `${prefix}${item.nama}` });
      
      const children = Array.isArray(item.children) ? item.children : [];
      children.forEach(child => walk(child, depth + 1));
    };

    links.forEach((link: Link) => {
      // Only start walk from top-level links to avoid duplicates
      if (!link.parent_id) walk(link, 0);
    });
    
    return options;
  }, [links, editingItem]);

  const formFields = useMemo((): FormField[] => [
    { name: 'nama', label: 'Nama Tautan', type: 'text', placeholder: 'Judul tautan...', required: true },
    { name: 'link', label: 'URL', type: 'text', placeholder: 'https://...', required: true },
    { 
      name: 'kategori', 
      label: 'Kategori', 
      type: 'select', 
      required: true,
      options: [
        { value: 'internal', label: 'Internal' },
        { value: 'eksternal', label: 'Eksternal' }
      ]
    },
    { 
      name: 'parent_id', 
      label: 'Parent (Opsional)', 
      type: 'select', 
      options: parentOptions,
      placeholder: '— Root (Utama) —'
    },
    { name: 'deskripsi', label: 'Deskripsi', type: 'textarea', placeholder: 'Keterangan singkat...', colSpan: 2 },
  ], [parentOptions]);

  // ----- Handlers -----
  const handleOpenCreate = () => {
    setEditingItem(null);
    setShowFormModal(true);
  };

  const handleOpenEdit = (item: Link) => {
    setEditingItem(item);
    setShowFormModal(true);
  };

  const handleFormSubmit = (formData: Record<string, unknown>) => {
    const payload = {
      ...formData,
      parent_id: formData.parent_id ? Number(formData.parent_id) : null,
    };
    if (editingItem) {
      updateMutation.mutate({ ...payload, id: editingItem.id });
    } else {
      createMutation.mutate(payload);
    }
  };

  const totalRecords = pagination.meta?.total ?? 0;

  return (
    <div className="space-y-6">
      <PageHeader 
        title="Polink" 
        description="Portal Link untuk mempermudah akses ke berbagai layanan internal dan eksternal."
      />

      {/* Toolbar */}
      <div className="bg-white/40 dark:bg-white/5 glass rounded-2xl border border-white/40 dark:border-white/10 p-2 flex flex-col md:flex-row gap-3 items-center">
        <div className="relative flex-1 group w-full">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Cari nama tautan..."
            className="w-full bg-transparent border-none focus:ring-0 focus:outline-none pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400"
          />
        </div>
        <div className="flex items-center gap-2 w-full md:w-auto overflow-x-auto no-scrollbar">
          <button
            onClick={handleOpenCreate}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-[12px] font-bold shadow-lg shadow-amber-500/20 transition whitespace-nowrap"
          >
            <Plus className="h-4 w-4" />
            Tambah Tautan
          </button>
        </div>
      </div>

      {isLoading ? (
        <LoadingSkeleton variant="cards" message="Memuat portal link..." />
      ) : error ? (
        <ApiErrorBoundary error={error} onRetry={refetch} />
      ) : topLevelLinks.length === 0 ? (
        <div className="bg-white/40 dark:bg-white/5 glass rounded-3xl border border-white/30 dark:border-white/10 p-16 text-center">
          <div className="inline-flex items-center justify-center h-16 w-16 rounded-3xl bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 mb-4">
            <LinkIcon className="h-8 w-8" />
          </div>
          <h3 className="text-lg font-bold text-slate-900 dark:text-white">Tidak Ada Tautan</h3>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
            {search ? 'Pencarian tidak menemukan hasil.' : 'Belum ada tautan yang terdaftar.'}
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          {topLevelLinks.map((item: Link) => (
            <LinkCard
              key={item.id}
              item={item}
              onEdit={handleOpenEdit}
              onDelete={setDeleteTarget}
            />
          ))}
        </div>
      )}

      {/* Pagination */}
      {pagination.meta && pagination.meta.lastPage > 1 && (
        <div className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5">
          <div className="flex items-center gap-4">
            <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
              Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari{' '}
              <span className="text-gray-900 dark:text-white font-bold">{pagination.meta.lastPage || 1}</span>
            </p>
            <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
            <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
              Menampilkan {links.length} dari {pagination.meta.total} data
            </p>
          </div>
          <div className="flex gap-2">
            <button
              onClick={handlePrevPage}
              disabled={!pagination.canGoPrev || isLoading}
              className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
            >
              Sebelumnya
            </button>
            <button
              onClick={handleNextPage}
              disabled={!pagination.canGoNext || isLoading}
              className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
            >
              Berikutnya
            </button>
          </div>
        </div>
      )}

      {/* Modals */}
      <EntityFormModal
        open={showFormModal}
        onClose={() => setShowFormModal(false)}
        onSubmit={handleFormSubmit}
        title={editingItem ? 'Edit Tautan' : 'Tambah Tautan'}
        description="Kelola tautan portal untuk mempermudah akses layanan."
        fields={formFields}
        initialData={editingItem ? {
          ...editingItem,
          parent_id: editingItem.parent_id ? String(editingItem.parent_id) : '',
        } : null}
        isLoading={createMutation.isPending || updateMutation.isPending}
        mode={editingItem ? 'edit' : 'create'}
      />

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus Tautan"
        message={`Yakin ingin menghapus "${deleteTarget?.nama}"? Menghapus tautan utama juga akan menghapus semua sub-link di dalamnya.`}
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}