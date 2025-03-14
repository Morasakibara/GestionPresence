<?php

namespace App\Http\Controllers;

use App\Models\WorkplaceLocation;
use Illuminate\Http\Request;

class WorkplaceLocationController extends Controller
{
    public function index()
    {
        $locations = WorkplaceLocation::all();
        return view('admin.workplace-locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.workplace-locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'rayon' => 'required|integer|min:10|max:1000',
        ]);

        WorkplaceLocation::create($request->all());

        return redirect()->route('admin.workplace-locations.index')
            ->with('success', 'Lieu de travail ajouté avec succès.');
    }

    public function edit(WorkplaceLocation $workplaceLocation)
    {
        return view('admin.workplace-locations.edit', compact('workplaceLocation'));
    }

    public function update(Request $request, WorkplaceLocation $workplaceLocation)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'rayon' => 'required|integer|min:10|max:1000',
            'actif' => 'boolean',
        ]);

        $workplaceLocation->update($request->all());

        return redirect()->route('admin.workplace-locations.index')
            ->with('success', 'Lieu de travail mis à jour avec succès.');
    }

    public function destroy(WorkplaceLocation $workplaceLocation)
    {
        $workplaceLocation->delete();

        return redirect()->route('admin.workplace-locations.index')
            ->with('success', 'Lieu de travail supprimé avec succès.');
    }
}
