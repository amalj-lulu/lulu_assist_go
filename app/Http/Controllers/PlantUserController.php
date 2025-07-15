<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PlantUserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->with('plants')->get();
        return view('plant_user.index', compact('users'));
    }

    public function create()
    {
        $plants = Plant::all();
        $user = new User();
        $user->setRelation('plants', collect());

        if (request()->ajax()) {
            return view('plant_user.partials.form', [
                'user' => $user,
                'plants' => $plants,
                'action' => route('plant-user.store'),
                'method' => 'POST',
                'isEdit' => false,
            ]);
        }

        return view('plant_user.create', compact('plants'));
    }

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
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $profilePath = $request->file('profile_picture')?->store('profile_pictures', 'public');

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile'   => $validated['mobile'],
            'password' => Hash::make($validated['password']),
            'profile_picture' => $profilePath,
            'role' => 'user',
        ]);

        $user->plants()->sync($validated['plants']);

        session()->flash('success', 'User created successfully.');

        return $request->ajax()
            ? response()->json(['success' => true])
            : redirect()->route('plant-user.index');
    }

    public function edit(User $plant_user)
    {
        $plants = Plant::all();
        $plant_user->load('plants');

        if (request()->ajax()) {
            return view('plant_user.partials.form', [
                'user' => $plant_user,
                'plants' => $plants,
                'action' => route('plant-user.update', $plant_user),
                'method' => 'PUT',
                'isEdit' => true,
            ]);
        }

        return view('plant_user.edit', compact('plant_user', 'plants'));
    }

    public function update(Request $request, User $plant_user)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($plant_user->id)],
            'mobile'   => 'required|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'plants'   => 'required|array',
            'plants.*' => 'exists:plants,id',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $plant_user->fill([
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'mobile' => $validated['mobile'],
        ]);

        if (!empty($validated['password'])) {
            $plant_user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($plant_user->profile_picture && Storage::disk('public')->exists($plant_user->profile_picture)) {
                Storage::disk('public')->delete($plant_user->profile_picture);
            }
            $plant_user->profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $plant_user->save();
        $plant_user->plants()->sync($validated['plants']);

        session()->flash('success', 'User updated successfully.');

        return $request->ajax()
            ? response()->json(['success' => true])
            : redirect()->route('plant-user.index');
    }

    public function destroy(User $plant_user)
    {
        $plant_user->plants()->detach();
        $plant_user->delete();

        session()->flash('success', 'User deleted successfully.');

        return request()->ajax()
            ? response()->json(['success' => true])
            : redirect()->route('plant-user.index');
    }

    public function show(User $plant_user)
    {
        return request()->ajax()
            ? view('plant_user.partials.show', ['user' => $plant_user])
            : view('plant_user.show', ['user' => $plant_user]);
    }
}
