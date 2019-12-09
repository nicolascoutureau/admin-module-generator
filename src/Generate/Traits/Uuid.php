<?php

namespace Elifbyte\AdminGenerator\Generate\Traits;

use Ramsey\Uuid\Uuid as Generator;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

trait Uuid
{
    protected static function bootUuid()
    {
        static::creating(function ($model) {
            try {
                $model->{$model->getKeyName()} = Generator::uuid4()->toString();
            } catch (UnsatisfiedDependencyException $e) {
                abort(500, $e->getMessage());
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'uuid';
    }
}