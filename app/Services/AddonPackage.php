<?php

namespace App\Services;

use ZipArchive;

/**
 * Reads and validates an uploaded addon .zip — without extracting it. The
 * marketplace only needs the manifest (addon.json) for metadata; the zip itself
 * is stored as-is and served to LMS installs later.
 *
 * Separator normalisation handles Windows-made zips (PowerShell Compress-Archive
 * writes "\" which Linux treats as a literal filename char).
 */
class AddonPackage
{
    /** @return array the parsed addon.json manifest */
    public static function readManifest(string $zipPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Could not open the ZIP archive (it may be corrupt or not a real .zip).');
        }

        $manifest = null;
        $depth = PHP_INT_MAX;

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $raw = $zip->getNameIndex($i);
                $name = ltrim(str_replace('\\', '/', $raw), '/');

                if ($name === '' || str_contains($name, '..') || str_contains($name, ':')) {
                    throw new \RuntimeException('Archive contains an unsafe path: ' . $raw);
                }
                if (str_starts_with($name, '__MACOSX/') || str_starts_with(basename($name), '._')) {
                    continue;
                }

                if (basename($name) === 'addon.json') {
                    $d = substr_count($name, '/');
                    if ($d < $depth) {
                        $decoded = json_decode($zip->getFromIndex($i), true);
                        if (is_array($decoded)) {
                            $manifest = $decoded;
                            $depth = $d;
                        }
                    }
                }
            }
        } finally {
            $zip->close();
        }

        if (! is_array($manifest) || empty($manifest['name']) || empty($manifest['vendor'])) {
            throw new \RuntimeException('The archive is missing a valid addon.json (it must contain "name" and "vendor").');
        }

        return $manifest;
    }

    /** Convenience accessors with sensible fallbacks. */
    public static function displayName(array $m): string
    {
        return $m['display_name'] ?? $m['name'];
    }

    public static function version(array $m): string
    {
        return (string) ($m['version'] ?? '1.0.0');
    }
}
