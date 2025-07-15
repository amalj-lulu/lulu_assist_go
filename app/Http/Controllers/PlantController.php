<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlantController extends Controller
{
    public function index()
    {
        $plants = Plant::all();
        return view('plants.index', compact('plants'));
    }

    public function create()
    {
        $plant = new Plant(); // empty instance for form binding

        if (request()->ajax()) {
            return view('plants.partials.form', [
                'plant' => $plant,
                'action' => route('plants.store'),
                'method' => 'POST',
                'isEdit' => false,
            ]);
        }

        return view('plants.create', compact('plant'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:plants,code',
            'is_warehouse' => 'nullable|boolean',
        ]);

        $validated['is_warehouse'] = $request->has('is_warehouse');
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_modal', true)
                ->with('modal_url', route('plants.create'));
        }

        $validated = $validator->validated();
        Plant::create($validated);

        session()->flash('success', 'Plant created successfully.');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => session('success'),
            ]);
        }

        return redirect()->route('plants.index');
    }

    public function show(Plant $plant)
    {
        if (request()->ajax()) {
            return view('plants.partials.show', compact('plant'));
        }

        return view('plants.show', compact('plant'));
    }


    public function edit(Plant $plant)
    {
        if (request()->ajax()) {
            return view('plants.partials.form', [
                'plant' => $plant,
                'action' => route('plants.update', $plant),
                'method' => 'PUT',
                'isEdit' => true,
            ]);
        }

        return view('plants.edit', [
            'plant' => $plant,
        ]);
    }


    public function update(Request $request, Plant $plant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:plants,code,' . $plant->id,
            'is_warehouse' => 'nullable|boolean',
        ]);

        $validated['is_warehouse'] = $request->has('is_warehouse');
        $plant->update($validated);

        // Set flash message for both cases
        session()->flash('success', 'Plant updated successfully.');

        // AJAX request: return JSON (message will be handled in JS)
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => session('success'), // pass flash message to JS
            ]);
        }

        // Non-AJAX: Redirect with flash
        return redirect()->route('plants.index');
    }

    public function destroy(Plant $plant)
    {
        $plant->delete();
        return redirect()->route('plants.index')->with('success', 'Plant deleted successfully.');
    }
}
