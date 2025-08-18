<?php

namespace zxf\Laravel\BuilderQuery;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;

class WhereHasJoin
{
    /**
     * @var Eloquent\Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $relation;

    /**
     * @var string
     */
    protected $nextRelation;

    /**
     * @var \Closure
     */
    protected $callback;

    /**
     * @var string
     */
    protected $method = 'join';

    public function __construct(Eloquent\Builder $builder, $relation, $callback)
    {
        $this->builder = $builder;
        $this->relation = $relation;
        $this->callback = $callback;
    }

    /**
     * @return Eloquent\Builder
     *
     * @throws \Exception
     */
    public function execute()
    {
        if (! $this->relation) {
            return $this->builder;
        }

        return $this->where(
            $this->formatRelation()
        );
    }

    /**
     * @param  Relations\Relation  $relation
     * @return Eloquent\Builder
     *
     * @throws \Exception
     */
    protected function where($relation)
    {

        $relationQuery = $this->getRelationQuery($relation);

        $method = $this->method;

        if ($relation instanceof Relations\HasOne || $relation instanceof Relations\BelongsTo) {

            $relationTable = $relationQuery->getModel()->getTable();
            if ($relation instanceof Relations\BelongsTo) {
                $first = $relation->getQualifiedForeignKeyName();
                $second = $relation->getQualifiedOwnerKeyName();
            } else {
                $first = $relationTable.'.'.$relation->getForeignKeyName();
                $secTable = $this->builder->getModel()->getTable();
                $second = $secTable.'.'.$relation->getLocalKeyName();
            }

            if (collect($this->builder->getQuery()->joins)->where('table', $relationTable)->count() == 0) {
                $this->builder->{$method}($relationTable, $first, $second);
            }

            return $this->builder->where($this->withRelationQueryCallback($relationQuery));
        }

        throw new \Exception(sprintf('%s does not support "whereHasJoin".', get_class($relation)));
    }

    /**
     * @param  Relations\Relation  $relation
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getRelationQuery($relation)
    {
        $q = $relation->getQuery();

        if ($this->builder->getModel()->getConnectionName() !== $q->getModel()->getConnectionName()) {
            $databaseName = $this->getRelationDatabaseName($q);
            $table = $q->getModel()->getTable();

            if (! Str::contains($table, ["`$databaseName`.", "{$databaseName}."])) {
                $q->from("{$databaseName}.{$table}");
            }
        }

        return $q;
    }

    protected function getRelationDatabaseName($q)
    {
        return config('database.connections.'.$q->getModel()->getConnectionName().'.database');
    }

    protected function getRelationQualifiedForeignKeyName($relation)
    {
        if (method_exists($relation, 'getQualifiedForeignKeyName')) {
            return $relation->getQualifiedForeignKeyName();
        }

        return $relation->getQualifiedForeignKey();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function formatRelation()
    {
        if (is_object($this->relation)) {
            $relation = $this->relation;
        } else {
            $relationNames = explode('.', $this->relation);
            $this->nextRelation = implode('.', array_slice($relationNames, 1));

            $currentRelationMethod = $relationNames[0];

            $relation = Relations\Relation::noConstraints(function () use ($currentRelationMethod) {
                return $this->builder->getRelation($currentRelationMethod);

                //                return $this->builder->getModel()->$method();
            });
            //            dd($relation->getRelated());
        }

        return $relation;
    }

    /**
     * @param  Eloquent\Builder  $relation
     * @return Eloquent\Builder
     */
    protected function withRelationQueryCallback($relationQuery)
    {
        $callback = $this->callback;

        return function ($query) use ($callback, $relationQuery) {
            $callback($query, $relationQuery);
        };
    }
}
