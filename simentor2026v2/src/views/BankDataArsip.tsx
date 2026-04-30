import { BankDataPage } from '../components/BankDataPage';

export function BankDataArsip() {
  return (
    <BankDataPage
      dataType="ARSIP INTERNAL"
      title="Arsip Data"
      description="Dokumen publikasi, laporan, dan tabel statistik yang diarsip."
      addLabel="Arsip Baru"
      queryKey="ipds-arsip-datas"
    />
  );
}
