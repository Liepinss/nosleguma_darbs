<?php

namespace App\Http\Controllers;

use App\Models\AdoptionApplication;
use App\Models\Animal;
use Illuminate\Http\Request;

class AdoptionApplicationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'animal_id' => ['required', 'integer', 'exists:animals,id'],
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:32'],
            'address' => ['nullable', 'string'],
            'experience' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $application = AdoptionApplication::create([
            'user_id' => $user->id,
            'animal_id' => $data['animal_id'],
            'applicant_name' => $data['applicant_name'],
            'applicant_email' => $data['applicant_email'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'experience' => $data['experience'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        $application->load('animal');

        return response()->json($this->applicationArray($application), 201);
    }

    public function mine(Request $request)
    {
        $rows = AdoptionApplication::query()
            ->where('user_id', $request->user()->id)
            ->with('animal')
            ->orderByDesc('id')
            ->get();

        return response()->json($rows->map(fn (AdoptionApplication $a) => $this->applicationArray($a)));
    }

    public function destroyMine(Request $request, int $id)
    {
        $application = AdoptionApplication::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($id)
            ->firstOrFail();

        $application->delete();

        return response()->json(['ok' => true]);
    }

    public function adminIndex()
    {
        $rows = AdoptionApplication::query()->with(['animal', 'user'])->orderByDesc('id')->get();

        return response()->json($rows->map(fn (AdoptionApplication $a) => $this->applicationArray($a)));
    }

    public function adminDestroy(int $id)
    {
        AdoptionApplication::query()->whereKey($id)->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function applicationArray(AdoptionApplication $a): array
    {
        $animal = $a->relationLoaded('animal') ? $a->animal : $a->animal()->first();

        return [
            'id' => $a->id,
            'user_id' => $a->user_id,
            'animal_id' => $a->animal_id,
            'animal_name' => $animal?->name,
            'animal_image' => $animal?->image,
            'applicant_name' => $a->applicant_name,
            'applicant_email' => $a->applicant_email,
            'phone' => $a->phone,
            'address' => $a->address,
            'experience' => $a->experience,
            'message' => $a->message,
            'status' => $a->status,
            'submitted_at' => $a->created_at?->toIso8601String(),
        ];
    }
}
