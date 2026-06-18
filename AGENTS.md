# Agent Instructions

This repository is a Contao bundle for a revocation flow. Keep changes small, PHP 8.3 compatible, and aligned with the existing Contao Widerruf bundle structure.

## What to read first
- [composer.json](composer.json) for package constraints and autoloading.
- [src/KirstenroschanskiContaoWiderrufBundle.php](src/KirstenroschanskiContaoWiderrufBundle.php) for bundle bootstrap behavior.
- [config/services.yaml](config/services.yaml) and [config/routes.yaml](config/routes.yaml) for service and route wiring.

## Code map
- [src/Checkout/](src/Checkout/) contains the revocation business logic.
- [src/Controller/](src/Controller/) contains the revocation API endpoint.
- [src/Controller/ContentElement/](src/Controller/ContentElement/) contains the revocation content element controller.
- [src/Model/](src/Model/) contains the revocation Contao model wrapper.
- [contao/dca/](contao/dca/) and [contao/languages/](contao/languages/) hold the revocation metadata and translations.
- [templates/](templates/) and [contao/templates/](contao/templates/) hold the revocation Twig templates.
- [public/](public/) contains the revocation frontend asset.

## Conventions to preserve
- Use `declare(strict_types=1);` in PHP files.
- Prefer constructor injection with `private readonly` typed properties.
- Keep the revocation table and callbacks on the existing `tl_widerruf` naming scheme.
- Keep public-facing strings and exception messages in the existing German tone unless the surrounding file already uses English.
- Content elements should continue to use `#[AsContentElement(...)]` and the current template naming pattern.
- API routes should stay under the `/_widerruf` prefix.

## Working notes
- There does not appear to be a test suite in this repository. If you need validation, prefer focused syntax checks, linting, or manual verification over inventing a new test harness.
- Some content-element controllers include a route-generation fallback for stale route caches; preserve that behavior unless you are intentionally changing route handling.

## Editing guidance
- Link to existing documentation or code instead of repeating it here.
- When adding new code, follow the nearest existing pattern rather than introducing a new abstraction.
- If you need broader guidance for a new area, create a separate focused customization file instead of expanding this one.
