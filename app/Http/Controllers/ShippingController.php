<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ResponseApi;

class ShippingController extends Controller
{
    use ResponseApi;
    // Koordinat tetap dari coffee shop
    private $shopLat = -6.753569079156668;
    private $shopLng = 110.84238253895886;

    public function calculateOngkir(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $userLat = $request->latitude;
        $userLng = $request->longitude;

        $distance = $this->calculateDistance($this->shopLat, $this->shopLng, $userLat, $userLng); // in KM

        // Tentukan ongkir berdasarkan jarak (bisa disesuaikan)
        if ($distance <= 2) {
            $ongkir = 0;
        } elseif ($distance <= 5) {
            $ongkir = 5000;
        } elseif ($distance <= 10) {
            $ongkir = 10000;
        } else {
            return $this->success([
                'success' => false,
                'message' => 'Diluar jangkauan pengiriman. Maksimal 10KM.',
                'distance_km' => round($distance, 2)
            ], 400);
        }

        return $this->success([
            'success' => true,
            'ongkir' => $ongkir,
            'distance_km' => round($distance, 2)
        ]);
    }

    // Fungsi menghitung jarak antara 2 koordinat (Haversine)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Kilometer
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}
