# projstart

A small PHP application used as a hands-on subject for learning CI/CD.

[![CI](https://github.com/mabouattour5-action/projstart/actions/workflows/ci.yml/badge.svg)](https://github.com/mabouattour5-action/projstart/actions/workflows/ci.yml)

## What is in here

| Path | Purpose |
| --- | --- |
| `src/PriceCalculator.php` | Cart subtotal, discount codes and VAT |
| `tests/PriceCalculatorTest.php` | 10 PHPUnit tests |
| `.github/workflows/ci.yml` | The pipeline: runs on every push and pull request |
| `.github/actions/setup-php-project/` | Composite action: PHP + Composer cache + install |
| `public/index.php` | Web entry point |
| `public/health.php` | Reports the live version; used by the deploy smoke test |
| `.github/workflows/release.yml` | Continuous delivery: verify, build, publish on a tag |
| `.github/workflows/deploy.yml` | Deploys a published release over SSH |
| `tools/build-release.sh` | Builds the deployable archive |
| `tools/make-archive.php` | Cross-platform zip + checksum |
| `tools/check-coverage.php` | Fails the build below the coverage threshold |
| `phpstan.neon` | Static analysis config (level 9) |
| `.php-cs-fixer.dist.php` | Coding standard config (PSR-12 + PHP 8.2 migration) |
| `phpunit.xml` | Test runner configuration |
| `.gitattributes` | Forces LF in the repo so Windows and Linux CI agree |

## Requirements

- PHP >= 8.2
- Composer 2

## Getting started

```bash
composer install
composer test
```

## Commands

| Command | Does |
| --- | --- |
| `composer test` | Run the PHPUnit suite |
| `composer coverage` | Run with coverage and enforce the 90% minimum (needs pcov or Xdebug) |
| `composer audit` | Check locked dependencies for known CVEs |
| `composer stan` | Run PHPStan static analysis |
| `composer cs` | Report style violations (changes nothing) |
| `composer cs:fix` | Rewrite files to match the standard |
| `composer ci` | Everything the pipeline runs |

Run `composer ci` before pushing and the pipeline should not surprise you.

## The pipeline

Every push to `main` and every pull request targeting `main` triggers `CI`.
Five jobs run in parallel on separate machines:

| Job | Checks |
| --- | --- |
| Dependencies | `composer validate --strict` and `composer audit --locked` |
| Coding standards | PHP-CS-Fixer, report only |
| Static analysis | PHPStan level 9 |
| Tests (PHP 8.2) | PHPUnit |
| Tests (PHP 8.3) | PHPUnit |
| Tests (PHP 8.4) | PHPUnit |
| Coverage | PHPUnit with pcov, minimum 90% line coverage |

A sixth job, **CI Gate**, waits for all of them and fails if any failed.
Branch protection requires only `CI Gate`, so changing the PHP matrix
never requires editing the repository ruleset.

## Releasing

Tag a commit and push the tag:

```bash
git tag -a v1.0.0 -m "First release"
git push origin v1.0.0
```

That triggers `Release`, which:

1. **Verifies** - re-runs the whole CI workflow against the tagged commit
2. **Builds** - `tools/build-release.sh` produces a versioned zip with no
   dev dependencies, plus a sha256 checksum and a `BUILD-INFO.txt`
3. **Publishes** - waits for manual approval on the `production`
   environment, then attaches the artifact to a GitHub Release

The artifact is built once and published as-is. Nothing is rebuilt
between verification and release.

To build one locally:

```bash
bash tools/build-release.sh v0.0.0-local
composer install   # restore dev dependencies afterwards
```

## Deploying

Publishing a release triggers `Deploy`, which downloads the artifact,
verifies its checksum, uploads it over SSH and switches a symlink.

```
<deploy path>/
  releases/
    v1.0.0/
    v1.0.1/
  current -> releases/v1.0.1     <- web root points here
```

Uploading into a new directory and repointing `current` makes the switch
atomic: a request is served entirely by one release or the other, never a
half-copied mixture. The previous release stays on disk, so rollback is a
symlink change rather than a rebuild.

The deploy fails if `health.php` does not report the tag that was just
shipped, and rolls back automatically.

### Required configuration

Repository **variables** (Settings > Secrets and variables > Actions > Variables):

| Name | Example |
| --- | --- |
| `SSH_HOST` | `ssh-youraccount.alwaysdata.net` |
| `SSH_USER` | `youraccount` |
| `DEPLOY_PATH` | `/home/youraccount/www` |
| `SITE_URL` | `https://youraccount.alwaysdata.net` |

Repository **secrets**:

| Name | Contents |
| --- | --- |
| `SSH_PRIVATE_KEY` | The deploy key private half |
| `SSH_KNOWN_HOSTS` | Output of `ssh-keyscan <host>` |

### Manual redeploy or rollback

Actions > Deploy > Run workflow, and give it any existing tag.
