<?php

namespace App\Helper;

use App\Object\DtoMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;
use Throwable;

class ApiResponseHelper
{
    public static function returnSuccess(string $msg = '', mixed $data = null): DtoMessage
    {
        return (new DtoMessage)->setSuccess()->addMessage($msg)->setData($data);
    }

    public static function created(string $msg = '', mixed $data = null): DtoMessage
    {
        return self::returnSuccess($msg, $data)->setStatus(Response::HTTP_CREATED);
    }

    public static function returnError(string $msg, mixed $data = null): DtoMessage
    {
        return (new DtoMessage)->setError()->addMessage($msg)->setData($data);
    }

    public static function paginated(string $msg, LengthAwarePaginator $paginator, array $extra = []): DtoMessage
    {
        $dto = [
            'items' => array_values($paginator->items()),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => max($paginator->lastPage(), 1),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];

        return self::returnSuccess($msg, array_merge($dto, $extra));
    }

    public static function badRequest(string $msg, mixed $data = null): DtoMessage
    {
        return self::returnError($msg, $data)->setStatus(Response::HTTP_BAD_REQUEST);
    }

    public static function forbidden(string $msg = 'No tienes permisos para realizar esta acción.'): DtoMessage
    {
        return self::returnError($msg)->setStatus(Response::HTTP_FORBIDDEN);
    }

    public static function unprocessable(string $msg, mixed $data = null): DtoMessage
    {
        return self::returnError($msg, $data)->setStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function noAplicable(string $msg): DtoMessage
    {
        return self::returnError($msg)->setValidation()->setStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function storeSuccess(string $resource = 'Recurso', mixed $data = null): DtoMessage
    {
        $msg = "{$resource} creado correctamente.";

        return self::created($msg, $data);
    }

    public static function updateSuccess(string $resource = 'Recurso', mixed $data = null): DtoMessage
    {
        $msg = "{$resource} actualizado correctamente.";

        return self::returnSuccess($msg, $data);
    }

    public static function destroySuccess(string $resource = 'Recurso'): DtoMessage
    {
        $msg = "{$resource} eliminado correctamente.";

        return self::returnSuccess($msg);
    }

    public static function notFound(string $resource = 'Recurso'): DtoMessage
    {
        $msg = "El recurso '{$resource}' no fue encontrado.";

        return self::returnError($msg)->setStatus(Response::HTTP_NOT_FOUND);
    }

    public static function unauthorized(string $msg = 'No tienes permisos para realizar esta acción.'): DtoMessage
    {
        return self::returnError($msg)->setStatus(Response::HTTP_UNAUTHORIZED);
    }

    public static function validationError(mixed $errors): DtoMessage
    {
        $dto = (new DtoMessage)->setValidation()->setStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $errorList = [];
        if ($errors instanceof Validator) {
            $errorList = $errors->errors()->all();
        } elseif ($errors instanceof MessageBag) {
            $errorList = $errors->all();
        } elseif (is_object($errors) && method_exists($errors, 'all')) {
            $errorList = $errors->all();
        } elseif (is_array($errors)) {
            $errorList = $errors;
        } else {
            $errorList = (array) $errors;
        }

        foreach ($errorList as $error) {
            if (is_string($error)) {
                $dto->addMessage($error);
            }
        }

        return $dto;
    }

    public static function generalError(Throwable $e, string $customMsg = 'Ocurrió un error inesperado.', int $status = Response::HTTP_INTERNAL_SERVER_ERROR): DtoMessage
    {
        $dto = self::returnError($customMsg)->setStatus($status);
        if (config('app.debug')) {
            $dto->setData([
                'debug' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return $dto;
    }

    public static function quotaExceeded(): DtoMessage
    {
        $msg = 'Has superado el límite de consultas de tu plan.';

        return self::returnError($msg)->setStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    public static function invalidCredentials(): DtoMessage
    {
        return self::returnError('Usuario o contraseña incorrectos.')->setStatus(Response::HTTP_UNAUTHORIZED);
    }
}
