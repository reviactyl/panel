<?php

namespace App\Contracts\Repository;

use App\Exceptions\Model\DataValidationException;
use App\Exceptions\Repository\RecordNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * Return an identifier or Model object to be used by the repository.
     */
    public function model(): string;

    /**
     * Return the model being used for this repository instance.
     */
    public function getModel(): Model;

    /**
     * Returns an instance of a query builder.
     */
    public function getBuilder(): Builder;

    /**
     * Returns the columns to be selected or returned by the query.
     */
    public function getColumns(): array;

    /**
     * An array of columns to filter the response by.
     */
    public function setColumns(array|string $columns = ['*']): self;

    /**
     * Stop repository update functions from returning a fresh
     * model when changes are committed.
     */
    public function withoutFreshModel(): self;

    /**
     * Return a fresh model with a repository updates a model.
     */
    public function withFreshModel(): self;

    /**
     * Set whether the repository should return a fresh model
     * when changes are committed.
     */
    public function setFreshModel(bool $fresh = true): self;

    /**
     * Create a new model instance and persist it to the database.
     *
     * @throws DataValidationException
     */
    public function create(array $fields, bool $validate = true, bool $force = false): mixed;

    /**
     * Find a model that has the specific ID passed.
     *
     * @throws RecordNotFoundException
     */
    public function find(int $id): mixed;

    /**
     * Find a model matching an array of where clauses.
     */
    public function findWhere(array $fields): Collection;

    /**
     * Find and return the first matching instance for the given fields.
     *
     * @throws RecordNotFoundException
     */
    public function findFirstWhere(array $fields): mixed;

    /**
     * Return a count of records matching the passed arguments.
     */
    public function findCountWhere(array $fields): int;

    /**
     * Delete a given record from the database.
     */
    public function delete(int $id): int;

    /**
     * Delete records matching the given attributes.
     */
    public function deleteWhere(array $attributes): int;

    /**
     * Update a given record with the passed array of fields.
     *
     * @throws DataValidationException
     * @throws RecordNotFoundException
     */
    public function update(int|string $id, array $fields, bool $validate = true, bool $force = false): mixed;

    /**
     * Perform a mass update where matching records are updated using whereIn.
     * This does not perform any model data validation.
     */
    public function updateWhereIn(string $column, array $values, array $fields): int;

    /**
     * Update a record if it exists in the database, otherwise create it.
     *
     * @throws DataValidationException
     */
    public function updateOrCreate(array $where, array $fields, bool $validate = true, bool $force = false): mixed;

    /**
     * Return all records associated with the given model.
     */
    public function all(): Collection;

    /**
     * Return a paginated result set using a search term if set on the repository.
     */
    public function paginated(int $perPage): LengthAwarePaginator;

    /**
     * Insert a single or multiple records into the database at once skipping
     * validation and mass assignment checking.
     */
    public function insert(array $data): bool;

    /**
     * Insert multiple records into the database and ignore duplicates.
     */
    public function insertIgnore(array $values): bool;

    /**
     * Get the amount of entries in the database.
     */
    public function count(): int;
}
