<?php

declare(strict_types=1);

namespace App\DTOs\SmartOlt;

final readonly class ZoneData
{
    public function __construct(
        public string $id,
        public string $name,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
