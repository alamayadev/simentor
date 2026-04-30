# GET `/miniapp/survey`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/miniapp/survey` |
| **Description** | Survey list |
| **HTTP Status** | `200` |
| **Response Time** | 0.03s |
| **Generated** | 2026-04-18 20:50:55 |

## Response

```json
{
    "success": true,
    "message": "Surveys retrieved successfully",
    "data": [
        {
            "id": 1,
            "survey_name": "Contoh Validasi",
            "survey_description": "Contoh validasi antar rincian",
            "json_file": {
                "title": "Validasi Matriks Lintas Halaman",
                "pages": [
                    {
                        "name": "page1",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "anggota_dasar",
                                "title": "Halaman 1: Daftar Anggota",
                                "columns": [
                                    {
                                        "name": "nama",
                                        "title": "Nama",
                                        "cellType": "text",
                                        "isRequired": true
                                    },
                                    {
                                        "name": "Hubungaan_KRT",
                                        "title": "Hubungan",
                                        "cellType": "dropdown",
                                        "choices": [
                                            "Kepala RT",
                                            "Istri/Suami",
                                            "Anak",
                                            "Lainnya"
                                        ]
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        "name": "page2",
                        "elements": [
                            {
                                "type": "dropdown",
                                "name": "daftar_nama_anggota",
                                "visible": false,
                                "choicesFromQuestion": "anggota_dasar",
                                "choicesFromQuestionMode": "all",
                                "choiceValuesFromQuestion": "nama",
                                "choiceTextsFromQuestion": "nama"
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "rincian_status",
                                "title": "Halaman 2: Rincian Status",
                                "columns": [
                                    {
                                        "name": "pilih_nama",
                                        "title": "Pilih Nama",
                                        "cellType": "dropdown",
                                        "choicesFromQuestion": "daftar_nama_anggota",
                                        "choicesFromQuestionMode": "all"
                                    },
                                    {
                                        "name": "status_perkawinan",
                                        "title": "Status Perkawinan",
                                        "cellType": "dropdown",
                                        "choices": [
                                            "Belum Menikah",
                                            "Menikah",
                                            "Cerai Hidup",
                                            "Cerai Mati"
                                        ],
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Untuk anggota dengan hubungan Kepala RT atau Istri/Suami, status perkawinan harus Menikah.",
                                                "expression": "matrixRowValueRequiresWhenAllExist('anggota_dasar', 'nama', {row.pilih_nama}, 'Hubungaan_KRT', 'Kepala RT|Istri/Suami', {row.status_perkawinan}, 'Menikah')"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "checkErrorsMode": "onValueChanged"
            },
            "source_json": {
                "title": "Validasi Matriks Lintas Halaman",
                "pages": [
                    {
                        "name": "page1",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "anggota_dasar",
                                "title": "Halaman 1: Daftar Anggota",
                                "columns": [
                                    {
                                        "name": "nama",
                                        "title": "Nama",
                                        "cellType": "text",
                                        "isRequired": true
                                    },
                                    {
                                        "name": "Hubungaan_KRT",
                                        "title": "Hubungan",
                                        "cellType": "dropdown",
                                        "choices": [
                                            "Kepala RT",
                                            "Istri/Suami",
                                            "Anak",
                                            "Lainnya"
                                        ]
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        "name": "page2",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "rincian_status",
                                "title": "Halaman 2: Rincian Status",
                                "columns": [
                                    {
                                        "name": "pilih_nama",
                                        "title": "Pilih Nama",
                                        "cellType": "dropdown",
                                        "choicesFromQuestion": "anggota_dasar",
                                        "choiceValuesFromQuestion": "nama"
                                    },
                                    {
                                        "name": "status_perkawinan",
                                        "title": "Status Perkawinan",
                                        "cellType": "dropdown",
                                        "choices": [
                                            "Belum Menikah",
                                            "Menikah",
                                            "Cerai Hidup",
                                            "Cerai Mati"
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "checkErrorsMode": "onValueChanged"
            },
            "validation_rules": {
                "helpers": [
                    {
                        "type": "choicesFromMatrix",
                        "name": "daftar_nama_anggota",
                        "page": "page2",
                        "sourceQuestion": "anggota_dasar",
                        "valueColumn": "nama",
                        "textColumn": "nama",
                        "visible": false
                    }
                ],
                "rules": [
                    {
                        "type": "matrixRowValueRequiresWhenAllExist",
                        "targetQuestion": "rincian_status",
                        "targetColumn": "status_perkawinan",
                        "message": "Untuk anggota dengan hubungan Kepala RT atau Istri/Suami, status perkawinan harus Menikah.",
                        "sourceQuestion": "anggota_dasar",
                        "sourceKeyColumn": "nama",
                        "sourceMatchColumn": "Hubungaan_KRT",
                        "requiredSourceValues": [
                            "Kepala RT",
                            "Istri/Suami"
                        ],
                        "rowLookupColumn": "pilih_nama",
                        "requiredValue": "Menikah"
                    }
                ],
                "rewrites": [
                    {
                        "type": "matrixColumnChoicesFromHelper",
                        "targetQuestion": "rincian_status",
                        "targetColumn": "pilih_nama",
                        "helperQuestion": "daftar_nama_anggota"
                    }
                ]
            },
            "start_at": "2026-04-12T01:00:00.000000Z",
            "end_at": "2026-04-30T10:00:00.000000Z",
            "created_at": "2026-04-12T07:06:30.000000Z",
            "updated_at": "2026-04-12T07:32:14.000000Z"
        },
        {
            "id": 2,
            "survey_name": "Survey Rumah Tangga",
            "survey_description": "Contoh Survei 2 level, Rmt dan ART",
            "json_file": {
                "title": "Survei Karakteristik Rumah Tangga",
                "pages": [
                    {
                        "name": "halaman_wilayah",
                        "elements": [
                            {
                                "type": "text",
                                "name": "kode_wilayah",
                                "title": "Nomor Kode Wilayah / Sampel",
                                "description": "Masukkan kode wilayah untuk memvalidasi data lokasi",
                                "isRequired": true,
                                "inputType": "number"
                            },
                            {
                                "type": "text",
                                "name": "provinsi",
                                "title": "Provinsi",
                                "readOnly": true,
                                "placeholder": "Akan terisi otomatis..."
                            },
                            {
                                "type": "text",
                                "name": "kabupaten",
                                "title": "Kabupaten/Kota",
                                "readOnly": true,
                                "placeholder": "Akan terisi otomatis..."
                            },
                            {
                                "type": "text",
                                "name": "kecamatan",
                                "title": "Kecamatan",
                                "readOnly": true,
                                "placeholder": "Akan terisi otomatis..."
                            },
                            {
                                "type": "text",
                                "name": "desa",
                                "title": "Desa/Kelurahan",
                                "readOnly": true,
                                "placeholder": "Akan terisi otomatis..."
                            },
                            {
                                "type": "comment",
                                "name": "alamat",
                                "title": "Alamat Lengkap (Termasuk RT/RW)",
                                "isRequired": true
                            },
                            {
                                "type": "text",
                                "name": "nama_surveyor",
                                "title": "Nama Surveyor",
                                "isRequired": true
                            }
                        ],
                        "title": "Halaman I: Informasi Wilayah"
                    },
                    {
                        "name": "halaman_anggota",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "anggota_keluarga",
                                "title": "Daftar Anggota Rumah Tangga",
                                "columns": [
                                    {
                                        "name": "nama",
                                        "title": "Nama Lengkap",
                                        "cellType": "text",
                                        "isRequired": true
                                    },
                                    {
                                        "name": "umur",
                                        "title": "Umur",
                                        "cellType": "text",
                                        "isRequired": true,
                                        "inputType": "number"
                                    },
                                    {
                                        "name": "jk",
                                        "title": "Jenis Kelamin",
                                        "cellType": "dropdown",
                                        "isRequired": true,
                                        "choices": [
                                            "Laki-laki",
                                            "Perempuan"
                                        ]
                                    },
                                    {
                                        "name": "hubungan",
                                        "title": "Hubungan dengan Kepala RT",
                                        "cellType": "dropdown",
                                        "choices": [
                                            "Kepala RT",
                                            "Istri/Suami",
                                            "Anak",
                                            "Menantu",
                                            "Cucu",
                                            "Orang Tua",
                                            "Lainnya"
                                        ]
                                    }
                                ],
                                "rowCount": 1,
                                "addRowText": "Tambah Anggota"
                            }
                        ],
                        "title": "Halaman II: Keterangan Anggota Rumah Tangga"
                    },
                    {
                        "name": "halaman_keterangan_rt",
                        "elements": [
                            {
                                "type": "radiogroup",
                                "name": "jenis_atap",
                                "title": "Jenis atap terluas rumah?",
                                "isRequired": true,
                                "choices": [
                                    "Beton",
                                    "Genteng",
                                    "Seng",
                                    "Asbes",
                                    "Bambu",
                                    "Jerami/Ijuk"
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "jenis_dinding",
                                "title": "Jenis dinding terluas rumah?",
                                "isRequired": true,
                                "choices": [
                                    "Tembok",
                                    "Plesteran",
                                    "Kayu",
                                    "Bambu",
                                    "Lainnya"
                                ]
                            },
                            {
                                "type": "dropdown",
                                "name": "jenis_kloset",
                                "title": "Jenis fasilitas tempat buang air besar (kloset)?",
                                "isRequired": true,
                                "choices": [
                                    "Leher angsa",
                                    "Plengsengan",
                                    "Cemplung/Cubluk",
                                    "Tidak ada"
                                ]
                            },
                            {
                                "type": "text",
                                "name": "sumber_pendapatan",
                                "title": "Sumber utama pendapatan rumah tangga?",
                                "isRequired": true
                            },
                            {
                                "type": "text",
                                "name": "total_pengeluaran",
                                "title": "Total rata-rata pengeluaran sebulan (Rp)?",
                                "isRequired": true,
                                "inputType": "number"
                            }
                        ],
                        "title": "Halaman III: Keterangan Rumah Tangga"
                    }
                ],
                "showProgressBar": true,
                "progressBarLocation": "top",
                "completeText": "Selesai dan Kirim"
            },
            "source_json": {
                "title": "Survei Karakteristik Rumah Tangga",
                "pages": [
                    {
                        "name": "halaman_wilayah",
                        "elements": [
                            {
                                "type": "text",
                                "name": "kode_wilayah",
                                "title": "Nomor Kode Wilayah / Sampel",
                                "description": "Masukkan kode wilayah untuk memvalidasi data lokasi",
                                "isRequired": true,
                                "inputType": "number"
                            },
                            {
                                "type": "text",
                                "name": "provinsi",
                                "title": "Provinsi",
                                "readOnly": true,
                                "placeholder": "Akan terisi otomatis..."
                            },
                            {
                                "type": "text",
                                "name": "kabupaten",
                                "title": "Kabupaten/Kota",
                                "readOnly": true,
                                "placeholder": "Akan terisi otomatis..."
                            },
                            {
                                "type": "text",
                                "name": "kecamatan",
                                "title": "Kecamatan",
                                "readOnly": true,
                                "placeholder": "Akan terisi otomatis..."
                            },
                            {
                                "type": "text",
                                "name": "desa",
                                "title": "Desa/Kelurahan",
                                "readOnly": true,
                                "placeholder": "Akan terisi otomatis..."
                            },
                            {
                                "type": "comment",
                                "name": "alamat",
                                "title": "Alamat Lengkap (Termasuk RT/RW)",
                                "isRequired": true
                            },
                            {
                                "type": "text",
                                "name": "nama_surveyor",
                                "title": "Nama Surveyor",
                                "isRequired": true
                            }
                        ],
                        "title": "Halaman I: Informasi Wilayah"
                    },
                    {
                        "name": "halaman_anggota",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "anggota_keluarga",
                                "title": "Daftar Anggota Rumah Tangga",
                                "columns": [
                                    {
                                        "name": "nama",
                                        "title": "Nama Lengkap",
                                        "cellType": "text",
                                        "isRequired": true
                                    },
                                    {
                                        "name": "umur",
                                        "title": "Umur",
                                        "cellType": "text",
                                        "isRequired": true,
                                        "inputType": "number"
                                    },
                                    {
                                        "name": "jk",
                                        "title": "Jenis Kelamin",
                                        "cellType": "dropdown",
                                        "isRequired": true,
                                        "choices": [
                                            "Laki-laki",
                                            "Perempuan"
                                        ]
                                    },
                                    {
                                        "name": "hubungan",
                                        "title": "Hubungan dengan Kepala RT",
                                        "cellType": "dropdown",
                                        "choices": [
                                            "Kepala RT",
                                            "Istri/Suami",
                                            "Anak",
                                            "Menantu",
                                            "Cucu",
                                            "Orang Tua",
                                            "Lainnya"
                                        ]
                                    }
                                ],
                                "rowCount": 1,
                                "addRowText": "Tambah Anggota"
                            }
                        ],
                        "title": "Halaman II: Keterangan Anggota Rumah Tangga"
                    },
                    {
                        "name": "halaman_keterangan_rt",
                        "elements": [
                            {
                                "type": "radiogroup",
                                "name": "jenis_atap",
                                "title": "Jenis atap terluas rumah?",
                                "isRequired": true,
                                "choices": [
                                    "Beton",
                                    "Genteng",
                                    "Seng",
                                    "Asbes",
                                    "Bambu",
                                    "Jerami/Ijuk"
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "jenis_dinding",
                                "title": "Jenis dinding terluas rumah?",
                                "isRequired": true,
                                "choices": [
                                    "Tembok",
                                    "Plesteran",
                                    "Kayu",
                                    "Bambu",
                                    "Lainnya"
                                ]
                            },
                            {
                                "type": "dropdown",
                                "name": "jenis_kloset",
                                "title": "Jenis fasilitas tempat buang air besar (kloset)?",
                                "isRequired": true,
                                "choices": [
                                    "Leher angsa",
                                    "Plengsengan",
                                    "Cemplung/Cubluk",
                                    "Tidak ada"
                                ]
                            },
                            {
                                "type": "text",
                                "name": "sumber_pendapatan",
                                "title": "Sumber utama pendapatan rumah tangga?",
                                "isRequired": true
                            },
                            {
                                "type": "text",
                                "name": "total_pengeluaran",
                                "title": "Total rata-rata pengeluaran sebulan (Rp)?",
                                "isRequired": true,
                                "inputType": "number"
                            }
                        ],
                        "title": "Halaman III: Keterangan Rumah Tangga"
                    }
                ],
                "showProgressBar": true,
                "progressBarLocation": "top",
                "completeText": "Selesai dan Kirim"
            },
            "validation_rules": {
                "helpers": [],
                "rules": [],
                "rewrites": []
            },
            "start_at": "2026-04-11T17:00:00.000000Z",
            "end_at": "2026-04-30T10:00:00.000000Z",
            "created_at": "2026-04-12T08:58:56.000000Z",
            "updated_at": "2026-04-12T08:58:56.000000Z"
        },
        {
            "id": 3,
            "survey_name": "Podes 2024",
            "survey_description": "Kuesioner Kegiatan Potensi Desa 2024",
            "json_file": {
                "title": "Pemutakhiran Data Perkembangan Desa 2025 (PODES2025-DESA)",
                "description": "Daftar ini diisi oleh petugas berdasarkan hasil pencacahan/wawancara dengan narasumber terkait yang berwenang dan relevan, serta penelusuran dokumen desa/kelurahan",
                "logoPosition": "right",
                "completedHtml": "<h3>Terima kasih! Data telah tersimpan.</h3>",
                "pages": [
                    {
                        "name": "page1",
                        "elements": [
                            {
                                "type": "dropdown",
                                "name": "provinsi",
                                "title": "101 Provinsi",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "32",
                                        "text": "[32] JAWA BARAT"
                                    }
                                ]
                            },
                            {
                                "type": "dropdown",
                                "name": "kabupaten_kota",
                                "title": "102 Kabupaten/Kota",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "15",
                                        "text": "[15] KARAWANG"
                                    }
                                ],
                                "textWrapEnabled": false
                            },
                            {
                                "type": "dropdown",
                                "name": "kecamatan",
                                "title": "103 Kecamatan",
                                "isRequired": true,
                                "choicesByUrl": {
                                    "url": "http://127.0.0.1:9001/api/meta/wilayah/kecamatan?kdprov={provinsi}&kdkab={kabupaten_kota}",
                                    "valueName": "value",
                                    "titleName": "text",
                                    "allowEmptyResponse": true
                                }
                            },
                            {
                                "type": "text",
                                "name": "desa_kelurahan",
                                "title": "104 Desa/Kelurahan",
                                "isRequired": true
                            },
                            {
                                "type": "radiogroup",
                                "name": "status_daerah",
                                "title": "105 Status Daerah",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Perkotaan"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Perdesaan"
                                    }
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "sk_pembentukan",
                                "title": "106 SK pembentukan/pengesahan desa/kelurahan",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Permendagri/Kepmendagri"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Perda Provinsi"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Perda Kabupaten"
                                    },
                                    {
                                        "value": "4",
                                        "text": "SK Gubernur/Bupati"
                                    },
                                    {
                                        "value": "5",
                                        "text": "Lainnya"
                                    }
                                ]
                            },
                            {
                                "type": "panel",
                                "name": "panel_status_definitif",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "batas_wilayah_jelas",
                                        "title": "a. Ada wilayah desa/kelurahan dengan batas yang jelas",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "penduduk_menetap",
                                        "title": "b. Ada penduduk yang menetap di wilayah desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pemerintah_desa",
                                        "title": "c. Ada pemerintah desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    }
                                ],
                                "title": "107 Status definitif desa dan operasional desa/kelurahan"
                            }
                        ],
                        "title": "I. KETERANGAN TEMPAT"
                    },
                    {
                        "name": "page2",
                        "elements": [
                            {
                                "type": "html",
                                "name": "header_petugas",
                                "html": "<h3>II. KETERANGAN PETUGAS</h3>"
                            },
                            {
                                "type": "panel",
                                "name": "panel_201_203",
                                "elements": [
                                    {
                                        "type": "panel",
                                        "name": "panel_kunjungan_1",
                                        "elements": [
                                            {
                                                "type": "text",
                                                "name": "kunjungan1_tanggal",
                                                "title": "201 Tanggal Kunjungan",
                                                "isRequired": true,
                                                "inputType": "date",
                                                "placeholder": "dd/mm/yyyy"
                                            },
                                            {
                                                "type": "text",
                                                "name": "kunjungan1_no",
                                                "startWithNewLine": false,
                                                "title": "No. Kunjungan",
                                                "placeholder": "No"
                                            }
                                        ],
                                        "title": "Kunjungan Pertama"
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_kunjungan_2",
                                        "elements": [
                                            {
                                                "type": "text",
                                                "name": "kunjungan2_tanggal",
                                                "title": "203 Tanggal Kunjungan",
                                                "inputType": "date",
                                                "placeholder": "dd/mm/yyyy"
                                            },
                                            {
                                                "type": "text",
                                                "name": "kunjungan2_no",
                                                "startWithNewLine": false,
                                                "title": "No. Kunjungan",
                                                "placeholder": "No"
                                            }
                                        ],
                                        "title": "Kunjungan Kedua (Jika Ada)"
                                    }
                                ],
                                "title": "Kunjungan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_pengawas",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "nama_pengawas",
                                        "title": "204 Nama Pengawas/Pemeriksa",
                                        "isRequired": true,
                                        "placeholder": "Nama lengkap pengawas/pemeriksa"
                                    },
                                    {
                                        "type": "text",
                                        "name": "tanggal_pemeriksaan",
                                        "title": "206 Tanggal Pemeriksaan",
                                        "isRequired": true,
                                        "inputType": "date",
                                        "placeholder": "dd/mm/yyyy"
                                    }
                                ],
                                "title": "Pemeriksaan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_pengesahan",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "tempat_pengesahan",
                                        "title": "Tempat",
                                        "placeholder": "Nama desa/kelurahan"
                                    },
                                    {
                                        "type": "text",
                                        "name": "tanggal_pengesahan",
                                        "title": "Tanggal",
                                        "inputType": "date",
                                        "placeholder": "dd/mm/yyyy"
                                    },
                                    {
                                        "type": "text",
                                        "name": "nama_kepala_desa",
                                        "title": "Mengetahui Kepala Desa/Lurah",
                                        "placeholder": "Nama lengkap dan tanda tangan/cap"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "status_desa_kelurahan_coret",
                                        "title": "*) Coret yang tidak sesuai",
                                        "choices": [
                                            {
                                                "value": "desa",
                                                "text": "Desa"
                                            },
                                            {
                                                "value": "kelurahan",
                                                "text": "Kelurahan"
                                            }
                                        ]
                                    }
                                ],
                                "title": "Pengesahan"
                            },
                            {
                                "type": "html",
                                "name": "footer_petugas",
                                "html": "<p><em>DAFTAR INI DIISI OLEH PETUGAS BERDASARKAN HASIL PENCACAHAN/WAWANCARA DENGAN NARASUMBER TERKAIT YANG BERWENANG DAN RELEVAN, SERTA PENELUSURAN DOKUMEN DESA/KELURAHAN</em></p>"
                            }
                        ],
                        "title": "II. KETERANGAN PETUGAS"
                    },
                    {
                        "name": "page3",
                        "elements": [
                            {
                                "type": "radiogroup",
                                "name": "status_pemerintahan",
                                "title": "301 Status pemerintahan",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Desa"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Kelurahan"
                                    },
                                    {
                                        "value": "3",
                                        "text": "UPT/SPT"
                                    },
                                    {
                                        "value": "4",
                                        "text": "Nagari"
                                    }
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "berbatasan_laut",
                                "title": "302 a. Ada wilayah desa/kelurahan yang berbatasan langsung dengan laut",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Ada"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Tidak ada"
                                    }
                                ]
                            },
                            {
                                "type": "panel",
                                "name": "panel_pemanfaatan_laut",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "perikanan_tangkap",
                                        "title": "1) Perikanan tangkap (mencakup seluruh biota laut)",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "perikanan_budidaya",
                                        "title": "2) Perikanan budidaya (mencakup seluruh biota laut)",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "tambak_garam",
                                        "title": "3) Tambak garam",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "wisata_bahari",
                                        "title": "4) Wisata bahari",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "transportasi_umum",
                                        "title": "5) Transportasi umum",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "visibleIf": "{berbatasan_laut} = '1'",
                                "title": "b. Jika berbatasan dengan laut, pemanfaatan laut"
                            },
                            {
                                "type": "radiogroup",
                                "name": "lokasi_terhadap_hutan",
                                "title": "303 a. Lokasi wilayah desa/kelurahan terhadap kawasan hutan/hutan",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Di dalam kawasan hutan"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Di tepi/sekitar kawasan hutan"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Di luar kawasan hutan"
                                    }
                                ]
                            },
                            {
                                "type": "checkbox",
                                "name": "fungsi_kawasan_hutan",
                                "visibleIf": "{lokasi_terhadap_hutan} <> '3'",
                                "title": "b. Fungsi kawasan hutan/hutan (Pilihan boleh lebih dari satu)",
                                "choices": [
                                    {
                                        "value": "A",
                                        "text": "Konservasi"
                                    },
                                    {
                                        "value": "B",
                                        "text": "Lindung"
                                    },
                                    {
                                        "value": "C",
                                        "text": "Produksi"
                                    }
                                ]
                            },
                            {
                                "type": "text",
                                "name": "jumlah_rw",
                                "title": "304 a. Jumlah Rukun Warga (RW) di desa/kelurahan",
                                "inputType": "number",
                                "min": 0
                            },
                            {
                                "type": "text",
                                "name": "jumlah_rt",
                                "title": "b. Jumlah Rukun Tetangga (RT) di desa/kelurahan",
                                "inputType": "number",
                                "min": 0
                            }
                        ],
                        "title": "III. KETERANGAN UMUM DESA/KELURAHAN"
                    },
                    {
                        "name": "page4",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_penduduk",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "penduduk_laki",
                                        "title": "a. Jumlah penduduk laki-laki",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "penduduk_perempuan",
                                        "title": "b. Jumlah penduduk perempuan",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_keluarga",
                                        "title": "c. Jumlah keluarga",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "keluarga_pertanian",
                                        "title": "d. Jumlah keluarga pertanian",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0
                                    }
                                ],
                                "title": "401 Penduduk dan keluarga pada 1 Januari 2025"
                            }
                        ],
                        "title": "IV. KEPENDUDUKAN"
                    },
                    {
                        "name": "page5",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_listrik",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "listrik_pln",
                                        "title": "1. Jumlah keluarga pengguna listrik PLN",
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "listrik_non_pln",
                                        "title": "2. Jumlah keluarga pengguna listrik Non-PLN",
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "bukan_pengguna_listrik",
                                        "title": "b. Jumlah keluarga bukan pengguna listrik",
                                        "inputType": "number",
                                        "min": 0
                                    }
                                ],
                                "title": "501 Keluarga pengguna listrik"
                            },
                            {
                                "type": "radiogroup",
                                "name": "penerangan_jalan",
                                "title": "502 a. Penerangan di jalan utama desa/kelurahan",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Ada, sebagian besar"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Ada, sebagian kecil"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Tidak ada"
                                    }
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "sumber_penerangan",
                                "visibleIf": "{penerangan_jalan} <> '3'",
                                "title": "b. Sumber penerangan di jalan utama desa/kelurahan",
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Listrik diusahakan oleh pemerintah"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Listrik diusahakan oleh non pemerintah"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Non listrik"
                                    }
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "bahan_bakar_memasak",
                                "title": "503 Bahan bakar untuk memasak sebagian besar keluarga",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Listrik"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Elpiji 5,5 kg"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Elpiji 12 kg"
                                    },
                                    {
                                        "value": "4",
                                        "text": "Elpiji 3 kg"
                                    },
                                    {
                                        "value": "5",
                                        "text": "Gas kota"
                                    },
                                    {
                                        "value": "6",
                                        "text": "Biogas"
                                    },
                                    {
                                        "value": "7",
                                        "text": "Minyak tanah"
                                    },
                                    {
                                        "value": "8",
                                        "text": "Briket"
                                    },
                                    {
                                        "value": "9",
                                        "text": "Arang"
                                    },
                                    {
                                        "value": "10",
                                        "text": "Kayu bakar"
                                    },
                                    {
                                        "value": "11",
                                        "text": "Lainnya"
                                    }
                                ]
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "pencemaran_lingkungan",
                                "title": "504 Pencemaran lingkungan hidup (polusi) selama setahun terakhir",
                                "defaultValue": [
                                    {
                                        "jenis_pencemaran": "Air"
                                    },
                                    {
                                        "jenis_pencemaran": "Tanah"
                                    },
                                    {
                                        "jenis_pencemaran": "Udara"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_pencemaran",
                                        "title": "Jenis Pencemaran",
                                        "cellType": "text",
                                        "readOnly": true
                                    },
                                    {
                                        "name": "kejadian",
                                        "title": "Kejadian",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "sumber_utama",
                                        "title": "Sumber pencemaran utama",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Rumah tangga"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Pabrik/industri/usaha"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Lainnya"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "pengaduan",
                                        "title": "Pengaduan warga ke aparat",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "rowCount": 3
                            }
                        ],
                        "title": "V. PERUMAHAN DAN LINGKUNGAN HIDUP"
                    },
                    {
                        "name": "page6",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "bencana_alam",
                                "title": "601 Kejadian/bencana alam",
                                "defaultValue": [
                                    {
                                        "jenis_bencana": "a. Tanah longsor"
                                    },
                                    {
                                        "jenis_bencana": "b. Banjir"
                                    },
                                    {
                                        "jenis_bencana": "c. Banjir bandang"
                                    },
                                    {
                                        "jenis_bencana": "d. Gempa bumi"
                                    },
                                    {
                                        "jenis_bencana": "e. Tsunami"
                                    },
                                    {
                                        "jenis_bencana": "f. Gelombang pasang laut"
                                    },
                                    {
                                        "jenis_bencana": "g. Angin puyuh/puting beliung/topan"
                                    },
                                    {
                                        "jenis_bencana": "h. Letusan gunung api"
                                    },
                                    {
                                        "jenis_bencana": "i. Kebakaran hutan dan lahan"
                                    },
                                    {
                                        "jenis_bencana": "j. Kekeringan"
                                    },
                                    {
                                        "jenis_bencana": "k. Abrasi"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_bencana",
                                        "title": "Jenis Bencana",
                                        "cellType": "text",
                                        "readOnly": true
                                    },
                                    {
                                        "name": "kejadian_2024",
                                        "title": "Tahun 2024 (Ada/Tidak)",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "korban_2024",
                                        "title": "Korban 2024",
                                        "cellType": "checkbox",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Hilang"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Luka/sakit"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Mengungsi"
                                            },
                                            {
                                                "value": "D",
                                                "text": "Tidak ada korban"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "kejadian_2025",
                                        "title": "Jan-Mei 2025 (Ada/Tidak)",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "korban_2025",
                                        "title": "Korban 2025",
                                        "cellType": "checkbox",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Hilang"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Luka/sakit"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Mengungsi"
                                            },
                                            {
                                                "value": "D",
                                                "text": "Tidak ada korban"
                                            }
                                        ]
                                    }
                                ],
                                "allowRemoveRows": false,
                                "rowCount": 11
                            },
                            {
                                "type": "panel",
                                "name": "panel_mitigasi",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "sistem_peringatan_dini",
                                        "title": "a. Sistem peringatan dini bencana alam",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "peringatan_dini_tsunami",
                                        "title": "b. Sistem peringatan dini khusus tsunami",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Bukan wilayah potensi tsunami"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "perlengkapan_keselamatan",
                                        "title": "c. Perlengkapan keselamatan (perahu karet, tenda, masker, dll.)",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "rambu_evakuasi",
                                        "title": "d. Rambu-rambu dan jalur evakuasi bencana",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "normalisasi_sungai",
                                        "title": "e. Pembuatan, perawatan, atau normalisasi: sungai, kanal, tanggul, dll.",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "602 Fasilitas/upaya antisipasi/mitigasi bencana alam"
                            }
                        ],
                        "title": "VI. BENCANA ALAM DAN MITIGASI BENCANA ALAM"
                    },
                    {
                        "name": "page_7",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "fasilitas_pendidikan",
                                "title": "701 Keberadaan fasilitas pendidikan menurut jenjang pendidikan di desa/kelurahan",
                                "description": "Isi jumlah fasilitas pendidikan. Jika tidak ada (0), isi jarak dan kemudahan menuju fasilitas terdekat.",
                                "defaultValue": [
                                    {
                                        "jenis_fasilitas": "a. TK (Taman Kanak-Kanak)"
                                    },
                                    {
                                        "jenis_fasilitas": "b. RA/BA (Raudhatul Athfal/Bustanul Athfal)"
                                    },
                                    {
                                        "jenis_fasilitas": "c. SD (Sekolah Dasar)"
                                    },
                                    {
                                        "jenis_fasilitas": "d. MI (Madrasah Ibtidaiyah)"
                                    },
                                    {
                                        "jenis_fasilitas": "e. SMP (Sekolah Menengah Pertama)"
                                    },
                                    {
                                        "jenis_fasilitas": "f. MTs (Madrasah Tsanawiyah)"
                                    },
                                    {
                                        "jenis_fasilitas": "g. SMA (Sekolah Menengah Atas)"
                                    },
                                    {
                                        "jenis_fasilitas": "h. MA (Madrasah Aliyah)"
                                    },
                                    {
                                        "jenis_fasilitas": "i. SMK (Sekolah Menengah Kejuruan)"
                                    },
                                    {
                                        "jenis_fasilitas": "j. Akademi/Perguruan Tinggi"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_fasilitas",
                                        "title": "Jenis fasilitas pendidikan",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "200px"
                                    },
                                    {
                                        "name": "jumlah_negeri",
                                        "title": "Negeri",
                                        "cellType": "text",
                                        "width": "80px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jumlah_swasta",
                                        "title": "Swasta",
                                        "cellType": "text",
                                        "width": "80px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jarak",
                                        "title": "Jarak (km)",
                                        "cellType": "text",
                                        "width": "90px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah Negeri dan Swasta 0, jarak harus diisi",
                                                "expression": "({row.jumlah_negeri} > 0 or {row.jumlah_swasta} > 0) or ({row.jumlah_negeri} == 0 and {row.jumlah_swasta} == 0 and {row.jarak} > 0)"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    },
                                    {
                                        "name": "kemudahan",
                                        "title": "Kemudahan untuk mencapai",
                                        "cellType": "dropdown",
                                        "width": "150px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah Negeri dan Swasta 0, kemudahan harus dipilih",
                                                "expression": "({row.jumlah_negeri} > 0 or {row.jumlah_swasta} > 0) or ({row.jumlah_negeri} == 0 and {row.jumlah_swasta} == 0 and {row.kemudahan} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sangat mudah"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Mudah"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Sulit"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Sangat sulit"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false,
                                "rowCount": 10
                            },
                            {
                                "type": "html",
                                "name": "header_kesehatan",
                                "html": "<h3>VII. KESEHATAN</h3>"
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "fasilitas_kesehatan",
                                "title": "702 Keberadaan fasilitas pelayanan kesehatan di desa/kelurahan",
                                "description": "Isi jumlah fasilitas kesehatan. Jika tidak ada (0), isi jarak dan kemudahan menuju fasilitas terdekat.",
                                "defaultValue": [
                                    {
                                        "jenis_fasilitas": "a. Rumah sakit"
                                    },
                                    {
                                        "jenis_fasilitas": "b. Klinik utama"
                                    },
                                    {
                                        "jenis_fasilitas": "c. Balai kesehatan"
                                    },
                                    {
                                        "jenis_fasilitas": "d. Puskesmas dengan rawat inap"
                                    },
                                    {
                                        "jenis_fasilitas": "e. Puskesmas tanpa rawat inap"
                                    },
                                    {
                                        "jenis_fasilitas": "f. Puskesmas pembantu"
                                    },
                                    {
                                        "jenis_fasilitas": "g. Klinik pratama"
                                    },
                                    {
                                        "jenis_fasilitas": "h. Praktik mandiri dokter"
                                    },
                                    {
                                        "jenis_fasilitas": "i. Praktik mandiri bidan"
                                    },
                                    {
                                        "jenis_fasilitas": "j. Poskesdes (pos kesehatan desa)"
                                    },
                                    {
                                        "jenis_fasilitas": "k. Polindes (pondok bersalin desa)"
                                    },
                                    {
                                        "jenis_fasilitas": "l. Apotek"
                                    },
                                    {
                                        "jenis_fasilitas": "m. Toko khusus obat/jamu"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_fasilitas",
                                        "title": "Jenis fasilitas pelayanan kesehatan",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "220px"
                                    },
                                    {
                                        "name": "jumlah",
                                        "title": "Jumlah",
                                        "cellType": "text",
                                        "width": "80px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jarak",
                                        "title": "Jarak (km)",
                                        "cellType": "text",
                                        "width": "90px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah = 0, jarak harus diisi",
                                                "expression": "{row.jumlah} > 0 or ({row.jumlah} == 0 and {row.jarak} > 0)"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    },
                                    {
                                        "name": "kemudahan",
                                        "title": "Kemudahan untuk mencapai",
                                        "cellType": "dropdown",
                                        "width": "150px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah = 0, kemudahan harus dipilih",
                                                "expression": "{row.jumlah} > 0 or ({row.jumlah} == 0 and {row.kemudahan} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sangat mudah"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Mudah"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Sulit"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Sangat sulit"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false,
                                "rowCount": 13
                            },
                            {
                                "type": "panel",
                                "name": "panel_703",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_posyandu_aktif",
                                        "title": "a. Jumlah posyandu aktif",
                                        "description": "unit",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "posyandu_sebulan_sekali",
                                        "title": "b. Posyandu dengan kegiatan/pelayanan setiap sebulan sekali",
                                        "description": "unit",
                                        "isRequired": true,
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jumlah tidak boleh melebihi total posyandu aktif",
                                                "expression": "{posyandu_sebulan_sekali} <= {jumlah_posyandu_aktif}"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "posyandu_dua_bulan_atau_lebih",
                                        "title": "c. Posyandu dengan kegiatan/pelayanan setiap 2 bulan sekali atau lebih",
                                        "description": "unit",
                                        "isRequired": true,
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jumlah tidak boleh melebihi total posyandu aktif",
                                                "expression": "{posyandu_dua_bulan_atau_lebih} <= {jumlah_posyandu_aktif}"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_posbindu",
                                        "title": "d. Pos Pembinaan Terpadu (Posbindu)",
                                        "description": "unit",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "title": "703 Jumlah Upaya Kesehatan Bersumberdaya Masyarakat (UKBM) selama setahun terakhir"
                            }
                        ],
                        "title": "VII. PENDIDIKAN DAN KESEHATAN"
                    },
                    {
                        "name": "page_8",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_801",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "lalu_lintas_melalui",
                                        "title": "a. Lalu lintas dari/ke desa/kelurahan melalui",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Darat"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Air"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Darat dan air"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Udara"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_transportasi_darat",
                                        "elements": [
                                            {
                                                "type": "radiogroup",
                                                "name": "jenis_permukaan_jalan",
                                                "title": "1) Jenis permukaan jalan darat antar desa/kelurahan yang terluas",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Aspal/beton"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Diperkeras (kerikil, batu, dll.)"
                                                    },
                                                    {
                                                        "value": "3",
                                                        "text": "Tanah"
                                                    },
                                                    {
                                                        "value": "4",
                                                        "text": "Lainnya (jalan setapak, kayu/papan, dll)"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "dapat_dilalui_kendaraan",
                                                "title": "2) Jalan darat antar desa/kelurahan dapat dilalui kendaraan bermotor roda 4 atau lebih",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Sepanjang tahun"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Sepanjang tahun kecuali saat tertentu (ketika turun hujan, pasang, dll.)"
                                                    },
                                                    {
                                                        "value": "3",
                                                        "text": "Selama musim kemarau"
                                                    },
                                                    {
                                                        "value": "4",
                                                        "text": "Tidak dapat dilalui sepanjang tahun"
                                                    }
                                                ]
                                            }
                                        ],
                                        "visibleIf": "{lalu_lintas_melalui} = '1' or {lalu_lintas_melalui} = '3'",
                                        "title": "b. Jika lalu lintas melalui darat atau darat dan air"
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_angkutan_umum",
                                        "elements": [
                                            {
                                                "type": "checkbox",
                                                "name": "keberadaan_angkutan_umum",
                                                "title": "1) Keberadaan angkutan umum (Pilihan boleh lebih dari satu)",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "A",
                                                        "text": "Ada, dengan trayek tetap"
                                                    },
                                                    {
                                                        "value": "B",
                                                        "text": "Ada, tanpa trayek tetap"
                                                    },
                                                    {
                                                        "value": "X",
                                                        "text": "Tidak ada angkutan umum"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "operasional_angkutan",
                                                "visibleIf": "{keberadaan_angkutan_umum} contains 'A' or {keberadaan_angkutan_umum} contains 'B'",
                                                "title": "2) Operasional angkutan umum yang utama",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Setiap hari"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Tidak setiap hari"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "jam_operasi_angkutan",
                                                "visibleIf": "{keberadaan_angkutan_umum} contains 'A' or {keberadaan_angkutan_umum} contains 'B'",
                                                "title": "3) Jam operasi angkutan umum yang utama",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Siang dan malam hari"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Hanya siang/malam hari"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "ketersediaan_angkutan_online",
                                                "title": "4) Ketersediaan angkutan online (memesan angkutan online)",
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Ada"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Tidak ada"
                                                    }
                                                ]
                                            }
                                        ],
                                        "title": "c. Angkutan umum yang melewati desa/kelurahan"
                                    }
                                ],
                                "title": "801 Prasarana dan sarana transportasi antar desa/kelurahan"
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "sarana_transportasi_kantor",
                                "title": "802 Sarana transportasi dari kantor kepala desa/lurah ke kantor camat/bupati/walikota",
                                "defaultValue": [
                                    {
                                        "tujuan": "a. Kantor camat"
                                    },
                                    {
                                        "tujuan": "b. Kantor bupati/walikota"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "tujuan",
                                        "title": "Tujuan",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "120px"
                                    },
                                    {
                                        "name": "sarana_biasa",
                                        "title": "Sarana transportasi yang biasa digunakan",
                                        "cellType": "checkbox",
                                        "width": "250px",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Angkutan umum"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Kendaraan pribadi"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Jalan kaki, sepeda, dll."
                                            }
                                        ]
                                    },
                                    {
                                        "name": "jenis_angkutan",
                                        "title": "Jenis angkutan umum",
                                        "cellType": "checkbox",
                                        "width": "300px",
                                        "visibleIf": "{row.sarana_biasa} contains 'A'",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Ojek sepeda motor"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Kendaraan bermotor roda 3 atau lebih"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Perahu (bermotor/tidak bermotor)"
                                            },
                                            {
                                                "value": "D",
                                                "text": "Pesawat terbang"
                                            },
                                            {
                                                "value": "E",
                                                "text": "Lainnya (becak, delman, pedati, dll)"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "angkutan_utama",
                                        "title": "Angkutan umum yang utama",
                                        "cellType": "dropdown",
                                        "width": "150px",
                                        "visibleIf": "{row.sarana_biasa} contains 'A'",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Ojek sepeda motor"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Kendaraan bermotor roda 3 atau lebih"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Perahu (bermotor/tidak bermotor)"
                                            },
                                            {
                                                "value": "D",
                                                "text": "Pesawat terbang"
                                            },
                                            {
                                                "value": "E",
                                                "text": "Lainnya (becak, delman, pedati, dll)"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    },
                                    {
                                        "name": "jarak_tempuh",
                                        "title": "Jarak tempuh (km)",
                                        "cellType": "text",
                                        "width": "100px",
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    },
                                    {
                                        "name": "waktu_tempuh_jam",
                                        "title": "Jam",
                                        "cellType": "text",
                                        "width": "60px",
                                        "inputType": "number",
                                        "min": 0,
                                        "max": 99,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "waktu_tempuh_menit",
                                        "title": "Menit",
                                        "cellType": "text",
                                        "width": "60px",
                                        "inputType": "number",
                                        "min": 0,
                                        "max": 59,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "biaya_transportasi",
                                        "title": "Biaya transportasi (Ribu Rupiah)",
                                        "cellType": "text",
                                        "width": "120px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false
                            },
                            {
                                "type": "panel",
                                "name": "panel_telepon",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "keluarga_telepon_kabel",
                                        "title": "a. Jumlah keluarga yang berlangganan telepon kabel",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pengguna_handphone",
                                        "title": "b. Keberadaan warga yang menggunakan telepon seluler/handphone",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sebagian besar warga"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Sebagian kecil warga"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "803 Telepon"
                            },
                            {
                                "type": "panel",
                                "name": "panel_menara_telepon",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_bts",
                                        "title": "a. Jumlah menara telepon seluler atau Base Transceiver Station (BTS)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_operator",
                                        "title": "b. Jumlah operator layanan komunikasi telepon seluler/handphone yang menjangkau di desa/kelurahan",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sinyal_telepon",
                                        "title": "c. Sinyal telepon seluler/handphone di sebagian besar wilayah desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sinyal sangat kuat"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Sinyal kuat"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Sinyal lemah"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Tidak ada sinyal"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sinyal_internet",
                                        "visibleIf": "{sinyal_telepon} <> '4'",
                                        "title": "d. Sinyal internet telepon seluler/handphone di sebagian besar wilayah di desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "5G/4G/LTE"
                                            },
                                            {
                                                "value": "2",
                                                "text": "3G/H/H+/EVDO"
                                            },
                                            {
                                                "value": "3",
                                                "text": "2,5G/E/GPRS"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Tidak ada sinyal internet"
                                            }
                                        ]
                                    }
                                ],
                                "title": "804 Keberadaan menara telepon seluler, sinyal telepon dan sinyal internet di desa/kelurahan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_pos",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "kantor_pos",
                                        "title": "a. Kantor pos/pos pembantu/rumah pos",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Beroperasi"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Jarang beroperasi"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak beroperasi"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "layanan_pos_keliling",
                                        "title": "b. Layanan pos keliling",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "jasa_ekspedisi",
                                        "title": "c. Perusahaan/agen jasa ekspedisi (pengiriman barang/dokumen) swasta",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Beroperasi"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Jarang beroperasi"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak beroperasi"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "805 Pos dan Ekspedisi"
                            }
                        ],
                        "title": "VIII. ANGKUTAN, KOMUNIKASI DAN INFORMASI"
                    },
                    {
                        "name": "page_9",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_901",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "pangkalan_minyak_tanah",
                                        "title": "a. Keberadaan pangkalan/agen/penjual minyak tanah (termasuk penjual minyak tanah keliling)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pangkalan_lpg",
                                        "title": "b. Keberadaan pangkalan/agen/penjual LPG (warung, toko, supermarket, penjual gas keliling)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "901 Bahan Bakar"
                            },
                            {
                                "type": "panel",
                                "name": "panel_902",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "bank_pemerintah",
                                        "title": "a. 1) Jumlah Bank Umum Pemerintah (BRI, BNI, Mandiri, BPD, BTN) yang beroperasi",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "bank_swasta",
                                        "title": "2) Jumlah Bank Umum Swasta (BCA, Permata, Sinarmas, CIMB, dll) yang beroperasi",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "bpr",
                                        "title": "3) Jumlah Bank Perkreditan Rakyat (BPR) yang beroperasi",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jarak_bank_terdekat",
                                        "visibleIf": "{bank_pemerintah} = 0 and {bank_swasta} = 0 and {bpr} = 0",
                                        "title": "b. Jika tidak ada bank, perkiraan jarak ke bank terdekat (km)",
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    }
                                ],
                                "title": "902 Perbankan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_903",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "kud",
                                        "title": "a. Jumlah Koperasi Unit Desa (KUD) yang masih aktif",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "kopinkra",
                                        "title": "b. Jumlah Koperasi Industri Kecil dan Kerajinan Rakyat (Kopinkra)/Usaha mikro yang masih aktif",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "ksp",
                                        "title": "c. Jumlah Koperasi Simpan Pinjam (KSP/Kospin) yang masih aktif",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "koperasi_lainnya",
                                        "title": "d. Jumlah koperasi lainnya yang masih aktif",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "title": "903 Koperasi"
                            },
                            {
                                "type": "panel",
                                "name": "panel_904",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "kur",
                                        "title": "a. Kredit Usaha Rakyat (KUR)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "kpp_e",
                                        "title": "b. Kredit Ketahanan Pangan dan Energi (KPP-E)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "kuk",
                                        "title": "c. Kredit Usaha Kecil (KUK)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "kube",
                                        "title": "d. Kelompok Usaha Bersama (KUBE)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "904 Fasilitas Kredit"
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "fasilitas_ekonomi",
                                "title": "905 Jumlah fasilitas ekonomi di desa/kelurahan",
                                "description": "Isi jumlah fasilitas ekonomi. Jika tidak ada (0), isi jarak dan kemudahan menuju fasilitas terdekat.",
                                "defaultValue": [
                                    {
                                        "jenis_fasilitas": "a. Kelompok pertokoan (minimal 10 toko dan mengelompok dalam satu lokasi)"
                                    },
                                    {
                                        "jenis_fasilitas": "b. Pasar dengan bangunan permanen (memiliki atap, lantai, dan dinding)"
                                    },
                                    {
                                        "jenis_fasilitas": "c. Pasar dengan bangunan semi permanen (memiliki atap dan lantai, tanpa dinding)"
                                    },
                                    {
                                        "jenis_fasilitas": "d. Pasar tanpa bangunan (misalnya: pasar subuh, pasar terapung, dll.)"
                                    },
                                    {
                                        "jenis_fasilitas": "e. Minimarket/swalayan/supermarket"
                                    },
                                    {
                                        "jenis_fasilitas": "f. Restoran/rumah makan (usaha pangan siap saji di bangunan tetap, pembeli biasanya dikenai pajak)"
                                    },
                                    {
                                        "jenis_fasilitas": "g. Warung/kedai makanan minuman (usaha pangan siap saji di bangunan tetap, pembeli biasanya tidak dikenai pajak)"
                                    },
                                    {
                                        "jenis_fasilitas": "h. Hotel (menyediakan jasa akomodasi dan ada restoran, penginapan dengan izin usaha sebagai hotel)"
                                    },
                                    {
                                        "jenis_fasilitas": "i. Penginapan: hostel/motel/losmen/wisma"
                                    },
                                    {
                                        "jenis_fasilitas": "j. Toko/warung kelontong (tempat usaha di bangunan tetap untuk menjual berbagai jenis barang keperluan sehari-hari secara eceran, tanpa ada sistem pelayanan mandiri)"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_fasilitas",
                                        "title": "Jenis fasilitas ekonomi",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "280px"
                                    },
                                    {
                                        "name": "jumlah",
                                        "title": "Jumlah",
                                        "cellType": "text",
                                        "width": "80px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jarak",
                                        "title": "Jarak (km)",
                                        "cellType": "text",
                                        "width": "90px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah = 0, jarak harus diisi",
                                                "expression": "{row.jumlah} > 0 or ({row.jumlah} == 0 and {row.jarak} > 0)"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    },
                                    {
                                        "name": "kemudahan",
                                        "title": "Kemudahan untuk mencapai",
                                        "cellType": "dropdown",
                                        "width": "150px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah = 0, kemudahan harus dipilih",
                                                "expression": "{row.jumlah} > 0 or ({row.jumlah} == 0 and {row.kemudahan} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sangat mudah"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Mudah"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Sulit"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Sangat sulit"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false,
                                "rowCount": 10
                            },
                            {
                                "type": "panel",
                                "name": "panel_906",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "sumber_penghasilan_utama",
                                        "title": "a. Sumber penghasilan utama sebagian besar penduduk desa/kelurahan berasal dari lapangan usaha",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Pertanian"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Industri"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Jasa"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sub_sektor_pertanian",
                                        "visibleIf": "{sumber_penghasilan_utama} = '1'",
                                        "title": "b. Jika sektor pertanian, jenis sub sektor utama sebagian besar penduduk",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Tanaman Pangan"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tanaman Hortikultura"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tanaman Perkebunan"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Peternakan"
                                            },
                                            {
                                                "value": "5",
                                                "text": "Perikanan"
                                            },
                                            {
                                                "value": "6",
                                                "text": "Kehutanan"
                                            },
                                            {
                                                "value": "7",
                                                "text": "Jasa Pertanian"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "text",
                                        "name": "komoditas_utama",
                                        "visibleIf": "{sumber_penghasilan_utama} = '1'",
                                        "title": "c. Komoditas utama dari sub sektor utama sebagian besar penduduk desa/kelurahan",
                                        "placeholder": "Tuliskan komoditas utama"
                                    }
                                ],
                                "title": "906 Sumber Penghasilan Utama"
                            },
                            {
                                "type": "panel",
                                "name": "panel_907",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_industri_mikro_kecil",
                                        "title": "a. Jumlah industri mikro dan kecil (memiliki tenaga kerja kurang dari 20 pekerja)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_sentra_industri",
                                        "title": "b. Jumlah Sentra Industri",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "produk_sentra_industri",
                                        "visibleIf": "{jumlah_sentra_industri} > 0",
                                        "title": "c. Jika terdapat sentra industri, tuliskan produk pada sentra industri yang mempunyai muatan usaha terbanyak",
                                        "placeholder": "Tuliskan produk unggulan"
                                    }
                                ],
                                "title": "907 Industri Mikro dan Kecil"
                            },
                            {
                                "type": "panel",
                                "name": "panel_908",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "keberadaan_produk_unggulan",
                                        "title": "a. Keberadaan produk barang unggulan/utama di desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "text",
                                        "name": "produk_unggulan_makanan",
                                        "visibleIf": "{keberadaan_produk_unggulan} = '1'",
                                        "title": "b. 1) Produk barang unggulan/utama desa/kelurahan - Makanan",
                                        "placeholder": "Tuliskan produk makanan unggulan"
                                    },
                                    {
                                        "type": "text",
                                        "name": "produk_unggulan_non_makanan",
                                        "visibleIf": "{keberadaan_produk_unggulan} = '1'",
                                        "title": "2) Produk barang unggulan/utama desa/kelurahan - Non Makanan",
                                        "placeholder": "Tuliskan produk non makanan unggulan"
                                    }
                                ],
                                "title": "908 Produk Unggulan Desa/Kelurahan"
                            }
                        ],
                        "title": "IX. EKONOMI"
                    },
                    {
                        "name": "page_10",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_1001",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "kejadian_perkelahian_massal",
                                        "title": "a. Kejadian perkelahian massal di desa/kelurahan selama setahun terakhir",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_perkelahian_detail",
                                        "elements": [
                                            {
                                                "type": "text",
                                                "name": "jumlah_perkelahian_massal",
                                                "title": "b. Jika ada kejadian perkelahian massal, jumlah perkelahian massal yang terjadi",
                                                "isRequired": true,
                                                "inputType": "number",
                                                "min": 1,
                                                "placeholder": "0"
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "korban_meninggal",
                                                "title": "c. 1) Keberadaan korban meninggal",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Ada"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Tidak ada"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "korban_luka",
                                                "title": "2) Keberadaan korban luka-luka",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Ada"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Tidak ada"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "checkbox",
                                                "name": "penyebab_perkelahian",
                                                "title": "d. Penyebab perkelahian (Pilihan boleh lebih dari satu)",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "A",
                                                        "text": "Harta"
                                                    },
                                                    {
                                                        "value": "B",
                                                        "text": "Kekuasaan"
                                                    },
                                                    {
                                                        "value": "C",
                                                        "text": "Asmara"
                                                    },
                                                    {
                                                        "value": "D",
                                                        "text": "Ideologi/kepercayaan"
                                                    },
                                                    {
                                                        "value": "E",
                                                        "text": "Keramaian (olah raga, hiburan, dll.)"
                                                    },
                                                    {
                                                        "value": "F",
                                                        "text": "Ketidakpuasan atas kebijakan/pelayanan"
                                                    }
                                                ]
                                            }
                                        ],
                                        "visibleIf": "{kejadian_perkelahian_massal} = '1'",
                                        "title": "Detail Perkelahian Massal"
                                    }
                                ],
                                "title": "1001 Perkelahian Massal"
                            },
                            {
                                "type": "checkbox",
                                "name": "upaya_penyelesaian_perkelahian",
                                "visibleIf": "{kejadian_perkelahian_massal} = '1'",
                                "title": "1002 Upaya penyelesaian perkelahian massal dilakukan oleh (Pilihan boleh lebih dari satu)",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "A",
                                        "text": "Aparat keamanan"
                                    },
                                    {
                                        "value": "B",
                                        "text": "Aparat pemerintah"
                                    },
                                    {
                                        "value": "C",
                                        "text": "Tokoh masyarakat"
                                    },
                                    {
                                        "value": "D",
                                        "text": "Tokoh agama"
                                    },
                                    {
                                        "value": "E",
                                        "text": "Lainnya"
                                    },
                                    {
                                        "value": "F",
                                        "text": "Tidak ada"
                                    }
                                ]
                            },
                            {
                                "type": "panel",
                                "name": "panel_1003",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "pos_keamanan",
                                        "title": "a. Pembangunan/pemeliharaan pos keamanan lingkungan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "regu_keamanan",
                                        "title": "b. Pembentukan/pengaturan regu keamanan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "penambahan_hansip",
                                        "title": "c. Penambahan jumlah anggota hansip/linmas",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pelaporan_tamu",
                                        "title": "d. Pelaporan tamu yang menginap lebih dari 24 jam ke aparat lingkungan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sistem_keamanan_inisiatif_warga",
                                        "title": "e. Pengaktifan sistem keamanan lingkungan berasal dari inisiatif warga",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    }
                                ],
                                "title": "1003 Kegiatan warga desa/kelurahan untuk menjaga keamanan lingkungan di desa/kelurahan selama setahun terakhir"
                            }
                        ],
                        "title": "X. KEAMANAN"
                    },
                    {
                        "name": "page_11",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_1101",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "sistem_informasi_desa",
                                        "title": "a. Keberadaan sistem informasi desa",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada, diperbaharui"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Ada, tidak diperbaharui"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_update_sid",
                                        "elements": [
                                            {
                                                "type": "dropdown",
                                                "name": "bulan_update_sid",
                                                "title": "Bulan",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Januari"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Februari"
                                                    },
                                                    {
                                                        "value": "3",
                                                        "text": "Maret"
                                                    },
                                                    {
                                                        "value": "4",
                                                        "text": "April"
                                                    },
                                                    {
                                                        "value": "5",
                                                        "text": "Mei"
                                                    },
                                                    {
                                                        "value": "6",
                                                        "text": "Juni"
                                                    },
                                                    {
                                                        "value": "7",
                                                        "text": "Juli"
                                                    },
                                                    {
                                                        "value": "8",
                                                        "text": "Agustus"
                                                    },
                                                    {
                                                        "value": "9",
                                                        "text": "September"
                                                    },
                                                    {
                                                        "value": "10",
                                                        "text": "Oktober"
                                                    },
                                                    {
                                                        "value": "11",
                                                        "text": "November"
                                                    },
                                                    {
                                                        "value": "12",
                                                        "text": "Desember"
                                                    }
                                                ],
                                                "placeholder": "Pilih bulan"
                                            },
                                            {
                                                "type": "text",
                                                "name": "tahun_update_sid",
                                                "title": "Tahun",
                                                "isRequired": true,
                                                "inputType": "number",
                                                "min": 2000,
                                                "max": 2025,
                                                "placeholder": "2025"
                                            }
                                        ],
                                        "visibleIf": "{sistem_informasi_desa} = '1' or {sistem_informasi_desa} = '2'",
                                        "title": "b. Jika ada, kapan terakhir diperbaharui"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sistem_keuangan_desa",
                                        "title": "c. Penggunaan sistem keuangan desa",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada, diperbaharui"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Ada, tidak diperbaharui"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "visibleIf": "{status_pemerintahan} = '1' or {status_pemerintahan} = '3' or {status_pemerintahan} = '4'",
                                "title": "1101 Sistem Informasi dan Keuangan Desa"
                            },
                            {
                                "type": "panel",
                                "name": "panel_1102",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_bumdes",
                                        "title": "a. Jumlah unit usaha BUMDes",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "tanah_kas_desa",
                                        "title": "b. Tanah kas desa/ulayat",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "tambatan_perahu",
                                        "title": "c. Tambatan perahu",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pasar_desa",
                                        "title": "d. Pasar (pasar desa, pasar hewan, pelelangan ikan yang dikelola desa, pelelangan hasil pertanian)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "bangunan_milik_desa",
                                        "title": "e. Bangunan milik desa (balai desa, balai rakyat, lapangan olah raga, dll)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "hutan_milik_desa",
                                        "title": "f. Hutan milik desa",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "mata_air_milik_desa",
                                        "title": "g. Mata air milik desa",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "tempat_wisata",
                                        "title": "h. Tempat wisata/Pemandian umum",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "aset_desa_lainnya",
                                        "title": "i. Aset desa lainnya (kekayaan asli desa lainnya, hibah/sumbangan/sejenisnya dll)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "visibleIf": "{status_pemerintahan} = '1' or {status_pemerintahan} = '3' or {status_pemerintahan} = '4'",
                                "title": "1102 Kepemilikan Badan Usaha dan Aset Desa"
                            },
                            {
                                "type": "text",
                                "name": "jumlah_peraturan_desa",
                                "visibleIf": "{status_pemerintahan} = '1' or {status_pemerintahan} = '3' or {status_pemerintahan} = '4'",
                                "title": "1103 Jumlah peraturan desa tahun 2024",
                                "isRequired": true,
                                "inputType": "number",
                                "min": 0,
                                "placeholder": "0"
                            }
                        ],
                        "title": "XI. KEUANGAN DAN ASET DESA",
                        "description": "Blok ini akan terisi jika Blok III R301, status pemerintahannya adalah Desa atau UPT/SPT atau Nagari"
                    },
                    {
                        "name": "page_12",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "keberadaan_kades_sekdes",
                                "title": "1201 Keberadaan kepala desa/lurah dan sekretaris kepala desa/lurah",
                                "defaultValue": [
                                    {
                                        "jabatan": "a. Kepala Desa/Lurah"
                                    },
                                    {
                                        "jabatan": "b. Sekretaris Desa/Sekretaris Kelurahan"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jabatan",
                                        "title": "Pemerintah desa/kelurahan",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "180px"
                                    },
                                    {
                                        "name": "keberadaan",
                                        "title": "Keberadaan",
                                        "cellType": "dropdown",
                                        "isRequired": true,
                                        "width": "100px",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "umur",
                                        "title": "Umur (tahun)",
                                        "cellType": "text",
                                        "width": "90px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jabatan ada, umur harus diisi",
                                                "expression": "{row.keberadaan} = '2' or ({row.keberadaan} = '1' and {row.umur} > 0)"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "max": 120,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jenis_kelamin",
                                        "title": "Jenis kelamin",
                                        "cellType": "dropdown",
                                        "width": "120px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jabatan ada, jenis kelamin harus dipilih",
                                                "expression": "{row.keberadaan} = '2' or ({row.keberadaan} = '1' and {row.jenis_kelamin} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Laki-laki"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Perempuan"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    },
                                    {
                                        "name": "pendidikan",
                                        "title": "Pendidikan tertinggi yang ditamatkan",
                                        "cellType": "dropdown",
                                        "width": "180px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jabatan ada, pendidikan harus dipilih",
                                                "expression": "{row.keberadaan} = '2' or ({row.keberadaan} = '1' and {row.pendidikan} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Tidak pernah sekolah"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak tamat SD/Sederajat"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tamat SD/Sederajat"
                                            },
                                            {
                                                "value": "4",
                                                "text": "SMP/Sederajat"
                                            },
                                            {
                                                "value": "5",
                                                "text": "SMU/Sederajat"
                                            },
                                            {
                                                "value": "6",
                                                "text": "Akademi/DIII"
                                            },
                                            {
                                                "value": "7",
                                                "text": "Diploma IV/S1"
                                            },
                                            {
                                                "value": "8",
                                                "text": "S2"
                                            },
                                            {
                                                "value": "9",
                                                "text": "S3"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false
                            },
                            {
                                "type": "html",
                                "name": "keterangan_pendidikan",
                                "html": "<p><small><strong>Kode Pendidikan:</strong> 1=Tidak pernah sekolah, 2=Tidak tamat SD, 3=Tamat SD, 4=SMP, 5=SMU, 6=Akademi/DIII, 7=Diploma IV/S1, 8=S2, 9=S3</small></p>"
                            },
                            {
                                "type": "panel",
                                "name": "panel_1202",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_sekretariat",
                                        "title": "a. Sekretariat Desa/Kelurahan (kaur, kasi, dll.)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_pelaksana_teknis",
                                        "title": "b. Pelaksana Teknis (kasi kesejahteraan, kasi pelayanan, dll.)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_pelaksana_kewilayahan",
                                        "title": "c. Pelaksana Kewilayahan (kadus, ketua RW, ketua RT, dll.)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_pegawai_lainnya",
                                        "title": "d. Pegawai Desa/Kelurahan lainnya (staf administrasi, tenaga kontrak, dll.)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "title": "1202 Jumlah aparatur pemerintahan desa/kelurahan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_1203",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "keberadaan_bpd_lmk",
                                        "title": "a. Badan Permusyawaratan Desa/Lembaga Musyawarah Kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "anggota_perempuan_bpd",
                                        "visibleIf": "{keberadaan_bpd_lmk} = '1'",
                                        "title": "b. Jika ada, apakah ada anggota yang perempuan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_musyawarah_desa",
                                        "title": "c. Jumlah kegiatan musyawarah desa/kelurahan yang dilakukan selama tahun 2024",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "title": "1203 Badan Permusyawaratan Desa/Lembaga Musyawarah Kelurahan"
                            },
                            {
                                "type": "html",
                                "name": "header_catatan",
                                "html": "<h3>XIII. CATATAN</h3>"
                            },
                            {
                                "type": "comment",
                                "name": "catatan",
                                "title": "Catatan petugas/pengawas",
                                "rows": 5,
                                "placeholder": "Tuliskan catatan penting terkait pencacahan..."
                            }
                        ],
                        "title": "XII. KETERANGAN APARATUR PEMERINTAHAN DESA/KELURAHAN"
                    }
                ],
                "showQuestionNumbers": "onPage"
            },
            "source_json": {
                "title": "Pemutakhiran Data Perkembangan Desa 2025 (PODES2025-DESA)",
                "description": "Daftar ini diisi oleh petugas berdasarkan hasil pencacahan/wawancara dengan narasumber terkait yang berwenang dan relevan, serta penelusuran dokumen desa/kelurahan",
                "logoPosition": "right",
                "completedHtml": "<h3>Terima kasih! Data telah tersimpan.</h3>",
                "pages": [
                    {
                        "name": "page1",
                        "elements": [
                            {
                                "type": "dropdown",
                                "name": "provinsi",
                                "title": "101 Provinsi",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "32",
                                        "text": "[32] JAWA BARAT"
                                    }
                                ]
                            },
                            {
                                "type": "dropdown",
                                "name": "kabupaten_kota",
                                "title": "102 Kabupaten/Kota",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "15",
                                        "text": "[15] KARAWANG"
                                    }
                                ],
                                "textWrapEnabled": false
                            },
                            {
                                "type": "dropdown",
                                "name": "kecamatan",
                                "title": "103 Kecamatan",
                                "isRequired": true,
                                "choicesByUrl": {
                                    "url": "http://127.0.0.1:9001/api/meta/wilayah/kecamatan?kdprov={provinsi}&kdkab={kabupaten_kota}",
                                    "valueName": "value",
                                    "titleName": "text",
                                    "allowEmptyResponse": true
                                }
                            },
                            {
                                "type": "text",
                                "name": "desa_kelurahan",
                                "title": "104 Desa/Kelurahan",
                                "isRequired": true
                            },
                            {
                                "type": "radiogroup",
                                "name": "status_daerah",
                                "title": "105 Status Daerah",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Perkotaan"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Perdesaan"
                                    }
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "sk_pembentukan",
                                "title": "106 SK pembentukan/pengesahan desa/kelurahan",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Permendagri/Kepmendagri"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Perda Provinsi"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Perda Kabupaten"
                                    },
                                    {
                                        "value": "4",
                                        "text": "SK Gubernur/Bupati"
                                    },
                                    {
                                        "value": "5",
                                        "text": "Lainnya"
                                    }
                                ]
                            },
                            {
                                "type": "panel",
                                "name": "panel_status_definitif",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "batas_wilayah_jelas",
                                        "title": "a. Ada wilayah desa/kelurahan dengan batas yang jelas",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "penduduk_menetap",
                                        "title": "b. Ada penduduk yang menetap di wilayah desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pemerintah_desa",
                                        "title": "c. Ada pemerintah desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    }
                                ],
                                "title": "107 Status definitif desa dan operasional desa/kelurahan"
                            }
                        ],
                        "title": "I. KETERANGAN TEMPAT"
                    },
                    {
                        "name": "page2",
                        "elements": [
                            {
                                "type": "html",
                                "name": "header_petugas",
                                "html": "<h3>II. KETERANGAN PETUGAS</h3>"
                            },
                            {
                                "type": "panel",
                                "name": "panel_201_203",
                                "elements": [
                                    {
                                        "type": "panel",
                                        "name": "panel_kunjungan_1",
                                        "elements": [
                                            {
                                                "type": "text",
                                                "name": "kunjungan1_tanggal",
                                                "title": "201 Tanggal Kunjungan",
                                                "isRequired": true,
                                                "inputType": "date",
                                                "placeholder": "dd/mm/yyyy"
                                            },
                                            {
                                                "type": "text",
                                                "name": "kunjungan1_no",
                                                "startWithNewLine": false,
                                                "title": "No. Kunjungan",
                                                "placeholder": "No"
                                            }
                                        ],
                                        "title": "Kunjungan Pertama"
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_kunjungan_2",
                                        "elements": [
                                            {
                                                "type": "text",
                                                "name": "kunjungan2_tanggal",
                                                "title": "203 Tanggal Kunjungan",
                                                "inputType": "date",
                                                "placeholder": "dd/mm/yyyy"
                                            },
                                            {
                                                "type": "text",
                                                "name": "kunjungan2_no",
                                                "startWithNewLine": false,
                                                "title": "No. Kunjungan",
                                                "placeholder": "No"
                                            }
                                        ],
                                        "title": "Kunjungan Kedua (Jika Ada)"
                                    }
                                ],
                                "title": "Kunjungan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_pengawas",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "nama_pengawas",
                                        "title": "204 Nama Pengawas/Pemeriksa",
                                        "isRequired": true,
                                        "placeholder": "Nama lengkap pengawas/pemeriksa"
                                    },
                                    {
                                        "type": "text",
                                        "name": "tanggal_pemeriksaan",
                                        "title": "206 Tanggal Pemeriksaan",
                                        "isRequired": true,
                                        "inputType": "date",
                                        "placeholder": "dd/mm/yyyy"
                                    }
                                ],
                                "title": "Pemeriksaan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_pengesahan",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "tempat_pengesahan",
                                        "title": "Tempat",
                                        "placeholder": "Nama desa/kelurahan"
                                    },
                                    {
                                        "type": "text",
                                        "name": "tanggal_pengesahan",
                                        "title": "Tanggal",
                                        "inputType": "date",
                                        "placeholder": "dd/mm/yyyy"
                                    },
                                    {
                                        "type": "text",
                                        "name": "nama_kepala_desa",
                                        "title": "Mengetahui Kepala Desa/Lurah",
                                        "placeholder": "Nama lengkap dan tanda tangan/cap"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "status_desa_kelurahan_coret",
                                        "title": "*) Coret yang tidak sesuai",
                                        "choices": [
                                            {
                                                "value": "desa",
                                                "text": "Desa"
                                            },
                                            {
                                                "value": "kelurahan",
                                                "text": "Kelurahan"
                                            }
                                        ]
                                    }
                                ],
                                "title": "Pengesahan"
                            },
                            {
                                "type": "html",
                                "name": "footer_petugas",
                                "html": "<p><em>DAFTAR INI DIISI OLEH PETUGAS BERDASARKAN HASIL PENCACAHAN/WAWANCARA DENGAN NARASUMBER TERKAIT YANG BERWENANG DAN RELEVAN, SERTA PENELUSURAN DOKUMEN DESA/KELURAHAN</em></p>"
                            }
                        ],
                        "title": "II. KETERANGAN PETUGAS"
                    },
                    {
                        "name": "page3",
                        "elements": [
                            {
                                "type": "radiogroup",
                                "name": "status_pemerintahan",
                                "title": "301 Status pemerintahan",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Desa"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Kelurahan"
                                    },
                                    {
                                        "value": "3",
                                        "text": "UPT/SPT"
                                    },
                                    {
                                        "value": "4",
                                        "text": "Nagari"
                                    }
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "berbatasan_laut",
                                "title": "302 a. Ada wilayah desa/kelurahan yang berbatasan langsung dengan laut",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Ada"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Tidak ada"
                                    }
                                ]
                            },
                            {
                                "type": "panel",
                                "name": "panel_pemanfaatan_laut",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "perikanan_tangkap",
                                        "title": "1) Perikanan tangkap (mencakup seluruh biota laut)",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "perikanan_budidaya",
                                        "title": "2) Perikanan budidaya (mencakup seluruh biota laut)",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "tambak_garam",
                                        "title": "3) Tambak garam",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "wisata_bahari",
                                        "title": "4) Wisata bahari",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "transportasi_umum",
                                        "title": "5) Transportasi umum",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "visibleIf": "{berbatasan_laut} = '1'",
                                "title": "b. Jika berbatasan dengan laut, pemanfaatan laut"
                            },
                            {
                                "type": "radiogroup",
                                "name": "lokasi_terhadap_hutan",
                                "title": "303 a. Lokasi wilayah desa/kelurahan terhadap kawasan hutan/hutan",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Di dalam kawasan hutan"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Di tepi/sekitar kawasan hutan"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Di luar kawasan hutan"
                                    }
                                ]
                            },
                            {
                                "type": "checkbox",
                                "name": "fungsi_kawasan_hutan",
                                "visibleIf": "{lokasi_terhadap_hutan} <> '3'",
                                "title": "b. Fungsi kawasan hutan/hutan (Pilihan boleh lebih dari satu)",
                                "choices": [
                                    {
                                        "value": "A",
                                        "text": "Konservasi"
                                    },
                                    {
                                        "value": "B",
                                        "text": "Lindung"
                                    },
                                    {
                                        "value": "C",
                                        "text": "Produksi"
                                    }
                                ]
                            },
                            {
                                "type": "text",
                                "name": "jumlah_rw",
                                "title": "304 a. Jumlah Rukun Warga (RW) di desa/kelurahan",
                                "inputType": "number",
                                "min": 0
                            },
                            {
                                "type": "text",
                                "name": "jumlah_rt",
                                "title": "b. Jumlah Rukun Tetangga (RT) di desa/kelurahan",
                                "inputType": "number",
                                "min": 0
                            }
                        ],
                        "title": "III. KETERANGAN UMUM DESA/KELURAHAN"
                    },
                    {
                        "name": "page4",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_penduduk",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "penduduk_laki",
                                        "title": "a. Jumlah penduduk laki-laki",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "penduduk_perempuan",
                                        "title": "b. Jumlah penduduk perempuan",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_keluarga",
                                        "title": "c. Jumlah keluarga",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "keluarga_pertanian",
                                        "title": "d. Jumlah keluarga pertanian",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0
                                    }
                                ],
                                "title": "401 Penduduk dan keluarga pada 1 Januari 2025"
                            }
                        ],
                        "title": "IV. KEPENDUDUKAN"
                    },
                    {
                        "name": "page5",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_listrik",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "listrik_pln",
                                        "title": "1. Jumlah keluarga pengguna listrik PLN",
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "listrik_non_pln",
                                        "title": "2. Jumlah keluarga pengguna listrik Non-PLN",
                                        "inputType": "number",
                                        "min": 0
                                    },
                                    {
                                        "type": "text",
                                        "name": "bukan_pengguna_listrik",
                                        "title": "b. Jumlah keluarga bukan pengguna listrik",
                                        "inputType": "number",
                                        "min": 0
                                    }
                                ],
                                "title": "501 Keluarga pengguna listrik"
                            },
                            {
                                "type": "radiogroup",
                                "name": "penerangan_jalan",
                                "title": "502 a. Penerangan di jalan utama desa/kelurahan",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Ada, sebagian besar"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Ada, sebagian kecil"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Tidak ada"
                                    }
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "sumber_penerangan",
                                "visibleIf": "{penerangan_jalan} <> '3'",
                                "title": "b. Sumber penerangan di jalan utama desa/kelurahan",
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Listrik diusahakan oleh pemerintah"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Listrik diusahakan oleh non pemerintah"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Non listrik"
                                    }
                                ]
                            },
                            {
                                "type": "radiogroup",
                                "name": "bahan_bakar_memasak",
                                "title": "503 Bahan bakar untuk memasak sebagian besar keluarga",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "1",
                                        "text": "Listrik"
                                    },
                                    {
                                        "value": "2",
                                        "text": "Elpiji 5,5 kg"
                                    },
                                    {
                                        "value": "3",
                                        "text": "Elpiji 12 kg"
                                    },
                                    {
                                        "value": "4",
                                        "text": "Elpiji 3 kg"
                                    },
                                    {
                                        "value": "5",
                                        "text": "Gas kota"
                                    },
                                    {
                                        "value": "6",
                                        "text": "Biogas"
                                    },
                                    {
                                        "value": "7",
                                        "text": "Minyak tanah"
                                    },
                                    {
                                        "value": "8",
                                        "text": "Briket"
                                    },
                                    {
                                        "value": "9",
                                        "text": "Arang"
                                    },
                                    {
                                        "value": "10",
                                        "text": "Kayu bakar"
                                    },
                                    {
                                        "value": "11",
                                        "text": "Lainnya"
                                    }
                                ]
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "pencemaran_lingkungan",
                                "title": "504 Pencemaran lingkungan hidup (polusi) selama setahun terakhir",
                                "defaultValue": [
                                    {
                                        "jenis_pencemaran": "Air"
                                    },
                                    {
                                        "jenis_pencemaran": "Tanah"
                                    },
                                    {
                                        "jenis_pencemaran": "Udara"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_pencemaran",
                                        "title": "Jenis Pencemaran",
                                        "cellType": "text",
                                        "readOnly": true
                                    },
                                    {
                                        "name": "kejadian",
                                        "title": "Kejadian",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "sumber_utama",
                                        "title": "Sumber pencemaran utama",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Rumah tangga"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Pabrik/industri/usaha"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Lainnya"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "pengaduan",
                                        "title": "Pengaduan warga ke aparat",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "rowCount": 3
                            }
                        ],
                        "title": "V. PERUMAHAN DAN LINGKUNGAN HIDUP"
                    },
                    {
                        "name": "page6",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "bencana_alam",
                                "title": "601 Kejadian/bencana alam",
                                "defaultValue": [
                                    {
                                        "jenis_bencana": "a. Tanah longsor"
                                    },
                                    {
                                        "jenis_bencana": "b. Banjir"
                                    },
                                    {
                                        "jenis_bencana": "c. Banjir bandang"
                                    },
                                    {
                                        "jenis_bencana": "d. Gempa bumi"
                                    },
                                    {
                                        "jenis_bencana": "e. Tsunami"
                                    },
                                    {
                                        "jenis_bencana": "f. Gelombang pasang laut"
                                    },
                                    {
                                        "jenis_bencana": "g. Angin puyuh/puting beliung/topan"
                                    },
                                    {
                                        "jenis_bencana": "h. Letusan gunung api"
                                    },
                                    {
                                        "jenis_bencana": "i. Kebakaran hutan dan lahan"
                                    },
                                    {
                                        "jenis_bencana": "j. Kekeringan"
                                    },
                                    {
                                        "jenis_bencana": "k. Abrasi"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_bencana",
                                        "title": "Jenis Bencana",
                                        "cellType": "text",
                                        "readOnly": true
                                    },
                                    {
                                        "name": "kejadian_2024",
                                        "title": "Tahun 2024 (Ada/Tidak)",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "korban_2024",
                                        "title": "Korban 2024",
                                        "cellType": "checkbox",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Hilang"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Luka/sakit"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Mengungsi"
                                            },
                                            {
                                                "value": "D",
                                                "text": "Tidak ada korban"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "kejadian_2025",
                                        "title": "Jan-Mei 2025 (Ada/Tidak)",
                                        "cellType": "dropdown",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "korban_2025",
                                        "title": "Korban 2025",
                                        "cellType": "checkbox",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Hilang"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Luka/sakit"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Mengungsi"
                                            },
                                            {
                                                "value": "D",
                                                "text": "Tidak ada korban"
                                            }
                                        ]
                                    }
                                ],
                                "allowRemoveRows": false,
                                "rowCount": 11
                            },
                            {
                                "type": "panel",
                                "name": "panel_mitigasi",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "sistem_peringatan_dini",
                                        "title": "a. Sistem peringatan dini bencana alam",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "peringatan_dini_tsunami",
                                        "title": "b. Sistem peringatan dini khusus tsunami",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Bukan wilayah potensi tsunami"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "perlengkapan_keselamatan",
                                        "title": "c. Perlengkapan keselamatan (perahu karet, tenda, masker, dll.)",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "rambu_evakuasi",
                                        "title": "d. Rambu-rambu dan jalur evakuasi bencana",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "normalisasi_sungai",
                                        "title": "e. Pembuatan, perawatan, atau normalisasi: sungai, kanal, tanggul, dll.",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "602 Fasilitas/upaya antisipasi/mitigasi bencana alam"
                            }
                        ],
                        "title": "VI. BENCANA ALAM DAN MITIGASI BENCANA ALAM"
                    },
                    {
                        "name": "page_7",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "fasilitas_pendidikan",
                                "title": "701 Keberadaan fasilitas pendidikan menurut jenjang pendidikan di desa/kelurahan",
                                "description": "Isi jumlah fasilitas pendidikan. Jika tidak ada (0), isi jarak dan kemudahan menuju fasilitas terdekat.",
                                "defaultValue": [
                                    {
                                        "jenis_fasilitas": "a. TK (Taman Kanak-Kanak)"
                                    },
                                    {
                                        "jenis_fasilitas": "b. RA/BA (Raudhatul Athfal/Bustanul Athfal)"
                                    },
                                    {
                                        "jenis_fasilitas": "c. SD (Sekolah Dasar)"
                                    },
                                    {
                                        "jenis_fasilitas": "d. MI (Madrasah Ibtidaiyah)"
                                    },
                                    {
                                        "jenis_fasilitas": "e. SMP (Sekolah Menengah Pertama)"
                                    },
                                    {
                                        "jenis_fasilitas": "f. MTs (Madrasah Tsanawiyah)"
                                    },
                                    {
                                        "jenis_fasilitas": "g. SMA (Sekolah Menengah Atas)"
                                    },
                                    {
                                        "jenis_fasilitas": "h. MA (Madrasah Aliyah)"
                                    },
                                    {
                                        "jenis_fasilitas": "i. SMK (Sekolah Menengah Kejuruan)"
                                    },
                                    {
                                        "jenis_fasilitas": "j. Akademi/Perguruan Tinggi"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_fasilitas",
                                        "title": "Jenis fasilitas pendidikan",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "200px"
                                    },
                                    {
                                        "name": "jumlah_negeri",
                                        "title": "Negeri",
                                        "cellType": "text",
                                        "width": "80px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jumlah_swasta",
                                        "title": "Swasta",
                                        "cellType": "text",
                                        "width": "80px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jarak",
                                        "title": "Jarak (km)",
                                        "cellType": "text",
                                        "width": "90px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah Negeri dan Swasta 0, jarak harus diisi",
                                                "expression": "({row.jumlah_negeri} > 0 or {row.jumlah_swasta} > 0) or ({row.jumlah_negeri} == 0 and {row.jumlah_swasta} == 0 and {row.jarak} > 0)"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    },
                                    {
                                        "name": "kemudahan",
                                        "title": "Kemudahan untuk mencapai",
                                        "cellType": "dropdown",
                                        "width": "150px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah Negeri dan Swasta 0, kemudahan harus dipilih",
                                                "expression": "({row.jumlah_negeri} > 0 or {row.jumlah_swasta} > 0) or ({row.jumlah_negeri} == 0 and {row.jumlah_swasta} == 0 and {row.kemudahan} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sangat mudah"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Mudah"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Sulit"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Sangat sulit"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false,
                                "rowCount": 10
                            },
                            {
                                "type": "html",
                                "name": "header_kesehatan",
                                "html": "<h3>VII. KESEHATAN</h3>"
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "fasilitas_kesehatan",
                                "title": "702 Keberadaan fasilitas pelayanan kesehatan di desa/kelurahan",
                                "description": "Isi jumlah fasilitas kesehatan. Jika tidak ada (0), isi jarak dan kemudahan menuju fasilitas terdekat.",
                                "defaultValue": [
                                    {
                                        "jenis_fasilitas": "a. Rumah sakit"
                                    },
                                    {
                                        "jenis_fasilitas": "b. Klinik utama"
                                    },
                                    {
                                        "jenis_fasilitas": "c. Balai kesehatan"
                                    },
                                    {
                                        "jenis_fasilitas": "d. Puskesmas dengan rawat inap"
                                    },
                                    {
                                        "jenis_fasilitas": "e. Puskesmas tanpa rawat inap"
                                    },
                                    {
                                        "jenis_fasilitas": "f. Puskesmas pembantu"
                                    },
                                    {
                                        "jenis_fasilitas": "g. Klinik pratama"
                                    },
                                    {
                                        "jenis_fasilitas": "h. Praktik mandiri dokter"
                                    },
                                    {
                                        "jenis_fasilitas": "i. Praktik mandiri bidan"
                                    },
                                    {
                                        "jenis_fasilitas": "j. Poskesdes (pos kesehatan desa)"
                                    },
                                    {
                                        "jenis_fasilitas": "k. Polindes (pondok bersalin desa)"
                                    },
                                    {
                                        "jenis_fasilitas": "l. Apotek"
                                    },
                                    {
                                        "jenis_fasilitas": "m. Toko khusus obat/jamu"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_fasilitas",
                                        "title": "Jenis fasilitas pelayanan kesehatan",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "220px"
                                    },
                                    {
                                        "name": "jumlah",
                                        "title": "Jumlah",
                                        "cellType": "text",
                                        "width": "80px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jarak",
                                        "title": "Jarak (km)",
                                        "cellType": "text",
                                        "width": "90px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah = 0, jarak harus diisi",
                                                "expression": "{row.jumlah} > 0 or ({row.jumlah} == 0 and {row.jarak} > 0)"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    },
                                    {
                                        "name": "kemudahan",
                                        "title": "Kemudahan untuk mencapai",
                                        "cellType": "dropdown",
                                        "width": "150px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah = 0, kemudahan harus dipilih",
                                                "expression": "{row.jumlah} > 0 or ({row.jumlah} == 0 and {row.kemudahan} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sangat mudah"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Mudah"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Sulit"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Sangat sulit"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false,
                                "rowCount": 13
                            },
                            {
                                "type": "panel",
                                "name": "panel_703",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_posyandu_aktif",
                                        "title": "a. Jumlah posyandu aktif",
                                        "description": "unit",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "posyandu_sebulan_sekali",
                                        "title": "b. Posyandu dengan kegiatan/pelayanan setiap sebulan sekali",
                                        "description": "unit",
                                        "isRequired": true,
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jumlah tidak boleh melebihi total posyandu aktif",
                                                "expression": "{posyandu_sebulan_sekali} <= {jumlah_posyandu_aktif}"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "posyandu_dua_bulan_atau_lebih",
                                        "title": "c. Posyandu dengan kegiatan/pelayanan setiap 2 bulan sekali atau lebih",
                                        "description": "unit",
                                        "isRequired": true,
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jumlah tidak boleh melebihi total posyandu aktif",
                                                "expression": "{posyandu_dua_bulan_atau_lebih} <= {jumlah_posyandu_aktif}"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_posbindu",
                                        "title": "d. Pos Pembinaan Terpadu (Posbindu)",
                                        "description": "unit",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "title": "703 Jumlah Upaya Kesehatan Bersumberdaya Masyarakat (UKBM) selama setahun terakhir"
                            }
                        ],
                        "title": "VII. PENDIDIKAN DAN KESEHATAN"
                    },
                    {
                        "name": "page_8",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_801",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "lalu_lintas_melalui",
                                        "title": "a. Lalu lintas dari/ke desa/kelurahan melalui",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Darat"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Air"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Darat dan air"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Udara"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_transportasi_darat",
                                        "elements": [
                                            {
                                                "type": "radiogroup",
                                                "name": "jenis_permukaan_jalan",
                                                "title": "1) Jenis permukaan jalan darat antar desa/kelurahan yang terluas",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Aspal/beton"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Diperkeras (kerikil, batu, dll.)"
                                                    },
                                                    {
                                                        "value": "3",
                                                        "text": "Tanah"
                                                    },
                                                    {
                                                        "value": "4",
                                                        "text": "Lainnya (jalan setapak, kayu/papan, dll)"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "dapat_dilalui_kendaraan",
                                                "title": "2) Jalan darat antar desa/kelurahan dapat dilalui kendaraan bermotor roda 4 atau lebih",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Sepanjang tahun"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Sepanjang tahun kecuali saat tertentu (ketika turun hujan, pasang, dll.)"
                                                    },
                                                    {
                                                        "value": "3",
                                                        "text": "Selama musim kemarau"
                                                    },
                                                    {
                                                        "value": "4",
                                                        "text": "Tidak dapat dilalui sepanjang tahun"
                                                    }
                                                ]
                                            }
                                        ],
                                        "visibleIf": "{lalu_lintas_melalui} = '1' or {lalu_lintas_melalui} = '3'",
                                        "title": "b. Jika lalu lintas melalui darat atau darat dan air"
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_angkutan_umum",
                                        "elements": [
                                            {
                                                "type": "checkbox",
                                                "name": "keberadaan_angkutan_umum",
                                                "title": "1) Keberadaan angkutan umum (Pilihan boleh lebih dari satu)",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "A",
                                                        "text": "Ada, dengan trayek tetap"
                                                    },
                                                    {
                                                        "value": "B",
                                                        "text": "Ada, tanpa trayek tetap"
                                                    },
                                                    {
                                                        "value": "X",
                                                        "text": "Tidak ada angkutan umum"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "operasional_angkutan",
                                                "visibleIf": "{keberadaan_angkutan_umum} contains 'A' or {keberadaan_angkutan_umum} contains 'B'",
                                                "title": "2) Operasional angkutan umum yang utama",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Setiap hari"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Tidak setiap hari"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "jam_operasi_angkutan",
                                                "visibleIf": "{keberadaan_angkutan_umum} contains 'A' or {keberadaan_angkutan_umum} contains 'B'",
                                                "title": "3) Jam operasi angkutan umum yang utama",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Siang dan malam hari"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Hanya siang/malam hari"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "ketersediaan_angkutan_online",
                                                "title": "4) Ketersediaan angkutan online (memesan angkutan online)",
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Ada"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Tidak ada"
                                                    }
                                                ]
                                            }
                                        ],
                                        "title": "c. Angkutan umum yang melewati desa/kelurahan"
                                    }
                                ],
                                "title": "801 Prasarana dan sarana transportasi antar desa/kelurahan"
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "sarana_transportasi_kantor",
                                "title": "802 Sarana transportasi dari kantor kepala desa/lurah ke kantor camat/bupati/walikota",
                                "defaultValue": [
                                    {
                                        "tujuan": "a. Kantor camat"
                                    },
                                    {
                                        "tujuan": "b. Kantor bupati/walikota"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "tujuan",
                                        "title": "Tujuan",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "120px"
                                    },
                                    {
                                        "name": "sarana_biasa",
                                        "title": "Sarana transportasi yang biasa digunakan",
                                        "cellType": "checkbox",
                                        "width": "250px",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Angkutan umum"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Kendaraan pribadi"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Jalan kaki, sepeda, dll."
                                            }
                                        ]
                                    },
                                    {
                                        "name": "jenis_angkutan",
                                        "title": "Jenis angkutan umum",
                                        "cellType": "checkbox",
                                        "width": "300px",
                                        "visibleIf": "{row.sarana_biasa} contains 'A'",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Ojek sepeda motor"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Kendaraan bermotor roda 3 atau lebih"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Perahu (bermotor/tidak bermotor)"
                                            },
                                            {
                                                "value": "D",
                                                "text": "Pesawat terbang"
                                            },
                                            {
                                                "value": "E",
                                                "text": "Lainnya (becak, delman, pedati, dll)"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "angkutan_utama",
                                        "title": "Angkutan umum yang utama",
                                        "cellType": "dropdown",
                                        "width": "150px",
                                        "visibleIf": "{row.sarana_biasa} contains 'A'",
                                        "choices": [
                                            {
                                                "value": "A",
                                                "text": "Ojek sepeda motor"
                                            },
                                            {
                                                "value": "B",
                                                "text": "Kendaraan bermotor roda 3 atau lebih"
                                            },
                                            {
                                                "value": "C",
                                                "text": "Perahu (bermotor/tidak bermotor)"
                                            },
                                            {
                                                "value": "D",
                                                "text": "Pesawat terbang"
                                            },
                                            {
                                                "value": "E",
                                                "text": "Lainnya (becak, delman, pedati, dll)"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    },
                                    {
                                        "name": "jarak_tempuh",
                                        "title": "Jarak tempuh (km)",
                                        "cellType": "text",
                                        "width": "100px",
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    },
                                    {
                                        "name": "waktu_tempuh_jam",
                                        "title": "Jam",
                                        "cellType": "text",
                                        "width": "60px",
                                        "inputType": "number",
                                        "min": 0,
                                        "max": 99,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "waktu_tempuh_menit",
                                        "title": "Menit",
                                        "cellType": "text",
                                        "width": "60px",
                                        "inputType": "number",
                                        "min": 0,
                                        "max": 59,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "biaya_transportasi",
                                        "title": "Biaya transportasi (Ribu Rupiah)",
                                        "cellType": "text",
                                        "width": "120px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false
                            },
                            {
                                "type": "panel",
                                "name": "panel_telepon",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "keluarga_telepon_kabel",
                                        "title": "a. Jumlah keluarga yang berlangganan telepon kabel",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pengguna_handphone",
                                        "title": "b. Keberadaan warga yang menggunakan telepon seluler/handphone",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sebagian besar warga"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Sebagian kecil warga"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "803 Telepon"
                            },
                            {
                                "type": "panel",
                                "name": "panel_menara_telepon",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_bts",
                                        "title": "a. Jumlah menara telepon seluler atau Base Transceiver Station (BTS)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_operator",
                                        "title": "b. Jumlah operator layanan komunikasi telepon seluler/handphone yang menjangkau di desa/kelurahan",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sinyal_telepon",
                                        "title": "c. Sinyal telepon seluler/handphone di sebagian besar wilayah desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sinyal sangat kuat"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Sinyal kuat"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Sinyal lemah"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Tidak ada sinyal"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sinyal_internet",
                                        "visibleIf": "{sinyal_telepon} <> '4'",
                                        "title": "d. Sinyal internet telepon seluler/handphone di sebagian besar wilayah di desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "5G/4G/LTE"
                                            },
                                            {
                                                "value": "2",
                                                "text": "3G/H/H+/EVDO"
                                            },
                                            {
                                                "value": "3",
                                                "text": "2,5G/E/GPRS"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Tidak ada sinyal internet"
                                            }
                                        ]
                                    }
                                ],
                                "title": "804 Keberadaan menara telepon seluler, sinyal telepon dan sinyal internet di desa/kelurahan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_pos",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "kantor_pos",
                                        "title": "a. Kantor pos/pos pembantu/rumah pos",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Beroperasi"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Jarang beroperasi"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak beroperasi"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "layanan_pos_keliling",
                                        "title": "b. Layanan pos keliling",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "jasa_ekspedisi",
                                        "title": "c. Perusahaan/agen jasa ekspedisi (pengiriman barang/dokumen) swasta",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Beroperasi"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Jarang beroperasi"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak beroperasi"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "805 Pos dan Ekspedisi"
                            }
                        ],
                        "title": "VIII. ANGKUTAN, KOMUNIKASI DAN INFORMASI"
                    },
                    {
                        "name": "page_9",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_901",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "pangkalan_minyak_tanah",
                                        "title": "a. Keberadaan pangkalan/agen/penjual minyak tanah (termasuk penjual minyak tanah keliling)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pangkalan_lpg",
                                        "title": "b. Keberadaan pangkalan/agen/penjual LPG (warung, toko, supermarket, penjual gas keliling)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "901 Bahan Bakar"
                            },
                            {
                                "type": "panel",
                                "name": "panel_902",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "bank_pemerintah",
                                        "title": "a. 1) Jumlah Bank Umum Pemerintah (BRI, BNI, Mandiri, BPD, BTN) yang beroperasi",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "bank_swasta",
                                        "title": "2) Jumlah Bank Umum Swasta (BCA, Permata, Sinarmas, CIMB, dll) yang beroperasi",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "bpr",
                                        "title": "3) Jumlah Bank Perkreditan Rakyat (BPR) yang beroperasi",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jarak_bank_terdekat",
                                        "visibleIf": "{bank_pemerintah} = 0 and {bank_swasta} = 0 and {bpr} = 0",
                                        "title": "b. Jika tidak ada bank, perkiraan jarak ke bank terdekat (km)",
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    }
                                ],
                                "title": "902 Perbankan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_903",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "kud",
                                        "title": "a. Jumlah Koperasi Unit Desa (KUD) yang masih aktif",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "kopinkra",
                                        "title": "b. Jumlah Koperasi Industri Kecil dan Kerajinan Rakyat (Kopinkra)/Usaha mikro yang masih aktif",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "ksp",
                                        "title": "c. Jumlah Koperasi Simpan Pinjam (KSP/Kospin) yang masih aktif",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "koperasi_lainnya",
                                        "title": "d. Jumlah koperasi lainnya yang masih aktif",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "title": "903 Koperasi"
                            },
                            {
                                "type": "panel",
                                "name": "panel_904",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "kur",
                                        "title": "a. Kredit Usaha Rakyat (KUR)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "kpp_e",
                                        "title": "b. Kredit Ketahanan Pangan dan Energi (KPP-E)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "kuk",
                                        "title": "c. Kredit Usaha Kecil (KUK)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "kube",
                                        "title": "d. Kelompok Usaha Bersama (KUBE)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "title": "904 Fasilitas Kredit"
                            },
                            {
                                "type": "matrixdynamic",
                                "name": "fasilitas_ekonomi",
                                "title": "905 Jumlah fasilitas ekonomi di desa/kelurahan",
                                "description": "Isi jumlah fasilitas ekonomi. Jika tidak ada (0), isi jarak dan kemudahan menuju fasilitas terdekat.",
                                "defaultValue": [
                                    {
                                        "jenis_fasilitas": "a. Kelompok pertokoan (minimal 10 toko dan mengelompok dalam satu lokasi)"
                                    },
                                    {
                                        "jenis_fasilitas": "b. Pasar dengan bangunan permanen (memiliki atap, lantai, dan dinding)"
                                    },
                                    {
                                        "jenis_fasilitas": "c. Pasar dengan bangunan semi permanen (memiliki atap dan lantai, tanpa dinding)"
                                    },
                                    {
                                        "jenis_fasilitas": "d. Pasar tanpa bangunan (misalnya: pasar subuh, pasar terapung, dll.)"
                                    },
                                    {
                                        "jenis_fasilitas": "e. Minimarket/swalayan/supermarket"
                                    },
                                    {
                                        "jenis_fasilitas": "f. Restoran/rumah makan (usaha pangan siap saji di bangunan tetap, pembeli biasanya dikenai pajak)"
                                    },
                                    {
                                        "jenis_fasilitas": "g. Warung/kedai makanan minuman (usaha pangan siap saji di bangunan tetap, pembeli biasanya tidak dikenai pajak)"
                                    },
                                    {
                                        "jenis_fasilitas": "h. Hotel (menyediakan jasa akomodasi dan ada restoran, penginapan dengan izin usaha sebagai hotel)"
                                    },
                                    {
                                        "jenis_fasilitas": "i. Penginapan: hostel/motel/losmen/wisma"
                                    },
                                    {
                                        "jenis_fasilitas": "j. Toko/warung kelontong (tempat usaha di bangunan tetap untuk menjual berbagai jenis barang keperluan sehari-hari secara eceran, tanpa ada sistem pelayanan mandiri)"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jenis_fasilitas",
                                        "title": "Jenis fasilitas ekonomi",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "280px"
                                    },
                                    {
                                        "name": "jumlah",
                                        "title": "Jumlah",
                                        "cellType": "text",
                                        "width": "80px",
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jarak",
                                        "title": "Jarak (km)",
                                        "cellType": "text",
                                        "width": "90px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah = 0, jarak harus diisi",
                                                "expression": "{row.jumlah} > 0 or ({row.jumlah} == 0 and {row.jarak} > 0)"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "step": 0.1,
                                        "placeholder": "0.0"
                                    },
                                    {
                                        "name": "kemudahan",
                                        "title": "Kemudahan untuk mencapai",
                                        "cellType": "dropdown",
                                        "width": "150px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jumlah = 0, kemudahan harus dipilih",
                                                "expression": "{row.jumlah} > 0 or ({row.jumlah} == 0 and {row.kemudahan} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Sangat mudah"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Mudah"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Sulit"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Sangat sulit"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false,
                                "rowCount": 10
                            },
                            {
                                "type": "panel",
                                "name": "panel_906",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "sumber_penghasilan_utama",
                                        "title": "a. Sumber penghasilan utama sebagian besar penduduk desa/kelurahan berasal dari lapangan usaha",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Pertanian"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Industri"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Jasa"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sub_sektor_pertanian",
                                        "visibleIf": "{sumber_penghasilan_utama} = '1'",
                                        "title": "b. Jika sektor pertanian, jenis sub sektor utama sebagian besar penduduk",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Tanaman Pangan"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tanaman Hortikultura"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tanaman Perkebunan"
                                            },
                                            {
                                                "value": "4",
                                                "text": "Peternakan"
                                            },
                                            {
                                                "value": "5",
                                                "text": "Perikanan"
                                            },
                                            {
                                                "value": "6",
                                                "text": "Kehutanan"
                                            },
                                            {
                                                "value": "7",
                                                "text": "Jasa Pertanian"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "text",
                                        "name": "komoditas_utama",
                                        "visibleIf": "{sumber_penghasilan_utama} = '1'",
                                        "title": "c. Komoditas utama dari sub sektor utama sebagian besar penduduk desa/kelurahan",
                                        "placeholder": "Tuliskan komoditas utama"
                                    }
                                ],
                                "title": "906 Sumber Penghasilan Utama"
                            },
                            {
                                "type": "panel",
                                "name": "panel_907",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_industri_mikro_kecil",
                                        "title": "a. Jumlah industri mikro dan kecil (memiliki tenaga kerja kurang dari 20 pekerja)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_sentra_industri",
                                        "title": "b. Jumlah Sentra Industri",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "produk_sentra_industri",
                                        "visibleIf": "{jumlah_sentra_industri} > 0",
                                        "title": "c. Jika terdapat sentra industri, tuliskan produk pada sentra industri yang mempunyai muatan usaha terbanyak",
                                        "placeholder": "Tuliskan produk unggulan"
                                    }
                                ],
                                "title": "907 Industri Mikro dan Kecil"
                            },
                            {
                                "type": "panel",
                                "name": "panel_908",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "keberadaan_produk_unggulan",
                                        "title": "a. Keberadaan produk barang unggulan/utama di desa/kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "text",
                                        "name": "produk_unggulan_makanan",
                                        "visibleIf": "{keberadaan_produk_unggulan} = '1'",
                                        "title": "b. 1) Produk barang unggulan/utama desa/kelurahan - Makanan",
                                        "placeholder": "Tuliskan produk makanan unggulan"
                                    },
                                    {
                                        "type": "text",
                                        "name": "produk_unggulan_non_makanan",
                                        "visibleIf": "{keberadaan_produk_unggulan} = '1'",
                                        "title": "2) Produk barang unggulan/utama desa/kelurahan - Non Makanan",
                                        "placeholder": "Tuliskan produk non makanan unggulan"
                                    }
                                ],
                                "title": "908 Produk Unggulan Desa/Kelurahan"
                            }
                        ],
                        "title": "IX. EKONOMI"
                    },
                    {
                        "name": "page_10",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_1001",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "kejadian_perkelahian_massal",
                                        "title": "a. Kejadian perkelahian massal di desa/kelurahan selama setahun terakhir",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_perkelahian_detail",
                                        "elements": [
                                            {
                                                "type": "text",
                                                "name": "jumlah_perkelahian_massal",
                                                "title": "b. Jika ada kejadian perkelahian massal, jumlah perkelahian massal yang terjadi",
                                                "isRequired": true,
                                                "inputType": "number",
                                                "min": 1,
                                                "placeholder": "0"
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "korban_meninggal",
                                                "title": "c. 1) Keberadaan korban meninggal",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Ada"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Tidak ada"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "radiogroup",
                                                "name": "korban_luka",
                                                "title": "2) Keberadaan korban luka-luka",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Ada"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Tidak ada"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "checkbox",
                                                "name": "penyebab_perkelahian",
                                                "title": "d. Penyebab perkelahian (Pilihan boleh lebih dari satu)",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "A",
                                                        "text": "Harta"
                                                    },
                                                    {
                                                        "value": "B",
                                                        "text": "Kekuasaan"
                                                    },
                                                    {
                                                        "value": "C",
                                                        "text": "Asmara"
                                                    },
                                                    {
                                                        "value": "D",
                                                        "text": "Ideologi/kepercayaan"
                                                    },
                                                    {
                                                        "value": "E",
                                                        "text": "Keramaian (olah raga, hiburan, dll.)"
                                                    },
                                                    {
                                                        "value": "F",
                                                        "text": "Ketidakpuasan atas kebijakan/pelayanan"
                                                    }
                                                ]
                                            }
                                        ],
                                        "visibleIf": "{kejadian_perkelahian_massal} = '1'",
                                        "title": "Detail Perkelahian Massal"
                                    }
                                ],
                                "title": "1001 Perkelahian Massal"
                            },
                            {
                                "type": "checkbox",
                                "name": "upaya_penyelesaian_perkelahian",
                                "visibleIf": "{kejadian_perkelahian_massal} = '1'",
                                "title": "1002 Upaya penyelesaian perkelahian massal dilakukan oleh (Pilihan boleh lebih dari satu)",
                                "isRequired": true,
                                "choices": [
                                    {
                                        "value": "A",
                                        "text": "Aparat keamanan"
                                    },
                                    {
                                        "value": "B",
                                        "text": "Aparat pemerintah"
                                    },
                                    {
                                        "value": "C",
                                        "text": "Tokoh masyarakat"
                                    },
                                    {
                                        "value": "D",
                                        "text": "Tokoh agama"
                                    },
                                    {
                                        "value": "E",
                                        "text": "Lainnya"
                                    },
                                    {
                                        "value": "F",
                                        "text": "Tidak ada"
                                    }
                                ]
                            },
                            {
                                "type": "panel",
                                "name": "panel_1003",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "pos_keamanan",
                                        "title": "a. Pembangunan/pemeliharaan pos keamanan lingkungan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "regu_keamanan",
                                        "title": "b. Pembentukan/pengaturan regu keamanan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "penambahan_hansip",
                                        "title": "c. Penambahan jumlah anggota hansip/linmas",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pelaporan_tamu",
                                        "title": "d. Pelaporan tamu yang menginap lebih dari 24 jam ke aparat lingkungan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sistem_keamanan_inisiatif_warga",
                                        "title": "e. Pengaktifan sistem keamanan lingkungan berasal dari inisiatif warga",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ya"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak"
                                            }
                                        ]
                                    }
                                ],
                                "title": "1003 Kegiatan warga desa/kelurahan untuk menjaga keamanan lingkungan di desa/kelurahan selama setahun terakhir"
                            }
                        ],
                        "title": "X. KEAMANAN"
                    },
                    {
                        "name": "page_11",
                        "elements": [
                            {
                                "type": "panel",
                                "name": "panel_1101",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "sistem_informasi_desa",
                                        "title": "a. Keberadaan sistem informasi desa",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada, diperbaharui"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Ada, tidak diperbaharui"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "panel",
                                        "name": "panel_update_sid",
                                        "elements": [
                                            {
                                                "type": "dropdown",
                                                "name": "bulan_update_sid",
                                                "title": "Bulan",
                                                "isRequired": true,
                                                "choices": [
                                                    {
                                                        "value": "1",
                                                        "text": "Januari"
                                                    },
                                                    {
                                                        "value": "2",
                                                        "text": "Februari"
                                                    },
                                                    {
                                                        "value": "3",
                                                        "text": "Maret"
                                                    },
                                                    {
                                                        "value": "4",
                                                        "text": "April"
                                                    },
                                                    {
                                                        "value": "5",
                                                        "text": "Mei"
                                                    },
                                                    {
                                                        "value": "6",
                                                        "text": "Juni"
                                                    },
                                                    {
                                                        "value": "7",
                                                        "text": "Juli"
                                                    },
                                                    {
                                                        "value": "8",
                                                        "text": "Agustus"
                                                    },
                                                    {
                                                        "value": "9",
                                                        "text": "September"
                                                    },
                                                    {
                                                        "value": "10",
                                                        "text": "Oktober"
                                                    },
                                                    {
                                                        "value": "11",
                                                        "text": "November"
                                                    },
                                                    {
                                                        "value": "12",
                                                        "text": "Desember"
                                                    }
                                                ],
                                                "placeholder": "Pilih bulan"
                                            },
                                            {
                                                "type": "text",
                                                "name": "tahun_update_sid",
                                                "title": "Tahun",
                                                "isRequired": true,
                                                "inputType": "number",
                                                "min": 2000,
                                                "max": 2025,
                                                "placeholder": "2025"
                                            }
                                        ],
                                        "visibleIf": "{sistem_informasi_desa} = '1' or {sistem_informasi_desa} = '2'",
                                        "title": "b. Jika ada, kapan terakhir diperbaharui"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "sistem_keuangan_desa",
                                        "title": "c. Penggunaan sistem keuangan desa",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada, diperbaharui"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Ada, tidak diperbaharui"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "visibleIf": "{status_pemerintahan} = '1' or {status_pemerintahan} = '3' or {status_pemerintahan} = '4'",
                                "title": "1101 Sistem Informasi dan Keuangan Desa"
                            },
                            {
                                "type": "panel",
                                "name": "panel_1102",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_bumdes",
                                        "title": "a. Jumlah unit usaha BUMDes",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "tanah_kas_desa",
                                        "title": "b. Tanah kas desa/ulayat",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "tambatan_perahu",
                                        "title": "c. Tambatan perahu",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "pasar_desa",
                                        "title": "d. Pasar (pasar desa, pasar hewan, pelelangan ikan yang dikelola desa, pelelangan hasil pertanian)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "bangunan_milik_desa",
                                        "title": "e. Bangunan milik desa (balai desa, balai rakyat, lapangan olah raga, dll)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "hutan_milik_desa",
                                        "title": "f. Hutan milik desa",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "mata_air_milik_desa",
                                        "title": "g. Mata air milik desa",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "tempat_wisata",
                                        "title": "h. Tempat wisata/Pemandian umum",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "aset_desa_lainnya",
                                        "title": "i. Aset desa lainnya (kekayaan asli desa lainnya, hibah/sumbangan/sejenisnya dll)",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    }
                                ],
                                "visibleIf": "{status_pemerintahan} = '1' or {status_pemerintahan} = '3' or {status_pemerintahan} = '4'",
                                "title": "1102 Kepemilikan Badan Usaha dan Aset Desa"
                            },
                            {
                                "type": "text",
                                "name": "jumlah_peraturan_desa",
                                "visibleIf": "{status_pemerintahan} = '1' or {status_pemerintahan} = '3' or {status_pemerintahan} = '4'",
                                "title": "1103 Jumlah peraturan desa tahun 2024",
                                "isRequired": true,
                                "inputType": "number",
                                "min": 0,
                                "placeholder": "0"
                            }
                        ],
                        "title": "XI. KEUANGAN DAN ASET DESA",
                        "description": "Blok ini akan terisi jika Blok III R301, status pemerintahannya adalah Desa atau UPT/SPT atau Nagari"
                    },
                    {
                        "name": "page_12",
                        "elements": [
                            {
                                "type": "matrixdynamic",
                                "name": "keberadaan_kades_sekdes",
                                "title": "1201 Keberadaan kepala desa/lurah dan sekretaris kepala desa/lurah",
                                "defaultValue": [
                                    {
                                        "jabatan": "a. Kepala Desa/Lurah"
                                    },
                                    {
                                        "jabatan": "b. Sekretaris Desa/Sekretaris Kelurahan"
                                    }
                                ],
                                "columns": [
                                    {
                                        "name": "jabatan",
                                        "title": "Pemerintah desa/kelurahan",
                                        "cellType": "text",
                                        "readOnly": true,
                                        "width": "180px"
                                    },
                                    {
                                        "name": "keberadaan",
                                        "title": "Keberadaan",
                                        "cellType": "dropdown",
                                        "isRequired": true,
                                        "width": "100px",
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "name": "umur",
                                        "title": "Umur (tahun)",
                                        "cellType": "text",
                                        "width": "90px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jabatan ada, umur harus diisi",
                                                "expression": "{row.keberadaan} = '2' or ({row.keberadaan} = '1' and {row.umur} > 0)"
                                            }
                                        ],
                                        "inputType": "number",
                                        "min": 0,
                                        "max": 120,
                                        "placeholder": "0"
                                    },
                                    {
                                        "name": "jenis_kelamin",
                                        "title": "Jenis kelamin",
                                        "cellType": "dropdown",
                                        "width": "120px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jabatan ada, jenis kelamin harus dipilih",
                                                "expression": "{row.keberadaan} = '2' or ({row.keberadaan} = '1' and {row.jenis_kelamin} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Laki-laki"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Perempuan"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    },
                                    {
                                        "name": "pendidikan",
                                        "title": "Pendidikan tertinggi yang ditamatkan",
                                        "cellType": "dropdown",
                                        "width": "180px",
                                        "validators": [
                                            {
                                                "type": "expression",
                                                "text": "Jika jabatan ada, pendidikan harus dipilih",
                                                "expression": "{row.keberadaan} = '2' or ({row.keberadaan} = '1' and {row.pendidikan} notempty)"
                                            }
                                        ],
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Tidak pernah sekolah"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak tamat SD/Sederajat"
                                            },
                                            {
                                                "value": "3",
                                                "text": "Tamat SD/Sederajat"
                                            },
                                            {
                                                "value": "4",
                                                "text": "SMP/Sederajat"
                                            },
                                            {
                                                "value": "5",
                                                "text": "SMU/Sederajat"
                                            },
                                            {
                                                "value": "6",
                                                "text": "Akademi/DIII"
                                            },
                                            {
                                                "value": "7",
                                                "text": "Diploma IV/S1"
                                            },
                                            {
                                                "value": "8",
                                                "text": "S2"
                                            },
                                            {
                                                "value": "9",
                                                "text": "S3"
                                            }
                                        ],
                                        "placeholder": "Pilih"
                                    }
                                ],
                                "allowAddRows": false,
                                "allowRemoveRows": false
                            },
                            {
                                "type": "html",
                                "name": "keterangan_pendidikan",
                                "html": "<p><small><strong>Kode Pendidikan:</strong> 1=Tidak pernah sekolah, 2=Tidak tamat SD, 3=Tamat SD, 4=SMP, 5=SMU, 6=Akademi/DIII, 7=Diploma IV/S1, 8=S2, 9=S3</small></p>"
                            },
                            {
                                "type": "panel",
                                "name": "panel_1202",
                                "elements": [
                                    {
                                        "type": "text",
                                        "name": "jumlah_sekretariat",
                                        "title": "a. Sekretariat Desa/Kelurahan (kaur, kasi, dll.)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_pelaksana_teknis",
                                        "title": "b. Pelaksana Teknis (kasi kesejahteraan, kasi pelayanan, dll.)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_pelaksana_kewilayahan",
                                        "title": "c. Pelaksana Kewilayahan (kadus, ketua RW, ketua RT, dll.)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_pegawai_lainnya",
                                        "title": "d. Pegawai Desa/Kelurahan lainnya (staf administrasi, tenaga kontrak, dll.)",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "title": "1202 Jumlah aparatur pemerintahan desa/kelurahan"
                            },
                            {
                                "type": "panel",
                                "name": "panel_1203",
                                "elements": [
                                    {
                                        "type": "radiogroup",
                                        "name": "keberadaan_bpd_lmk",
                                        "title": "a. Badan Permusyawaratan Desa/Lembaga Musyawarah Kelurahan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "radiogroup",
                                        "name": "anggota_perempuan_bpd",
                                        "visibleIf": "{keberadaan_bpd_lmk} = '1'",
                                        "title": "b. Jika ada, apakah ada anggota yang perempuan",
                                        "isRequired": true,
                                        "choices": [
                                            {
                                                "value": "1",
                                                "text": "Ada"
                                            },
                                            {
                                                "value": "2",
                                                "text": "Tidak ada"
                                            }
                                        ]
                                    },
                                    {
                                        "type": "text",
                                        "name": "jumlah_musyawarah_desa",
                                        "title": "c. Jumlah kegiatan musyawarah desa/kelurahan yang dilakukan selama tahun 2024",
                                        "isRequired": true,
                                        "inputType": "number",
                                        "min": 0,
                                        "placeholder": "0"
                                    }
                                ],
                                "title": "1203 Badan Permusyawaratan Desa/Lembaga Musyawarah Kelurahan"
                            },
                            {
                                "type": "html",
                                "name": "header_catatan",
                                "html": "<h3>XIII. CATATAN</h3>"
                            },
                            {
                                "type": "comment",
                                "name": "catatan",
                                "title": "Catatan petugas/pengawas",
                                "rows": 5,
                                "placeholder": "Tuliskan catatan penting terkait pencacahan..."
                            }
                        ],
                        "title": "XII. KETERANGAN APARATUR PEMERINTAHAN DESA/KELURAHAN"
                    }
                ],
                "showQuestionNumbers": "onPage"
            },
            "validation_rules": {
                "helpers": [],
                "rules": [],
                "rewrites": []
            },
            "start_at": "2025-12-31T17:00:00.000000Z",
            "end_at": "2026-12-30T17:00:00.000000Z",
            "created_at": "2026-04-12T09:40:19.000000Z",
            "updated_at": "2026-04-12T14:14:00.000000Z"
        }
    ]
}
```

---
*Generated at: 2026-04-18 20:50:55*
