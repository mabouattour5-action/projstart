<?php

declare(strict_types=1);

/**
 * Deployment health endpoint.
 *
 * Reports the version currently serving traffic, read from the
 * BUILD-INFO.txt that tools/build-release.sh writes into every artifact.
 *
 * This is what makes a deploy verifiable: the pipeline can assert that the
 * version it just shipped is the version answering requests. Without it,
 * "the deploy succeeded" only means "rsync exited zero".
 */

use App\PriceCalculator;

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$buildInfo = [];
$buildInfoPath = __DIR__ . '/../BUILD-INFO.txt';

if (is_readable($buildInfoPath)) {
    $contents = file_get_contents($buildInfoPath);

    if ($contents !== false) {
        foreach (explode("\n", $contents) as $line) {
            $parts = explode(':', $line, 2);

            if (count($parts) === 2) {
                $buildInfo[trim($parts[0])] = trim($parts[1]);
            }
        }
    }
}

// Prove the application actually works, not merely that files copied.
$calculator = new PriceCalculator();
$selfTestPassed = $calculator->total([['price' => 19.99, 'quantity' => 1]]) === 23.99;

$payload = [
    'status' => $selfTestPassed ? 'ok' : 'degraded',
    'version' => $buildInfo['version'] ?? 'unknown',
    'commit' => $buildInfo['commit'] ?? 'unknown',
    'built' => $buildInfo['built'] ?? 'unknown',
    'php' => PHP_VERSION,
    'self_test' => $selfTestPassed ? 'passed' : 'failed',
];

http_response_code($selfTestPassed ? 200 : 503);

echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
