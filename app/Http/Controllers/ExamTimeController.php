<?php

namespace App\Http\Controllers;

use App\Models\ExamTime;
use Illuminate\Http\Request;

class ExamTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $examTimes = ExamTime::orderBy('sort_order')->orderBy('id')->get();

        return view('config.exam-times', compact('examTimes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        ExamTime::create([
            'label' => $validated['label'],
            'value' => $validated['value'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('config.exam-times.index')->with('success', 'Exam time added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ExamTime $examTime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExamTime $examTime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamTime $examTime)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $examTime->update([
            'label' => $validated['label'],
            'value' => $validated['value'],
            'sort_order' => $validated['sort_order'] ?? $examTime->sort_order,
            'is_active' => $validated['is_active'] ?? $examTime->is_active,
        ]);

        return redirect()->route('config.exam-times.index')->with('success', 'Exam time updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamTime $examTime)
    {
        $examTime->delete();

        return redirect()->route('config.exam-times.index')->with('success', 'Exam time deleted successfully.');
    }

    /**
     * Toggle the active status of an exam time slot.
     */
    public function toggleActive(ExamTime $examTime)
    {
        $examTime->is_active = !$examTime->is_active;
        $examTime->save();

        $status = $examTime->is_active ? 'activated' : 'deactivated';
        return redirect()->route('config.exam-times.index')
            ->with('success', "Exam time slot {$status} successfully.");
    }
}
