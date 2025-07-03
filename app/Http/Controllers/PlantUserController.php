<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;


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
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'mobile'   => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'plants'   => 'required|array',
            'plants.*' => 'exists:plants,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_modal', true)
                ->with('modal_url', route('plant-user.create'));
        }

        $validated = $validator->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile'   => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'user', // ensure the role is set
        ]);

        $user->plants()->sync($validated['plants']);

        return redirect()->route('plant-user.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing a user's details and plants.
     */
    public function edit(User $plant_user)
    {
        $plants = Plant::all();
        $user = $plant_user->load('plants');

        // No need to manually dump errors; Laravel handles this automatically
        return view('plant_user.edit', compact('plants', 'user'));
    }


    /**
     * Update the user and their plant assignments.
     */
    public function update(Request $request, User $plant_user)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($plant_user->id)],
            'mobile'   => 'required|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'plants'   => 'required|array',
            'plants.*' => 'exists:plants,id',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_modal', true)
                ->with('modal_url', route('plant-user.edit', $plant_user->id));
        }


        $validated = $validator->validated();

        $plant_user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile'   => $validated['mobile'],
            'password' => $validated['password']
                ? Hash::make($validated['password'])
                : $plant_user->password,
        ]);

        $plant_user->plants()->sync($validated['plants']);

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

    /**
     * Show the details of the user.
     */
    public function show(User $plant_user)
    {
        return view('plant_user.show', ['user' => $plant_user]);
    }
}
