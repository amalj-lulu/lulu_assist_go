<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PlantUserController extends Controller
{
    /**
     * Display a listing of the users with plants.
     */
    public function index()
    {
        $users = User::where('role', 'user')
            ->with('plants')
            ->paginate(10);

        return view('plant_user.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $plants = Plant::all();
        return view('plant_user.create', compact('plants'));
    }

    /**
     * Store a newly created user and assign plants.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'plants'   => 'nullable|array',
            'plants.*' => 'exists:plants,id',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile'   => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $user->plants()->sync($validated['plants'] ?? []);

        return redirect()->route('plant-user.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing a user's details and plants.
     */
    public function edit(User $plant_user)
    {
        $plants = Plant::all();
        $user = $plant_user->load('plants');
        return view('plant_user.edit', compact('user', 'plants'));
    }

    /**
     * Update the user and their plant assignments.
     */
    public function update(Request $request, User $plant_user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($plant_user->id)],
            'mobile'   => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'plants'   => 'nullable|array',
            'plants.*' => 'exists:plants,id',
        ]);

        $plant_user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile'   => $validated['mobile'] ?? null,
            'password' => $validated['password'] ? Hash::make($validated['password']) : $plant_user->password,
        ]);

        $plant_user->plants()->sync($validated['plants'] ?? []);

        return redirect()->route('plant-user.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $plant_user)
    {
        $plant_user->plants()->detach();
        $plant_user->delete();

        return redirect()->route('plant-user.index')->with('success', 'User deleted successfully.');
    }
    public function show(User $plant_user)  // match the route param name
    {
        return view('plant_user.show', ['user' => $plant_user]);
    }
}
