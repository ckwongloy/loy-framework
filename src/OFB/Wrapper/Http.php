<?php

declare(strict_types=1);

namespace Dof\Framework\OFB\Wrapper;

class Http
{
    public function wrapout()
    {
        return ['__DATA__' => 'data', 'status' => 200, 'message' => 'ok', 'meta', 'extra'];
    }
}
