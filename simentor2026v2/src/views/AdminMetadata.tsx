import React, { useState, useMemo } from 'react';
import {
  Plus,
  Database,
  Pencil,
  Trash2,
  Ellipsis,
  Loader2,
  AlertCircle,
  Tag,
  Layers,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react';
import { motion } from 'motion/react';
import { PageHeader } from '../components/PageHeader';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { adminMetasService } from '../lib/api-services';
import type { Meta } from '../types/api';
import { EntityFormModal, FormField } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';

type NoticeState = {
  tone: 'success' | 'error';
  message: string;
} | null;

export function AdminMetadata() {
  const [openMenuId, setOpenMenuId] = useState<number | null>(null);
  const [notice, setNotice] = useState<NoticeState>(null);
  
  // Modal states
  const [modalConfig, setModalConfig] = useState<{
    open: boolean;
    mode: 'create' | 'edit';
    initialData?: Partial<Meta>;
    parentId?: number | null;
  }>({ open: false, mode: 'create' });

  // Delete states
  const [deleteConfig, setDeleteConfig] = useState<{
    open: boolean;
    metaId?: number;
    metaName?: string;
  }>({ open: false });

  // Fetch data
  const { data: tree, isLoading, error, refetch } = useApiQuery(
    ['admin-metas-tree'],
    () => adminMetasService.tree()
  );

  // Mutations
  const saveMutation = useApiMutation(
    (data: Partial<Meta>) => {
      if (modalConfig.mode === 'edit' && modalConfig.initialData?.id) {
        return adminMetasService.update(modalConfig.initialData.id, data);
      }
      return adminMetasService.create({ ...data, parent_id: modalConfig.parentId });
    },
    {
      invalidateKeys: [['admin-metas-tree']],
      onSuccess: () => {
        setNotice({ 
          tone: 'success', 
          message: `Metadata berhasil ${modalConfig.mode === 'create' ? 'ditambahkan' : 'diperbarui'}.` 
        });
        setModalConfig({ open: false, mode: 'create' });
      },
      onError: (err) => {
        setNotice({ tone: 'error', message: err.message || 'Gagal menyimpan metadata.' });
      }
    }
  );

  const deleteMutation = useApiMutation(
    (id: number) => adminMetasService.delete(id),
    {
      invalidateKeys: [['admin-metas-tree']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Metadata berhasil dihapus.' });
        setDeleteConfig({ open: false });
      },
      onError: (err) => {
        setNotice({ tone: 'error', message: err.message || 'Gagal menghapus metadata.' });
      }
    }
  );

  // Handlers
  const handleAddParent = () => {
    setModalConfig({ open: true, mode: 'create', parentId: null });
    setNotice(null);
  };

  const handleAddChild = (parentId: number) => {
    setModalConfig({ open: true, mode: 'create', parentId });
    setNotice(null);
  };

  const handleEdit = (meta: Meta) => {
    setModalConfig({ 
      open: true, 
      mode: 'edit', 
      initialData: meta,
      parentId: meta.parent_id 
    });
    setNotice(null);
    setOpenMenuId(null);
  };

  const handleDeleteRequest = (meta: Meta) => {
    setDeleteConfig({ 
      open: true, 
      metaId: meta.id, 
      metaName: meta.name 
    });
    setNotice(null);
    setOpenMenuId(null);
  };

  const formFields: FormField[] = [
    { name: 'name', label: 'Nama Metadata', type: 'text', required: true, colSpan: 2 },
    { name: 'name2', label: 'Nama Lain', type: 'text', required: false, colSpan: 2 },
  ];

  if (error) {
    return (
      <div className="p-8 flex flex-col items-center justify-center min-h-[400px]">
        <AlertCircle className="h-12 w-12 text-red-500 mb-4" />
        <h3 className="text-lg font-bold text-gray-900 dark:text-white">Gagal Memuat Data</h3>
        <p className="text-gray-500 dark:text-gray-400 mt-2">{error.message}</p>
        <button 
          onClick={() => refetch()}
          className="mt-6 px-6 py-2 bg-amber-400 hover:bg-amber-500 text-gray-900 rounded-full font-bold transition-all"
        >
          Coba Lagi
        </button>
      </div>
    );
  }

  return (
    <div className="p-4 md:p-8 space-y-8">
      <PageHeader
        title="Manajemen Metadata"
        description="Kelola kategori data statis dan metadata sistem secara hierarkis."
        actions={
          <button
            data-scan="tombol tambah kategori"
            onClick={handleAddParent}
            className="flex items-center gap-2 px-6 py-2.5 bg-amber-400 hover:bg-amber-500 text-gray-900 rounded-full text-[13px] font-bold shadow-lg shadow-amber-500/20 transition-all active:scale-95"
          >
            <Plus className="h-4 w-4" />
            Tambah Kategori
          </button>
        }
      />

      {notice && (
        <motion.div
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          className={`p-4 rounded-2xl border flex items-center gap-3 ${
            notice.tone === 'success'
              ? 'bg-green-50 dark:bg-green-500/10 border-green-200 dark:border-green-500/20 text-green-700 dark:text-green-400'
              : 'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400'
          }`}
        >
          <div className={`h-8 w-8 rounded-xl flex items-center justify-center shrink-0 ${
             notice.tone === 'success' ? 'bg-green-100 dark:bg-green-500/20' : 'bg-red-100 dark:bg-red-500/20'
          }`}>
            {notice.tone === 'success' ? <Plus className="h-4 w-4" /> : <AlertCircle className="h-4 w-4" />}
          </div>
          <p className="text-sm font-medium">{notice.message}</p>
          <button onClick={() => setNotice(null)} className="ml-auto text-current opacity-50 hover:opacity-100">
            <Plus className="h-4 w-4 rotate-45" />
          </button>
        </motion.div>
      )}

      {isLoading ? (
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          {[1, 2, 3].map((i) => (
            <div key={i} className="h-64 rounded-3xl bg-gray-100 dark:bg-white/5 animate-pulse" />
          ))}
        </div>
      ) : (
        <div 
          data-scan="daftar kategori metadata"
          className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 items-start"
        >
          {tree?.data?.map((parent: Meta) => (
            <MetaCard
              key={parent.id}
              parent={parent}
              openMenuId={openMenuId}
              setOpenMenuId={setOpenMenuId}
              onAddChild={() => handleAddChild(parent.id)}
              onEdit={handleEdit}
              onDelete={handleDeleteRequest}
            />
          ))}
          
          {(tree?.data?.length === 0) && (
            <div className="col-span-full py-20 flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-white/10 rounded-3xl">
              <Layers className="h-12 w-12 text-gray-300 dark:text-gray-700 mb-4" />
              <p className="text-gray-500 dark:text-gray-400 font-medium">Belum ada data metadata.</p>
              <button onClick={handleAddParent} className="mt-4 text-amber-500 font-bold hover:underline">
                Klik di sini untuk menambah kategori pertama.
              </button>
            </div>
          )}
        </div>
      )}

      <EntityFormModal
        open={modalConfig.open}
        onClose={() => setModalConfig({ ...modalConfig, open: false })}
        mode={modalConfig.mode}
        title={modalConfig.mode === 'create' ? (modalConfig.parentId ? 'Tambah Sub-Metadata' : 'Tambah Kategori Metadata') : 'Edit Metadata'}
        description={modalConfig.mode === 'create' ? 'Masukkan detail metadata baru yang akan ditambahkan ke sistem.' : 'Perbarui informasi metadata yang dipilih.'}
        fields={formFields}
        initialData={modalConfig.initialData as Record<string, unknown>}
        onSubmit={(data) => saveMutation.mutate(data)}
        isLoading={saveMutation.isPending}
      />

      <ConfirmDialog
        open={deleteConfig.open}
        onClose={() => setDeleteConfig({ open: false })}
        onConfirm={() => deleteConfig.metaId && deleteMutation.mutate(deleteConfig.metaId)}
        title="Hapus Metadata"
        message={`Apakah Anda yakin ingin menghapus metadata "${deleteConfig.metaName}"? Tindakan ini tidak dapat dibatalkan.`}
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}

function MetaCard({
  parent,
  openMenuId,
  setOpenMenuId,
  onAddChild,
  onEdit,
  onDelete,
}: {
  parent: Meta;
  openMenuId: number | null;
  setOpenMenuId: (id: number | null) => void;
  onAddChild: () => void;
  onEdit: (meta: Meta) => void;
  onDelete: (meta: Meta) => void;
}) {
  const [currentPage, setCurrentPage] = useState(1);
  const pageSize = 10;
  
  const children = parent.children || [];
  const totalPages = Math.ceil(children.length / pageSize);
  const paginatedChildren = children.slice((currentPage - 1) * pageSize, currentPage * pageSize);

  // Reset page if current page is empty (e.g. after deletion)
  if (currentPage > 1 && paginatedChildren.length === 0) {
    setCurrentPage(totalPages || 1);
  }

  return (
    <motion.div
      layout
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      className="bg-white/70 dark:bg-gray-900/40 backdrop-blur-md border border-gray-200/50 dark:border-white/10 rounded-3xl overflow-hidden shadow-xl shadow-black/5 flex flex-col min-h-[300px]"
    >
      {/* Card Header */}
      <div className="px-6 py-5 border-b border-gray-200/50 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="h-10 w-10 rounded-2xl bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center shrink-0">
            <Tag className="h-5 w-5 text-amber-600 dark:text-amber-400" />
          </div>
          <div>
            <h3 className="text-sm font-bold text-gray-900 dark:text-white line-clamp-1">{parent.name}</h3>
            <p className="text-[11px] text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">
              {parent.name2 ? parent.name2 : 'KATEGORI'}
            </p>
          </div>
        </div>
        
        <div className="flex items-center gap-1">
          <button
            data-scan="tombol tambah sub-item"
            onClick={onAddChild}
            className="p-2 rounded-xl hover:bg-white dark:hover:bg-white/10 text-gray-500 dark:text-gray-400 transition-colors"
            title="Tambah Sub-item"
          >
            <Plus className="h-4 w-4" />
          </button>
          
          <ActionMenu
            id={parent.id}
            isOpen={openMenuId === parent.id}
            onToggle={() => setOpenMenuId(openMenuId === parent.id ? null : parent.id)}
            onEdit={() => onEdit(parent)}
            onDelete={() => onDelete(parent)}
          />
        </div>
      </div>

      {/* Card Body - Children Table */}
      <div className="flex-1 overflow-auto p-4">
        {parent.children && parent.children.length > 0 ? (
          <table className="w-full text-left border-separate border-spacing-y-2">
            <thead>
              <tr className="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                <th className="px-3 pb-2">Nama</th>
                <th className="px-3 pb-2 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {paginatedChildren.map((child) => (
                <tr key={child.id} className="group transition-all">
                  <td className="px-3 py-2 bg-gray-50/50 dark:bg-white/5 rounded-l-xl border-y border-l border-gray-100 dark:border-white/5 group-hover:bg-white dark:group-hover:bg-white/10">
                    <div className="flex flex-col">
                      <span className="text-xs font-bold text-gray-700 dark:text-gray-200">{child.name}</span>
                      {child.name2 && (
                        <div className="flex items-center gap-1.5 mt-0.5">
                          <div className="h-1 w-1 rounded-full bg-amber-400" />
                          <span className="text-[10px] text-gray-400 dark:text-gray-500 font-medium tracking-wide italic leading-none">{child.name2}</span>
                        </div>
                      )}
                    </div>
                  </td>
                  <td className="px-3 py-2 bg-gray-50/50 dark:bg-white/5 rounded-r-xl border-y border-r border-gray-100 dark:border-white/5 text-right group-hover:bg-white dark:group-hover:bg-white/10">
                    <ActionMenu
                      id={child.id}
                      isOpen={openMenuId === child.id}
                      onToggle={() => setOpenMenuId(openMenuId === child.id ? null : child.id)}
                      onEdit={() => onEdit(child)}
                      onDelete={() => onDelete(child)}
                    />
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <div className="h-full flex flex-col items-center justify-center py-10 opacity-40">
            <Plus className="h-8 w-8 text-gray-400 mb-2" />
            <p className="text-xs font-medium text-gray-500">Belum ada sub-item</p>
          </div>
        )}
      </div>

      {/* Pagination Footer */}
      {totalPages > 1 && (
        <div className="px-6 py-3 border-t border-gray-200/50 dark:border-white/10 bg-gray-50/30 dark:bg-white/[0.02] flex items-center justify-between">
          <span className="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
            Hal {currentPage} dari {totalPages}
          </span>
          <div className="flex items-center gap-1">
            <button
              onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
              disabled={currentPage === 1}
              className="p-1 rounded-lg hover:bg-white dark:hover:bg-white/10 text-gray-500 disabled:opacity-30 disabled:hover:bg-transparent transition-all"
            >
              <ChevronLeft className="h-4 w-4" />
            </button>
            <button
              onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))}
              disabled={currentPage === totalPages}
              className="p-1 rounded-lg hover:bg-white dark:hover:bg-white/10 text-gray-500 disabled:opacity-30 disabled:hover:bg-transparent transition-all"
            >
              <ChevronRight className="h-4 w-4" />
            </button>
          </div>
        </div>
      )}
    </motion.div>
  );
}

function ActionMenu({
  id,
  isOpen,
  onToggle,
  onEdit,
  onDelete,
}: {
  id: number;
  isOpen: boolean;
  onToggle: () => void;
  onEdit: () => void;
  onDelete: () => void;
}) {
  return (
    <div className="relative">
      <button
        onClick={(e) => {
          e.stopPropagation();
          onToggle();
        }}
        className="p-2 rounded-xl hover:bg-white dark:hover:bg-white/10 text-gray-500 dark:text-gray-400 transition-colors"
      >
        <Ellipsis className="h-4 w-4" />
      </button>

      {isOpen && (
        <>
          <div className="fixed inset-0 z-10" onClick={onToggle} />
          <div className="absolute right-0 top-full mt-1 z-20 min-w-[140px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl py-1.5 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <button
              data-scan="tombol edit metadata"
              onClick={(e) => {
                e.stopPropagation();
                onEdit();
              }}
              className="w-full flex items-center gap-2 px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
            >
              <Pencil className="h-3.5 w-3.5 text-amber-500" />
              Edit Data
            </button>
            <div className="h-px bg-gray-100 dark:bg-white/5 mx-2 my-1" />
            <button
              data-scan="tombol hapus metadata"
              onClick={(e) => {
                e.stopPropagation();
                onDelete();
              }}
              className="w-full flex items-center gap-2 px-4 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
            >
              <Trash2 className="h-3.5 w-3.5" />
              Hapus Data
            </button>
          </div>
        </>
      )}
    </div>
  );
}
