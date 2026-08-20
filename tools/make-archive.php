<?php

declare(strict_types=1);

/**
 * Zips a directory and writes a sha256 checksum beside it.
 *
 * Uses PHP's own zip extension rather than a `zip` binary, because Git Bash
 * on Windows does not ship one. A build script that only runs on the CI
 * runner cannot be debugged anywhere else.
 *
 * Usage: php tools/make-archive.php build/dist/projstart-v1.0.0
 */

/** Windows hands back paths with backslash separators; zip entries need slashes. */
const WINDOWS_SEPARATOR = DIRECTORY_SEPARATOR;

function normalise(string $path): string
{
    return str_replace(WINDOWS_SEPARATOR, '/', $path);
}

$sourceDir = $argv[1] ?? null;

if ($sourceDir === null) {
    fwrite(STDERR, 'usage: php tools/make-archive.php <directory>' . PHP_EOL);
    exit(1);
}

$sourceDir = rtrim(normalise($sourceDir), '/');

if (!is_dir($sourceDir)) {
    fwrite(STDERR, sprintf('Not a directory: "%s".%s', $sourceDir, PHP_EOL));
    exit(1);
}

$archivePath = $sourceDir . '.zip';
$parentDir = normalise(dirname($sourceDir));

$zip = new ZipArchive();

if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, sprintf('Could not create archive "%s".%s', $archivePath, PHP_EOL));
    exit(1);
}

$entries = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST,
);

$fileCount = 0;

foreach ($entries as $entry) {
    if (!$entry instanceof SplFileInfo) {
        continue;
    }

    $absolute = normalise($entry->getPathname());
    // Keep the top-level directory inside the archive, so unzipping
    // produces one folder rather than spraying files into the cwd.
    $relative = substr($absolute, strlen($parentDir) + 1);

    if ($entry->isDir()) {
        $zip->addEmptyDir($relative);

        continue;
    }

    $zip->addFile($absolute, $relative);
    ++$fileCount;
}

$zip->close();

$checksum = hash_file('sha256', $archivePath);

if ($checksum === false) {
    fwrite(STDERR, sprintf('Could not hash "%s".%s', $archivePath, PHP_EOL));
    exit(1);
}

$checksumLine = sprintf('%s  %s%s', $checksum, basename($archivePath), PHP_EOL);
file_put_contents($archivePath . '.sha256', $checksumLine);

printf(
    'Archived %d files into %s (%s bytes)%s',
    $fileCount,
    $archivePath,
    number_format((float) filesize($archivePath), 0, '.', ' '),
    PHP_EOL,
);

echo $checksumLine;
