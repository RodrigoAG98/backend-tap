<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Profile::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string',
            'sections'=>'required|array',
        ]);

        $newProfile = Profile::create([
            'profile_code'=>sprintf('%s-%s',now('H:i'),Str::limit($data['name'])),
            'name'=>$data['name'],
            'sections'=>$data['sections'],
        ]);

        return response()->json($newProfile);
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        return response()->json($profile);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $data = $request->validate([
            'name'=>'required|string',
            'sections'=>'required|array',
        ]);

        $profile->update([
            'name'=>$data['name'],
            'sections'=>$data['sections'],
        ]);

        return response()->json($profile);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        $msg = sprintf('Perfil: %s eliminado');

        $profile->delete();

        return response()->json($msg);
    }
}
