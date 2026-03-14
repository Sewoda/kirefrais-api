<?php
// app/Http/Controllers/AddressController.php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Http\Requests\Address\StoreAddressRequest;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->addresses()->with('deliveryZone')->get());
    }

    public function store(StoreAddressRequest $request)
    {
        // Si c'est l'adresse par défaut, on enlève l'ancien flag des autres adresses
        if ($request->is_default) {
            Address::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $address = Address::create([
            'user_id'          => $request->user()->id,
            'delivery_zone_id' => $request->delivery_zone_id,
            'label'            => $request->label,
            'address_text'     => $request->address_text,
            'landmark'         => $request->landmark,
            'latitude'         => $request->latitude,
            'longitude'        => $request->longitude,
            'city'             => $request->city ?? 'Lomé',
            'is_default'       => $request->is_default ?? false,
        ]);

        return response()->json($address->load('deliveryZone'), 201);
    }

    public function destroy(Request $request, int $id)
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        $address->delete();
        return response()->json(['message' => 'Adresse supprimée.']);
    }
}
