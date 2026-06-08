Models.dev Platform
===================

Models.dev platform bridge for Symfony AI provides up-to-date model catalogs for many
AI providers, sourced from the [models.dev](https://models.dev) community registry and
shipped as the standalone `symfony/models-dev` package.

It decouples the model-catalog lifecycle from the Symfony AI release cycle: drop a
models.dev `ModelCatalog` into any bridge and refresh the model list (and its discovered
capabilities) with `composer update symfony/models-dev`, without waiting for a new release
or hand-curating catalogs.

See the [full documentation](https://symfony.com/doc/current/ai/components/platform/models-dev.html)
for usage and configuration details.

models.dev Documentation
------------------------

 * [models.dev](https://models.dev/)
 * [Catalog JSON (`api.json`)](https://models.dev/api.json)
 * [Source](https://github.com/sst/models.dev)

Resources
---------

 * [Contributing](https://symfony.com/doc/current/contributing/index.html)
 * [Report issues](https://github.com/symfony/ai/issues) and
   [send Pull Requests](https://github.com/symfony/ai/pulls)
   in the [main Symfony AI repository](https://github.com/symfony/ai)
