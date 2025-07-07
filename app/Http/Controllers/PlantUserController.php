<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;


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
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_modal', true)
                ->with('modal_url', route('plant-user.create'));
        }

        $validated = $validator->validated();

        $profilePath = null;

        if ($request->hasFile('profile_picture')) {
            $profilePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        }


        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile'   => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'profile_picture' => $profilePath,
            'profile_thumb' => "GD",
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

    public function update(Request $request, User $plant_user)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', Rule::unique('users')->ignore($plant_user->id)],
            'mobile'          => 'required|string|max:20',
            'password'        => 'nullable|string|min:6|confirmed',
            'plants'          => 'required|array',
            'plants.*'        => 'exists:plants,id',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_modal', true)
                ->with('modal_url', route('plant-user.edit', $plant_user->id));
        }

        // 2. Apply Validated Fields
        $validated = $validator->validated();

        $plant_user->name   = $validated['name'];
        $plant_user->email  = $validated['email'];
        $plant_user->mobile = $validated['mobile'];

        // 3. Update password if provided
        if (!empty($validated['password'])) {
            $plant_user->password = bcrypt($validated['password']);
        }

        // 4. Handle Profile Picture
        if ($request->hasFile('profile_picture')) {
            // Delete old picture
            if ($plant_user->profile_picture && Storage::disk('public')->exists($plant_user->profile_picture)) {
                Storage::disk('public')->delete($plant_user->profile_picture);
            }

            // Store new file
            $profilePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $plant_user->profile_picture = $profilePath;
        }

        $plant_user->save();

        // 5. Sync Plants (assuming many-to-many relation)
        if ($request->has('plants')) {
            $plant_user->plants()->sync($validated['plants']);
        }

        // 6. Redirect back with success
        return redirect()->back()->with('success', 'Plant user updated successfully.');
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
