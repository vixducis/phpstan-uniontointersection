<?php

namespace App\PHPStan;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Type;

class UnionToIntersectionTypeResolver
implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension
{
	private TypeNodeResolver $typeNodeResolver;

	public function setTypeNodeResolver(TypeNodeResolver $typeNodeResolver): void
	{
		$this->typeNodeResolver = $typeNodeResolver;
	}

	public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
	{
		if (!$typeNode instanceof GenericTypeNode) {
			return null;
		}

		$typeName = $typeNode->type;
		if ($typeName->name !== 'UnionToIntersection') {
			return null;
		}

		$arguments = $typeNode->genericTypes;
		if (count($arguments) !== 1) {
			return null;
		}

		$type = $this->typeNodeResolver->resolve($arguments[0], $nameScope);
		if ($type instanceof UnionType) {
			return new IntersectionType($type->getTypes());
		}

        if ($type instanceof ObjectType) {
            return $type;
        }

		if ($type instanceof TemplateType) {
			return new UnionToIntersectionType($type);
		}

		return null;
	}
}
