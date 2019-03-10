<?php

namespace App\GraphQl\Resolver;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Resolver\TypeResolver;

class NodeResolver implements ResolverInterface, AliasedInterface
{
    private $resolveType;
    public function __construct(TypeResolver $typeResolver)
    {
        $this->resolveType = $typeResolver;
    }

    public function resolveNode($value)
    {
        return ['id' => $value];
    }

    public function resolveType()
    {
        return $this->resolveType->resolve('User');
    }

    static public function getAliases()
    {
        return [
            'resolveNode' => 'node',
            'resolveType' => 'node_type'
        ];
    }
}
