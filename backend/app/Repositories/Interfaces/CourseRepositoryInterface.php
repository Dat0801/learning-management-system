<?php

namespace App\Repositories\Interfaces;

interface CourseRepositoryInterface
{
    public function all(array $filters = []);
    public function getRecommended($limit = 3);
    public function getPopular($limit = 3);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
