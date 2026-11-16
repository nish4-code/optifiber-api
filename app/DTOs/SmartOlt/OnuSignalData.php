<?php

declare(strict_types=1);

namespace App\DTOs\SmartOlt;

final readonly class OnuSignalData
{
    public function __construct(
        public string $onuSignal,
        public string $onuSignalValue,
        public string $onuSignal1310,
        public string $onuSignal1490,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            onuSignal: (string) ($data['onu_signal'] ?? 'Unknown'),
            onuSignalValue: (string) ($data['onu_signal_value'] ?? ''),
            onuSignal1310: (string) ($data['onu_signal_1310'] ?? ''),
            onuSignal1490: (string) ($data['onu_signal_1490'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'onu_signal' => $this->onuSignal,
            'onu_signal_value' => $this->onuSignalValue,
            'onu_signal_1310' => $this->onuSignal1310,
            'onu_signal_1490' => $this->onuSignal1490,
        ];
    }
}
