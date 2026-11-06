<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Type;
use App\Models\Work;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $position = (int) $work->files()->max('position');
        foreach ($request->file('files_urls', []) as $index => $upload) {
            [$kind, $type] = $this->classify($upload);
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

    public function classify($upload): array
    {
        $mime = $upload->getMimeType() ?? '';
        $kind = str_starts_with($mime, 'audio/') ? 'audio' : ((str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/')) ? 'image' : 'document');
        $group = Group::where('group_name', 'Type de fichier')->firstOrFail();
        $type = Type::where('group_id', $group->id)->get()->first(
            fn ($type) => str_contains(Str::ascii(mb_strtolower($type->getTranslation('type_name', 'fr'))), $kind)
        );
        if (! $type) {
            throw ValidationException::withMessages(['replacement' => __('work_editor.type_missing')]);
        }

        return [$kind, $type];
    }

    public function store($upload, string $directory, array &$stored): string
    {
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$upload->extension();
        try {
            try {
                $this->writeUpload($upload, $path);
            } catch (\Throwable $error) {
                // Buckets with ACLs disabled reject the disk's public-read ACL.
                // Retry only this explicit error, keeping all other failures visible.
                if ($this->awsError($error)?->getAwsErrorCode() !== 'AccessControlListNotSupported') {
                    throw $error;
                }
                $this->writeUpload($upload, $path, ['ACL' => 'bucket-owner-full-control']);
            }
        } catch (\Throwable $error) {
            $aws = $this->awsError($error);
            Log::error('Work file upload failed', [
                'disk' => 's3', 'path' => $path,
                'aws_error_code' => $aws?->getAwsErrorCode(),
                'aws_error_message' => $aws?->getAwsErrorMessage(),
                'http_status' => $aws?->getStatusCode(),
                'exception_class' => $error::class,
            ]);
            $code = $aws?->getAwsErrorCode();
            throw new \RuntimeException(__('work_editor.storage_error').($code ? ' (S3: '.$code.')' : ''), 0, $error);
        }
        $stored[] = $path;

        return $path;
    }

    private function writeUpload($upload, string $path, array $options = []): void
    {
        $stream = fopen($upload->getRealPath(), 'rb');
        if ($stream === false) {
            throw new \RuntimeException('Unable to read uploaded file.');
        }
        try {
            // Flysystem throws the original storage error instead of returning
            // false when the Laravel disk has throw=false (including cached config).
            Storage::disk('s3')->getDriver()->writeStream($path, $stream, $options);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function awsError(\Throwable $error): ?AwsException
    {
        do {
            if ($error instanceof AwsException) {
                return $error;
            }
            $error = $error->getPrevious();
        } while ($error);

        return null;
    }

    public function deleteUrl(?string $url): void
    {
        if (! $url) {
            return;
        }
        $path = $this->pathForUrl($url);
        if (! Storage::disk('s3')->delete($path)) {
            throw new \RuntimeException(__('work_editor.storage_error'));
        }
    }

    public function pathForUrl(string $url): string
    {
        $base = rtrim(Storage::disk('s3')->url(''), '/').'/';
        if (! str_starts_with($url, $base)) {
            throw new \RuntimeException(__('work_editor.storage_error'));
        }
        $path = substr($url, strlen($base));
        if (! preg_match('~^(images|documents|audios)/works/\d+/~', $path) || str_contains($path, '..')) {
            throw new \RuntimeException(__('work_editor.storage_error'));
        }

        return $path;
    }
}
