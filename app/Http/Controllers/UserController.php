<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Http\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(User::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user'=>'required|unique:users,user',
            'name'=>'required|string',
            'telephone'=>'required|string'
        ]);

        $newUser = User::create([
            'user_code'=>sprintf('%s-%s',now()->format('H:i'),now()->format('Y')),
            'user'=>$data['user'],
            'name'=>$data['name'],
            'telephone'=>$request->input('telephone'),
            'password'=>Hash::make(sprintf('%s.%s',now()->format('Y'),strtolower($data['user'])))
        ]);

        return response()->json($newUser);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'user'=>'required|unique:users,user',
            'name'=>'required|string',
            'telephone'=>'required|string'
        ]);

        $user->update([
            'user'=>$data['user'],
            'name'=>$data['name'],
            'telephone'=>$request->input('telephone'),
        ]);

        //TODO: almacenar foto de perfil

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $msg = sprintf('Usuario: %s eliminado',$user->name);

        $user->delete();

        return response()->json($msg);
    }
}
