<?php
// app/Http/Controllers/Admin/AdminZoneController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

class AdminZoneController extends Controller
{
    public function index()
    {
        return response()->json(DeliveryZone::latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:100',
            'city'               => 'required|string|max:100',
            'delivery_fee'       => 'required|numeric|min:0',
            'estimated_minutes'  => 'required|integer|min:1',
        ]);

        $zone = DeliveryZone::create($request->all());
        return response()->json(['message' => 'Zone créée.', 'zone' => $zone], 201);
    }

    public function update(Request $request, int $id)
    {
        $zone = DeliveryZone::findOrFail($id);
        $zone->update($request->all());
        return response()->json(['message' => 'Zone mise à jour.', 'zone' => $zone]);
    }

    public function destroy(int $id)
    {
        DeliveryZone::findOrFail($id)->delete();
        return response()->json(['message' => 'Zone supprimée.']);
    }
}
