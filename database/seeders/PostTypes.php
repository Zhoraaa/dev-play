<?php

namespace Database\Seeders;

use App\Models\PostType as PostTypesModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class PostTypes extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        PostTypesModel::factory()->create(['name'=>'Пост']);
        PostTypesModel::factory()->create(['name'=>'Тикет']);
    }
}
