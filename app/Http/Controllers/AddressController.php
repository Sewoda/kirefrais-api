<?php
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
        \Illuminate\Support\Facades\Log::info('Tentative d\'ajout d\'adresse', [
            'user_id' => $request->user()->id,
            'data' => $request->all()
        ]);

        try {
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la création de l\'adresse', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erreur serveur lors de la création de l\'adresse.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, int $id)
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        $address->delete();
        return response()->json(['message' => 'Adresse supprimée.']);
    }
}
