<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Type;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkFiles
{
    public const RULES = [
        'files_urls' => 'nullable|array|max:50',
        'files_urls.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,avi,mov,mkv,webm,pdf,doc,docx,txt,mp3,wav,flac,ogg,m4a|max:512000',
        'part_titles' => 'nullable|array',
        'part_titles.*' => 'nullable|string|max:1000',
        'files_names' => 'nullable|array',
        'files_names.*' => 'nullable|string|max:1000',
        'part_covers' => 'nullable|array',
        'part_covers.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
    ];

    public function append(Work $work, Request $request, array &$stored): void
    {
        $group = Group::where('group_name', 'Type de fichier')->firstOrFail();
        $types = Type::where('group_id', $group->id)->get();
        $position = (int) $work->files()->max('position');
        foreach ($request->file('files_urls', []) as $index => $upload) {
            $mime = $upload->getMimeType();
            $kind = str_starts_with($mime, 'audio/') ? 'audio' : ((str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/')) ? 'image' : 'document');
            $type = $types->first(fn ($type) => str_contains(Str::ascii(mb_strtolower($type->getTranslation('type_name', 'fr'))), $kind));
            if (! $type) {
                throw ValidationException::withMessages(['files_urls' => __('work_editor.type_missing')]);
            }
            $path = $this->store($upload, "{$kind}s/works/{$work->id}", $stored);
            $cover = isset($request->file('part_covers', [])[$index])
                ? $this->store($request->file('part_covers')[$index], "images/works/{$work->id}/covers", $stored) : null;
            $work->files()->create([
                'file_name' => $request->input("files_names.$index") ?: $upload->getClientOriginalName(), 'file_url' => Storage::disk('s3')->url($path),
                'part_title' => $request->input("part_titles.$index"),
                'part_cover_url' => $cover ? Storage::disk('s3')->url($cover) : null,
                'position' => ++$position, 'type_id' => $type->id,
            ]);
        }
    }

    public function store($upload, string $directory, array &$stored): string
    {
        $path = $upload->storeAs($directory, Str::uuid().'.'.$upload->extension(), 's3');
        if (! $path) {
            throw new \RuntimeException(__('work_editor.storage_error'));
        }
        $stored[] = $path;

        return $path;
    }

    public function deleteUrl(?string $url): void
    {
        if (! $url) {
            return;
        }
        $base = rtrim(Storage::disk('s3')->url(''), '/').'/';
        if (! str_starts_with($url, $base)) {
            throw new \RuntimeException(__('work_editor.storage_error'));
        }
        $path = substr($url, strlen($base));
        if (! preg_match('~^(images|documents|audios)/works/\d+/~', $path) || str_contains($path, '..')) {
            throw new \RuntimeException(__('work_editor.storage_error'));
        }
        if (! Storage::disk('s3')->delete($path)) {
            throw new \RuntimeException(__('work_editor.storage_error'));
        }
    }
}
