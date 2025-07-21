<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category; 
use App\Models\Building;
class CampusMapController extends Controller
{
    public function index() {
       $buildings = [
        [
            'id' => 1,
            'name' => 'Admin Building',
            'description' => 'Handles all administrative tasks.',
            'category' => 'Admin',
            'rooms' => ['101', '102'],
            'coordinates' => '8.2365, 124.2453',
        ],
        [
            'id' => 2,
            'name' => 'Science Hall',
            'description' => 'Science laboratory and lecture rooms.',
            'category' => 'Academic',
            'rooms' => ['201', '202'],
            'coordinates' => '8.2372, 124.2458',
        ],
    ];

    $categories = ['Academic', 'Facility', 'Admin'];

    return view('campus-map', compact('buildings', 'categories'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'rooms' => 'nullable|array',
            'rooms.*' => 'string|nullable'
        ]);

        Building::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'rooms' => $request->rooms,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->back()->with('success', 'Building added successfully.');
    }

    public function update(Request $request, Building $building)
    {
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'rooms' => 'nullable|array',
            'rooms.*' => 'string|nullable'
        ]);

        $building->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'rooms' => $request->rooms,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->back()->with('success', 'Building updated successfully.');
    }

    public function destroy(Building $building)
    {
        $building->delete();
        return redirect()->back()->with('success', 'Building deleted successfully.');
    }
}
