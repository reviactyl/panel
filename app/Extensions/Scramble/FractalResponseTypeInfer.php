<?php

namespace App\Extensions\Scramble;

use App\Extensions\Spatie\Fractalistic\Fractal;
use App\Http\Controllers\Api\Application\ApplicationApiController;
use Dedoc\Scramble\Infer\Extensions\Event\MethodCallEvent;
use Dedoc\Scramble\Infer\Extensions\MethodReturnTypeExtension;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\GenericClassStringType;
use Dedoc\Scramble\Support\Type\IntegerType;
use Dedoc\Scramble\Support\Type\KeyedArrayType;
use Dedoc\Scramble\Support\Type\Literal\LiteralStringType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\StringType;
use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Type\UnknownType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

            return $class instanceof LiteralStringType ? new ObjectType($class->getValue()) : null;
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

        $operation = $operationType->getValue();
        $attributes = $event->scope->index->getClass($transformer->name)?->getMethodDefinition('transform')?->getReturnType();
        $resourceName = $event->scope->index->getClass($transformer->name)?->getMethodDefinition('getResourceName')?->getReturnType();
        if (! $attributes) {
            return null;
        }

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
}
