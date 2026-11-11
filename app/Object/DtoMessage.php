<?php

namespace App\Object;

use App\Enums\MessageType;
use Illuminate\Http\JsonResponse;
use JsonSerializable;

class DtoMessage implements JsonSerializable
{
    public array $listMessage = [];

    public string $type = MessageType::ERROR->value;

    public mixed $data = null;

    public int $status = 200;

    public function __construct()
    {
        $this->listMessage = [];
        $this->type = MessageType::ERROR->value;
        $this->status = 200;
    }

    public function setSuccess(): self
    {
        $this->type = MessageType::SUCCESS->value;

        return $this;
    }

    public function setError(): self
    {
        $this->type = MessageType::ERROR->value;

        return $this;
    }

    public function setWarning(): self
    {
        $this->type = MessageType::WARNING->value;

        return $this;
    }

    public function setValidation(): self
    {
        $this->type = MessageType::VALIDATION->value;

        return $this;
    }

    public function addMessage(string $msg): self
    {
        $this->listMessage[] = $msg;

        return $this;
    }

    public function setData(mixed $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function addDataSet($key, $value): self
    {
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'mo' => [
                'listMessage' => $this->listMessage,
                'type' => $this->type,
            ],
            'dto' => $this->data ?? (object) [],
        ];
    }

    public function send(?int $status = null): JsonResponse
    {
        return response()->json($this, $status ?? $this->status);
    }
}
