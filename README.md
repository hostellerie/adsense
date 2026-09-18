# AdSense for Geeklog

AdSense is a planned Geeklog plugin for integrating Google AdSense without requiring advertising code to be hard-coded into a theme.

The project is intended to support two very different site requirements:

- **full automation**, where Google Auto Ads controls most advertising placement;
- **controlled placement**, where the site administrator chooses exactly where named AdSense placements may appear.

A **hybrid mode** will allow both approaches to be used together.

> **Development status:** active 1.0 development. The plugin foundation, native Geeklog configuration, AdSense bootstrap loading, named manual placements, `[ad:PLACEMENT]`, administrator preview mode, legacy autotag conflict detection, and automatic `dist/` packaging are implemented. Items still marked as planned below or in the roadmap are not yet release-validated.

## Goals

The plugin should provide a stable advertising layer between Geeklog content and Google AdSense while keeping themes and stored content as independent as possible from individual AdSense slot IDs.

Core goals:

- configure the AdSense publisher ID in Geeklog;
- load the AdSense script once and only once;
- support **Disabled**, **Auto Ads**, **Manual placements**, and **Hybrid** modes;
- define reusable named placements rather than embedding Google slot IDs in content;
- provide a native namespaced autotag for new content;
- preserve historical autotags already stored in existing sites;
- support Geeklog blocks and content-aware placements;
- provide exclusions for administration, users, groups, content types, topics, and URLs;
- validate or assist with `ads.txt`;
- remain safe for multisite Geeklog installations;
- provide diagnostics before adding reporting or account-management features.

## Current implementation

The current `develop-1.0` branch includes:

- standard Geeklog autoinstall metadata and `plugin.json`;
- native Geeklog Configuration settings for serving mode, publisher ID, debug preview, and legacy aliases;
- a site-scoped `adsense_placements` table for named manual placements;
- an administration page for creating, editing, disabling, and deleting placements;
- centralized rendering of responsive AdSense units;
- native `[ad:PLACEMENT]` autotag support;
- Auto Ads, Manual, Hybrid, and Disabled serving modes;
- administrator-only placeholders instead of live ads when debug preview is enabled;
- conflict-aware legacy alias discovery for `adsense`, `inarticle`, `infeed`, and `leaderboard`;
- read-only `ads.txt` diagnostics against the active site's root file and configured publisher ID;
- explicit non-ownership of `amazon` and `youtube`;
- a GitHub Actions build that validates PHP syntax and `plugin.json`, rejects hidden archive entries, and writes an installable ZIP to `dist/`.

Runtime validation on the supported Geeklog/PHP combinations is still required before the 1.0 release.

## Legacy content and automatic placement

Historical AdSense-compatible autotags may exist in many kinds of Geeklog content, not only stories and static pages. They can also appear in blocks, forum posts, comments, Documents content, plugin descriptions, and other text-bearing content provided by installed plugins.

The planned migration model is deliberately non-destructive:

- existing content can keep historical tags and continue to render them;
- administrators can run a read-only site audit across registered content providers;
- destructive tag removal, if used, requires an explicit previewed administrator action;
- new automatic ad placement should happen at render time and should not write AdSense markers into newly created content automatically;
- plugin-owned content must be audited or modified through shared Geeklog/provider capabilities rather than direct AdSense queries against another plugin's private tables;
- AdSense follows the same provider-neutral discovery principle as Agent: prefer `PLG_getItemInfo()`, bounded `PLG_invokeService()` capabilities, or documented versioned plugin contracts;
- AdSense does not depend on Agent being installed: Agent and AdSense independently consume the same owning-plugin contracts;
- `[amazon]` and `[youtube]` remain outside AdSense migration;
- legacy parameters such as `:1` can be accepted as historical syntax and ignored when they have no configured meaning.

This allows a site to keep old content untouched while gradually moving new rendering to the AdSense plugin.

### Relationship with Agent

Agent is a reference consumer of Geeklog plugin capabilities, not a required dependency of AdSense.

The intended architecture is:

```text
Content/plugin owner
        ↓
shared Geeklog contract / capability
        ↓
   ┌────┴────┐
   ↓         ↓
 Agent     AdSense
```

Both consumers should feature-detect what an owning plugin actually exposes. AdSense should never call Agent merely to access Forum, Documents, MediaGallery, Videos, Maps or another plugin, and should never fall back to that plugin's private SQL merely because Agent is absent.

## Advertising modes

### Auto Ads

The simplest mode.

Geeklog loads the required AdSense publisher script and Google Auto Ads performs automatic placement according to the configuration stored in the AdSense account.

The plugin should not duplicate every Auto Ads option exposed by Google.

### Manual placements

The administrator defines named placements such as:

```text
article-middle
article-bottom
leaderboard
sidebar
feed
multiplex
```

Each placement can be associated with an AdSense ad unit and with Geeklog placement rules.

Stored content should reference the placement name, not a Google slot ID.

### Hybrid

Auto Ads remains enabled while selected manual placements are also rendered by Geeklog.

This allows a site to use Google automation while reserving important positions for explicitly managed units.

## Native autotag

New AdSense-managed content should use a dedicated namespace:

```text
[ad:article-middle]
[ad:article-bottom]
[ad:leaderboard]
[ad:sidebar]
```

The `ad` namespace is intentional. Existing Geeklog sites may already contain a historical `[adsense]` autotag, so the plugin must not claim that legacy name in a way that changes existing content unexpectedly.

## Historical autotag compatibility

Existing sites may contain these historical autotags:

```text
[adsense]
[amazon]
[inarticle]
[infeed]
[leaderboard]
[youtube]
```

The plugin must preserve them.

The intended compatibility policy is:

| Historical autotag | AdSense plugin policy |
| --- | --- |
| `[adsense]` | May be mapped to a configurable default AdSense placement |
| `[inarticle]` | May be mapped to a named in-article placement |
| `[infeed]` | May be mapped to a named feed placement |
| `[leaderboard]` | May be mapped to a named leaderboard placement |
| `[amazon]` | External; must not be taken over by AdSense |
| `[youtube]` | External; must not be taken over by AdSense |

Compatibility should be implemented through runtime aliases or an adapter layer whenever possible.

Installing, upgrading, disabling, or uninstalling AdSense must **not silently rewrite existing stories, static pages, forum posts, or other stored content**.

A future migration tool may offer an explicit preview and opt-in conversion, but destructive bulk replacement is not part of the default design.

## Placement model

A placement is a stable Geeklog-side identifier.

For example:

```text
[ad:article-middle]
        |
        v
article-middle
        |
        v
AdSense slot 1234567890
```

The slot can later be replaced without editing every article that references the placement.

Planned placement properties include:

- name and stable identifier;
- enabled/disabled state;
- AdSense slot ID;
- format;
- responsive behavior;
- eligible content types;
- topics;
- URL rules;
- user/group rules;
- minimum content length;
- insertion position;
- exclusion/protected zones.

## Legacy autotag discovery

The administration interface should eventually provide a read-only audit of historical autotags before any migration is offered.

Example:

```text
adsense       -> historical provider -> AdSense-compatible
inarticle     -> historical provider -> AdSense-compatible
infeed        -> historical provider -> AdSense-compatible
leaderboard   -> historical provider -> AdSense-compatible
amazon        -> external
youtube       -> external
```

The scanner should be able to report where a tag occurs without changing the stored content.

## Multisite

The plugin is intended to follow the Geeklog multisite development principles documented in the Memorandum project.

A multisite installation should be able to use different advertising strategies per site, for example:

```text
site-a.example   Auto Ads
site-b.example   Hybrid
site-c.example   Manual
site-d.example   Disabled
```

Shared code must not imply shared site configuration or shared runtime state.

## Diagnostics

The first stable release should favor diagnostics over complex reporting.

Planned checks include:

- publisher ID configured;
- AdSense loader present;
- duplicate loader detection;
- HTTPS state;
- `ads.txt` reachability and publisher entry;
- active advertising mode;
- known manual placements;
- invalid or incomplete placement definitions;
- legacy autotag conflicts;
- advertising disabled on administration pages.

A debug/preview mode is also planned so administrators can inspect placement positions without requesting live ads.

## Privacy and consent

AdSense should not become a full consent-management platform.

The plugin may integrate with Google or third-party consent mechanisms and should be able to defer ad loading when required, but consent policy and consent storage should remain clearly separated from advertising placement logic.

## Geeklog interoperability

Development should follow the relevant guidance in the [Geeklog Memorandum](https://github.com/hostellerie/memorandum), especially:

- `plugin-api-reference-2.2.2.md`
- `plugin-configuration-migration-guide-2.2.2.md`
- `plugin-configuration-tooltips.md`
- `plugin-content-interoperability-contract.md`
- `multisite-development-principles.md`
- `plugin-metadata-manifest.md`
- `plugin-shared-files-upgrade-safety.md`

Content-aware advertising should prefer shared Geeklog/plugin contracts over direct knowledge of another plugin's internal database schema.

## Roadmap

See [ROADMAP.md](ROADMAP.md) for the planned implementation phases.

## Target

The initial development target is **AdSense 1.0** for modern Geeklog installations, with compatibility and migration safety treated as release requirements rather than optional cleanup work.

## License

See [LICENSE](LICENSE).
