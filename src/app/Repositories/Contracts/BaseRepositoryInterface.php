<?php

namespace App\Repositories\Contracts;

interface BaseRepositoryInterface
{
    public function all();
    public function paginate(?int $perPage = null);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}