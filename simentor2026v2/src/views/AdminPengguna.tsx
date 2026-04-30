import React, { useMemo, useState, useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  Plus,
  Search,
  User,
  Shield,
  Key,
  Mail,
  Pencil,
  Trash2,
  Ellipsis,
  X,
  Loader2,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useQueryClient } from '@tanstack/react-query';
import { useApiMutation, useApiQuery } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { useAuth } from '../hooks/useAuth';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { adminPermissionsService, adminUsersService } from '../lib/api-services';
import type {
  Permission as ApiPermission,
  Role as ApiRole,
  User as ApiUser,
} from '../types/api';

type UserTab = 'organik' | 'mitra';

type Pengguna = {
  id: number;
  nama: string;
  email: string;
  peran: string;
  status: string;
  lastLogin: string;
  izin: string[];
  source: UserTab;
  roles: string[];
  permissions: string[];
};

type UserFormValues = {
  name: string;
  email: string;
  password: string;
  roles: string[];
  permissions: string[];
};

type NoticeState = {
  tone: 'success' | 'error';
  message: string;
} | null;

const emptyForm: UserFormValues = {
  name: '',
  email: '',
  password: '',
  roles: [],
  permissions: [],
};

const peranStyles: Record<string, string> = {
  'super-admin': 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400',
  kepala: 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400',
  katim: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400',
  admin: 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-500/10 dark:text-orange-400',
  mitra: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
  staf: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
};

const tabMeta: Record<UserTab, { label: string; description: string }> = {
  organik: {
    label: 'Organik',
    description: 'Akun pegawai internal dan staf kantor.',
  },
  mitra: {
    label: 'Mitra',
    description: 'Akun mitra lapangan dan pengguna eksternal.',
  },
};

const columnHelper = createColumnHelper<Pengguna>();

const columns = [
  columnHelper.display({
    id: 'select',
    header: () => {
      return null;
    },
    cell: (info) => {
      const row = info.row.original;
      const meta = info.table.options.meta as {
        selectedIds: number[];
        toggleSelected: (id: number) => void;
      };
      return (
        <input
          type="checkbox"
          checked={meta.selectedIds.includes(row.id)}
          onChange={() => meta.toggleSelected(row.id)}
          className="size-4 rounded border-gray-300 accent-amber-500"
          aria-label={`Pilih ${row.nama}`}
        />
      );
    },
  }),
  columnHelper.accessor('nama', {
    header: 'Pengguna',
    cell: (info) => (
      <div className="flex items-center gap-3">
        <div className="h-9 w-9 rounded-full bg-gradient-to-br from-blue-200 to-indigo-300 dark:from-blue-500/30 dark:to-indigo-500/30 flex items-center justify-center">
          <User className="h-4 w-4 text-blue-700 dark:text-blue-300" />
        </div>
        <div className="flex flex-col gap-0.5">
          <span className="font-bold text-gray-900 dark:text-white text-[13px] leading-tight">{info.getValue()}</span>
          <span className="text-[11px] text-gray-500">{info.row.original.email}</span>
        </div>
      </div>
    ),
  }),
  columnHelper.accessor('peran', {
    header: 'Peran',
    cell: (info) => {
      const peran = info.getValue();
      const style = peranStyles[peran] || 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400';
      return (
        <span className={`px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider ${style}`}>
          {peran}
        </span>
      );
    },
  }),
  columnHelper.accessor('status', {
    header: 'Status',
    cell: (info) => (
      <span className="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400">
        {info.getValue()}
      </span>
    ),
  }),
  columnHelper.accessor('izin', {
    header: 'Izin',
    cell: (info) => (
      <div className="flex flex-wrap gap-1 max-w-[220px]">
        {info.getValue().slice(0, 2).map((permission) => (
          <span key={permission} className="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/5 text-[10px] font-mono text-gray-600 dark:text-gray-400">
            {permission}
          </span>
        ))}
        {info.getValue().length > 2 && (
          <span className="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/5 text-[10px] text-gray-400">
            +{info.getValue().length - 2}
          </span>
        )}
      </div>
    ),
  }),
  columnHelper.accessor('lastLogin', {
    header: 'Login Terakhir',
    cell: (info) => <span className="text-[12px] text-gray-500 dark:text-gray-400">{info.getValue()}</span>,
  }),
  columnHelper.display({
    id: 'actions',
    header: () => <div className="text-right">Aksi</div>,
    cell: (info) => {
      const row = info.row.original;
      const meta = info.table.options.meta as {
        openMenuId: number | null;
        setOpenMenuId: (id: number | null) => void;
        canManagePermissions: boolean;
        onEdit: (user: Pengguna) => void;
        onManagePermissions: (user: Pengguna) => void;
        onDelete: (user: Pengguna) => void;
      };
      return (
        <div className="text-right">
          <ActionMenu
            row={row}
            isOpen={meta.openMenuId === row.id}
            canManagePermissions={meta.canManagePermissions}
            onToggle={() => meta.setOpenMenuId(meta.openMenuId === row.id ? null : row.id)}
            onEdit={() => meta.onEdit(row)}
            onManagePermissions={() => meta.onManagePermissions(row)}
            onDelete={() => meta.onDelete(row)}
          />
        </div>
      );
    },
  }),
];

function ActionMenu({
  row,
  isOpen,
  canManagePermissions,
  onToggle,
  onEdit,
  onManagePermissions,
  onDelete,
}: {
  row: Pengguna;
  isOpen: boolean;
  canManagePermissions: boolean;
  onToggle: () => void;
  onEdit: () => void;
  onManagePermissions: () => void;
  onDelete: () => void;
}) {
  return (
    <div className="relative flex justify-end">
      <button
        data-scan="menu aksi pengguna"
        onClick={(event) => {
          event.stopPropagation();
          onToggle();
        }}
        className="inline-flex items-center justify-center rounded-none text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 outline-none"
        type="button"
        aria-label={`Menu aksi ${row.nama}`}
      >
        <Ellipsis className="h-4 w-4" />
      </button>

      {isOpen && (
        <div className="absolute right-0 top-full mt-1 z-50 min-w-[160px] bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/10 rounded-xl shadow-xl py-1 transform origin-top-right transition-all overflow-hidden scale-in-95">
          <button
            data-scan="tombol edit pengguna"
            onClick={(event) => {
              event.stopPropagation();
              onEdit();
            }}
            className="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
          >
            <Pencil className="h-3.5 w-3.5 text-amber-500" />
            Edit Pengguna
          </button>
          {canManagePermissions && (
            <button
              data-scan="tombol kelola izin"
              onClick={(event) => {
                event.stopPropagation();
                onManagePermissions();
              }}
              className="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
            >
              <Shield className="h-3.5 w-3.5 text-emerald-500" />
              Kelola Izin Akses
            </button>
          )}
          <div className="h-px bg-gray-100 dark:bg-white/5 mx-2" />
          <button
            data-scan="tombol hapus pengguna"
            onClick={(event) => {
              event.stopPropagation();
              onDelete();
            }}
            className="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
          >
            <Trash2 className="h-3.5 w-3.5" />
            Hapus Pengguna
          </button>
        </div>
      )}
    </div>
  );
}

function PermissionsModal({
  open,
  userName,
  permissionOptions,
  selectedPermissions,
  isLoading,
  onClose,
  onTogglePermission,
  onSubmit,
}: {
  open: boolean;
  userName: string;
  permissionOptions: string[];
  selectedPermissions: string[];
  isLoading: boolean;
  onClose: () => void;
  onTogglePermission: (name: string) => void;
  onSubmit: () => void;
}) {
  if (!open) return null;

  return (
    <div data-scan="modal izin akses" className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div 
        className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-2xl mx-4 max-h-[85vh] flex flex-col"
      >
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">Ubah Izin Akses</h2>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Perbarui izin akses untuk {userName}.
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
            type="button"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto px-6 py-5">
          <div className="max-h-[420px] overflow-y-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 p-3 space-y-2">
            {permissionOptions.length === 0 ? (
              <p className="text-sm text-gray-500 dark:text-gray-400">Daftar izin belum tersedia.</p>
            ) : (
              permissionOptions.map((permission) => (
                <label key={permission} className="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-white dark:hover:bg-white/5 transition-colors">
                  <input
                    type="checkbox"
                    checked={selectedPermissions.includes(permission)}
                    onChange={() => onTogglePermission(permission)}
                    className="size-4 rounded border-gray-300 accent-amber-500"
                  />
                  <span className="text-sm font-medium text-gray-700 dark:text-gray-200">{permission}</span>
                </label>
              ))
            )}
          </div>
        </div>

        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
          <button
            type="button"
            onClick={onClose}
            disabled={isLoading}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm disabled:opacity-50"
          >
            Batal
          </button>
          <button
            type="button"
            onClick={onSubmit}
            disabled={isLoading}
            className="flex items-center gap-1.5 px-5 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all disabled:opacity-50"
          >
            {isLoading && <Loader2 className="h-3.5 w-3.5 animate-spin" />}
            Simpan Izin
          </button>
        </div>
      </div>
    </div>
  );
}

function BulkRolesModal({
  open,
  selectedCount,
  roleOptions,
  selectedRoles,
  isLoading,
  onClose,
  onToggleRole,
  onSubmit,
}: {
  open: boolean;
  selectedCount: number;
  roleOptions: string[];
  selectedRoles: string[];
  isLoading: boolean;
  onClose: () => void;
  onToggleRole: (name: string) => void;
  onSubmit: () => void;
}) {
  if (!open) return null;

  return (
    <div data-scan="modal bulk roles" className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-xl mx-4 max-h-[80vh] flex flex-col">
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">Bulk Update Roles</h2>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Terapkan peran ke {selectedCount} pengguna yang dipilih.
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
            type="button"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto px-6 py-5">
          <div className="max-h-[360px] overflow-y-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 p-3 space-y-2">
            {roleOptions.length === 0 ? (
              <p className="text-sm text-gray-500 dark:text-gray-400">Daftar peran belum tersedia.</p>
            ) : (
              roleOptions.map((role) => (
                <label key={role} className="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-white dark:hover:bg-white/5 transition-colors">
                  <input
                    type="checkbox"
                    checked={selectedRoles.includes(role)}
                    onChange={() => onToggleRole(role)}
                    className="size-4 rounded border-gray-300 accent-amber-500"
                  />
                  <span className="text-sm font-medium text-gray-700 dark:text-gray-200">{role}</span>
                </label>
              ))
            )}
          </div>
        </div>

        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
          <button
            type="button"
            onClick={onClose}
            disabled={isLoading}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm disabled:opacity-50"
          >
            Batal
          </button>
          <button
            type="button"
            onClick={onSubmit}
            disabled={isLoading}
            className="flex items-center gap-1.5 px-5 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all disabled:opacity-50"
          >
            {isLoading && <Loader2 className="h-3.5 w-3.5 animate-spin" />}
            Terapkan Roles
          </button>
        </div>
      </div>
    </div>
  );
}

function UserFormModal({
  open,
  mode,
  values,
  roleOptions,
  permissionOptions,
  isSuperAdmin,
  isLoading,
  onClose,
  onChange,
  onToggleRole,
  onTogglePermission,
  onSubmit,
}: {
  open: boolean;
  mode: 'create' | 'edit';
  values: UserFormValues;
  roleOptions: string[];
  permissionOptions: string[];
  isSuperAdmin: boolean;
  isLoading: boolean;
  onClose: () => void;
  onChange: (field: keyof UserFormValues, value: string) => void;
  onToggleRole: (name: string) => void;
  onTogglePermission: (name: string) => void;
  onSubmit: () => void;
}) {
  if (!open) return null;

  return (
    <div data-scan="modal form pengguna" className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div 
        className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-3xl mx-4 max-h-[88vh] flex flex-col"
      >
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">
              {mode === 'create' ? 'Tambah Pengguna' : 'Edit Pengguna'}
            </h2>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Kelola profil akun, peran, dan izin akses pengguna.
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
            type="button"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto px-6 py-5 space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Nama Lengkap
              </label>
              <input
                type="text"
                value={values.name}
                onChange={(event) => onChange('name', event.target.value)}
                placeholder="Nama pengguna..."
                className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
              />
            </div>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Email
              </label>
              <input
                type="email"
                value={values.email}
                onChange={(event) => onChange('email', event.target.value)}
                placeholder="nama@email.com"
                className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
              />
            </div>
          </div>

          <div>
            <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
              {mode === 'create' ? 'Password' : 'Password Baru'}
            </label>
            <input
              type="password"
              value={values.password}
              onChange={(event) => onChange('password', event.target.value)}
              placeholder={mode === 'create' ? 'Masukkan password...' : 'Kosongkan jika tidak diubah'}
              className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
            />
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div className="space-y-3">
              <div>
                <h3 className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                  Peran
                </h3>
                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  Pilih satu atau lebih peran untuk akun ini.
                </p>
              </div>
              <div className="max-h-56 overflow-y-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 p-3 space-y-2">
                {roleOptions.length === 0 ? (
                  <p className="text-sm text-gray-500 dark:text-gray-400">Daftar peran belum tersedia.</p>
                ) : (
                  roleOptions.map((role) => (
                    <label key={role} className="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-white dark:hover:bg-white/5 transition-colors">
                      <input
                        type="checkbox"
                        checked={values.roles.includes(role)}
                        onChange={() => onToggleRole(role)}
                        className="size-4 rounded border-gray-300 accent-amber-500"
                      />
                      <span className="text-sm font-medium text-gray-700 dark:text-gray-200">{role}</span>
                    </label>
                  ))
                )}
              </div>
            </div>

            <div className="space-y-3">
              <div>
                <h3 className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                  Izin Akses
                </h3>
                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  {isSuperAdmin ? 'Pilih izin granular jika akun membutuhkan akses khusus.' : 'Hanya super-admin yang dapat mengubah izin granular.'}
                </p>
              </div>
              <div className="max-h-56 overflow-y-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 p-3 space-y-2">
                {permissionOptions.length === 0 ? (
                  <p className="text-sm text-gray-500 dark:text-gray-400">Daftar izin belum tersedia.</p>
                ) : (
                  permissionOptions.map((permission) => (
                    <label key={permission} className={`flex items-center gap-3 rounded-xl px-3 py-2 transition-colors ${isSuperAdmin ? 'hover:bg-white dark:hover:bg-white/5' : 'opacity-60'}`}>
                      <input
                        type="checkbox"
                        checked={values.permissions.includes(permission)}
                        onChange={() => onTogglePermission(permission)}
                        className="size-4 rounded border-gray-300 accent-amber-500"
                        disabled={!isSuperAdmin}
                      />
                      <span className="text-sm font-medium text-gray-700 dark:text-gray-200">{permission}</span>
                    </label>
                  ))
                )}
              </div>
            </div>
          </div>
        </div>

        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
          <button
            type="button"
            onClick={onClose}
            disabled={isLoading}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm disabled:opacity-50"
          >
            Batal
          </button>
          <button
            type="button"
            onClick={onSubmit}
            disabled={isLoading}
            className="flex items-center gap-1.5 px-5 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all disabled:opacity-50"
          >
            {isLoading && <Loader2 className="h-3.5 w-3.5 animate-spin" />}
            {mode === 'create' ? 'Simpan' : 'Perbarui'}
          </button>
        </div>
      </div>
    </div>
  );
}

export function AdminPengguna() {
  const queryClient = useQueryClient();
  const { user } = useAuth();
  const [search, setSearch] = useState('');
  const [activeTab, setActiveTab] = useState<UserTab>('organik');
  const [openMenuId, setOpenMenuId] = useState<number | null>(null);
  const [showFormModal, setShowFormModal] = useState(false);
  const [showBulkRolesModal, setShowBulkRolesModal] = useState(false);
  const [editingUser, setEditingUser] = useState<Pengguna | null>(null);
  const [permissionsUser, setPermissionsUser] = useState<Pengguna | null>(null);
  const [selectedPermissions, setSelectedPermissions] = useState<string[]>([]);
  const [selectedIds, setSelectedIds] = useState<number[]>([]);
  const [selectedBulkRoles, setSelectedBulkRoles] = useState<string[]>([]);
  const [deleteTarget, setDeleteTarget] = useState<Pengguna | null>(null);
  const [notice, setNotice] = useState<NoticeState>(null);
  const [formValues, setFormValues] = useState<UserFormValues>(emptyForm);
  const debouncedSearch = useDebounce(search, 300);
  const pagination = usePagination(10);

  const isSuperAdmin = useMemo(() => {
    const roles = user?.roles ?? [];
    return roles.some((role) => (typeof role === 'string' ? role === 'super-admin' : role.name === 'super-admin'));
  }, [user]);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['admin-users', debouncedSearch, activeTab, pagination.params],
    () => adminUsersService.list({
      ...pagination.params,
      type: activeTab,
      'filter[name]': debouncedSearch || undefined
    }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  useEffect(() => {
    pagination.reset();
  }, [debouncedSearch, activeTab]);

  const { data: permissionsResponse } = useApiQuery(
    ['admin-permissions'],
    () => adminPermissionsService.list({ per_page: 200 }),
  );

  const createMutation = useApiMutation(
    (payload: { name: string; email: string; password: string; roles?: string[]; permissions?: string[] }) =>
      adminUsersService.create(payload),
    {
      invalidateKeys: [['admin-users']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Pengguna berhasil ditambahkan.' });
        setShowFormModal(false);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menambahkan pengguna.' });
      },
    },
  );

  const updateMutation = useApiMutation(
    ({ id, data }: { id: number; data: { name?: string; email?: string; password?: string; roles?: string[]; permissions?: string[] } }) =>
      adminUsersService.update(id, data),
    {
      invalidateKeys: [['admin-users']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Pengguna berhasil diperbarui.' });
        setShowFormModal(false);
        setEditingUser(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal memperbarui pengguna.' });
      },
    },
  );

  const deleteMutation = useApiMutation(
    (id: number) => adminUsersService.delete(id),
    {
      invalidateKeys: [['admin-users']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Pengguna berhasil dihapus.' });
        setDeleteTarget(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menghapus pengguna.' });
      },
    },
  );

  const permissionsMutation = useApiMutation(
    ({ id, permissions }: { id: number; permissions: string[] }) =>
      adminUsersService.updatePermissions(id, { permissions }),
    {
      invalidateKeys: [['admin-users']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Izin akses pengguna berhasil diperbarui.' });
        setPermissionsUser(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal memperbarui izin akses pengguna.' });
      },
    },
  );

  const bulkRolesMutation = useApiMutation(
    (payload: { user_ids: number[]; roles: string[] }) => adminUsersService.bulkUpdateRoles(payload),
    {
      invalidateKeys: [['admin-users']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Peran pengguna berhasil diperbarui.' });
        setShowBulkRolesModal(false);
        setSelectedIds([]);
        setSelectedBulkRoles([]);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal memperbarui peran pengguna.' });
      },
    },
  );

  const roleOptions = useMemo(
    () => (response?.data?.roles || []).map((role: ApiRole) => role.name).filter(Boolean),
    [response],
  );

  const permissionOptions = useMemo(() => {
    const permissions = permissionsResponse?.data?.data || [];
    return permissions.map((permission: ApiPermission) => permission.name).filter(Boolean);
  }, [permissionsResponse]);

  const penggunaByTab = useMemo<Record<UserTab, Pengguna[]>>(() => {
    const organik = response?.data?.organik_data || [];
    const mitra = response?.data?.mitra_data || [];

    const mapUsers = (users: ApiUser[], source: UserTab) =>
      users.map((u) => ({
        id: u.id,
        nama: u.name,
        email: u.email,
        peran: u.roles?.[0]?.name || (source === 'mitra' ? 'mitra' : 'staf'),
        status: 'aktif',
        lastLogin: u.updated_at
          ? new Date(u.updated_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' })
          : '-',
        izin: u.permissions?.map((permission) => permission.name) || [],
        source,
        roles: u.roles?.map((role) => role.name) || [],
        permissions: u.permissions?.map((permission) => permission.name) || [],
      }));

    return {
      organik: mapUsers(organik, 'organik'),
      mitra: mapUsers(mitra, 'mitra'),
    };
  }, [response]);

  const currentData = penggunaByTab[activeTab];
  const allData = [...penggunaByTab.organik, ...penggunaByTab.mitra];
  const isAllCurrentSelected = currentData.length > 0 && currentData.every((item) => selectedIds.includes(item.id));

  const stats = useMemo(
    () => ({
      total: allData.length,
      aktif: allData.filter((item) => item.status === 'aktif').length,
      admin: allData.filter((item) => item.peran === 'super-admin' || item.peran === 'admin').length,
      tabCount: currentData.length,
    }),
    [allData, currentData.length],
  );

  const table = useReactTable({
    data: currentData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    meta: {
      openMenuId,
      setOpenMenuId,
      selectedIds,
      toggleSelected: (id: number) => {
        setSelectedIds((prev) => (prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]));
      },
      canManagePermissions: isSuperAdmin,
      onEdit: (selectedUser: Pengguna) => {
        setEditingUser(selectedUser);
        setFormValues({
          name: selectedUser.nama,
          email: selectedUser.email,
          password: '',
          roles: selectedUser.roles,
          permissions: selectedUser.permissions,
        });
        setNotice(null);
        setShowFormModal(true);
        setOpenMenuId(null);
      },
      onManagePermissions: (selectedUser: Pengguna) => {
        setPermissionsUser(selectedUser);
        setSelectedPermissions(selectedUser.permissions);
        setNotice(null);
        setOpenMenuId(null);
      },
      onDelete: (selectedUser: Pengguna) => {
        setDeleteTarget(selectedUser);
        setNotice(null);
        setOpenMenuId(null);
      },
    },
  });

  const closeFormModal = () => {
    setShowFormModal(false);
    setEditingUser(null);
    setFormValues(emptyForm);
  };

  const openCreateModal = () => {
    setEditingUser(null);
    setFormValues(emptyForm);
    setNotice(null);
    setShowFormModal(true);
  };

  const handleFormChange = (field: keyof UserFormValues, value: string) => {
    setFormValues((prev) => ({ ...prev, [field]: value }));
  };

  const toggleRole = (roleName: string) => {
    setFormValues((prev) => ({
      ...prev,
      roles: prev.roles.includes(roleName)
        ? prev.roles.filter((item) => item !== roleName)
        : [...prev.roles, roleName],
    }));
  };

  const togglePermission = (permissionName: string) => {
    if (!isSuperAdmin) return;
    setFormValues((prev) => ({
      ...prev,
      permissions: prev.permissions.includes(permissionName)
        ? prev.permissions.filter((item) => item !== permissionName)
        : [...prev.permissions, permissionName],
    }));
  };

  const togglePermissionsSelection = (permissionName: string) => {
    setSelectedPermissions((prev) =>
      prev.includes(permissionName)
        ? prev.filter((item) => item !== permissionName)
        : [...prev, permissionName],
    );
  };

  const toggleBulkRole = (roleName: string) => {
    setSelectedBulkRoles((prev) =>
      prev.includes(roleName)
        ? prev.filter((item) => item !== roleName)
        : [...prev, roleName],
    );
  };

  const handleFormSubmit = async () => {
    const payload = {
      name: formValues.name.trim(),
      email: formValues.email.trim(),
      password: formValues.password.trim(),
      roles: formValues.roles,
      permissions: isSuperAdmin ? formValues.permissions : [],
    };

    if (!payload.name) {
      setNotice({ tone: 'error', message: 'Nama pengguna wajib diisi.' });
      return;
    }

    if (!payload.email) {
      setNotice({ tone: 'error', message: 'Email pengguna wajib diisi.' });
      return;
    }

    if (!editingUser && !payload.password) {
      setNotice({ tone: 'error', message: 'Password wajib diisi untuk pengguna baru.' });
      return;
    }

    setNotice(null);

    if (editingUser) {
      const updatePayload: {
        name?: string;
        email?: string;
        password?: string;
        roles?: string[];
        permissions?: string[];
      } = {
        name: payload.name,
        email: payload.email,
        roles: payload.roles,
        permissions: isSuperAdmin ? payload.permissions : undefined,
      };

      if (payload.password) {
        updatePayload.password = payload.password;
      }

      await updateMutation.mutateAsync({ id: editingUser.id, data: updatePayload });
      return;
    }

    await createMutation.mutateAsync(payload);
  };

  const handlePermissionsSubmit = async () => {
    if (!permissionsUser) return;
    setNotice(null);
    await permissionsMutation.mutateAsync({
      id: permissionsUser.id,
      permissions: selectedPermissions,
    });
  };

  const handleBulkRolesSubmit = async () => {
    if (selectedIds.length === 0) {
      setNotice({ tone: 'error', message: 'Pilih minimal satu pengguna.' });
      return;
    }

    if (selectedBulkRoles.length === 0) {
      setNotice({ tone: 'error', message: 'Pilih minimal satu peran.' });
      return;
    }

    setNotice(null);
    await bulkRolesMutation.mutateAsync({
      user_ids: selectedIds,
      roles: selectedBulkRoles,
    });
  };

  React.useEffect(() => {
    setSelectedIds([]);
  }, [activeTab, debouncedSearch]);

  const handleRefresh = async () => {
    setNotice(null);
    await queryClient.invalidateQueries({ queryKey: ['admin-users'] });
    await refetch();
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Manajemen Pengguna"
        description="Kelola akun pengguna, peran, dan izin akses sistem."
        actions={
          <button
            data-scan="tombol tambah pengguna"
            onClick={openCreateModal}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Tambah Pengguna
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

      <div 
        data-scan="ringkasan statistik"
        className="grid grid-cols-2 lg:grid-cols-4 gap-4"
      >
        {[
          { label: 'Total Pengguna', value: stats.total, color: 'text-orange-600 dark:text-orange-400', icon: <User className="h-5 w-5" /> },
          { label: 'Aktif', value: stats.aktif, color: 'text-emerald-600 dark:text-emerald-400', icon: <Shield className="h-5 w-5" /> },
          { label: 'Admin', value: stats.admin, color: 'text-red-600 dark:text-red-400', icon: <Key className="h-5 w-5" /> },
          { label: tabMeta[activeTab].label, value: stats.tabCount, color: 'text-blue-600 dark:text-blue-400', icon: <Mail className="h-5 w-5" /> },
        ].map((item, index) => (
          <motion.div
            data-scan="kartu statistik"
            key={item.label}
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.08 }}
            className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-5 space-y-2"
          >
            <div className="flex items-center justify-between">
              <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{item.label}</p>
              <span className="opacity-30">{item.icon}</span>
            </div>
            <p className={`text-2xl font-black ${item.color}`}>{isLoading ? '...' : item.value}</p>
          </motion.div>
        ))}
      </div>

      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-6 space-y-5 shadow-sm">
        <div 
          data-scan="tab kategori pengguna"
          className="flex flex-wrap gap-2"
        >
          {(['organik', 'mitra'] as const).map((tab) => (
            <button
              key={tab}
              type="button"
              onClick={() => {
                setActiveTab(tab);
                setOpenMenuId(null);
              }}
              className={`px-4 py-2 rounded-full text-[11px] font-bold tracking-[0.15em] uppercase transition-all ${
                activeTab === tab
                  ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm'
                  : 'bg-white/70 dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-white/15'
              }`}
            >
              {tabMeta[tab].label}
            </button>
          ))}
        </div>

        <div 
          data-scan="pencarian dan aksi"
          className="flex flex-wrap items-end gap-3"
        >
          <div className="flex w-full max-w-sm flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Pencarian</label>
            <div className="relative group">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-orange-500 transition-colors" />
              <input
                data-scan="input pencarian"
                type="text"
                value={search}
                onChange={(event) => setSearch(event.target.value)}
                placeholder={`Cari pengguna ${tabMeta[activeTab].label.toLowerCase()}...`}
                className="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 pl-10 pr-4 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
              />
            </div>
          </div>

          <button
            data-scan="tombol reset"
            onClick={() => {
              setSearch('');
              setNotice(null);
            }}
            className="h-10 px-5 rounded-full bg-white/70 dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-[11px] font-bold shadow-sm hover:bg-white transition-all"
          >
            Reset
          </button>

          <button
            data-scan="tombol muat ulang"
            onClick={() => void handleRefresh()}
            className="h-10 px-5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-bold shadow-sm transition-all"
          >
            Muat Ulang
          </button>
        </div>

        <div className="rounded-[18px] border border-gray-100/80 dark:border-white/5 bg-white/70 dark:bg-white/[0.03] px-4 py-3">
          <p className="text-[11px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
            Tab Aktif
          </p>
          <p className="mt-1 text-sm text-gray-700 dark:text-gray-200">{tabMeta[activeTab].description}</p>
        </div>
      </div>

      <div 
        data-scan="tabel pengguna"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data pengguna..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Pengguna" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    {table.getHeaderGroups().map((headerGroup) => (
                      <React.Fragment key={headerGroup.id}>
                        {headerGroup.headers.map((header) => (
                          <th key={header.id} className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
                            {header.column.id === 'select' ? (
                              <input
                                type="checkbox"
                                checked={isAllCurrentSelected}
                                onChange={() => {
                                  setSelectedIds((prev) =>
                                    isAllCurrentSelected
                                      ? prev.filter((id) => !currentData.some((item) => item.id === id))
                                      : Array.from(new Set([...prev, ...currentData.map((item) => item.id)])),
                                  );
                                }}
                                className="size-4 rounded border-gray-300 accent-amber-500"
                                aria-label="Pilih semua pengguna"
                              />
                            ) : (
                              flexRender(header.column.columnDef.header, header.getContext())
                            )}
                          </th>
                        ))}
                      </React.Fragment>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                  {table.getRowModel().rows.map((row) => (
                    <tr key={row.id} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                      {row.getVisibleCells().map((cell) => (
                        <td key={cell.id} className="px-6 py-4">
                          {flexRender(cell.column.columnDef.cell, cell.getContext())}
                        </td>
                      ))}
                    </tr>
                  ))}
                  {table.getRowModel().rows.length === 0 && (
                    <tr>
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data pengguna pada tab {tabMeta[activeTab].label.toLowerCase()}.
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
              <div className="flex items-center gap-4">
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari{' '}
                  <span className="text-gray-900 dark:text-white font-bold">{pagination.totalPages}</span>
                </p>
                <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Menampilkan <span className="font-bold text-gray-900 dark:text-white">{currentData.length}</span> dari{' '}
                  <span className="font-bold text-gray-900 dark:text-white">{pagination.totalRecords}</span> data
                </p>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => pagination.handlePrev()}
                  disabled={!pagination.canPrev || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Sebelumnya
                </button>
                <button
                  onClick={() => pagination.handleNext()}
                  disabled={!pagination.canNext || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Berikutnya
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      {selectedIds.length > 0 && (
        <div 
          data-scan="bar aksi massal"
          className="sticky bottom-0 z-10 rounded-[20px] border border-white/50 dark:border-white/10 bg-white/90 dark:bg-gray-950/90 backdrop-blur px-5 py-4 shadow-lg"
        >
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div className="text-[11px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
              {selectedIds.length} pengguna dipilih
            </div>
            <button
              type="button"
              data-scan="tombol bulk update roles"
              onClick={() => {
                setSelectedBulkRoles([]);
                setShowBulkRolesModal(true);
                setNotice(null);
              }}
              className="px-4 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all"
            >
              Bulk Update Roles
            </button>
          </div>
        </div>
      )}

      <UserFormModal
        open={showFormModal}
        mode={editingUser ? 'edit' : 'create'}
        values={formValues}
        roleOptions={roleOptions}
        permissionOptions={permissionOptions}
        isSuperAdmin={isSuperAdmin}
        isLoading={createMutation.isPending || updateMutation.isPending}
        onClose={closeFormModal}
        onChange={handleFormChange}
        onToggleRole={toggleRole}
        onTogglePermission={togglePermission}
        onSubmit={() => void handleFormSubmit()}
      />

      <PermissionsModal
        open={!!permissionsUser}
        userName={permissionsUser?.nama || 'pengguna'}
        permissionOptions={permissionOptions}
        selectedPermissions={selectedPermissions}
        isLoading={permissionsMutation.isPending}
        onClose={() => setPermissionsUser(null)}
        onTogglePermission={togglePermissionsSelection}
        onSubmit={() => void handlePermissionsSubmit()}
      />

      <BulkRolesModal
        open={showBulkRolesModal}
        selectedCount={selectedIds.length}
        roleOptions={roleOptions}
        selectedRoles={selectedBulkRoles}
        isLoading={bulkRolesMutation.isPending}
        onClose={() => setShowBulkRolesModal(false)}
        onToggleRole={toggleBulkRole}
        onSubmit={() => void handleBulkRolesSubmit()}
      />

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus Pengguna"
        message={`Yakin ingin menghapus akun "${deleteTarget?.nama}"? Tindakan ini tidak dapat dibatalkan.`}
        confirmLabel="Hapus"
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
