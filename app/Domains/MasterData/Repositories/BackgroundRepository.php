<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Background;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BackgroundRepository
{
    public function getPaginated(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Background::with('media')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $term = '%' . strtolower($search) . '%';
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Background
    {
        return Background::with('media')->find($id);
    }

    public function create(array $data): Background
    {
        return Background::create($data);
    }

    public function update(Background $background, array $data): Background
    {
        $background->update($data);

        return $background;
    }

    public function delete(Background $background): ?bool
    {
        return $background->delete();
    }
}
