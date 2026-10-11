# Upgrading

This branch targets CakePHP 6. See [composer.json](https://github.com/dereuromark/cakephp-ratings/blob/cake6/composer.json) for supported versions.

Use a request identity for current authentication integrations. Legacy AuthUser and Auth lookups remain available; the plugin does not require the removed core Auth component. Call behavior methods through `$table->getBehavior('Ratable')` because CakePHP 6 no longer delegates arbitrary behavior methods through the table. `isRatedBy()` returns a query and must be followed by `first()` when you need the vote entity.

The Ratings association now includes the model discriminator. Records from different tables sharing the same ID no longer load each other's votes, and deleting a rated record preserves another model's votes. Calculated result writes work with locked entity fields; keep those fields protected from submitted data.

JSON responses now use JsonView serialization with a flat `{status, message, rating}` payload. Applications previously relying on a custom `rated` JSON template or a nested `data.message` must update their client handling or render their own response with automatic redirect disabled. The optional star widget initializes the input and preserves the containing form. HTML attributes and JavaScript identifiers are escaped.

The bundled migration always creates `ratings` with an integer user ID. Review custom schemas before running it; runtime table configuration does not alter it. Legacy `countRates` and `modelCallbacks` flags do not enable CakePHP 6 counter-cache behavior or suppress ORM events.

Request-supplied redirect URLs are rejected; use boolean form values and put trusted redirect targets in component configuration.

CakePHP 6 extension classes use `defaultConfig`, `config`, `table` and `View` for inherited properties. Entity mass-assignment rules use `patchable`; per-operation overrides use `patchableFields`. Update application subclasses and entities when moving to this branch.
