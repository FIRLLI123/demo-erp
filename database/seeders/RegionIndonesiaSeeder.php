<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionIndonesiaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. PROVINSI: JAWA BARAT
        $provId = DB::table('master_provinsi')->updateOrInsert(
            ['code' => '32'],
            ['name' => 'Jawa Barat', 'updated_at' => now(), 'created_at' => now()]
        );
        $provinsi = DB::table('master_provinsi')->where('code', '32')->first();
        $provId = $provinsi->id;

        // 2. KOTA & KABUPATEN: BOGOR & SUKABUMI
        $kotaKabData = [
            [
                'code' => '3271',
                'name' => 'Kota Bogor',
                'type' => 'KOTA',
                'districts' => [
                    'Bogor Barat',
                    'Bogor Selatan',
                    'Bogor Tengah',
                    'Bogor Timur',
                    'Bogor Utara',
                    'Tanah Sareal',
                ]
            ],
            [
                'code' => '3201',
                'name' => 'Kabupaten Bogor',
                'type' => 'KABUPATEN',
                'districts' => [
                    'Babakan Madang', 'Bojonggede', 'Caringin', 'Cariu', 'Ciampea', 'Ciawi', 'Cibinong',
                    'Cibungbulang', 'Cigombong', 'Cigudeg', 'Cijeruk', 'Cileungsi', 'Ciomas', 'Cisarua',
                    'Ciseeng', 'Citeureup', 'Dramaga', 'Gunung Putri', 'Gunung Sindur', 'Jasinga',
                    'Jonggol', 'Kemang', 'Klapanunggal', 'Leuwiliang', 'Leuwisadeng', 'Megamendung',
                    'Nanggung', 'Pamijahan', 'Parung', 'Parung Panjang', 'Ranca Bungur', 'Rumpin',
                    'Sukajaya', 'Sukamakmur', 'Sukaraja', 'Tajurhalang', 'Tamansari', 'Tanjungsari',
                    'Tenjo', 'Tenjolaya'
                ]
            ],
            [
                'code' => '3272',
                'name' => 'Kota Sukabumi',
                'type' => 'KOTA',
                'districts' => [
                    'Baros',
                    'Cibeureum',
                    'Cikole',
                    'Citamiang',
                    'Gunungpuyuh',
                    'Lembursitu',
                    'Warudoyong'
                ]
            ],
            [
                'code' => '3202',
                'name' => 'Kabupaten Sukabumi',
                'type' => 'KABUPATEN',
                'districts' => [
                    'Bantargadung', 'Bojonggenteng', 'Caringin', 'Ciambar', 'Cibadak', 'Cibitung',
                    'Cicantayan', 'Cicurug', 'Cidadap', 'Ciemas', 'Cikakak', 'Cikembar', 'Cikidang',
                    'Cimanggu', 'Cisolok', 'Cisaat', 'Cireunghas', 'Ciracap', 'Curugkembar',
                    'Gegerbitung', 'Gunung Guruh', 'Jampang Kulon', 'Jampang Tengah', 'Kabandungan',
                    'Kadudampit', 'Kalapanunggal', 'Kalibunder', 'Kebonpedes', 'Lengkong', 'Nagrak',
                    'Nyalindung', 'Pabuaran', 'Parakansalak', 'Parungkuda', 'Pelabuhanratu',
                    'Purabaya', 'Sagaranten', 'Simpenan', 'Sukabumi', 'Sukalarang', 'Sukaraja',
                    'Surade', 'Tegalbuleud', 'Waluran', 'Warungkiara'
                ]
            ],
        ];

        foreach ($kotaKabData as $kab) {
            DB::table('master_kota_kab')->updateOrInsert(
                ['code' => $kab['code']],
                [
                    'provinsi_id' => $provId,
                    'name' => $kab['name'],
                    'type' => $kab['type'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $kabRecord = DB::table('master_kota_kab')->where('code', $kab['code'])->first();

            foreach ($kab['districts'] as $distName) {
                DB::table('master_kecamatan')->updateOrInsert(
                    [
                        'kota_kab_id' => $kabRecord->id,
                        'name' => $distName
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            }
        }
    }
}
