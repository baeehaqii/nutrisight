<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus semua data yang ada terlebih dahulu
        $this->truncatePermissionTables();
        
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Membuat permissions dengan nama yang lebih spesifik
        $permissions = [
            // Kelola pengguna
            'lihat_pengguna',
            'tambah_pengguna',
            'ubah_pengguna',
            'hapus_pengguna',

            // Kelola konten
            'lihat_konten',
            'tambah_konten',
            'ubah_konten',
            'hapus_konten',
        ];

        // Gunakan firstOrCreate untuk menghindari error duplikasi
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buat role admin jika belum ada
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Admin mendapatkan semua izin
        $adminRole->syncPermissions(Permission::all());

        // Buat role pengguna biasa jika belum ada
        $userRole = Role::firstOrCreate(['name' => 'pengguna']);

        // Pengguna biasa hanya bisa melihat
        $userRole->syncPermissions([
            'lihat_konten',
        ]);
    }
    
    /**
     * Truncate the permission tables.
     */
    protected function truncatePermissionTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Schema::disableForeignKeyConstraints();
        
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        
        Schema::enableForeignKeyConstraints();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}