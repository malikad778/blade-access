# laravel-blade-ally

> **Static Accessibility Analyzer for Laravel Blade & Livewire Templates**  
> Catch WCAG 2.1 AA violations in your PHP source before a single request is served.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/malikad778/laravel-blade-ally.svg)](https://packagist.org/packages/malikad778/laravel-blade-ally)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-10%2C%2011%2C%2012-red)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)
[![EAA Compliant](https://img.shields.io/badge/EAA-2025%20Ready-blueviolet)](https://ec.europa.eu/accessibility)

---

## Why laravel-blade-ally Exists

The **European Accessibility Act (EAA)** came into force in June 2025. WCAG 2.1 AA compliance is now a legal requirement for public-facing web applications across the EU. Enforcement has begun.

Existing tools like **axe-core**, **Lighthouse**, and **WAVE** run in the browser at runtime - after your code has been deployed. They catch nothing in CI. They cannot analyze dynamic Blade conditionals, Livewire component trees, or slot-rendered layouts. They require a running server.

**laravel-blade-ally** is the missing layer: a static analysis engine that parses your Blade templates and Livewire component PHP+HTML as source code, detects WCAG violations before compilation, and integrates into your CI/CD pipeline exactly like `laravel-migration-guard` - zero configuration, artisan commands, and a hard exit code that blocks bad deploys.

---

## Feature Overview

### Core Engine
- Full Blade template parser (handles `@if`, `@foreach`, `@include`, `@extends`, `@yield`, `@component`, `@slot`, slots, anonymous components, x-components)
- Livewire v2 and v3 component analyzer (PHP class + Blade view parsed together as a unit)
- Alpine.js event handler awareness (`x-on:click`, `@click`, `x-show`, `x-transition`)
- Configurable rule engine - enable, disable, or tune every rule independently
- Multi-format reporting: terminal, JSON, HTML, JUnit XML (for CI), SARIF (for GitHub Code Scanning)
- Baseline system - suppress existing violations, catch only new ones (like PHPStan baselines)
- Severity levels: `error` (WCAG A), `warning` (WCAG AA), `info` (WCAG AAA / best practice)

### WCAG Rule Coverage (72 Rules Across 13 Categories)

#### Images (SC 1.1.1 - Non-text Content)
- `img-missing-alt` - `<img>` without `alt` attribute
- `img-empty-alt-on-meaningful` - `<img>` with `alt=""` that appears to be meaningful (heuristic on src/context)
- `img-alt-redundant` - `alt` duplicates adjacent visible text
- `img-alt-filename` - `alt` contains the filename or file extension
- `img-alt-too-long` - `alt` exceeds 150 characters
- `svg-missing-title` - `<svg>` used as image without `<title>` or `aria-label`
- `svg-role-img` - `<svg>` acting as image without `role="img"`
- `icon-button-label` - icon-only `<button>` without accessible label
- `background-image-content` - CSS `background-image` used where content image is expected (detects inline `style=`)

#### Forms (SC 1.3.1, 3.3.1, 3.3.2)
- `input-missing-label` - `<input>` without matching `<label for="">`, `aria-label`, or `aria-labelledby`
- `input-placeholder-as-label` - `placeholder` used as the only labeling mechanism
- `select-missing-label` - `<select>` without label
- `textarea-missing-label` - `<textarea>` without label
- `fieldset-missing-legend` - grouped inputs in `<fieldset>` without `<legend>`
- `form-error-missing-suggestion` - validation error output without `role="alert"` or `aria-live`
- `required-not-indicated` - required fields without `aria-required` or visible indicator
- `input-type-date-accessible` - date inputs without accessible fallback pattern
- `autocomplete-attribute` - personal data fields missing appropriate `autocomplete` values (SC 1.3.5)
- `label-orphaned` - `<label for="X">` where `id="X"` does not exist in the template

#### Buttons & Links (SC 2.4.4, 4.1.2)
- `button-empty` - `<button>` with no text content and no `aria-label`/`aria-labelledby`
- `button-generic-label` - `<button>` labelled only "click here", "submit", "go", "more"
- `link-empty` - `<a href>` with no text content or accessible label
- `link-generic-label` - `<a>` labelled "click here", "read more", "here", "link"
- `link-opens-new-tab-no-warning` - `<a target="_blank">` without `aria-label` warning or icon
- `link-href-javascript` - `<a href="javascript:void(0)">` instead of `<button>`
- `button-role-on-div` - `<div role="button">` without `tabindex="0"` and keyboard handler
- `link-to-anchor-exists` - `<a href="#id">` where `id` does not exist in template

#### Headings (SC 1.3.1, 2.4.6)
- `heading-skipped` - heading levels skip (h1→h3, h2→h4, etc.)
- `heading-missing-h1` - page/layout has no `<h1>`
- `heading-multiple-h1` - more than one `<h1>` per page (outside SPA context)
- `heading-empty` - `<h1>`–`<h6>` with no text content
- `heading-used-for-styling` - heading tag used where visual size is the apparent intent (heuristic)

#### Tables (SC 1.3.1)
- `table-missing-caption` - data table without `<caption>` or `aria-label`
- `table-header-missing-scope` - `<th>` without `scope` attribute
- `table-header-empty` - `<th>` with no content
- `table-layout-role` - layout table without `role="presentation"` or `aria-hidden`
- `table-missing-headers` - data table with no `<th>` elements at all

#### Modals & Dialogs (SC 4.1.2)
- `dialog-missing-role` - modal-like element without `role="dialog"` or `role="alertdialog"`
- `dialog-missing-aria-label` - `role="dialog"` without `aria-label` or `aria-labelledby`
- `dialog-focus-management` - dialog opened without apparent focus management (no `autofocus`, no `wire:ignore` + JS focus)
- `dialog-escape-handling` - dialog without `@keydown.escape` or equivalent close handler

#### ARIA (SC 4.1.2)
- `aria-role-invalid` - `role` attribute with non-existent ARIA role value
- `aria-required-children` - ARIA role missing required child roles (e.g., `role="list"` needs `role="listitem"`)
- `aria-required-parent` - ARIA role used outside required parent (e.g., `role="listitem"` without `role="list"`)
- `aria-labelledby-target-missing` - `aria-labelledby="X"` where `id="X"` absent in template
- `aria-describedby-target-missing` - same for `aria-describedby`
- `aria-hidden-focusable` - `aria-hidden="true"` on element that is focusable
- `aria-expanded-no-control` - `aria-expanded` without associated controlled element
- `aria-live-region-static` - `aria-live` on element whose content never changes (static text)
- `aria-label-matches-text` - `aria-label` duplicates visible text (redundant)

#### Color & Contrast (SC 1.4.3 - heuristic/inline only)
- `inline-color-no-bg` - `style="color:..."` without a corresponding `background-color` (potential contrast failure)
- `hardcoded-color-value` - inline `style` with hardcoded hex color (flag for manual review)

#### Focus & Keyboard (SC 2.1.1, 2.4.3, 2.4.7)
- `tabindex-positive` - `tabindex` value greater than 0 (disrupts natural focus order)
- `tabindex-missing-on-interactive` - custom interactive elements (`role="button"`, `role="tab"`) without `tabindex`
- `focus-visible-suppressed` - `outline: none` or `outline: 0` in inline style without replacement
- `skip-link-missing` - no skip navigation link present in layout templates
- `skip-link-target-missing` - skip link `href="#main"` where `id="main"` does not exist

#### Language (SC 3.1.1, 3.1.2)
- `html-lang-missing` - `<html>` without `lang` attribute in layout template
- `html-lang-invalid` - `<html lang="XX">` with non-BCP47 language code
- `lang-attribute-inline` - `lang` on a content element without apparent language change

#### Media (SC 1.2.x)
- `video-missing-captions` - `<video>` without `<track kind="captions">`
- `video-autoplay-no-controls` - `<video autoplay>` without `controls` or `muted`
- `audio-missing-transcript` - `<audio>` without adjacent transcript link or description
- `iframe-missing-title` - `<iframe>` without `title` attribute

#### Dynamic / Livewire-Specific
- `livewire-poll-no-pause` - `wire:poll` without `wire:poll.visible` (accessibility and performance)
- `livewire-loading-no-aria` - `wire:loading` element without `aria-live` or `aria-busy`
- `livewire-navigate-focus` - `wire:navigate` links without focus restoration consideration
- `livewire-dispatch-focus` - `$dispatch` events that open UI without detected focus management

#### Page Structure (SC 1.3.1, 2.4.1)
- `landmark-missing-main` - layout without `<main>` or `role="main"`
- `landmark-missing-nav` - multiple `<ul>` navigation blocks without `<nav>`
- `landmark-duplicate-banner` - multiple `<header>` or `role="banner"` without nesting context
- `landmark-duplicate-main` - multiple `<main>` on same page
- `list-not-semantic` - visually list-like `<div>`/`<span>` patterns without `<ul>`/`<ol>` semantics

---

## Project File Structure

```
laravel-blade-ally/
│
├── composer.json
├── LICENSE.md
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
├── SECURITY.md
├── .github/
│   ├── workflows/
│   │   ├── tests.yml
│   │   ├── static-analysis.yml
│   │   └── release.yml
│   └── ISSUE_TEMPLATE/
│       ├── bug_report.md
│       └── rule_request.md
│
├── config/
│   └── blade-ally.php                          # Full published config
│
├── resources/
│   └── views/
│       └── report.blade.php                    # HTML report template
│
├── src/
│   ├── BladeAllyServiceProvider.php            # Package boot + config + commands
│   ├── Facades/
│   │   └── BladeAlly.php                       # BladeAlly::analyze(), BladeAlly::baseline()
│   │
│   ├── Console/
│   │   Commands/
│   │   ├── AnalyzeCommand.php                  # php artisan ally:analyze
│   │   ├── BaselineCommand.php                 # php artisan ally:baseline
│   │   ├── DiffCommand.php                     # php artisan ally:diff (compare two runs)
│   │   ├── RulesCommand.php                    # php artisan ally:rules (list all rules)
│   │   ├── ExplainCommand.php                  # php artisan ally:explain img-missing-alt
│   │   └── InitCommand.php                     # php artisan ally:init (publish config + baseline)
│   │
│   ├── Engine/
│   │   ├── Analyzer.php                        # Orchestrates the full analysis pipeline
│   │   ├── AnalysisResult.php                  # Value object: violations + metadata
│   │   ├── AnalysisOptions.php                 # Runtime options (paths, rules, severity filter)
│   │   └── AnalysisSummary.php                 # Aggregated counts by rule, severity, file
│   │
│   ├── Parsers/
│   │   ├── Contracts/
│   │   │   └── TemplateParserInterface.php
│   │   ├── BladeTemplateParser.php             # Resolves @extends, @include, @component chains
│   │   ├── BladeTokenizer.php                  # Tokenizes Blade directives + raw HTML
│   │   ├── BladeDirectiveResolver.php          # Expands @include, @component, x-components
│   │   ├── LivewireComponentParser.php         # Finds Blade view for a given Livewire class
│   │   ├── LivewireClassInspector.php          # Reflects on public properties, methods, events
│   │   ├── AlpineJsParser.php                  # Extracts x-on, x-show, x-transition from HTML
│   │   ├── HtmlAstBuilder.php                  # Converts tokenized HTML into traversable AST
│   │   ├── AriaAttributeResolver.php           # Resolves aria-labelledby/describedby targets
│   │   └── SlotContentResolver.php             # Resolves <x-slot> and named slot content
│   │
│   ├── Discovery/
│   │   ├── BladeFileDiscovery.php              # Finds all .blade.php files in configured paths
│   │   ├── LivewireComponentDiscovery.php      # Finds all Livewire components (v2 + v3)
│   │   ├── ViewComposerDiscovery.php           # Detects view composers that inject dynamic data
│   │   ├── AnonymousComponentDiscovery.php     # Finds x-* component definitions
│   │   └── LayoutDiscovery.php                 # Identifies layout templates (@extends targets)
│   │
│   ├── Rules/
│   │   ├── Contracts/
│   │   │   └── RuleInterface.php               # check(Node $node, RuleContext $ctx): array
│   │   ├── RuleRegistry.php                    # Registers + loads all rules
│   │   ├── RuleContext.php                     # Passes file path, full AST, parsed component to rule
│   │   ├── RuleSeverity.php                    # Enum: ERROR | WARNING | INFO
│   │   │
│   │   ├── Images/
│   │   │   ├── ImgMissingAltRule.php
│   │   │   ├── ImgEmptyAltOnMeaningfulRule.php
│   │   │   ├── ImgAltRedundantRule.php
│   │   │   ├── ImgAltFilenameRule.php
│   │   │   ├── ImgAltTooLongRule.php
│   │   │   ├── SvgMissingTitleRule.php
│   │   │   ├── SvgRoleImgRule.php
│   │   │   ├── IconButtonLabelRule.php
│   │   │   └── BackgroundImageContentRule.php
│   │   │
│   │   ├── Forms/
│   │   │   ├── InputMissingLabelRule.php
│   │   │   ├── InputPlaceholderAsLabelRule.php
│   │   │   ├── SelectMissingLabelRule.php
│   │   │   ├── TextareaMissingLabelRule.php
│   │   │   ├── FieldsetMissingLegendRule.php
│   │   │   ├── FormErrorMissingSuggestionRule.php
│   │   │   ├── RequiredNotIndicatedRule.php
│   │   │   ├── InputTypeDateAccessibleRule.php
│   │   │   ├── AutocompleteAttributeRule.php
│   │   │   └── LabelOrphanedRule.php
│   │   │
│   │   ├── ButtonsLinks/
│   │   │   ├── ButtonEmptyRule.php
│   │   │   ├── ButtonGenericLabelRule.php
│   │   │   ├── LinkEmptyRule.php
│   │   │   ├── LinkGenericLabelRule.php
│   │   │   ├── LinkOpensNewTabNoWarningRule.php
│   │   │   ├── LinkHrefJavascriptRule.php
│   │   │   ├── ButtonRoleOnDivRule.php
│   │   │   └── LinkToAnchorExistsRule.php
│   │   │
│   │   ├── Headings/
│   │   │   ├── HeadingSkippedRule.php
│   │   │   ├── HeadingMissingH1Rule.php
│   │   │   ├── HeadingMultipleH1Rule.php
│   │   │   ├── HeadingEmptyRule.php
│   │   │   └── HeadingUsedForStylingRule.php
│   │   │
│   │   ├── Tables/
│   │   │   ├── TableMissingCaptionRule.php
│   │   │   ├── TableHeaderMissingScopeRule.php
│   │   │   ├── TableHeaderEmptyRule.php
│   │   │   ├── TableLayoutRoleRule.php
│   │   │   └── TableMissingHeadersRule.php
│   │   │
│   │   ├── Dialogs/
│   │   │   ├── DialogMissingRoleRule.php
│   │   │   ├── DialogMissingAriaLabelRule.php
│   │   │   ├── DialogFocusManagementRule.php
│   │   │   └── DialogEscapeHandlingRule.php
│   │   │
│   │   ├── Aria/
│   │   │   ├── AriaRoleInvalidRule.php
│   │   │   ├── AriaRequiredChildrenRule.php
│   │   │   ├── AriaRequiredParentRule.php
│   │   │   ├── AriaLabelledbyTargetMissingRule.php
│   │   │   ├── AriaDescribedbyTargetMissingRule.php
│   │   │   ├── AriaHiddenFocusableRule.php
│   │   │   ├── AriaExpandedNoControlRule.php
│   │   │   ├── AriaLiveRegionStaticRule.php
│   │   │   └── AriaLabelMatchesTextRule.php
│   │   │
│   │   ├── Color/
│   │   │   ├── InlineColorNoBgRule.php
│   │   │   └── HardcodedColorValueRule.php
│   │   │
│   │   ├── Focus/
│   │   │   ├── TabindexPositiveRule.php
│   │   │   ├── TabindexMissingOnInteractiveRule.php
│   │   │   ├── FocusVisibleSuppressedRule.php
│   │   │   ├── SkipLinkMissingRule.php
│   │   │   └── SkipLinkTargetMissingRule.php
│   │   │
│   │   ├── Language/
│   │   │   ├── HtmlLangMissingRule.php
│   │   │   ├── HtmlLangInvalidRule.php
│   │   │   └── LangAttributeInlineRule.php
│   │   │
│   │   ├── Media/
│   │   │   ├── VideoMissingCaptionsRule.php
│   │   │   ├── VideoAutoplayNoControlsRule.php
│   │   │   ├── AudioMissingTranscriptRule.php
│   │   │   └── IframeMissingTitleRule.php
│   │   │
│   │   ├── Livewire/
│   │   │   ├── LivewirePollNoPauseRule.php
│   │   │   ├── LivewireLoadingNoAriaRule.php
│   │   │   ├── LivewireNavigateFocusRule.php
│   │   │   └── LivewireDispatchFocusRule.php
│   │   │
│   │   └── Structure/
│   │       ├── LandmarkMissingMainRule.php
│   │       ├── LandmarkMissingNavRule.php
│   │       ├── LandmarkDuplicateBannerRule.php
│   │       ├── LandmarkDuplicateMainRule.php
│   │       └── ListNotSemanticRule.php
│   │
│   ├── Violations/
│   │   ├── Violation.php                       # Value object: rule, file, line, col, severity, message, fix hint
│   │   ├── ViolationCollection.php             # Filterable, sortable collection of violations
│   │   ├── ViolationSuppression.php            # Handles blade-ally-ignore comments
│   │   └── ViolationDiff.php                   # Computes new violations vs. baseline
│   │
│   ├── Baseline/
│   │   ├── BaselineManager.php                 # Read/write baseline JSON file
│   │   ├── BaselineEntry.php                   # Single baseline entry (file, rule, fingerprint)
│   │   └── BaselineFingerprintGenerator.php    # Generates stable fingerprint for a violation
│   │
│   ├── Reporters/
│   │   ├── Contracts/
│   │   │   └── ReporterInterface.php
│   │   ├── TerminalReporter.php                # Rich colored terminal output
│   │   ├── JsonReporter.php                    # Machine-readable JSON
│   │   ├── HtmlReporter.php                    # Self-contained HTML report with charts
│   │   ├── JUnitReporter.php                   # JUnit XML for CI (GitHub Actions, Jenkins, etc.)
│   │   ├── SarifReporter.php                   # SARIF 2.1 for GitHub Code Scanning integration
│   │   ├── GithubAnnotationReporter.php        # ::error file=...,line=...,col=...:: format
│   │   ├── CheckstyleReporter.php              # Checkstyle XML for IDE integrations
│   │   └── ReporterFactory.php                 # Resolves reporter from config/flag
│   │
│   ├── Config/
│   │   ├── BladeAllyConfig.php                 # Typed config wrapper
│   │   ├── RuleConfiguration.php               # Per-rule severity overrides and options
│   │   └── PathConfiguration.php               # Template paths, exclusions, livewire paths
│   │
│   ├── Ignores/
│   │   ├── InlineIgnoreParser.php              # Parses {{-- blade-ally-ignore img-missing-alt --}}
│   │   ├── IgnoreFileParser.php                # Reads .blade-ally-ignore file (glob patterns)
│   │   └── IgnoreManager.php                   # Combines inline + file-level ignores
│   │
│   ├── Caching/
│   │   ├── AnalysisCache.php                   # Cache parsed ASTs by file hash
│   │   ├── CacheDriver.php                     # File-based cache with TTL
│   │   └── CacheInvalidator.php                # Invalidates cache on config change
│   │
│   ├── CI/
│   │   ├── ExitCodeResolver.php                # Resolve exit code from severity threshold config
│   │   ├── GithubActionsEnvironment.php        # Detects GitHub Actions env
│   │   └── CIEnvironmentDetector.php           # Detects CI environment type
│   │
│   ├── Support/
│   │   ├── AriaRoleDefinitions.php             # Full ARIA 1.2 role → required/owned attributes map
│   │   ├── HtmlElementDefinitions.php          # Native HTML semantics map (interactive, landmark, etc.)
│   │   ├── Bcp47LanguageCodes.php              # Valid BCP 47 language code set
│   │   ├── GenericLabelList.php                # "click here", "read more" etc.
│   │   ├── AutocompleteTokens.php              # Valid HTML autocomplete token list
│   │   ├── WcagCriteria.php                    # WCAG 2.1 SC descriptions + URLs per rule
│   │   └── ColorParser.php                     # Parses inline color values for contrast checks
│   │
│   └── Testing/
│       ├── BladeAllyTestHelper.php             # Use in tests: $this->assertNoAllyViolations($view)
│       ├── AssertNoViolations.php              # PHPUnit assertion
│       ├── AssertViolation.php                 # PHPUnit assertion: expect a specific violation
│       └── FakeAnalyzer.php                    # Test double for the Analyzer
│
├── tests/
│   ├── Pest.php
│   ├── TestCase.php
│   │
│   ├── Unit/
│   │   ├── Parsers/
│   │   │   ├── BladeTemplateParserTest.php
│   │   │   ├── BladeTokenizerTest.php
│   │   │   ├── LivewireComponentParserTest.php
│   │   │   ├── HtmlAstBuilderTest.php
│   │   │   └── AriaAttributeResolverTest.php
│   │   │
│   │   ├── Rules/
│   │   │   ├── Images/
│   │   │   │   ├── ImgMissingAltRuleTest.php
│   │   │   │   ├── ImgAltRedundantRuleTest.php
│   │   │   │   └── SvgMissingTitleRuleTest.php
│   │   │   ├── Forms/
│   │   │   │   ├── InputMissingLabelRuleTest.php
│   │   │   │   └── LabelOrphanedRuleTest.php
│   │   │   ├── ButtonsLinks/
│   │   │   │   ├── ButtonEmptyRuleTest.php
│   │   │   │   └── LinkGenericLabelRuleTest.php
│   │   │   ├── Headings/
│   │   │   │   └── HeadingSkippedRuleTest.php
│   │   │   ├── Dialogs/
│   │   │   │   └── DialogMissingRoleRuleTest.php
│   │   │   ├── Aria/
│   │   │   │   ├── AriaRoleInvalidRuleTest.php
│   │   │   │   └── AriaLabelledbyTargetMissingRuleTest.php
│   │   │   ├── Livewire/
│   │   │   │   ├── LivewirePollNoPauseRuleTest.php
│   │   │   │   └── LivewireLoadingNoAriaRuleTest.php
│   │   │   └── Structure/
│   │   │       └── LandmarkMissingMainRuleTest.php
│   │   │
│   │   ├── Baseline/
│   │   │   ├── BaselineManagerTest.php
│   │   │   └── BaselineFingerprintGeneratorTest.php
│   │   │
│   │   ├── Reporters/
│   │   │   ├── TerminalReporterTest.php
│   │   │   ├── JsonReporterTest.php
│   │   │   ├── JUnitReporterTest.php
│   │   │   └── SarifReporterTest.php
│   │   │
│   │   └── Ignores/
│   │       ├── InlineIgnoreParserTest.php
│   │       └── IgnoreFileParserTest.php
│   │
│   ├── Feature/
│   │   ├── AnalyzeCommandTest.php
│   │   ├── BaselineCommandTest.php
│   │   ├── DiffCommandTest.php
│   │   ├── ExplainCommandTest.php
│   │   ├── CiIntegrationTest.php
│   │   └── LivewireIntegrationTest.php
│   │
│   └── Fixtures/
│       ├── views/
│       │   ├── passing/                        # Templates with no violations
│       │   │   ├── complete-form.blade.php
│       │   │   ├── accessible-modal.blade.php
│       │   │   ├── data-table.blade.php
│       │   │   └── full-page-layout.blade.php
│       │   └── failing/                        # Templates with known violations
│       │       ├── img-no-alt.blade.php
│       │       ├── unlabeled-form.blade.php
│       │       ├── empty-buttons.blade.php
│       │       ├── broken-dialog.blade.php
│       │       └── skipped-headings.blade.php
│       └── livewire/
│           ├── AccessibleForm.php
│           ├── AccessibleForm.blade.php
│           ├── BrokenModal.php
│           └── BrokenModal.blade.php
│
└── stubs/
    ├── config.stub                             # blade-ally.php config stub
    ├── baseline.stub                           # Empty baseline JSON stub
    └── github-workflow.stub                    # Suggested GitHub Actions step
```

---

## Installation

```bash
composer require malikad778/laravel-blade-ally --dev
```

Publish the config:
```bash
php artisan ally:init
```

This publishes `config/blade-ally.php` and creates an empty `blade-ally-baseline.json`.

---

## Usage

### Analyze all templates
```bash
php artisan ally:analyze
```

### Analyze specific paths
```bash
php artisan ally:analyze --path=resources/views/components
```

### Filter by severity
```bash
php artisan ally:analyze --min-severity=error
```

### Output formats
```bash
php artisan ally:analyze --format=json --output=ally-report.json
php artisan ally:analyze --format=html --output=ally-report.html
php artisan ally:analyze --format=junit --output=ally-junit.xml
php artisan ally:analyze --format=sarif --output=ally.sarif
php artisan ally:analyze --format=github   # GitHub Actions annotations
```

### CI mode - fail on any error-severity violation
```bash
php artisan ally:analyze --ci
echo $?  # 0 = pass, 1 = violations found
```

### Create a baseline (suppress existing violations)
```bash
php artisan ally:baseline
```

### Show only new violations since baseline
```bash
php artisan ally:diff
```

### List all available rules
```bash
php artisan ally:rules
php artisan ally:rules --category=forms
php artisan ally:rules --severity=error
```

### Explain a specific rule with examples
```bash
php artisan ally:explain img-missing-alt
php artisan ally:explain input-missing-label
```

---

## Configuration (`config/blade-ally.php`)

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Template Paths
    |--------------------------------------------------------------------------
    | Directories to scan. Supports glob patterns.
    */
    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Paths
    |--------------------------------------------------------------------------
    | Glob patterns to exclude from analysis.
    */
    'exclude' => [
        resource_path('views/vendor'),
        resource_path('views/emails'),   // Email templates have different rules
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */
    'livewire' => [
        'enabled' => true,
        'component_paths' => [
            app_path('Livewire'),
            app_path('Http/Livewire'),
        ],
        'view_path' => resource_path('views/livewire'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity Threshold
    |--------------------------------------------------------------------------
    | Minimum severity level that causes a non-zero exit code in CI.
    | Options: 'error', 'warning', 'info'
    */
    'fail_on' => 'error',

    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    | Each rule can be: true (enabled, default severity),
    |                   false (disabled),
    |                   'warning' or 'error' (override severity),
    |                   or an array for rule-specific options.
    */
    'rules' => [
        'img-missing-alt'               => true,
        'img-alt-too-long'              => ['max_length' => 150],
        'input-missing-label'           => true,
        'input-placeholder-as-label'    => 'warning',
        'button-empty'                  => true,
        'link-empty'                    => true,
        'link-generic-label'            => 'warning',
        'heading-skipped'               => true,
        'heading-missing-h1'            => true,
        'dialog-missing-role'           => true,
        'html-lang-missing'             => true,
        'tabindex-positive'             => 'warning',
        'skip-link-missing'             => 'warning',
        'livewire-poll-no-pause'        => 'warning',
        // ... all 72 rules configurable
    ],

    /*
    |--------------------------------------------------------------------------
    | Baseline File
    |--------------------------------------------------------------------------
    */
    'baseline' => base_path('blade-ally-baseline.json'),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled'   => true,
        'directory' => storage_path('framework/cache/blade-ally'),
        'ttl'       => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting
    |--------------------------------------------------------------------------
    */
    'reporting' => [
        'default_format' => 'terminal',
        'show_fix_hints' => true,
        'show_wcag_links' => true,
        'group_by' => 'file',  // 'file' | 'rule' | 'severity'
    ],
];
```

---

## Suppressing Violations

### Inline - suppress a specific rule on the next line
```html
{{-- blade-ally-ignore img-missing-alt --}}
<img src="{{ $decorativeBackground }}">
```

### Inline - suppress all rules on the next line
```html
{{-- blade-ally-ignore-all --}}
<div role="button">...</div>
```

### File-level - `.blade-ally-ignore`
```
resources/views/vendor/**
resources/views/legacy/old-form.blade.php
```

---

## CI/CD Integration

### GitHub Actions
```yaml
- name: Run laravel-blade-ally
  run: php artisan ally:analyze --ci --format=github
```

### GitHub Code Scanning (SARIF)
```yaml
- name: Run laravel-blade-ally SARIF
  run: php artisan ally:analyze --format=sarif --output=ally.sarif

- name: Upload SARIF
  uses: github/codeql-action/upload-sarif@v3
  with:
    sarif_file: ally.sarif
```

### Laravel migration-guard style pre-deploy gate
```yaml
- name: Accessibility gate
  run: php artisan ally:diff --ci  # Only fails on NEW violations vs. baseline
```

---

## Testing Helpers

Use inside your own package or application tests:

```php
use MalikAd778\BladeAlly\Testing\BladeAllyTestHelper;

class MyComponentTest extends TestCase
{
    use BladeAllyTestHelper;

    public function test_contact_form_is_accessible(): void
    {
        $this->assertNoAllyViolations('components.contact-form');
    }

    public function test_only_warnings_not_errors(): void
    {
        $this->assertNoAllyErrors('components.legacy-widget');
    }

    public function test_specific_violation_present(): void
    {
        $this->assertAllyViolation('components.broken-button', 'button-empty');
    }
}
```

---

## Roadmap

- **v1.0** - 72 rules, Blade + Livewire v3, terminal + JSON + JUnit + SARIF reporters, baseline, CI integration
- **v1.1** - Filament component awareness, Volt (single-file Livewire) support
- **v1.2** - VS Code extension (LSP-backed inline violations)
- **v1.3** - Auto-fix mode for mechanical fixes (add `alt=""`, add `role="dialog"`, etc.)
- **v2.0** - WCAG 2.2 rule additions, color contrast ratio computation from Tailwind config
- **Commercial** - Hosted HTML dashboard, Slack/Teams notification integration, team-level compliance tracking

---

## Contributing

Read [CONTRIBUTING.md](CONTRIBUTING.md). New rules are the easiest contribution - implement `RuleInterface`, add a test fixture, open a PR.

## License

MIT - see [LICENSE.md](LICENSE.md)

---

## Author

**Adnan Malik** - [WebWhizy](https://webwhizy.com) · [GitHub](https://github.com/malikad778)  
Creator of [laravel-migration-guard](https://github.com/malikad778/Laravel-migration-guard) · [php-sentinel](https://github.com/malikad778/php-sentinel) · [nexus-inventory](https://github.com/malikad778/nexus-inventory)


dont write commments in code anywhere