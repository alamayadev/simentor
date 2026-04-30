import { BankDataPage } from '../components/BankDataPage';

export function BankDataRaw() {
  return (
    <BankDataPage
      dataType="RAW DATA"
      title="Bank Data Raw"
      description="Kelola data mentah (raw data) untuk pengolahan dan analisis."
      addLabel="Tambah Data"
      queryKey="ipds-raw-datas"
    />
  );
}