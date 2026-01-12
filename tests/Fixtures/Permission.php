<?php

declare(strict_types=1);

namespace Gksh\Transistor\Tests\Fixtures;

enum Permission: int
{
    case Read = 1 << 0;
    case Write = 1 << 1;
    case Delete = 1 << 2;
    case Admin = 1 << 3;
}
