<?php

namespace Charcoal\App\Handler\Error;

use Slim\Interfaces\ErrorRendererInterface;
use Slim\Exception\HttpException;
use Throwable;

abstract class CharcoalErrorRenderer implements ErrorRendererInterface
{
    protected string $defaultErrorTitle = 'Charcoal Application Error';

    protected string $defaultErrorDescription = 'A website error has occurred. Sorry for the temporary inconvenience.';

    protected function getErrorTitle(Throwable $exception): string
    {
        if ($exception instanceof HttpException) {
            return $exception->getTitle();
        }

        return $this->defaultErrorTitle;
    }

    protected function getErrorDescription(Throwable $exception): string
    {
        if ($exception instanceof HttpException) {
            return $exception->getDescription();
        }

        return $this->defaultErrorDescription;
    }
}
