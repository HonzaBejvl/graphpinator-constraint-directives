<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class IntConstraintDirective extends \Graphpinator\Directive\Directive implements
    \Graphpinator\Directive\Contract\FieldDefinitionLocation,
    \Graphpinator\Directive\Contract\ArgumentDefinitionLocation,
    \Graphpinator\Directive\Contract\VariableDefinitionLocation
{
    use TScalarConstraint;

    protected const NAME = 'intConstraint';
    protected const DESCRIPTION = 'Graphpinator intConstraint directive.';

    public function validateFieldUsage(
        \Graphpinator\Field\Field $field,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $field->getType()->getNamedType() instanceof \Graphpinator\Type\Spec\IntType;
    }

    public function validateArgumentUsage(
        \Graphpinator\Argument\Argument $argument,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $argument->getType()->getNamedType() instanceof \Graphpinator\Type\Spec\IntType;
    }

    public function validateVariableUsage(
        \Graphpinator\Normalizer\Variable\Variable $variable,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $variable->getType()->getNamedType() instanceof \Graphpinator\Type\Spec\IntType;
    }

    protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
    {
        return new \Graphpinator\Argument\ArgumentSet([
            \Graphpinator\Argument\Argument::create('min', \Graphpinator\Container\Container::Int()),
            \Graphpinator\Argument\Argument::create('max', \Graphpinator\Container\Container::Int()),
            \Graphpinator\Argument\Argument::create('oneOf', \Graphpinator\Container\Container::Int()->notNull()->list()),
        ]);
    }

    protected function afterGetFieldDefinition() : void
    {
        $this->arguments['oneOf']->addDirective(
            $this->constraintDirectiveAccessor->getList(),
            ['minItems' => 1],
        );
    }

    protected function specificValidateValue(
        \Graphpinator\Value\Value $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        $rawValue = $value->getRawValue();
        $min = $arguments->offsetGet('min')->getValue()->getRawValue();
        $max = $arguments->offsetGet('max')->getValue()->getRawValue();
        $oneOf = $arguments->offsetGet('oneOf')->getValue()->getRawValue();

        if (\is_int($min) && $rawValue < $min) {
            throw new Exception\MinConstraintNotSatisfied();
        }

        if (\is_int($max) && $rawValue > $max) {
            throw new Exception\MaxConstraintNotSatisfied();
        }

        if (\is_array($oneOf) && !\in_array($rawValue, $oneOf, true)) {
            throw new Exception\OneOfConstraintNotSatisfied();
        }
    }

    protected function specificValidateVariance(
        \Graphpinator\Value\ArgumentValueSet $biggerSet,
        \Graphpinator\Value\ArgumentValueSet $smallerSet,
    ) : void
    {
        $lhs = $biggerSet->getValuesForResolver();
        $rhs = $smallerSet->getValuesForResolver();

        if (\is_int($lhs['min']) && ($rhs['min'] === null || $rhs['min'] < $lhs['min'])) {
            throw new \Exception();
        }

        if (\is_int($lhs['max']) && ($rhs['max'] === null || $rhs['max'] > $lhs['max'])) {
            throw new \Exception();
        }

        if (\is_array($lhs['oneOf']) && ($rhs['oneOf'] === null || !self::varianceValidateOneOf($lhs['oneOf'], $rhs['oneOf']))) {
            throw new \Exception();
        }
    }
}
