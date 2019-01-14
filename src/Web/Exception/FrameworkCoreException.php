<?php

declare(strict_types=1);

namespace Loy\Framework\Web\Exception;

use Exception;
use ReflectionClass;
use Loy\Framework\Web\Response;

class FrameworkCoreException extends Exception
{
    public function __construct(string $error, int $code = 500)
    {
        $this->message = $error;
        $this->code    = $code;

        $error = (new ReflectionClass($this))->getShortName().': '.$this->message;

        Response::setMimeAlias('text')
            ->setBody($error)
            ->setStatus($this->code)
            ->send();
    }
}
