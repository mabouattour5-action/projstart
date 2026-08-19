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
| Coding standards | PHP-CS-Fixer, report only |
| Static analysis | PHPStan level 9 |
| Tests (PHP 8.2) | PHPUnit |
| Tests (PHP 8.3) | PHPUnit |
| Tests (PHP 8.4) | PHPUnit |

A sixth job, **CI Gate**, waits for all of them and fails if any failed.
Branch protection requires only `CI Gate`, so changing the PHP matrix
never requires editing the repository ruleset.
