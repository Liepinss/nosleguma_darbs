<?php

namespace App\Http\Controllers;

use App\Models\Animal;

class AnimalController extends Controller
{
    public function index()
    {
        return Animal::all();
    }

    public function store()
    {
        return Animal::create(request()->all());
    }

    public function show($id)
    {
        return Animal::findOrFail($id);
    }

    public function destroy($id)
    {
        Animal::destroy($id);
        return response()->json(['ok']);
    }
}