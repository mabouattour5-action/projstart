#!/usr/bin/env bash
#
# Builds the deployable artifact.
#
# Kept as a script rather than inline YAML so you can run the exact same
# build locally that the pipeline runs. A build you cannot reproduce on
# your own machine is a build you cannot debug.
#
# Usage: tools/build-release.sh v1.0.0
#
# WARNING: this runs `composer install --no-dev`, which REMOVES your dev
# dependencies. Run `composer install` afterwards to get them back.

set -euo pipefail

VERSION="${1:?usage: tools/build-release.sh <version>}"

DIST="build/dist"
NAME="projstart-${VERSION}"
STAGE="${DIST}/${NAME}"

rm -rf "${DIST}"
mkdir -p "${STAGE}"

# --no-dev        : PHPUnit, PHPStan and the fixer have no business in production
# --optimize-...  : build a classmap so the autoloader does not stat the disk
# --no-scripts    : nothing in this project needs them; fewer moving parts
composer install \
    --no-dev \
    --prefer-dist \
    --no-progress \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

# Ship only what runs. No tests, no CI config, no tooling.
cp -R src vendor composer.json composer.lock README.md "${STAGE}/"

# Record what this artifact actually is, so a box running it can be
# traced back to a commit without guesswork.
cat > "${STAGE}/BUILD-INFO.txt" <<INFO
version:    ${VERSION}
commit:     ${GITHUB_SHA:-$(git rev-parse HEAD)}
built:      ${BUILD_DATE:-$(date -u +%Y-%m-%dT%H:%M:%SZ)}
php:        $(php -r 'echo PHP_VERSION;')
INFO

# Archived by PHP rather than the zip binary, which Git Bash lacks.
php tools/make-archive.php "${STAGE}"
