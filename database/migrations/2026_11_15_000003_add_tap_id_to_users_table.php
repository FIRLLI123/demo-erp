<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'tap_id')) {
                $table->foreignId('tap_id')->nullable()->after('role_id')->constrained('taps')->nullOnDelete()->comment('Penugasan TAP Kantor Cabang (Null = Akses Semua Area IT DEV)');
            }
        });

        // Pastikan role IT DEV ada di tabel roles
        $itRole = DB::table('roles')->where('slug', 'it_dev')->first();
        if (!$itRole) {
            $itRoleId = DB::table('roles')->insertGetId([
                'name' => 'IT Developer & System Architect',
                'slug' => 'it_dev',
                'description' => 'Akses Omni Tanpa Batas ke Seluruh Wilayah TAP & Modul Sistem',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $itRoleId = $itRole->id;
        }

        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        $adminRoleId = $adminRole ? $adminRole->id : 2;

        // 1. Akun Khusus IT DEV (Akses Semua Area)
        $itUser = DB::table('users')->where('email', 'itdev@wahanapos.com')->first();
        if (!$itUser) {
            DB::table('users')->insert([
                'name' => 'IT Developer Wahana POS',
                'email' => 'itdev@wahanapos.com',
                'password' => Hash::make('password123'),
                'role_id' => $itRoleId,
                'tap_id' => null, // NULL = Akses Semua Area
                'phone' => '081299990001',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Pastikan user admin utama juga role IT DEV / Super Admin dengan tap_id NULL
        DB::table('users')->where('id', 1)->update([
            'role_id' => $itRoleId,
            'tap_id' => null,
            'password' => Hash::make('password123'),
            'updated_at' => now(),
        ]);

        // 2. Akun Sample Admin per TAP
        // TAP 1: Pasar Minggu
        $tap1 = DB::table('taps')->where('id', 1)->first();
        if ($tap1) {
            $u1 = DB::table('users')->where('email', 'admin.pasarminggu@wahanapos.com')->first();
            if (!$u1) {
                DB::table('users')->insert([
                    'name' => 'Admin TAP Pasar Minggu',
                    'email' => 'admin.pasarminggu@wahanapos.com',
                    'password' => Hash::make('password123'),
                    'role_id' => $adminRoleId,
                    'tap_id' => 1,
                    'phone' => '081211112222',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // TAP 2: Blok M
        $tap2 = DB::table('taps')->where('id', 2)->first();
        if ($tap2) {
            $u2 = DB::table('users')->where('email', 'admin.blokm@wahanapos.com')->first();
            if (!$u2) {
                DB::table('users')->insert([
                    'name' => 'Admin TAP Blok M',
                    'email' => 'admin.blokm@wahanapos.com',
                    'password' => Hash::make('password123'),
                    'role_id' => $adminRoleId,
                    'tap_id' => 2,
                    'phone' => '081233334444',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // TAP 3: Rawamangun
        $tap3 = DB::table('taps')->where('id', 3)->first();
        if ($tap3) {
            $u3 = DB::table('users')->where('email', 'admin.rawamangun@wahanapos.com')->first();
            if (!$u3) {
                DB::table('users')->insert([
                    'name' => 'Admin TAP Rawamangun',
                    'email' => 'admin.rawamangun@wahanapos.com',
                    'password' => Hash::make('password123'),
                    'role_id' => $adminRoleId,
                    'tap_id' => 3,
                    'phone' => '081255556666',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tap_id')) {
                $table->dropForeign(['tap_id']);
                $table->dropColumn('tap_id');
            }
        });
    }
};
