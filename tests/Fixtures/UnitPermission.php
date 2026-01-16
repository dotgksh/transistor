<?php

declare(strict_types=1);

namespace Gksh\Transistor\Tests\Fixtures;

enum UnitPermission
{
    case Read;
    case Write;
    case Delete;
    case Admin;
}
