<?php

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Background;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BackgroundRepository
{
    /**
     * Query pencarian background
     * @param ?string $search
     */
    public function searchQuery(?string $search,): Builder
    {
        $query = Background::with('media')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $term = '%' . strtolower($search) . '%';
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        return $query;
    }

    /**
     * Blueprint query background yang aktif.
     */
    public function queryActive(): Builder
    {
        return Background::where('is_active', true);
    }

    /**
     * Query total background count()
     */
    public function countBackground(): int
    {
        return Background::count();
    }

    /**
     * Mengambil semua background yang aktif.
     */
    public function getActive(): Collection
    {
        return $this->queryActive()->get();
    }

    /**
     * Menghitung total background yang aktif.
     */
    public function countActive(): int
    {
        return $this->queryActive()->count();
    }

    /**
     * Mencari background berdasarkan id
     * @param int $id
     */
    public function findById(int $id): ?Background
    {
        return Background::with('media')->find($id);
    }

    /**
     * Membuat background
     * @param array $data
     */
    public function create(array $data): Background
    {
        return Background::create($data);
    }

    /**
     * Memperbarui background
     * @param Background $background
     * @param array $data
     */
    public function update(Background $background, array $data): Background
    {
        $background->update($data);
        return $background;
    }

    /**
     * Menghapus background
     * @param Background $background
     */
    public function delete(Background $background): ?bool
    {
        return $background->delete();
    }
}
