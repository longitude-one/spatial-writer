# Repository Guidelines

This file defines the repository-specific development rules and the operating boundaries for AI agents working on `longitude-one/spatial-encoder`.

## Project Structure & Module Organization

This PHP library encodes spatial objects to WKB, EWKB, MySQL binary formats, and WKT text.

Source lives in `lib/`, using Composer PSR-4 autoloading with the `LongitudeOne\SpatialEncoder\` namespace prefix. `Encoder` delegates conversion to `Strategy/StrategyInterface`; MySQL encoding helpers live in `Strategy/MySQL/`.

Strategies preserve X/Y coordinate order. SRIDs do not trigger axis swapping.

Tests mirror the source under `tests/Unit/`. Composer PSR-4 development autoloading maps the `LongitudeOne\SpatialEncoder\Tests\` namespace prefix to `tests/`.

Quality tools have separate Composer installations and configuration under `quality/`. CI workflows live in `.github/workflows/`.

## Authority & Agent Responsibilities

The Product Owner and Technical Architect has final authority over:

- product requirements;
- public API design;
- architectural decisions;
- backward compatibility;
- breaking changes;
- normative interpretation of spatial standards.

AI agents are autonomous for implementation decisions within approved requirements and the existing architecture.

Agents MUST NOT override or silently reinterpret decisions made by the Product Owner or Technical Architect.

Agents MUST prefer the smallest coherent change that completely satisfies the assigned Issue and MUST NOT expand its functional scope without approval.

## Spatial Standards

Spatial behavior MUST follow the applicable standards.

Primary normative references include:

- ISO/IEC 13249-3 — SQL Multimedia and Application Packages — Spatial;
- ISO 19107 — Geographic information — Spatial schema;
- ISO 19111 — Geographic information — Referencing by coordinates;
- ISO 19136 — Geographic information — Geography Markup Language (GML).

Applicable OGC specifications MAY additionally be used when relevant.

When determining expected spatial behavior, agents SHOULD consider sources in the following order:

1. applicable ISO standards;
2. normative references incorporated by those standards;
3. applicable OGC specifications;
4. documented LongitudeOne architectural decisions;
5. database or third-party implementation behavior.

Behavior observed in PostgreSQL/PostGIS, MySQL, MariaDB, Microsoft SQL Server, GEOS, or another spatial implementation MUST NOT be considered normative solely because that implementation behaves that way.

Implementation behavior MAY be used for interoperability analysis.

When normative sources and implementations differ, agents MUST identify and document the difference.

When applicable normative requirements are ambiguous, agents MUST request a Normative Gate rather than independently choosing an interpretation.

## Documentation

Documentation lives in [`docs/`](docs/).

Read [`docs/strategies.md`](docs/strategies.md) for strategy behavior, examples, limitations, and external references.

Keep documentation in English and update it when formats, observable behavior, supported geometries, dimensionality, or public APIs change.

See [`tests/README.md`](tests/README.md) for testing instructions and [`CONTRIBUTING.md`](CONTRIBUTING.md) for contribution guidance.

Documentation required by an implementation is part of the work and MUST NOT be deferred solely to consider an Issue complete.

## Build, Test, and Development Commands

Use PHP 8.4 or newer and Composer 2. CI exercises PHP 8.4 and 8.5.

There is no application server or build step.

- `composer install`: install library and test dependencies.
- `composer install-quality-tools`: install the four quality tool environments.
- `composer test`: run PHPUnit.
- `vendor/bin/phpunit --no-coverage --filter WkbStrategyTest`: run focused tests without coverage.
- `composer test-local`: run tests and print the coverage summary; enable PCOV or Xdebug coverage first. Reports go to `.phpunit-cache/`.
- `composer quality`: apply PHP CS Fixer, then run PHPStan at level 9, PHP Mess Detector, and PHP_CodeSniffer. Review formatter changes before committing.

## Coding Style & Naming Conventions

Use four-space indentation, `declare(strict_types=1);`, explicit types, and the existing file headers.

Follow the configured PHP CS Fixer rules and PSR-2-based CodeSniffer standard.

Use:

- PascalCase class names;
- camelCase methods and variables;
- uppercase constants.

Match namespaces to directory paths.

For PHP changes, run `composer quality` and resolve all reported violations before submitting.

PHP CS Fixer's configuration at `quality/php-cs-fixer/.php-cs-fixer.php` is authoritative. Preserve its formatting.

Also satisfy PHPStan, PHP Mess Detector, and PHP_CodeSniffer using their repository configurations.

Agents MUST NOT weaken rules, tests, static analysis, or add suppressions merely to make an implementation pass.

Use `LongitudeOne\Core\Enum\GeometryTypeEnum` for geometry types.

Current fixtures use `SpatialTypes\Types\Dimension2` classes.

Pass SRIDs and collection elements through constructors, keeping member SRIDs consistent with their parent.

## Implementation Autonomy

Within an approved requirement and the existing architecture, agents MAY independently:

- choose appropriate algorithms;
- create or modify private methods;
- create internal abstractions;
- refactor internal implementation;
- improve naming;
- improve internal PHPDoc;
- add additional tests;
- simplify code;
- remove unreachable or redundant internal code when safe;
- choose between equivalent implementations that preserve existing contracts.

Agents MUST NOT perform unrelated refactoring merely because an opportunity is discovered while implementing an Issue.

New dependencies MUST NOT be introduced unless necessary.

A dependency that affects architecture, public API, supported environments, or backward compatibility MAY require an Architecture Gate.

## Testing Guidelines

Use PHPUnit 13, `*Test.php` classes, and descriptive `test*` methods.

New tests MUST be explicit and readable by a human.

Write complete constructors, literal coordinates, and complete expected outputs directly in each test.

Do not build geometry fixtures or expected results using:

- loops;
- generators;
- dynamic class names;
- shared fixture factories.

Prefer separate test methods over data providers.

Assert exact hexadecimal output for binary changes, including SRID and axis-order cases.

See `tests/README.md` for MySQL/PostGIS queries that generate reference fixtures.

Coverage is reported in CI; no numeric minimum is configured.

Every behavioral change MUST be covered by appropriate automated tests.

Tests SHOULD cover, when applicable:

- normal cases;
- boundary cases;
- EMPTY geometries;
- supported geometry types;
- invalid input;
- regression scenarios;
- backward compatibility.

When dimensionality is relevant, agents MUST explicitly consider:

- XY;
- XYZ;
- XYM;
- XYZM.

A Bug fix MUST include a regression test that fails without the fix whenever technically practical.

Existing tests MUST NOT be removed, weakened, skipped, or modified merely to make a new implementation pass unless the Issue explicitly requires a behavior change that invalidates those tests.

Before submitting PHP changes, agents MUST run the applicable test suite and `composer quality`.

## GitHub Development Workflow

GitHub Issues and the LongitudeOne Spatial Development Project are the source of truth for planned work.

Agents MUST NOT begin implementation of a Story or Bug unless its Project status is `Ready`.

Before modifying code, a development agent MUST:

1. read the complete assigned Issue;
2. identify its Acceptance Criteria;
3. inspect its parent Issue when applicable;
4. inspect blocking dependencies;
5. inspect applicable labels;
6. identify any required Architecture Gate.

If information required to implement the Issue is missing, ambiguous, contradictory, or blocked, the agent MUST report the problem rather than inventing a requirement.

During implementation, the agent MUST:

1. implement the approved behavior;
2. add or update appropriate tests;
3. update documentation when required;
4. run applicable tests and quality checks;
5. prepare a Pull Request referencing the Issue.

## Architecture Gates

Agents MUST request approval when a proposed or discovered change affects an architectural, normative, public, or compatibility contract.

### Architecture Gate

Required for decisions affecting:

- geometry or coordinate models;
- component responsibilities;
- architectural patterns;
- cross-library contracts;
- serialization architecture;
- significant abstractions affecting future architecture.

Issues involving such decisions SHOULD use `impact:architecture`.

### API Gate

Required when introducing, removing, or modifying supported public API contracts, including:

- public classes;
- public interfaces;
- public methods;
- method parameters;
- return types;
- public exceptions.

Issues involving such decisions SHOULD use `impact:api`.

### Breaking Change Gate

Required for changes that may break existing consumers, including incompatible changes to:

- public APIs;
- documented behavior;
- namespaces or public symbols;
- serialized representations;
- runtime requirements;
- dependency constraints;
- supported platforms.

Issues involving such changes MUST use `impact:breaking`.

Breaking changes MUST NOT be approved solely by an AI agent.

### Normative Gate

Required whenever implementation depends on an unresolved interpretation of an applicable spatial standard or specification.

Agents MAY:

- research normative sources;
- compare specifications;
- compare existing implementations;
- identify inconsistencies;
- propose possible interpretations;
- recommend an interpretation with supporting arguments.

Agents MUST NOT make the final normative decision.

### Gate Discovery During Development

If a required Gate is discovered during implementation, the agent MUST stop work affected by the unresolved decision.

The agent SHOULD report:

1. the question requiring a decision;
2. why the decision is required;
3. relevant normative or architectural constraints;
4. reasonable alternatives;
5. advantages and disadvantages of each alternative;
6. a recommended solution when appropriate.

Unrelated implementation work MAY continue when it does not depend on the unresolved decision.

## Commit Guidelines

Commits MUST follow the **Conventional Commits** convention.

The general format is:

```text
<type>[optional scope][!]: <description>

[optional body]

[optional footer(s)]
```

Commit descriptions SHOULD be concise, imperative, and describe one coherent change.

The repository uses `commit-and-tag-version` for release generation and changelog management.

The repository's `.versionrc.json` is authoritative for commit types recognized by the release tooling.

Currently configured types are:

- `feat`: new functionality;
- `fix`: bug fixes;
- `perf`: performance improvements;
- `refactor`: code changes that neither fix a bug nor add functionality;
- `docs`: documentation-only changes;
- `test`: adding or correcting tests;
- `build`: build system or dependency changes;
- `ci`: CI configuration or workflow changes;
- `revert`: revert a previous commit;
- `chore`: maintenance work not covered by another type.

Examples:

```text
feat: add EWKB support for measured coordinates
fix: preserve SRID in empty geometry collections
perf: reduce allocations when writing WKB
refactor: extract byte order handling
docs: document EWKB dimensionality
test: add XYZM point serialization cases
build: update PHPUnit dependency
ci: test PHP 8.5
chore: update development tooling
```

Scopes MAY be used when they improve clarity:

```text
feat(wkb): support XYZM coordinates
fix(mysql): preserve SRID in binary output
test(wkt): add empty geometry cases
```

Agents MUST NOT invent new commit types without updating `.versionrc.json` and receiving approval when that change affects release or changelog conventions.

### Breaking Changes

Breaking changes MUST follow Conventional Commits notation and MUST also satisfy the project's Breaking Change Gate.

A breaking change MAY be indicated with `!`:

```text
feat!: change public encoder API
```

or with a `BREAKING CHANGE:` footer:

```text
feat: change public encoder API

BREAKING CHANGE: Encoder::encode() now requires an explicit format.
```

Both MAY be used together when appropriate.

A Conventional Commits breaking-change marker describes the commit for release tooling; it does NOT constitute approval of the breaking change.

Agents MUST NOT create an unauthorized breaking change merely because it can be represented using Conventional Commits.

### Commit Scope

Commits SHOULD be focused and independently understandable.

Agents SHOULD separate unrelated concerns into separate commits when practical.

Agents MUST NOT combine unrelated refactoring, formatting, dependency updates, or documentation changes with functional work unless those changes are directly required by the same Issue.

Do not commit:

- `vendor/`;
- `.phpunit-cache/`;
- the ignored root `composer.lock`.

## Pull Request Guidelines

Pull Requests MUST:

- reference the relevant GitHub Issue;
- describe the problem and resulting behavior;
- summarize the implemented solution;
- list validation commands that were run;
- identify relevant tests;
- include binary examples when serialization changes;
- identify applicable standards when relevant;
- disclose architectural decisions;
- disclose compatibility implications;
- explicitly identify breaking changes;
- mention unresolved questions or limitations.

Agents MUST NOT hide uncertainty in a Pull Request.

## Independent Review

The agent that implements a change MUST NOT act as the final reviewer of its own work.

A separate Reviewer agent MUST review the Pull Request against:

- the GitHub Issue;
- Acceptance Criteria;
- applicable spatial standards;
- tests;
- backward compatibility;
- architecture;
- documentation;
- the project Definition of Done.

The development agent MAY respond to review findings and update its implementation.

All blocking findings MUST be resolved before the work can be considered complete.

## Completion

A development agent MUST NOT independently declare a Story or Bug `Done`.

Completing implementation means the change is ready for independent review.

An item becomes `Done` only after:

- all Acceptance Criteria are satisfied;
- applicable tests and quality checks pass;
- required documentation is complete;
- independent review is complete;
- required Architecture Gates are approved;
- the project's Definition of Done is satisfied;
- the Pull Request has been merged.

## Uncertainty and Escalation

Agents MUST make uncertainty explicit.

When uncertain whether a decision is:

- an implementation detail;
- an architectural decision;
- a public API decision;
- a breaking change;
- or a normative interpretation;

the agent MUST escalate the question rather than silently making the decision.
