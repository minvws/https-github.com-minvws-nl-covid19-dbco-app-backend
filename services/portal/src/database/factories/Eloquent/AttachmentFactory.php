<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;

use function sprintf;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'file_name' => sprintf('%s.pdf', $this->faker->word()),
        ];
    }
}
