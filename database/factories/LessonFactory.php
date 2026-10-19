<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'module_id' => Module::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(3, true),
            'example_commands' => "# Contoh Perintah\npwd\nls -la",
            'cheat_sheet' => "pwd : Cetak direktori kerja saat ini\nls -la : Tampilkan file tersembunyi",
            'order' => fake()->numberBetween(1, 100),
        ];
    }
}
