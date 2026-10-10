<?php

namespace App\Extensions\Scramble;

use App\Extensions\Spatie\Fractalistic\Fractal;
use App\Http\Controllers\Api\Application\ApplicationApiController;
use Dedoc\Scramble\Infer\Definition\ClassDefinition;
use Dedoc\Scramble\Infer\Extensions\Event\MethodCallEvent;
use Dedoc\Scramble\Infer\Extensions\MethodReturnTypeExtension;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\GenericClassStringType;
use Dedoc\Scramble\Support\Type\IntegerType;
use Dedoc\Scramble\Support\Type\KeyedArrayType;
use Dedoc\Scramble\Support\Type\Literal\LiteralStringType;
use Dedoc\Scramble\Support\Type\NullType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\StringType;
use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Type\Union;
use Dedoc\Scramble\Support\Type\UnknownType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\NullResource as FractalNullResource;

/** Infers Reviactyl's Fractal response envelope and transformer fields. */
class FractalResponseTypeInfer implements MethodReturnTypeExtension
{
    public function shouldHandle(ObjectType $type): bool
    {
        return $type->isInstanceOf(Fractal::class)
            || $type->isInstanceOf(ApplicationApiController::class);
    }

    public function getMethodReturnType(MethodCallEvent $event): ?Type
    {
        if ($event->getInstance()->isInstanceOf(ApplicationApiController::class) && $event->name === 'getTransformer') {
            $class = $event->getArg('abstract', 0);
            if ($class instanceof GenericClassStringType && $class->type instanceof ObjectType) {
                return $class->type;
            }

            return $class instanceof LiteralStringType ? new ObjectType($class->value) : null;
        }

        if (! $event->getInstance()->isInstanceOf(Fractal::class)) {
            return null;
        }

        if (in_array($event->name, ['item', 'collection'], true)) {
            $instance = $event->getInstance();
            $transformer = $instance instanceof Generic ? ($instance->templateTypes[1] ?? new UnknownType()) : new UnknownType();

            return new Generic(Fractal::class, [
                new LiteralStringType($event->name),
                $transformer,
                $event->getArg('data', 0),
            ]);
        }

        if ($event->name === 'transformWith') {
            $instance = $event->getInstance();
            $operation = $instance instanceof Generic ? ($instance->templateTypes[0] ?? null) : null;
            $data = $instance instanceof Generic ? ($instance->templateTypes[2] ?? new UnknownType()) : new UnknownType();

            return new Generic(Fractal::class, [
                $operation instanceof LiteralStringType ? $operation : new LiteralStringType('item'),
                $event->getArg('transformer', 0),
                $data,
            ]);
        }

        if ($event->name !== 'toArray') {
            return null;
        }

        $instance = $event->getInstance();
        $operationType = $instance instanceof Generic ? ($instance->templateTypes[0] ?? null) : null;
        $transformer = $instance instanceof Generic ? ($instance->templateTypes[1] ?? null) : null;
        if (! $instance instanceof Generic
            || ! $operationType instanceof LiteralStringType
            || ! $transformer instanceof ObjectType) {
            return null;
        }

        $operation = $operationType->value;
        $attributes = $event->scope->index->getClass($transformer->name)?->getMethodDefinition('transform')?->getReturnType();
        $transformerDefinition = $event->scope->index->getClass($transformer->name);
        $resourceName = $transformerDefinition?->getMethodDefinition('getResourceName')?->getReturnType();
        if (! $attributes) {
            return null;
        }

        $attributes = $this->addRelationships($attributes, $transformerDefinition, $event);

        $item = new KeyedArrayType([
            new ArrayItemType_('object', $resourceName instanceof LiteralStringType ? $resourceName : new StringType()),
            new ArrayItemType_('attributes', $attributes),
        ]);

        if ($operation === 'collection') {
            $response = new KeyedArrayType([
                new ArrayItemType_('object', new LiteralStringType('list')),
                new ArrayItemType_('data', new ArrayType($item)),
            ]);

            $data = $instance->templateTypes[2] ?? new UnknownType();
            if ($data->isInstanceOf(LengthAwarePaginator::class)) {
                $response->items[] = new ArrayItemType_('meta', new KeyedArrayType([
                    new ArrayItemType_('pagination', new KeyedArrayType([
                        new ArrayItemType_('total', new IntegerType()),
                        new ArrayItemType_('count', new IntegerType()),
                        new ArrayItemType_('per_page', new IntegerType()),
                        new ArrayItemType_('current_page', new IntegerType()),
                        new ArrayItemType_('total_pages', new IntegerType()),
                    ])),
                ]));
            }

            return $response;
        }

        return new KeyedArrayType([
            new ArrayItemType_('object', $resourceName instanceof LiteralStringType ? $resourceName : new StringType()),
            new ArrayItemType_('attributes', $attributes),
        ]);
    }

    private function addRelationships(Type $attributes, ?ClassDefinition $transformer, MethodCallEvent $event): Type
    {
        if (! $attributes instanceof KeyedArrayType || ! $transformer) {
            return $attributes;
        }

        $includes = $transformer->getPropertyDefinition('availableIncludes')?->defaultType;
        if (! $includes instanceof KeyedArrayType) {
            return $attributes;
        }

        $relationshipItems = [];
        foreach ($includes->items as $include) {
            $includeName = $include->value;
            if (! $includeName instanceof LiteralStringType) {
                continue;
            }

            $name = $includeName->value;
            $method = $transformer->getMethodDefinition('include'.Str::studly($name), $event->scope);
            if (! $method) {
                continue;
            }

            $relationshipItems[] = new ArrayItemType_(
                $name,
                $this->includedResourceType($method->getReturnType()),
                isOptional: true,
            );
        }

        if (! $relationshipItems) {
            return $attributes;
        }

        $attributes = $attributes->clone();
        $attributes->items[] = new ArrayItemType_(
            'relationships',
            new KeyedArrayType($relationshipItems),
            isOptional: true,
        );

        return $attributes;
    }

    private function includedResourceType(Type $type): Type
    {
        $types = $type instanceof Union ? $type->types : [$type];
        $schemas = [];

        foreach ($types as $includedType) {
            if ($includedType->isInstanceOf(FractalCollection::class)) {
                $schemas[] = new KeyedArrayType([
                    new ArrayItemType_('object', new LiteralStringType('list')),
                    new ArrayItemType_('data', new ArrayType(new KeyedArrayType([
                        new ArrayItemType_('object', new StringType()),
                        new ArrayItemType_('attributes', new UnknownType()),
                    ]))),
                ]);
            } elseif ($includedType->isInstanceOf(FractalItem::class)) {
                $schemas[] = new KeyedArrayType([
                    new ArrayItemType_('object', new StringType()),
                    new ArrayItemType_('attributes', new UnknownType()),
                ]);
            } elseif ($includedType->isInstanceOf(FractalNullResource::class)) {
                $schemas[] = new KeyedArrayType([
                    new ArrayItemType_('object', new LiteralStringType('null_resource')),
                    new ArrayItemType_('attributes', new NullType()),
                ]);
            }
        }

        return match (count($schemas)) {
            0 => new UnknownType(),
            1 => $schemas[0],
            default => new Union($schemas),
        };
    }
}
