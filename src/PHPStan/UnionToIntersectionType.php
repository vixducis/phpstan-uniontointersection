<?php

namespace App\PHPStan;

use PHPStan\Type\CompoundType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\LateResolvableType;
use PHPStan\Type\UnionType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\Traits\LateResolvableTypeTrait;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;

class UnionToIntersectionType
implements LateResolvableType, CompoundType
{
    use LateResolvableTypeTrait;

    public function __construct(private Type $type) {}

    public function getReferencedClasses(): array
    {
        return $this->type->getReferencedClasses();
    }

    public function getReferencedTemplateTypes(TemplateTypeVariance $positionVariance): array
    {
        return $this->type->getReferencedTemplateTypes($positionVariance);
    }

    public function equals(Type $type): bool
    {
        return $type instanceof self
            && $this->type->equals($type->type);
    }

    public function describe(VerbosityLevel $level): string
    {
        return sprintf('UnionToIntersection<%s>', $this->type->describe($level));
    }

    public function isResolvable(): bool
    {
        return false === TypeUtils::containsTemplateType($this->type);

    }

    protected function getResult(): Type
    {
        if ($this->type instanceof UnionType) {
            return new IntersectionType($this->type->getTypes());
        }

        return $this->type;
    }

    public function toPhpDocNode(): TypeNode
    {
        return new GenericTypeNode(
            new IdentifierTypeNode('UnionToIntersection'),
            [$this->type->toPhpDocNode()]
        );
    }

    public function generalize(GeneralizePrecision $precision): Type
    {
        return $this->type->generalize($precision);
    }

    /**
     * @param callable(Type): Type $cb
     */
    public function traverse(callable $cb): Type
    {
        $type = $cb($this->type);

        if ($this->type === $type) {
            return $this;
        }

        return new self($type);
    }

    public function traverseSimultaneously(Type $right, callable $cb): Type
    {
        if (!$right instanceof self) {
            return $this;
        }

        $type = $cb($this->type, $right->type);

        if ($this->type === $type) {
            return $this;
        }

        return new self($type);
    }
}
