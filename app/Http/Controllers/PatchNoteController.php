<?php
namespace App\Http\Controllers;

use App\Models\PatchNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatchNoteController extends Controller
{
    public function index()
    {
        $patchNotes = PatchNote::orderByDesc('date')->get();
        return view('patch-notes.index', compact('patchNotes'));
    }

    public function edit($id)
    {
        $patchNote = PatchNote::findOrFail($id);
        return view('patch-notes.edit', compact('patchNote'));
    }

    public function show($id)
    {
        $patchNote = PatchNote::findOrFail($id);
        return view('patch-notes.show', compact('patchNote'));
    }

    public function update(Request $request, $id)
    {
        $patchNote = PatchNote::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $patchNote->content = $request->input('content');
        $patchNote->save();

        return redirect()->route('patch-notes.index')->with('success', 'Patch note atualizado com sucesso!');
    }
}

