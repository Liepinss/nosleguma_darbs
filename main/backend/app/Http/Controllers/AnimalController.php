<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\Animal;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function index()
    {
        $takenIds = AdoptionApplication::query()->pluck('animal_id')->unique()->values()->all();

        return response()->json([
            'animals' => Animal::query()->orderBy('id')->get()->map(fn (Animal $a) => $this->animalArray($a)),
            'taken_animal_ids' => $takenIds,
        ]);
    }

    public function show(int $id)
    {
        $animal = Animal::findOrFail($id);

        return response()->json($this->animalArray($animal));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'species' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:2048'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $data['species'] = $data['species'] ?? 'Cits';
        $data['gender'] = $data['gender'] ?? null;
        $data['description'] = $data['description'] ?? '';
        $data['category_id'] = $data['category_id'] ?? 1;

        $animal = Animal::create($data);

        return response()->json($this->animalArray($animal), 201);
    }

    public function destroy(int $id)
    {
        Animal::destroy($id);

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function animalArray(Animal $a): array
    {
        return [
            'id' => $a->id,
            'name' => $a->name,
            'species' => $a->species,
            'gender' => $a->gender,
            'age' => $a->age,
            'description' => $a->description,
            'image' => $a->image,
            'category_id' => $a->category_id,
        ];
    }
}
