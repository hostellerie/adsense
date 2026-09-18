# AdSense for Geeklog — Roadmap

**Status:** Active development on `develop-1.0`.

This roadmap defines the intended direction of the AdSense plugin and incorporates the current engineering rules and interoperability guidance from the Geeklog Development & Modernization Memorandum.

Checked items should only be added when implementation and validation are complete.

## Compatibility baseline

The current Memorandum compatibility target applies to AdSense unless the project explicitly changes policy later:

- **Geeklog 2.1.1 through 2.2.2**
- **PHP 5.6 through PHP 8.1**

Consequences for AdSense:

- use the PHP 5.6-safe language subset in shared runtime code;
- do not use scalar/return type declarations, null coalescing, arrow functions, typed properties, attributes, union types, `match`, or other newer-only syntax;
- use newer Geeklog facilities only when they exist and provide a safe fallback for older supported versions;
- keep version-specific compatibility code isolated so it can be removed later;
- test both ends of the supported Geeklog/PHP range.

## Architectural role

AdSense is **not a content-owning plugin**.

It should therefore not implement content APIs such as `plugin_getiteminfo_adsense()`, lifecycle events, sitemap collection, or URL ownership merely to appear interoperable.

Its role is:

1. an advertising integration and renderer;
2. a consumer of current Geeklog/content context when placement rules require it;
3. later, an optional provider of AdSense reporting metrics.

AdSense must not query another plugin's private SQL tables to understand its content. When content context is available through Geeklog or the Plugin Content Interoperability Contract, use that contract. When it is unavailable, fail safely and avoid the context-dependent placement rather than introducing hard coupling.

## Design principles

- Keep AdSense configuration out of Geeklog themes whenever possible.
- Load the Google AdSense bootstrap script once.
- Prefer native Geeklog Plugin APIs over custom hooks when an appropriate API already exists.
- Separate Google account configuration from Geeklog placement rules.
- Use stable named placements instead of embedding slot IDs throughout content.
- Preserve existing content and historical autotags.
- Never silently take ownership of an autotag already provided by another plugin.
- Avoid automatic bulk rewriting of stored content.
- Treat multisite isolation as a design constraint, not a later feature.
- Keep shared executable files compatible with the previous supported persisted plugin state until each site completes its own upgrade.
- Use native Geeklog configuration storage for ordinary settings.
- Keep persistent data site-scoped.
- Protect state-changing administration operations with Geeklog security tokens and ACL checks.
- Prefer Geeklog/plugin interoperability contracts over plugin-specific coupling.
- Make diagnostics and safe failure behavior part of the core product.
- Keep external-provider failures from breaking normal Geeklog page rendering.

---

## 1.0 — Core integration and compatibility

### Plugin foundation

- [x] Create the standard Geeklog plugin structure.
- [x] Add root-level `autoinstall.php` and use Geeklog's standard installer rather than a legacy custom `admin/install.php` flow.
- [ ] Add install, uninstall, upgrade, enable, and disable lifecycle support.
- [ ] Declare and enforce the compatibility baseline: Geeklog 2.1.1–2.2.2 and PHP 5.6–8.1.
- [ ] Keep compatibility helpers isolated from normal business logic.
- [x] Add `plugin.json` at package root following Memorandum schema 1.
- [x] Keep `plugin.json` static, valid UTF-8 JSON, and free of executable or site-specific data.
- [ ] Ensure manifest `id`, public name, icon, Geeklog requirement, and PHP requirement remain consistent with native plugin installer metadata.
- [x] Add English language files as the canonical interface contract and provide safe language fallback.
- [ ] Use `.thtml` templates for significant administration/presentation markup where practical.
- [x] Keep runtime output theme-independent; Eclipse may be tested but must not be required.
- [x] Use Geeklog `DB_*` abstraction for any plugin database access.
- [x] Never hard-code a table prefix such as `gl_`.
- [ ] Initialize runtime variables explicitly and eliminate PHP 8 warnings/notices.

### Native Geeklog integration points

Use existing Plugin APIs instead of inventing equivalent mechanisms.

- [x] Use `plugin_getheadercode_adsense()` for AdSense head integration where compatible with the supported Geeklog versions.
- [ ] Verify a safe 2.1.1 fallback if header integration behavior differs.
- [x] Use `plugin_autotags_adsense()` for the native AdSense autotag implementation.
- [ ] Support the Geeklog autotag discovery/permission/parse modes required by the supported versions.
- [ ] Use contextual autotag parameters such as content type/id when available, without requiring them.
- [x] Use `plugin_getadminoption_adsense()` and/or `plugin_cclabel_adsense()` for administration entry points as appropriate.
- [x] Use `plugin_getconfigtooltip_adsense()` for configuration help instead of custom tooltip JavaScript.
- [ ] Use `plugin_migrate_adsense()` only if URL/path changes require site-specific AdSense state updates.
- [ ] Use `plugin_configchange_adsense()` only where reacting to configuration changes is actually necessary.
- [ ] Do not implement unrelated Plugin APIs merely for completeness.

### Native Geeklog configuration

Ordinary plugin configuration should use Geeklog's Configuration API.

- [x] Define configuration hierarchy explicitly as `subgroup -> tab -> fieldset -> settings`.
- [x] Add configuration defaults through `install_defaults.php`.
- [ ] Use valid `config::add()` parameter ordering for both Geeklog generations supported by the plugin.
- [ ] Use `NULL` for `selection_array` unless a real language-backed selection list is defined.
- [x] Define matching `$LANG_configsections`, `$LANG_confignames`, `$LANG_configsubgroups`, `$LANG_tab`, and `$LANG_fs` entries.
- [x] Define `$LANG_configselects` entries for every selection-backed option.
- [x] Expose readable labels for modes instead of raw `0/1/2/3` choices.
- [x] Add concise tooltips for technically significant settings such as serving mode, legacy aliases, consent behavior, debug mode, and compatibility options.
- [x] Retrieve plugin configuration through Geeklog's configuration system rather than querying `conf_values` directly on page requests.
- [x] Protect configuration reads with `isset()`-style PHP 5.6-safe fallbacks.
- [ ] Test a fresh configuration independently from upgrade/migration paths.
- [ ] Repair persisted configuration metadata explicitly during upgrades when required; do not assume replacing files rewrites `conf_values`.

### Security and administration

- [x] Define a dedicated AdSense administration feature/group through standard Geeklog installation metadata if required.
- [x] Enforce ACL permissions in every administration action, independently of menu visibility.
- [x] Protect every state-changing administration operation with Geeklog CSRF/security tokens.
- [x] Validate all IDs, placement names, slot IDs, URL patterns, and configuration inputs.
- [x] Escape output according to its HTML/attribute/JavaScript context.
- [ ] Never expose OAuth tokens, secrets, or sensitive provider configuration in diagnostics, tooltips, logs, `plugin.json`, or rendered page source.
- [x] Ensure preview/debug functions are administrator-only.
- [ ] Ensure diagnostics are read-only unless the administrator explicitly invokes a protected repair/action.

### Global AdSense configuration

- [x] Enable/disable the plugin's ad-serving layer.
- [x] Configure and validate the publisher ID (`ca-pub-...`).
- [ ] Implement explicit modes:
  - [ ] Disabled
  - [ ] Auto Ads
  - [ ] Manual placements
  - [ ] Hybrid
- [x] Inject the AdSense loader through the native Geeklog header integration path.
- [x] Prevent duplicate loader injection by the plugin itself.
- [ ] Detect likely duplicate AdSense loaders already present in the theme, header customizations, or other code.
- [x] Never render advertising inside Geeklog administration pages.
- [x] Fail safely if the publisher ID or required placement configuration is missing.

### Auto Ads

- [x] Support a minimal Auto Ads integration.
- [ ] Keep Google-side Auto Ads controls in AdSense rather than reproducing them unnecessarily in Geeklog.
- [ ] Document the boundary between Geeklog configuration and Google AdSense configuration.
- [x] Allow Auto Ads to coexist with explicitly managed placements in Hybrid mode.
- [ ] Ensure an unavailable external Google resource never causes a PHP/page-rendering failure.

### Named manual placements

- [x] Define a storage model for named placements.
- [x] Decide explicitly which data belongs in native Geeklog configuration and which data, if any, justifies a plugin table.
- [x] Keep ordinary scalar settings in the native Configuration API rather than creating a custom settings table.
- [x] Use a plugin table only if structured/repeating placement records cannot reasonably be represented by native configuration.
- [x] Store slot ID separately from placement name.
- [x] Support enable/disable per placement.
- [x] Support responsive ad units.
- [x] Support common AdSense format metadata without hard-coding presentation into article content.
- [x] Render every placement through one central renderer.
- [x] Avoid duplicated bootstrap scripts when multiple placements occur on one page.
- [ ] Provide initial placement targets for Geeklog blocks and explicit autotags.
- [x] Keep placement rendering functional when optional content context is unavailable.

### Native autotag namespace

- [x] Reserve `[ad:PLACEMENT]` for new AdSense plugin placements.
- [ ] Examples:
  - [ ] `[ad:article-middle]`
  - [ ] `[ad:article-bottom]`
  - [ ] `[ad:leaderboard]`
  - [ ] `[ad:sidebar]`
- [x] Declare and parse the autotag through `plugin_autotags_adsense()`.
- [x] Resolve the placement name through plugin configuration/storage.
- [x] Never require a Google slot ID to be stored in article text.
- [ ] Apply permission/exclusion logic during autotag rendering.
- [x] Return safe empty/debug output when a requested placement is disabled, unknown, or not permitted.
- [ ] Test autotag rendering in stories and static pages on Geeklog 2.1.1 and 2.2.2 before extending to other providers.

### Historical autotag preservation

Known historical autotags that must be considered from the beginning:

- `[adsense]`
- `[amazon]`
- `[inarticle]`
- `[infeed]`
- `[leaderboard]`
- `[youtube]`

Required behavior:

- [ ] Detect whether these tags are already registered by the Autotags plugin or another provider.
- [x] Do not claim or override an existing provider silently.
- [x] Treat `[adsense]`, `[inarticle]`, `[infeed]`, and `[leaderboard]` as possible AdSense-compatible legacy tags.
- [x] Keep `[amazon]` external to the AdSense plugin.
- [x] Keep `[youtube]` external to the AdSense plugin.
- [x] Implement a legacy alias/adapter model that can map compatible historical tags to named placements.
- [x] Make legacy mapping opt-in/configurable where ownership is ambiguous.
- [ ] Keep legacy compatibility runtime-only until an explicit site upgrade/migration changes persisted state.
- [x] Do not rewrite stored content during install, normal frontend execution, or ordinary upgrade.
- [x] Do not remove historical tags from content during uninstall.
- [ ] Warn before uninstall when native AdSense autotags remain in stored content.

Suggested default mapping model:

```text
[adsense]     -> ad-default
[inarticle]   -> article-middle
[infeed]      -> feed
[leaderboard] -> leaderboard

[amazon]      -> external / unchanged
[youtube]     -> external / unchanged
```

The actual mapping must remain configurable because historical definitions may differ between sites.

### Multisite isolation

- [x] Use the active Geeklog site context instead of implementing independent host/site detection.
- [x] Derive URLs and paths from the current site's `$_CONF` values.
- [x] Use the active site's `$_TABLES` mapping for plugin tables.
- [ ] Keep publisher ID, serving mode, placement definitions, legacy aliases, exclusions, consent settings, diagnostics state, and future API credentials isolated per site.
- [ ] Never cache one site's AdSense configuration in a way that can leak into another site context.
- [ ] Never scan or modify sibling sites during normal frontend/admin requests.
- [ ] Make install and upgrade routines operate only on the active site's state.
- [ ] Log enough site context during migration failures to identify the affected site without leaking secrets.
- [ ] Test at least two sites with different URLs, `path_data`, table prefixes/mappings, and AdSense configurations.

### Shared-files staggered-upgrade safety

This is a release requirement whenever sites share the same AdSense plugin files.

- [ ] New runtime files must remain operational with the previous supported persisted AdSense state until the active site completes its upgrade.
- [ ] Detect the active site's persisted plugin/schema/configuration state explicitly.
- [ ] Provide a temporary read-compatible path when new files are running against the previous persisted state.
- [ ] Do not run destructive migration merely because new files were uploaded.
- [ ] Perform persistent migrations only inside the explicit plugin upgrade path.
- [ ] Preserve the previous source of truth until the new state has been written and verified.
- [ ] Make upgrades idempotent/restartable where practical.
- [ ] Record the new plugin version only after required migration steps succeed.
- [ ] Ensure upgrading site A does not alter site B.
- [ ] Test the state where shared 1.x files are deployed while another site still has the previous persisted AdSense version.

### Persistent storage policy

AdSense 1.0 should avoid persistent files unless they are genuinely necessary.

- [x] Prefer native configuration and, if needed, site-scoped plugin tables for plugin state.
- [ ] Do not use Geeklog cache directories for persistent plugin data.
- [ ] If a future feature requires persistent files, derive storage from the active site's `$_CONF['path_data']`.
- [ ] Keep persistent storage outside the public web root unless direct public access is required.
- [ ] Never use persistent shared files across sites unless sharing is an explicit feature.
- [ ] Make any future storage migration copy/verify first and preserve the legacy source until success.

### ads.txt

`ads.txt` is a domain-root resource and must not be confused with ordinary plugin persistent storage.

- [x] Add an `ads.txt` status page.
- [ ] Check whether the active site's root `ads.txt` URL is reachable.
- [x] Check for the configured Google publisher ID.
- [x] Display the expected Google line when missing.
- [x] Prefer diagnostics and guided configuration in 1.0 rather than automatic file modification.
- [x] Do not assume the plugin directory or `path_data` maps to the domain-root `ads.txt`.
- [x] Ensure multisite/domain-aware validation uses the active site's URL.
- [ ] If automatic management is ever added, design it as an explicit protected action with ownership/backup/rollback checks rather than a normal page-load side effect.

### Exclusions

- [x] Exclude Geeklog administration.
- [ ] Add configurable exclusions for common non-content pages such as login, registration, search, and account pages.
- [ ] Support URL/path exclusion patterns.
- [ ] Support anonymous vs authenticated-user rules.
- [ ] Support group exclusions, including Root.
- [ ] Evaluate current-site topic/content context using Geeklog APIs where available.
- [ ] Ensure exclusions apply consistently to native autotags and automatic/manual rendering paths where technically applicable.
- [ ] Fail closed for an exclusion rule that cannot be evaluated safely.

### Diagnostics and preview

- [ ] Add a read-only environment/diagnostic page.
- [ ] Report current serving mode.
- [ ] Report publisher ID state without exposing unrelated secrets.
- [ ] Detect plugin-generated duplicate script attempts.
- [ ] Detect likely external duplicate loader code where practical.
- [ ] Report `ads.txt` state.
- [ ] Report placement configuration issues.
- [ ] Report legacy autotag name conflicts.
- [ ] Report the active site's persisted/configuration schema state when useful for upgrade diagnostics.
- [x] Add an administrator-only preview/debug mode that renders placement placeholders instead of live ads.
- [ ] Ensure diagnostics never trigger schema migrations or expensive content scans automatically.

### 1.0 release criteria

- [ ] Fresh install succeeds through Geeklog's standard installer.
- [ ] Upgrade path is defined, site-scoped, restartable where practical, and tested.
- [ ] New files can run safely before the active site's persisted state is upgraded.
- [ ] Uninstall behavior is non-destructive to stored content.
- [ ] Auto Ads works without theme modification on supported Geeklog themes.
- [ ] Manual placement autotag works through the native Geeklog autotag API.
- [ ] Hybrid mode works.
- [ ] Legacy autotag conflicts are detected safely.
- [ ] Historical autotags are not silently rewritten.
- [ ] `ads.txt` diagnostics work.
- [ ] Configuration UI works without warnings and with valid language metadata/tooltips.
- [ ] All state-changing admin actions use ACL checks and security tokens.
- [ ] Multisite isolation is tested.
- [ ] Staggered shared-files upgrade is tested with at least two site contexts.
- [ ] Geeklog 2.1.1 + PHP 5.6 baseline is tested.
- [ ] Geeklog 2.2.2 + PHP 8.1 baseline is tested.
- [ ] PHP errors/notices are clean with warnings enabled.
- [ ] `plugin.json` validates and matches native installer compatibility metadata.
- [ ] README reflects implemented behavior rather than planned behavior.
- [ ] Installable release archive is tested.

---

## 1.1 — Placement rules, content sources and legacy migration

### Content-aware placement rules

AdSense should consume available content context; it must not become coupled to another plugin's private schema.

The placement engine must work on **rendered content context**, not only on stories/static pages. New content should not need AdSense autotags stored in its database text unless an editor explicitly chooses to use them.

- [ ] Add automatic render-time placement rules that can work without writing markers into stored content.
- [ ] Target placements by Geeklog/content type when the current context can be identified safely.
- [ ] Target placements by topic using Geeklog-provided/current content context where available.
- [ ] Add minimum content-length conditions.
- [ ] Add insertion positions such as:
  - [ ] after introduction;
  - [ ] after paragraph N;
  - [ ] proportional position in long content;
  - [ ] before/after main body;
  - [ ] before comments or equivalent discussion area.
- [ ] Support more than one automatic placement only when spacing rules allow it.
- [ ] Define minimum spacing between automatically inserted placements.
- [ ] Avoid insertion inside protected structures such as tables, code blocks, forms, figures, blockquotes requiring integrity, and embedded media.
- [ ] Provide a documented no-ad marker/class or equivalent integration contract.
- [ ] Detect explicit/manual AdSense markers and avoid duplicate automatic placement.
- [ ] Add an option to disable all automatic insertion when compatible AdSense autotags already exist in the current content.
- [ ] Evaluate `plugin_templatesetvars_adsense()`, content-provider hooks, and other native rendering hooks before introducing theme modifications.
- [ ] Keep rendering compatible with both Geeklog 2.1.1 and 2.2.2.
- [ ] Treat unavailable provider context as "placement rule cannot be evaluated", not as a fatal error.

### Generic content-source contract

Historical autotags may exist in **any text-bearing Geeklog content**, including core content and plugin-owned content.

The audit/migration layer must therefore use a provider model instead of hard-coded assumptions about only `stories` and `staticpages`.

Core sources to support explicitly:

- [ ] stories/articles;
- [ ] static pages;
- [ ] Geeklog blocks;
- [ ] forum posts/messages where Forum is installed and exposes a compatible source;
- [ ] comments where safely supported;
- [ ] other core text-bearing content identified during implementation.

Plugin/provider sources to support through capability detection:

- [ ] Documents content/body/description fields;
- [ ] MediaGallery album/media descriptions where applicable;
- [ ] Videos titles/descriptions/body fields where applicable;
- [ ] Maps descriptions/content fields where applicable;
- [ ] Calendar/event descriptions where applicable;
- [ ] Links descriptions where applicable;
- [ ] custom plugin content that exposes a compatible audit/migration provider;
- [ ] future providers without requiring AdSense code changes.

Provider requirements:

- [ ] Define a small AdSense legacy-content provider contract/callback for plugins that need audit/migration support.
- [ ] A provider must expose stable content identity (`type + id`) and one or more named text fields.
- [ ] A provider must expose read operations independently from write operations.
- [ ] A provider must explicitly declare whether a field is writable by migration.
- [ ] A provider must provide its own save/update callback for modifications; AdSense must not update another plugin's tables directly.
- [ ] AdSense must never infer writable fields from another plugin's schema.
- [ ] Missing provider support means "not auditable/migratable through AdSense", not permission to query private tables.
- [ ] Provider failures must be isolated so one broken plugin cannot abort the entire site audit.
- [ ] Scans must remain active-site scoped in multisite installations.
- [ ] Provider capability discovery must be cached only for the current request/site context.

### Legacy autotag audit

Historical tags to recognize include at minimum compatible forms such as:

```text
[adsense:1]
[leaderboard:1]
[inarticle:1]
[infeed:1]
```

The parameter after `:` is historical syntax and may have no semantic meaning for AdSense. Migration logic must preserve the original source until an administrator explicitly chooses otherwise.

- [ ] Add an administrator-triggered read-only scanner for known historical AdSense-compatible autotags.
- [ ] Scan all registered core and plugin content providers, not only stories and static pages.
- [ ] Never run a full content scan during normal frontend requests, plugin install, or ordinary upgrade.
- [ ] Count occurrences by:
  - [ ] provider/plugin;
  - [ ] content type;
  - [ ] field name;
  - [ ] autotag name;
  - [ ] affected content item.
- [ ] Record stable `type + id` references rather than relying only on database row positions.
- [ ] Identify the current autotag owner/provider when determinable.
- [ ] Classify matches as:
  - [ ] AdSense-compatible;
  - [ ] external;
  - [ ] unresolved/conflicting.
- [ ] Treat historical parameters such as `:1` as syntax to preserve/ignore unless a provider/site-specific migration rule explicitly assigns meaning to them.
- [ ] Show representative excerpts around matches without exposing unrelated private content unnecessarily.
- [ ] Allow an administrator to filter the audit by provider/content type/tag.
- [ ] Allow an administrator to create explicit aliases from compatible historical tags to named placements.
- [ ] Protect mapping changes with ACL and CSRF token checks.
- [ ] Keep runtime aliasing as the default non-destructive compatibility strategy.

### Existing-content strategy

Provide a clear site-level strategy for historical AdSense-compatible autotags:

- [ ] **Preserve and interpret** — leave stored content unchanged and render historical tags through their current owner or the AdSense compatibility layer.
- [ ] **Ignore for AdSense** — leave stored content unchanged but do not let AdSense claim/render historical aliases.
- [ ] **Migrate/remove explicitly** — remove compatible historical tags only through an administrator-triggered migration after preview.

Rules:

- [ ] Installation and upgrade must default to non-destructive preservation.
- [ ] Uninstall must never strip tags from stored content.
- [ ] Disabling AdSense must not rewrite stored content.
- [ ] Switching automatic placement rules must not rewrite stored content.
- [ ] New automatic placements should be injected at render time and should not add `[ad:...]` markers to the database automatically.
- [ ] Explicit editor-authored `[ad:PLACEMENT]` remains supported as an intentional manual override.

### Optional migration/removal utility

The migration utility is for administrators who intentionally want to remove obsolete AdSense-compatible autotags from stored content after render-time placement rules are configured.

- [ ] Require explicit administrator action.
- [ ] Require a fresh read-only audit before a destructive migration.
- [ ] Provide dry-run/preview with counts and affected `type + id + field`.
- [ ] Allow selection by provider/content type rather than an all-or-nothing site operation.
- [ ] Allow selection by autotag name.
- [ ] Show before/after excerpts for representative or individually selected items.
- [ ] Remove only AdSense-compatible historical tags explicitly selected by the administrator.
- [ ] Never convert/remove `[amazon]` or `[youtube]` as part of AdSense migration.
- [ ] Preserve surrounding whitespace/markup as safely as possible when removing a tag.
- [ ] Do not insert new `[ad:...]` tags merely to replace removed historical tags when automatic render-time placement is the chosen strategy.
- [ ] Use provider-owned write callbacks for plugin content.
- [ ] Refuse modification when a provider is read-only or does not expose a supported write path.
- [ ] Make migration repeatable/restartable where practical.
- [ ] Record migration progress/results site-locally for restart/diagnostics if persistence is required.
- [ ] Back up or otherwise support safe rollback according to Geeklog/provider conventions before destructive changes.
- [ ] Never migrate another site's content in multisite mode.
- [ ] Keep batch sizes bounded to avoid timeouts on large sites.
- [ ] Provide resumable batches for large installations.
- [ ] Log failures per content item/provider without aborting unrelated providers.

### Runtime duplication rules during transition

During coexistence with the historical Autotags plugin:

- [ ] If Autotags currently owns `adsense`, `leaderboard`, `inarticle`, or `infeed`, AdSense must not register the same autotag name.
- [ ] Historical content continues to render through Autotags until ownership is intentionally transferred.
- [ ] AdSense native `[ad:PLACEMENT]` remains available for new/manual use.
- [ ] Automatic render-time placement must detect historical/manual ad markers and avoid duplicate ads according to configured policy.
- [ ] Once the historical provider is disabled, AdSense may claim configured compatible aliases without requiring stored-content rewrites.
- [ ] Legacy parameters such as `:1` must be accepted and ignored when they carry no configured semantic meaning.

---

## 1.2 — Plugin interoperability

Use the Geeklog Plugin Content Interoperability Contract only in the role that applies to AdSense: **consumer of content context**, not owner of content.

- [ ] Consume stable `type + id + canonical URL` when a compatible content provider exposes them.
- [ ] Prefer `PLG_getItemInfo()`/provider callbacks over direct reads of another plugin's database.
- [ ] Support placement context for core stories and static pages first.
- [ ] Evaluate context consumption from Forum.
- [ ] Evaluate context consumption from MediaGallery.
- [ ] Evaluate context consumption from Videos.
- [ ] Evaluate context consumption from Maps.
- [ ] Evaluate other compatible content plugins only through capability detection.
- [ ] Allow providers/themes to expose protected no-ad regions through a documented optional convention.
- [ ] Keep every integration optional and capability-driven.
- [ ] Never require a content plugin to depend on AdSense merely to publish content.
- [ ] Ensure AdSense being disabled or broken cannot block another plugin's content save/display workflow.

The AdSense plugin should consume shared contracts, not become a collection of direct dependencies on unrelated plugins.

---

## 1.x — Consent integration

Consent handling must remain separate from placement logic.

- [ ] Define a small consent-provider state check that is optional and capability-driven.
- [ ] Support "managed elsewhere" mode.
- [ ] Evaluate Google consent integration.
- [ ] Allow third-party CMP integration where a reliable signal is available.
- [ ] Delay ad loading when required by the configured consent mode.
- [ ] Avoid storing a second, competing consent state when an external CMP is authoritative.
- [ ] Fail safely when a configured consent provider is unavailable.
- [ ] Keep provider-specific credentials/state owned by the provider responsible for them.

---

## 2.0 — Reporting and AdSense API integration

Only after the core placement layer is stable.

The SEO & Measurement Interoperability Guide establishes a useful pattern for provider plugins: **URL-first services, normalized output, capability discovery, provider-owned credentials, and graceful failure**. AdSense reporting should follow the same architecture even though its metrics are monetization metrics rather than SEO metrics.

### API connection

- [ ] Evaluate Google AdSense Management API requirements and authentication flow.
- [ ] Add an optional read-only account connection.
- [ ] Keep Google credentials owned by AdSense and never expose raw provider credentials to Hub or another consumer.
- [ ] Keep API/reporting optional; ad rendering must work without API credentials.
- [ ] Ensure expired credentials or Google API outages do not affect normal site rendering.

### URL-first reporting service

- [ ] Evaluate a stable service such as `ADSENSE_getUrlMetrics($url, $period, $options = array())`.
- [ ] Accept canonical URLs/page paths without requiring knowledge of the owning plugin's SQL schema.
- [ ] Normalize common fields where the AdSense API provides them, for example:
  - [ ] provider;
  - [ ] period;
  - [ ] estimated earnings;
  - [ ] impressions;
  - [ ] clicks;
  - [ ] CTR;
  - [ ] page views where available.
- [ ] Keep provider-specific raw fields optional rather than leaking them into the common contract.
- [ ] Define explicit unavailable/error states instead of throwing failures into consuming plugins.

### Reporting UI and interoperability

- [ ] Display high-level performance metrics.
- [ ] Report performance by supported dimensions such as ad unit/placement when available.
- [ ] Add date ranges.
- [ ] Add device/platform breakdown where supported.
- [ ] Evaluate capability discovery so Hub/admin tools can detect AdSense reporting without hard-coded assumptions.
- [ ] Allow a future Hub integration to consume normalized AdSense metrics without owning AdSense authentication.
- [ ] Define any local cache/history retention explicitly and keep it site-scoped.
- [ ] If persistent report cache files are ever used, follow the persistent-storage guide; otherwise prefer an appropriate site-scoped database/cache mechanism.
- [ ] Do not turn Geeklog into a replacement for the complete Google AdSense console.
- [ ] Keep write/account-management operations out of scope unless a strong future use case justifies them.

---

## Out of scope for the first release

The following are intentionally not 1.0 goals:

- reproducing all Google Auto Ads settings inside Geeklog;
- building a complete CMP;
- editing Google account-level AdSense settings;
- rewriting historical content automatically;
- taking over `[amazon]` or `[youtube]` autotags;
- direct SQL integration with Forum, MediaGallery, Maps, Videos, or another content plugin;
- content ownership APIs that do not apply to AdSense;
- revenue optimization algorithms;
- automatic experimentation/A-B testing;
- full AdSense reporting;
- cross-site administration of AdSense settings from one Geeklog site.

---

## Memorandum compliance checklist

Before each release, verify the points that are applicable to AdSense:

### Compatibility

- [ ] Geeklog 2.1.1 compatibility verified.
- [ ] Geeklog 2.2.2 compatibility verified.
- [ ] PHP 5.6 compatibility verified.
- [ ] PHP 8.1 compatibility verified.
- [ ] No unsupported modern PHP syntax in shared runtime code.
- [ ] Newer Geeklog APIs have feature detection/fallback where required.

### Configuration

- [ ] Native Configuration API used for ordinary settings.
- [ ] Configuration hierarchy and selection metadata are valid.
- [ ] Language metadata is complete.
- [ ] Tooltips use the native Plugin API.
- [ ] Fresh install and upgrade configuration paths are tested separately.

### Security

- [ ] ACL checks are performed server-side.
- [ ] State-changing actions use Geeklog security tokens.
- [ ] Input validation and context-specific output escaping are in place.
- [ ] Diagnostics/logs do not expose sensitive credentials.

### Multisite and upgrades

- [ ] Active-site `$_CONF`/`$_TABLES` context is used.
- [ ] No cross-site scanning or implicit modification.
- [ ] Shared-files staggered-upgrade scenario is tested.
- [ ] Previous persisted state remains readable until explicit upgrade.
- [ ] Migrations are site-scoped, non-destructive, and retryable where practical.

### Metadata and packaging

- [x] `plugin.json` is valid and static.
- [x] Manifest requirements match actual release support.
- [x] Runtime/native installer metadata remains authoritative.
- [x] Release archive contains the expected Geeklog plugin structure.

### Interoperability

- [ ] AdSense remains a consumer/provider, not a fake content owner.
- [ ] No direct coupling to another plugin's tables.
- [ ] Optional capabilities are detected before use.
- [ ] External-provider failure cannot break core content workflows.

---

## Reference documents

Development should remain aligned with the Geeklog Memorandum, especially:

- `README.md` — current compatibility and engineering rules
- `plugin-api-reference-2.2.2.md`
- `plugin-configuration-migration-guide-2.2.2.md`
- `plugin-configuration-tooltips.md`
- `plugin-content-interoperability-contract.md`
- `multisite-development-principles.md`
- `plugin-metadata-manifest.md`
- `plugin-shared-files-upgrade-safety.md`
- `plugin-persistent-storage-guide.md`
- `seo-measurement-interoperability-guide.md`

Repository: https://github.com/hostellerie/memorandum

## Interpretation note

Not every Memorandum document applies equally to AdSense.

- The **Plugin API**, configuration, multisite, shared-files upgrade safety, manifest, storage, security, and compatibility rules apply directly.
- The **Plugin Content Interoperability Contract** applies mainly to how AdSense consumes content context; AdSense itself does not own addressable content.
- The **SEO & Measurement Interoperability Guide** becomes directly relevant only for the future reporting/API layer, where its URL-first and provider-isolation patterns are useful.
- LLM/Agent contracts and content lifecycle publishing requirements are not 1.0 requirements for AdSense.
