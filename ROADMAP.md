# AdSense for Geeklog — Roadmap

This roadmap defines the intended direction of the AdSense plugin beginning with the `develop-1.0` branch.

It is a planning document. Checked items should only be added when implementation and validation are complete.

## Design principles

- Keep AdSense configuration out of Geeklog themes whenever possible.
- Load the Google AdSense bootstrap script once.
- Separate Google account configuration from Geeklog placement rules.
- Use stable named placements instead of embedding slot IDs throughout content.
- Preserve existing content and historical autotags.
- Never silently take ownership of an autotag already provided by another plugin.
- Avoid automatic bulk rewriting of stored content.
- Keep multisite configuration and runtime state isolated per site.
- Prefer Geeklog/plugin interoperability contracts over plugin-specific coupling.
- Make diagnostics and safe failure behavior part of the core product.

---

## 1.0 — Core integration and compatibility

### Plugin foundation

- [ ] Create standard Geeklog plugin structure.
- [ ] Add install, uninstall, upgrade, enable, and disable lifecycle support.
- [ ] Define supported Geeklog/PHP versions before release.
- [ ] Add `plugin.json` metadata following the Memorandum manifest.
- [ ] Add language files.
- [ ] Add configuration groups with human-readable option labels and tooltips.
- [ ] Ensure admin pages are protected by Geeklog permissions.
- [ ] Verify multisite-safe configuration access.
- [ ] Add upgrade-safe handling for any shared/public files used by the plugin.

### Global AdSense configuration

- [ ] Enable/disable the plugin's ad-serving layer.
- [ ] Configure and validate the publisher ID (`ca-pub-...`).
- [ ] Implement explicit modes:
  - [ ] Disabled
  - [ ] Auto Ads
  - [ ] Manual placements
  - [ ] Hybrid
- [ ] Inject the AdSense loader in the appropriate document head.
- [ ] Prevent duplicate loader injection by the plugin itself.
- [ ] Detect likely duplicate AdSense loaders already present in the theme or custom code.
- [ ] Never render advertising inside Geeklog administration pages.

### Auto Ads

- [ ] Support a minimal Auto Ads integration.
- [ ] Keep Google-side Auto Ads controls in AdSense rather than reproducing them unnecessarily in Geeklog.
- [ ] Document the boundary between Geeklog configuration and Google AdSense configuration.
- [ ] Allow Auto Ads to coexist with explicitly managed placements in Hybrid mode.

### Named manual placements

- [ ] Define a storage model for named placements.
- [ ] Store slot ID separately from placement name.
- [ ] Support enable/disable per placement.
- [ ] Support responsive ad units.
- [ ] Support common AdSense format metadata without hard-coding presentation into article content.
- [ ] Render a placement through one central renderer.
- [ ] Avoid duplicated bootstrap scripts when multiple placements occur on the same page.
- [ ] Provide initial placement targets for Geeklog blocks and explicit autotags.

### Native autotag namespace

- [ ] Reserve `[ad:PLACEMENT]` for new AdSense plugin placements.
- [ ] Examples:
  - [ ] `[ad:article-middle]`
  - [ ] `[ad:article-bottom]`
  - [ ] `[ad:leaderboard]`
  - [ ] `[ad:sidebar]`
- [ ] Resolve the placement name through plugin configuration/storage.
- [ ] Never require a Google slot ID to be stored in article text.
- [ ] Return safe output when a requested placement is disabled or unknown.

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
- [ ] Do not claim or override an existing provider silently.
- [ ] Treat `[adsense]`, `[inarticle]`, `[infeed]`, and `[leaderboard]` as possible AdSense-compatible legacy tags.
- [ ] Keep `[amazon]` external to the AdSense plugin.
- [ ] Keep `[youtube]` external to the AdSense plugin.
- [ ] Implement a legacy alias/adapter model that can map compatible historical tags to named placements.
- [ ] Make legacy mapping opt-in/configurable where ownership is ambiguous.
- [ ] Do not rewrite stored content during install or ordinary upgrade.
- [ ] Do not remove historical tags from content during uninstall.
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

### ads.txt

- [ ] Add an `ads.txt` status page.
- [ ] Check whether the root `ads.txt` URL is reachable.
- [ ] Check for the configured Google publisher ID.
- [ ] Display the expected Google line when missing.
- [ ] Prefer diagnostics and guided configuration before implementing automatic file modification.
- [ ] Ensure multisite/domain-aware validation.

### Exclusions

- [ ] Exclude Geeklog administration.
- [ ] Add configurable exclusions for common non-content pages such as login, registration, search, and account pages.
- [ ] Support URL/path exclusion patterns.
- [ ] Support anonymous vs authenticated-user rules.
- [ ] Support group exclusions, including Root.
- [ ] Ensure exclusions apply consistently to native autotags and automatic/manual rendering paths where technically applicable.

### Diagnostics and preview

- [ ] Add a read-only environment/diagnostic page.
- [ ] Report current serving mode.
- [ ] Report publisher ID state without exposing unrelated secrets.
- [ ] Detect plugin-generated duplicate script attempts.
- [ ] Detect likely external duplicate loader code where practical.
- [ ] Report `ads.txt` state.
- [ ] Report placement configuration issues.
- [ ] Report legacy autotag name conflicts.
- [ ] Add an administrator-only preview/debug mode that renders placement placeholders instead of live ads.

### 1.0 release criteria

- [ ] Fresh install succeeds.
- [ ] Upgrade path is defined and tested.
- [ ] Uninstall behavior is non-destructive to stored content.
- [ ] Auto Ads works without theme modification on supported Geeklog themes.
- [ ] Manual placement autotag works.
- [ ] Hybrid mode works.
- [ ] Legacy autotag conflicts are detected safely.
- [ ] Historical autotags are not silently rewritten.
- [ ] `ads.txt` diagnostics work.
- [ ] Multisite isolation is tested.
- [ ] PHP errors/notices are clean in supported environments.
- [ ] README reflects implemented behavior rather than planned behavior.
- [ ] Installable release archive is tested.

---

## 1.1 — Placement rules and legacy audit

### Content-aware placement rules

- [ ] Target placements by Geeklog content type.
- [ ] Target placements by topic.
- [ ] Add minimum content-length conditions.
- [ ] Add article insertion positions such as:
  - [ ] after introduction;
  - [ ] after paragraph N;
  - [ ] proportional position in long content;
  - [ ] before/after article body;
  - [ ] before comments.
- [ ] Define minimum spacing between automatically inserted manual placements.
- [ ] Avoid insertion inside protected structures such as tables, code blocks, forms, figures, and embedded media.
- [ ] Provide a standard no-ad marker/class or equivalent integration contract.

### Legacy autotag audit

- [ ] Add a read-only scanner for known historical autotags.
- [ ] Count occurrences by content source where feasible.
- [ ] Identify current provider/owner when determinable.
- [ ] Classify tags as:
  - [ ] AdSense-compatible;
  - [ ] external;
  - [ ] unresolved/conflicting.
- [ ] Allow an administrator to create explicit aliases from compatible legacy tags to named placements.
- [ ] Provide a preview before any future content migration.
- [ ] Keep runtime aliasing as the preferred compatibility strategy.

### Optional migration utility

- [ ] Evaluate whether a content migration utility is actually necessary.
- [ ] If implemented, require explicit administrator action.
- [ ] Provide dry-run/preview.
- [ ] Provide counts and affected content IDs.
- [ ] Back up or otherwise support safe rollback according to Geeklog conventions.
- [ ] Never convert `[amazon]` or `[youtube]` into AdSense tags.

---

## 1.2 — Plugin interoperability

Use the Geeklog Plugin Content Interoperability Contract where appropriate.

- [ ] Allow compatible content providers to expose content context without AdSense knowing their internal schema.
- [ ] Support placement context for core stories and static pages first.
- [ ] Evaluate integration with Forum.
- [ ] Evaluate integration with MediaGallery.
- [ ] Evaluate integration with Videos.
- [ ] Evaluate integration with Maps.
- [ ] Evaluate integration with other compatible content plugins.
- [ ] Allow providers/themes to expose protected no-ad regions.
- [ ] Keep every integration optional and capability-driven.

The AdSense plugin should consume shared contracts, not become a collection of direct dependencies on unrelated plugins.

---

## 1.x — Consent integration

Consent handling must remain separate from placement logic.

- [ ] Define a small consent-provider interface/state check.
- [ ] Support "managed elsewhere" mode.
- [ ] Evaluate Google consent integration.
- [ ] Allow third-party CMP integration where a reliable signal is available.
- [ ] Delay ad loading when required by the configured consent mode.
- [ ] Avoid storing a second, competing consent state when an external CMP is authoritative.

---

## 2.0 — Reporting and AdSense API integration

Only after the core placement layer is stable:

- [ ] Evaluate Google AdSense Management API requirements and authentication flow.
- [ ] Add optional read-only account connection.
- [ ] Display high-level performance metrics.
- [ ] Report performance by supported dimensions such as ad unit/placement when available.
- [ ] Add date ranges.
- [ ] Add device/platform breakdown where supported.
- [ ] Keep reporting optional.
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
- revenue optimization algorithms;
- automatic experimentation/A-B testing;
- full AdSense reporting.

---

## Reference documents

Development should remain aligned with the Geeklog Memorandum, in particular:

- `plugin-api-reference-2.2.2.md`
- `plugin-configuration-migration-guide-2.2.2.md`
- `plugin-configuration-tooltips.md`
- `plugin-content-interoperability-contract.md`
- `multisite-development-principles.md`
- `plugin-metadata-manifest.md`
- `plugin-shared-files-upgrade-safety.md`

Repository: https://github.com/hostellerie/memorandum
