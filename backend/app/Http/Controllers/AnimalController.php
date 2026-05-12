<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Support\ActivityLogger;
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

        ActivityLogger::log($request, $request->user(), 'animal.created', [
            'animal_id' => $animal->id,
            'animal_name' => $animal->name,
            'animal_image' => $animal->image,
        ]);

        return response()->json($this->animalArray($animal), 201);
    }

    public function update(Request $request, int $id)
    {
        $animal = Animal::query()->findOrFail($id);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'species' => ['sometimes', 'nullable', 'string', 'max:255'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:255'],
            'age' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'image' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
        ]);

        $animal->fill($data);
        $animal->save();

        ActivityLogger::log($request, $request->user(), 'animal.updated', [
            'animal_id' => $animal->id,
            'animal_name' => $animal->name,
            'animal_image' => $animal->image,
        ]);

        return response()->json($this->animalArray($animal));
    }

    public function destroy(Request $request, int $id)
    {
        $a = Animal::query()->find($id);
        if ($a) {
            ActivityLogger::log($request, $request->user(), 'animal.deleted', [
                'animal_id' => $id,
                'animal_name' => $a->name,
                'animal_image' => $a->image,
            ]);
        }

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
