<?php

namespace App\GraphQl\CustomScalar;

use GraphQL\Error\UserError;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;

class DateTimeType extends ScalarType implements AliasedInterface
{

    public $name = 'DateTime';
    public $description = "An ISO-8601 encoded UTC date string.";

    /**
     * @param \DateTimeInterface $value
     * @return string
     */
    public function serialize($value)
    {
        return $value->format('Y-m-d\TH:i:s');
    }

    /**
     * @param mixed $value
     * @return \DateTimeImmutable
     * @throws UserError
     */
    public function parseValue($value)
    {
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            throw new UserError((string) $value . ": is not a valid DateTime.");
        }

    }

    /**
     * @param Node $valueNode
     * @param array|null $variables
     * @return \DateTimeImmutable
     * @throws UserError
     */
    public function parseLiteral($valueNode, array $variables = null)
    {
        try {
            return new \DateTimeImmutable($valueNode->value);
        } catch (\Exception $exception) {
            throw new UserError((string) $valueNode->value . ": is not a valid DateTime.");
        }
    }

    /**
     * Returns methods aliases.
     * @return array
     */
    public static function getAliases()
    {
        return ['DateTime'];
    }
}
