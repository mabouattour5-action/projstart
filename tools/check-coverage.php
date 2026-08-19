<?php

declare(strict_types=1);

/**
 * Fails the build when line coverage drops below a threshold.
 *
 * PHPUnit reports coverage but will not fail on it, so the pipeline needs
 * this. Reporting a number nobody enforces is how coverage quietly rots.
 *
 * Usage: php tools/check-coverage.php build/logs/clover.xml 90
 */

$reportPath = $argv[1] ?? 'build/logs/clover.xml';
$minimum = (float) ($argv[2] ?? 90.0);

if (!is_file($reportPath)) {
    fwrite(STDERR, sprintf('Coverage report not found at "%s".%s', $reportPath, PHP_EOL));
    exit(1);
}

$xml = simplexml_load_file($reportPath);

if ($xml === false) {
    fwrite(STDERR, sprintf('Could not parse "%s".%s', $reportPath, PHP_EOL));
    exit(1);
}

$metrics = $xml->project->metrics ?? null;

if ($metrics === null) {
    fwrite(STDERR, 'No <metrics> element in the coverage report.' . PHP_EOL);
    exit(1);
}

$statements = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];

if ($statements === 0) {
    fwrite(STDERR, 'The coverage report contains no statements.' . PHP_EOL);
    exit(1);
}

$percentage = $covered / $statements * 100;

printf(
    'Line coverage: %.2f%% (%d of %d statements). Minimum: %.2f%%.%s',
    $percentage,
    $covered,
    $statements,
    $minimum,
    PHP_EOL
);

if ($percentage < $minimum) {
    fwrite(STDERR, sprintf('FAILED: coverage is below the %.2f%% minimum.%s', $minimum, PHP_EOL));
    exit(1);
}

echo 'Coverage threshold met.' . PHP_EOL;
