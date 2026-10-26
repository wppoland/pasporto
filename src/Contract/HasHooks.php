<?php

declare(strict_types=1);

namespace Pasporto\Contract;

defined('ABSPATH') || exit;

interface HasHooks
{
    public function registerHooks(): void;
}
