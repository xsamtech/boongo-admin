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
    public function preview(Request $request, Work $work, int $file, \App\Services\WorkDocumentPreview $preview)
    {
        $data = $request->validate(['offset' => 'sometimes|integer|min:0', 'length' => 'sometimes|integer|min:1|max:262144']);
        $record = $work->files()->findOrFail($file);
        $extension = strtolower(pathinfo(parse_url($record->file_url, PHP_URL_PATH), PATHINFO_EXTENSION));
        abort_unless(in_array($extension, ['pdf', 'doc', 'docx', 'txt'], true), 415);
        $result = $extension === 'pdf'
            ? $preview->read($record->file_url, (int) ($data['offset'] ?? 0), (int) ($data['length'] ?? 65536))
            : ['text' => $preview->excerpt($record->file_url)];

        return response()->json($result)->header('Cache-Control', 'private, no-store');
    }

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
            'media_length' => ['nullable', 'regex:/^\d{2}:[0-5]\d:[0-5]\d$/'],
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'replacement' => str_replace('required|', 'nullable|', WorkFiles::RULES['files_urls.*']),
        ]);
        if (array_key_exists('media_length', $data) && $data['media_length'] !== null) {
            [$hours, $minutes, $seconds] = array_map('intval', explode(':', $data['media_length']));
            $data['media_length'] = $hours * 3600 + $minutes * 60 + $seconds;
        }
        $record = $work->files()->findOrFail($file);
        $stored = [];
        $oldCover = null;
        $oldFile = null;
        try {
            if ($request->hasFile('replacement')) {
                $upload = $request->file('replacement');
                [$kind, $type] = $files->classify($upload);
                $path = $files->store($upload, "{$kind}s/works/{$work->id}", $stored);
                $oldFile = $record->file_url;
                $data['file_url'] = Storage::disk('s3')->url($path);
                $data['type_id'] = $type->id;
                $data['file_name'] = $upload->getClientOriginalName();
                $oldCover = $record->part_cover_url;
                $data['part_cover_url'] = null;
                $data['media_length'] = $data['media_length'] ?? null;
            }
            if ($request->hasFile('cover')) {
                $oldCover = $record->part_cover_url;
                $path = $files->store($request->file('cover'), "images/works/{$work->id}/covers", $stored);
                $data['part_cover_url'] = Storage::disk('s3')->url($path);
            }
            unset($data['cover'], $data['replacement']);
            $record->update($data);
        } catch (\Throwable $error) {
            Storage::disk('s3')->delete($stored);
            throw $error;
        }
        $files->deleteUrl($oldCover);
        $files->deleteUrl($oldFile);

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Work $work)
    {
        $ids = array_map('intval', $request->validate(['ids' => 'required|array', 'ids.*' => 'required|integer|distinct'])['ids']);
        DB::transaction(function () use ($work, $ids) {
            $work->lockForUpdate()->findOrFail($work->id);
            $actual = $work->files()->pluck('id')->map(fn ($id) => (int) $id)->all();
            $sorted = $ids;
            sort($sorted);
            sort($actual);
            if ($sorted !== $actual) {
                throw \Illuminate\Validation\ValidationException::withMessages(['ids' => __('work_editor.order_changed')]);
            }
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
