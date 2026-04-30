<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ConvertEnumsToJsonTest extends TestCase
{
    private string $jsonFilePath;
    private array $metas;

    protected function setUp(): void
    {
        $this->jsonFilePath = __DIR__ . '/../metas.json';
        
        if (!file_exists($this->jsonFilePath)) {
            $this->markTestSkipped('metas.json file does not exist. Run convert_enums_to_json.php first.');
        }

        $jsonContent = file_get_contents($this->jsonFilePath);
        $this->metas = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Failed to decode metas.json: ' . json_last_error_msg());
        }
    }

    public function test_json_file_exists_and_is_valid()
    {
        $this->assertFileExists($this->jsonFilePath);
        $this->assertIsArray($this->metas);
    }

    public function test_all_entries_have_required_fields()
    {
        foreach ($this->metas as $meta) {
            $this->assertArrayHasKey('id', $meta, 'Entry missing "id" field');
            $this->assertArrayHasKey('parent_id', $meta, 'Entry missing "parent_id" field');
            $this->assertArrayHasKey('name', $meta, 'Entry missing "name" field');
            $this->assertArrayHasKey('name2', $meta, 'Entry missing "name2" field');
            $this->assertIsInt($meta['id'], 'ID must be an integer');
            $this->assertIsString($meta['name'], 'Name must be a string');
            $this->assertNull($meta['name2'], 'Name2 must be null');
        }
    }

    public function test_parent_ids_are_valid()
    {
        $validIds = array_column($this->metas, 'id');
        
        foreach ($this->metas as $meta) {
            if ($meta['parent_id'] !== null) {
                $this->assertContains($meta['parent_id'], $validIds, 
                    "Parent ID {$meta['parent_id']} does not exist in the metas array");
            }
        }
    }

    public function test_all_enum_types_are_present()
    {
        $expectedTypes = ['Fungsi', 'Golongan', 'Jabatan Tugas', 'Jabatan', 'Jenis Kegiatan', 'Jenis Keluhan', 'Pangkat', 'Satuan'];
        
        $parentEntries = array_filter($this->metas, fn($meta) => $meta['parent_id'] === null);
        $actualTypes = array_column($parentEntries, 'name');
        
        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $actualTypes, 
                "Expected enum type '$expectedType' not found in metas.json");
        }
    }

    public function test_fungsi_type_has_correct_cases()
    {
        $fungsiParentId = $this->findParentIdByName('Fungsi');
        $this->assertNotNull($fungsiParentId, 'Fungsi type not found');
        
        $fungsiCases = $this->getChildrenByParentId($fungsiParentId);
        $expectedCases = ['Umum', 'Distribusi', 'Produksi', 'Sosial', 'Nerwilis', 'IPDS'];
        
        $actualCases = array_column($fungsiCases, 'name');
        $this->assertEquals($expectedCases, $actualCases, 
            'Fungsi type cases do not match expected values');
    }

    public function test_golongan_type_has_correct_cases()
    {
        $golonganParentId = $this->findParentIdByName('Golongan');
        $this->assertNotNull($golonganParentId, 'Golongan type not found');
        
        $golonganCases = $this->getChildrenByParentId($golonganParentId);
        $expectedCases = ['Ia', 'Ib', 'Ic', 'Id', 'IIa', 'IIb', 'IIc', 'IId', 'IIIa', 'IIIb', 'IIIc', 'IIId', 'IVa', 'IVb', 'IVc', 'IVd', 'IVe'];
        
        $actualCases = array_column($golonganCases, 'name');
        $this->assertEquals($expectedCases, $actualCases, 
            'Golongan type cases do not match expected values');
    }

    public function test_jabatan_tugas_type_has_correct_cases()
    {
        $jabatanTugasParentId = $this->findParentIdByName('Jabatan Tugas');
        $this->assertNotNull($jabatanTugasParentId, 'Jabatan Tugas type not found');
        
        $jabatanTugasCases = $this->getChildrenByParentId($jabatanTugasParentId);
        $expectedCases = ['PCL', 'PML', 'OPERATOR', 'SUPERVISOR'];
        
        $actualCases = array_column($jabatanTugasCases, 'name');
        $this->assertEquals($expectedCases, $actualCases, 
            'Jabatan Tugas type cases do not match expected values');
    }

    public function test_jabatan_type_has_correct_cases()
    {
        $jabatanParentId = $this->findParentIdByName('Jabatan');
        $this->assertNotNull($jabatanParentId, 'Jabatan type not found');
        
        $jabatanCases = $this->getChildrenByParentId($jabatanParentId);
        $expectedCases = [
            'Kepala', 'Statistisi Terampil', 'Statistisi Mahir', 'Statistisi Penyelia',
            'Statistisi Ahli Pertama', 'Statistisi Ahli Muda', 'Statistisi Ahli Madya',
            'Statistisi Ahli Utama', 'Pranata Komputer Mahir', 'Pranata Komputer Penyelia',
            'Pranata Komputer Ahli Muda', 'Pranata Komputer Ahli Madya', 'Pranata Komputer Ahli Utama',
            'Pranata Keuangan APBN Terampil', 'Pranata Keuangan APBN Mahir', 'Pranata Keuangan APBN Penyelia',
            'Pranata Keuangan APBN Ahli Pertama', 'Pranata Keuangan APBN Ahli Muda',
            'Pranata Keuangan APBN Ahli Madya', 'Pranata Keuangan APBN Ahli Utama', 'Pegolah Data'
        ];
        
        $actualCases = array_column($jabatanCases, 'name');
        $this->assertEquals($expectedCases, $actualCases, 
            'Jabatan type cases do not match expected values');
    }

    public function test_jenis_kegiatan_type_has_correct_cases()
    {
        $jenisKegiatanParentId = $this->findParentIdByName('Jenis Kegiatan');
        $this->assertNotNull($jenisKegiatanParentId, 'Jenis Kegiatan type not found');
        
        $jenisKegiatanCases = $this->getChildrenByParentId($jenisKegiatanParentId);
        $expectedCases = ['PERSIAPAN', 'PENGUMPULAN DATA', 'PENGOLAHAN', 'DISEMINASI', 'PENGAWASAN/SUPERVISI'];
        
        $actualCases = array_column($jenisKegiatanCases, 'name');
        $this->assertEquals($expectedCases, $actualCases, 
            'Jenis Kegiatan type cases do not match expected values');
    }

    public function test_jenis_keluhan_type_has_correct_cases()
    {
        $jenisKeluhanParentId = $this->findParentIdByName('Jenis Keluhan');
        $this->assertNotNull($jenisKeluhanParentId, 'Jenis Keluhan type not found');
        
        $jenisKeluhanCases = $this->getChildrenByParentId($jenisKeluhanParentId);
        $expectedCases = ['Sistem', 'Software', 'Printer', 'Hardware PC/Laptop', 'Jaringan', 'Akun BPS', 'Lainnya'];
        
        $actualCases = array_column($jenisKeluhanCases, 'name');
        $this->assertEquals($expectedCases, $actualCases, 
            'Jenis Keluhan type cases do not match expected values');
    }

    public function test_pangkat_type_has_correct_cases()
    {
        $pangkatParentId = $this->findParentIdByName('Pangkat');
        $this->assertNotNull($pangkatParentId, 'Pangkat type not found');
        
        $pangkatCases = $this->getChildrenByParentId($pangkatParentId);
        $expectedCases = [
            'Juru Muda', 'Juru Muda Tk. I', 'Juru', 'Juru Tk. I',
            'Pengatur Muda', 'Pengatur Muda Tk. I', 'Pengatur', 'Pengatur Tk. I',
            'Penata Muda', 'Penata Muda Tk. I', 'Penata', 'Penata Tk. I',
            'Pembina', 'Pembina Tk. I', 'Pembina Utama Muda', 'Pembina Utama Madya', 'Pembina Utama'
        ];
        
        $actualCases = array_column($pangkatCases, 'name');
        $this->assertEquals($expectedCases, $actualCases, 
            'Pangkat type cases do not match expected values');
    }

    public function test_satuan_type_has_correct_cases()
    {
        $satuanParentId = $this->findParentIdByName('Satuan');
        $this->assertNotNull($satuanParentId, 'Satuan type not found');
        
        $satuanCases = $this->getChildrenByParentId($satuanParentId);
        $expectedCases = [
            'O-K', 'Lembar', 'O-H', 'Paket', 'Dok', 'DOK', 'Set', 'O-J', 'O-P', 'OJP',
            'Resp', 'Kunj', 'SLS', 'Desa', 'BS', 'Ruta', 'RUTA', 'O-B', 'BUAH', 'Segmen',
            'Sgmen', 'Tahun', 'Bulan', 'U/THN', 'THN', 'M2/TH', 'O-TH', 'EA/PS', 'EA/PR', 'EA'
        ];
        
        $actualCases = array_column($satuanCases, 'name');
        $this->assertEquals($expectedCases, $actualCases, 
            'Satuan type cases do not match expected values');
    }

    public function test_total_entry_count()
    {
        // 8 parent entries (enum types) + 107 child entries (enum cases) = 115 total
        // Each entry has 4 fields: id, parent_id, name, name2
        $this->assertCount(115, $this->metas, 'Total entry count does not match expected value');
    }

    public function test_no_duplicate_ids()
    {
        $ids = array_column($this->metas, 'id');
        $uniqueIds = array_unique($ids);
        
        $this->assertEquals(count($ids), count($uniqueIds), 
            'Duplicate IDs found in metas.json');
    }

    public function test_ids_are_sequential()
    {
        $ids = array_column($this->metas, 'id');
        sort($ids);
        
        for ($i = 0; $i < count($ids); $i++) {
            $this->assertEquals($i + 1, $ids[$i], 
                "IDs are not sequential. Expected " . ($i + 1) . " at position $i, got {$ids[$i]}");
        }
    }

    /**
     * Helper method to find parent ID by name
     */
    private function findParentIdByName(string $name): ?int
    {
        foreach ($this->metas as $meta) {
            if ($meta['parent_id'] === null && $meta['name'] === $name) {
                return $meta['id'];
            }
        }
        return null;
    }

    /**
     * Helper method to get all children by parent ID
     */
    private function getChildrenByParentId(int $parentId): array
    {
        return array_values(array_filter($this->metas, fn($meta) => $meta['parent_id'] === $parentId));
    }
}
