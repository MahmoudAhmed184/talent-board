<?php

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

function appModelClassesForAttributeAudit(): array
{
    $classes = [];

    foreach (glob(dirname(__DIR__, 2).'/app/Models/*.php') ?: [] as $file) {
        $class = 'App\\Models\\'.pathinfo($file, PATHINFO_FILENAME);

        if (class_exists($class) && is_subclass_of($class, Model::class)) {
            $classes[] = $class;
        }
    }

    sort($classes);

    return $classes;
}

function shortAttributeName(string $attribute): string
{
    $position = strrpos($attribute, '\\');

    return $position === false ? $attribute : substr($attribute, $position + 1);
}

test('app models declare attribute based metadata', function () {
    $models = appModelClassesForAttributeAudit();
    $requiredAttributes = [Table::class, Fillable::class, Hidden::class];
    $missing = [];

    foreach ($models as $model) {
        $reflection = new ReflectionClass($model);

        foreach ($requiredAttributes as $attribute) {
            if ($reflection->getAttributes($attribute) === []) {
                $missing[] = sprintf('%s is missing #[%s]', $model, shortAttributeName($attribute));
            }
        }
    }

    expect($models)->not->toBeEmpty()
        ->and($missing)->toBeEmpty();
});

test('app models do not declare legacy metadata property arrays', function () {
    $legacyProperties = ['table', 'fillable', 'guarded', 'hidden', 'visible', 'appends'];
    $regressions = [];

    foreach (appModelClassesForAttributeAudit() as $model) {
        $reflection = new ReflectionClass($model);

        foreach ($legacyProperties as $property) {
            if ($reflection->hasProperty($property)
                && $reflection->getProperty($property)->getDeclaringClass()->getName() === $model) {
                $regressions[] = sprintf('%s declares $%s', $model, $property);
            }
        }
    }

    expect($regressions)->toBeEmpty();
});
