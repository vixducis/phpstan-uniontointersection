<?php

namespace App;

use Exception;

interface InterfaceA
{
    public function doA(): void;
}
interface InterfaceB
{
    public function doB(): void;
}

/**
 * @template T
 */
class Ensure
{
    /**
     * @param class-string<T>[] $requirements
     */
    public function __construct(protected array $requirements) {}

    /**
     * @phpstan-assert UnionToIntersection<T> $obj
     */
    public function check(object $obj): void
    {
        foreach ($this->requirements as $requirement) {
            if (false === is_a($obj, $requirement)) {
                throw new Exception('requirement not met');
            }
        }
    }
}

function test(object $obj): void
{
    $test = new Ensure([InterfaceA::class, InterfaceB::class]);
    $test->check($obj);
    \PHPStan\dumptype($obj);
    $obj->doA();
    $obj->doB();
}
