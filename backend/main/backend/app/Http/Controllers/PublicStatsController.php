<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\User;

class PublicStatsController extends Controller
{
    /**
     * Publiskas statistikas mājas lapai (bez autentifikācijas).
     */
    public function index()
    {
        $takenIds = AdoptionApplication::query()->pluck('animal_id')->unique()->filter()->values();

        $animalsTotal = Animal::query()->count();
        $animalsAvailable = $takenIds->isEmpty()
            ? $animalsTotal
            : Animal::query()->whereNotIn('id', $takenIds->all())->count();

        return response()->json([
            'animals_total' => $animalsTotal,
            'animals_available' => $animalsAvailable,
            'adoption_applications_total' => AdoptionApplication::query()->count(),
            'registered_users_total' => User::query()->count(),
        ]);
    }
}
