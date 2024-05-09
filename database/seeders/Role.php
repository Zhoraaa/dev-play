<?php

namespace Database\Seeders;

use App\Models\Role as RoleModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class Role extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        RoleModel::factory()->create(['name'=>'Пользователь']);
        RoleModel::factory()->create(['name'=>'Разработчик']);
        RoleModel::factory()->create(['name'=>'Модератор']);
        RoleModel::factory()->create(['name'=>'Администратор']);
    }
}
