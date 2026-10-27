<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Work;
use App\Services\WorkFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WorkFileController extends Controller
{
    public function store(Request $request, Work $work, WorkFiles $files)
    {
        $request->validate(array_replace(WorkFiles::RULES, ['files_urls' => 'required|array|min:1|max:50']));
        $stored = [];
        try {
            DB::transaction(function () use ($work, $request, $files, &$stored) {
                $work->lockForUpdate()->findOrFail($work->id);
                $files->append($work, $request, $stored);
            });
        } catch (\Throwable $error) {
            Storage::disk('s3')->delete($stored);
            throw $error;
        }

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Work $work, int $file, WorkFiles $files)
    {
        $data = $request->validate([
            'part_title' => 'nullable|string|max:1000', 'file_name' => 'sometimes|required|string|max:1000',
            'media_length' => 'nullable|integer|min:0',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);
        $record = $work->files()->findOrFail($file);
        $stored = [];
        $oldCover = null;
        try {
            if ($request->hasFile('cover')) {
                $oldCover = $record->part_cover_url;
                $path = $files->store($request->file('cover'), "images/works/{$work->id}/covers", $stored);
                $data['part_cover_url'] = Storage::disk('s3')->url($path);
            }
            unset($data['cover']);
            $record->update($data);
        } catch (\Throwable $error) {
            Storage::disk('s3')->delete($stored);
            throw $error;
        }
        $files->deleteUrl($oldCover);

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Work $work)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'required|integer|distinct'])['ids'];
        DB::transaction(function () use ($work, $ids) {
            $work->lockForUpdate()->findOrFail($work->id);
            $actual = $work->files()->pluck('id')->all();
            $sorted = $ids;
            sort($sorted);
            sort($actual);
            abort_unless($sorted === $actual, 422);
            foreach ($ids as $position => $id) {
                $work->files()->whereKey($id)->update(['position' => $position + 1]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function destroy(Work $work, int $file, WorkFiles $files)
    {
        $record = $work->files()->findOrFail($file);
        $files->deleteUrl($record->file_url);
        $files->deleteUrl($record->part_cover_url);
        $record->delete();

        return response()->json(['success' => true]);
    }
}
