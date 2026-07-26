# Introduction

`bradietilley/php-builder` turns PHP structure into plain PHP objects. You compose
`PhpClass`, `PhpInterface`, `PhpTrait`, or `PhpEnum` builders, attach members
(constants, properties, methods, attributes), then call `toPhp()` to emit a full
source file — `<?php`, optional `declare(strict_types=1);`, namespace, `use`
imports, and the type body.

It is designed for **code generators**, scaffolding tools, IDE helpers, and any
pipeline that needs to write valid PHP without string-concatenating syntax by
hand.

## Design goals

- **Declarative builders.** Each PHP construct maps to a typed object
  (`PhpMethod`, `PhpProperty`, `PhpAttribute`, …) with named constructor
  arguments and public properties you can mutate after construction.
- **Import-aware.** FQCNs in extends/implements/traits, return types, attributes,
  and PHPDoc are collected into `use` statements. Name clashes are aliased
  automatically, or you can pass an explicit alias to `import()` — see
  [Imports & Aliasing](../imports/README.md).
- **Docblock-aware.** Array shapes, callable signatures, templates, `@throws`,
  and descriptions are rendered as PHPDoc when they cannot be expressed in native
  type syntax alone.
- **Modern PHP surface.** Constructor promotion, asymmetric visibility, property
  hooks, backed enums, trait adaptations, and generics templates are first-class.
- **Targetable.** Version-gated syntax (e.g. `final` promoted properties) can be
  toggled via [`PhpTarget`](../php-target/README.md).

## What it is not

- It is **not** an AST parser or rewriter. You build new source; you do not load
  or mutate existing files.
- It does **not** execute or validate the generated code beyond structural
  checks (e.g. hooks incompatible with `readonly`).
- It does **not** format opinionated style by default. Register a
  [`PhpFormatter`](../formatting/README.md) callback (Pint, PHP-CS-Fixer, …) if
  you want post-processing.

## When to use it

Reach for this package when a generator, artisan command, or build step needs to
emit classes/interfaces/traits/enums with correct imports and modern syntax —
instead of heredocs or template engines that drift out of sync with PHP.

Continue to [Installation](../installation/README.md).
