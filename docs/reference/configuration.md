# Configuration

The [example configuration](https://github.com/dereuromark/cakephp-ratings/blob/cake6/config/app.example.php) can be merged into the array returned by your application configuration file. It does not replace that file.

| Namespace | Key | Purpose |
| --- | --- | --- |
| Ratings | table | Runtime ratings table name; default `ratings` |
| Ratings | userClass | Users association class; default `Users` |
| Polymorphic | type | Bundled migration's foreign-key type; default `integer` |

Component and behavior settings are supplied when loading them; the `Ratings` namespace does not globally configure either one or the helper.

## Behavior settings

| Option | Default | Meaning |
| --- | --- | --- |
| modelClass | rated table alias | Association back to the rated table |
| rateClass | Ratings.Ratings | Ratings table class |
| foreignKey | foreign_key | Association foreign key; persistence uses the standard ratings schema |
| field | rating | Calculated result field |
| fieldSummary | rating_sum | Cached sum field |
| fieldCounter | rating_count | Cached count field |
| calculation | average | Average or sum |
| saveToField | true | Store result; false calculates only, a string selects a field |
| update | false | Permit replacement of a previous vote |
| userField | user_id | Owner field for the self-vote guard; null disables it |
| modelValidate | false | Rated-record validation when patching calculated fields |

`countRates` and `modelCallbacks` are retained legacy settings. CakePHP 6 does not implement the old association `counterCache` option or the save `callbacks` option used here. Do not rely on those flags to count votes or disable ORM events. Use the configured cache fields and normal CakePHP event handling.

## Component settings

`actions`, `modelName`, `userId`, `userIdField` and `allowFallbackClass` are component-specific. Defaults are all actions, automatic table detection, identity lookup, `id` and true respectively. `params` defaults to null `rate` and `rating`, with `redirect` true. The component passes its remaining configuration to the behavior only when attaching it.

The helper's form defaults are its public `defaults` array and per-call options. Constructor options are not automatically merged into the form's defaults.
