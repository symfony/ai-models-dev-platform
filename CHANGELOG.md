CHANGELOG
=========

0.10
----

 * `ModelCatalog` now wires each provider with the model class its bridge expects (e.g. `Claude`
   for Anthropic), so it drops directly into that bridge
 * `ProviderRegistry::getApiBaseUrl()` now resolves base URLs for providers whose models.dev entry
   omits them, via a well-known npm-package fallback
 * [BC BREAK] Remove `Factory` and `BridgeResolver`; build providers with the regular bridge
   factories using a models.dev `ModelCatalog`, and compose them into a `Platform`
 * [BC BREAK] Remove `ModelResolver`; compose providers into a `Platform` and let its default
   router route by catalog (order providers to disambiguate shared model ids)

0.8
---

 * [BC BREAK] Rename `PlatformFactory` to `Factory` with explicit `createProvider()` and `createPlatform()` methods

0.6
---

 * Add well-known base URLs for providers with dedicated npm packages
 * Skip specialized bridge check in `ModelsDevPlatformFactory` when a custom `$baseUrl` is provided

0.4
---

 * Add models.dev bridge with auto-discovered model catalogs
