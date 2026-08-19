# projstart

A small PHP application used as a hands-on subject for learning CI/CD.

<!-- BADGE: replaced with the real owner/repo once the GitHub remote exists -->
<!-- ![CI](https://github.com/OWNER/REPO/actions/workflows/ci.yml/badge.svg) -->

## What is in here

| Path | Purpose |
| --- | --- |
| `src/PriceCalculator.php` | Cart subtotal, discount codes and VAT |
| `tests/PriceCalculatorTest.php` | 10 PHPUnit tests |
| `.github/workflows/ci.yml` | The pipeline: runs on every push and pull request |
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

## The pipeline

Every push to `main` and every pull request targeting `main` triggers `CI`:

1. Check out the repository onto a clean Ubuntu runner
2. Install PHP 8.2
3. `composer validate --strict` - catch dependency drift
4. `composer install` - exact versions from `composer.lock`
5. `composer test` - the PHPUnit suite

A red build blocks the merge. The commands are the same ones you run
locally, so any CI failure reproduces on your machine with `composer test`.
