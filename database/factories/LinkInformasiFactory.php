<?php

namespace Database\Factories;

use App\Models\LinkInformasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinkInformasi>
 */
class LinkInformasiFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(3, true),
            'url' => fake()->url(),
            'keterangan' => fake()->optional()->sentence(),
            'status' => LinkInformasi::STATUS_AKTIF,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LinkInformasi::STATUS_NONAKTIF,
        ]);
    }
}
