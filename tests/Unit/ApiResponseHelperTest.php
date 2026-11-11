<?php

namespace Tests\Unit;

use App\Enums\MessageType;
use App\Helper\ApiResponseHelper;
use App\Object\DtoMessage;
use PHPUnit\Framework\TestCase;

class ApiResponseHelperTest extends TestCase
{
    public function test_return_success_structure(): void
    {
        $dto = ApiResponseHelper::returnSuccess('Operación exitosa', ['id' => 10]);

        $this->assertInstanceOf(DtoMessage::class, $dto);
        $json = $dto->jsonSerialize();

        $this->assertEquals(['Operación exitosa'], $json['mo']['listMessage']);
        $this->assertEquals(MessageType::SUCCESS->value, $json['mo']['type']);
        $this->assertEquals(['id' => 10], $json['dto']);
    }

    public function test_return_error_structure(): void
    {
        $dto = ApiResponseHelper::returnError('Error en validación');

        $json = $dto->jsonSerialize();
        $this->assertEquals(['Error en validación'], $json['mo']['listMessage']);
        $this->assertEquals(MessageType::ERROR->value, $json['mo']['type']);
    }

    public function test_created_helper_status(): void
    {
        $dto = ApiResponseHelper::created('Recurso creado', ['code' => 'NAP-01']);

        $this->assertEquals(201, $dto->status);
        $json = $dto->jsonSerialize();
        $this->assertEquals(['Recurso creado'], $json['mo']['listMessage']);
        $this->assertEquals(['code' => 'NAP-01'], $json['dto']);
    }
}
