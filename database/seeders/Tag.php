<?php

namespace Database\Seeders;

use App\Models\Tag as TagModel;
use Illuminate\Database\Seeder;


class Tag extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TagModel::factory()->create(['name'=>'2D']);
        TagModel::factory()->create(['name'=>'3D']);
        TagModel::factory()->create(['name'=>'Хоррор']);
        TagModel::factory()->create(['name'=>'Приключения']);
        TagModel::factory()->create(['name'=>'Гонки']);
        TagModel::factory()->create(['name'=>'Аркада']);
        TagModel::factory()->create(['name'=>'Выживание']);
        TagModel::factory()->create(['name'=>'Ритм-игра']);
        TagModel::factory()->create(['name'=>'Настольная игра']);
        TagModel::factory()->create(['name'=>'Android']);
        TagModel::factory()->create(['name'=>'PC']);
        TagModel::factory()->create(['name'=>'В разработке']);
        TagModel::factory()->create(['name'=>'Релиз']);
        TagModel::factory()->create(['name'=>'Психологический хоррор']);
        TagModel::factory()->create(['name'=>'Fantasy']);
        TagModel::factory()->create(['name'=>'Многопользовательская']);
        TagModel::factory()->create(['name'=>'Соревновательная']);
        TagModel::factory()->create(['name'=>'Мультиплеер']);
    }
}
