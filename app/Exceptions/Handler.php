<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions as BaseExceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler
{
    protected int $flags = JSON_UNESCAPED_SLASHES ^ JSON_UNESCAPED_UNICODE;

    public function __invoke(BaseExceptions $exceptions): BaseExceptions
    {
        $this->renderUnauthorized($exceptions);
        $this->renderNotFound($exceptions);
        $this->renderValidations($exceptions);

        return $exceptions;
    }

    protected function renderUnauthorized(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (AuthenticationException $e, ?Request $request = null) => $this->response(
                message: __('Unauthorized'),
                code: 401,
            )
        );
    }

    protected function renderNotFound(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (NotFoundHttpException $e, ?Request $request = null) => $this->response(
                message: __('Not Found'),
                code: 404,
            )
        );
    }

    protected function renderValidations(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (ValidationException $e) => response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->validator->errors()
            ], $e->status, options: $this->flags)
        );
    }

    protected function response(string $message, int $code): Response
    {
        return response()->json(compact('message'), $code, options: $this->flags);
    }
}

