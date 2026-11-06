<?php

namespace App\Services;

use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;

class WorkDocumentPreview
{
    public function read(string $url, int $offset, int $length): array
    {
        $path = app(WorkFiles::class)->pathForUrl($url);
        $disk = Storage::disk('s3');
        $size = $disk->size($path);
        abort_if($offset >= $size, 416);
        $length = min($length, $size - $offset);
        if ($disk instanceof AwsS3V3Adapter) {
            $config = $disk->getConfig();
            $result = $disk->getClient()->getObject([
                'Bucket' => $config['bucket'],
                'Key' => ltrim(rtrim($config['root'] ?? '', '/').'/'.$path, '/'),
                'Range' => 'bytes='.$offset.'-'.($offset + $length - 1),
            ]);
            $bytes = (string) $result['Body'];
        } else {
            $stream = $disk->readStream($path);
            if (! is_resource($stream)) {
                throw new \RuntimeException(__('work_editor.storage_error'));
            }
            try {
                if (fseek($stream, $offset) !== 0) {
                    throw new \RuntimeException(__('work_editor.storage_error'));
                }
                $bytes = stream_get_contents($stream, $length);
            } finally {
                fclose($stream);
            }
        }

        return ['size' => $size, 'offset' => $offset, 'data' => base64_encode($bytes)];
    }

    public function excerpt(string $url): string
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if ($extension === 'txt') {
            $chunk = $this->read($url, 0, 16384);

            return mb_substr(mb_convert_encoding(base64_decode($chunk['data']), 'UTF-8', 'UTF-8,Windows-1252'), 0, 2400);
        }
        if ($extension !== 'docx') {
            return '';
        }
        $path = app(WorkFiles::class)->pathForUrl($url);
        abort_if(Storage::disk('s3')->size($path) > 20 * 1024 * 1024, 413);
        $temporary = tmpfile();
        $source = Storage::disk('s3')->readStream($path);
        try {
            if (! is_resource($source) || ! is_resource($temporary)) {
                throw new \RuntimeException(__('work_editor.storage_error'));
            }
            stream_copy_to_stream($source, $temporary);
            $zip = new \ZipArchive;
            if ($zip->open(stream_get_meta_data($temporary)['uri']) !== true) {
                return '';
            }
            try {
                $stat = $zip->statName('word/document.xml');
                if (! $stat || $stat['size'] > 8 * 1024 * 1024) {
                    return '';
                }
                $xml = $zip->getFromName('word/document.xml');
            } finally {
                $zip->close();
            }
            $xml = preg_split('/<w:br\b[^>]*w:type="page"[^>]*\/>|<w:lastRenderedPageBreak\s*\/>/', $xml, 2)[0];
            $xml = preg_replace('/<\/w:p>/', "\n", $xml);

            return mb_substr(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'), 0, 2400);
        } finally {
            if (is_resource($source)) {
                fclose($source);
            }
            if (is_resource($temporary)) {
                fclose($temporary);
            }
        }
    }
}
