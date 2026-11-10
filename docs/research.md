# Engineering research

Sources inspected on 2026-11-10:

| Source | Observations | Engineering opportunity |
| --- | --- | --- |
| [Verta](https://hekmatinasser.github.io/verta/) | DateTime-based API, mutable setters, arithmetic, boundaries, validation, localization and Carbon interoperability | Explicit immutable return values and instance-scoped settings |
| [Morilog](https://github.com/morilog/jalali) | Jalalian value API and CalendarUtils conversion/parsing helpers | Specify input grammar, exceptions, supported range and microseconds |
| [Morilog issues](https://github.com/morilog/jalali/issues) | Reports #190 (missing parser year key), #180 (checkDate result) | Malformed-input regression tests; reports are not independently reproduced defects |
| [Laravel](https://laravel.com/docs/packages) | Discovery, service providers, config merging, publishable resources | Preserve native provider wiring and config caching |
| [Carbon](https://carbonphp.github.io/carbon/) | Native DateTime interoperability, arithmetic, timezone handling and localization | Compose native immutable dates; avoid global macros or test-clock state |

Verta's Composer manifest separates the Laravel adapter from hekmatinasser/jalali and includes validation. Both competitor repositories expose test directories; the Morilog tests page failed to render individual files. No coverage or speed advantage has been established. Carbon's original documentation URL returned 404; its repository links the replacement above.

## Calendar decision

Use K. M. Borkowski's [Persian calendar specification](https://www.astro.uni.torun.pl/~kb/Papers/EMP/PersianC-EMP.htm), Table III, for the Gregorian March date of Nowruz. Implement the published mathematical procedure independently, without copying competitor code. Gregorian date arithmetic uses PHP's proleptic Gregorian calendar in UTC. Support Jalali years 1–3177. Leap years use the same procedure's leap indicator, so conversion and validation share one convention.

This is a deterministic tabular model, not a guarantee of future official astronomical calendars. The paper discusses uncertainty in historical and future equinoxes. Other arithmetic calendars (including 2820-year cycles) can disagree; never switch algorithms implicitly.

## Acceptance measures

Exhaustive valid-day round trips, independent published date anchors, malformed-input rejection, stable microseconds and timezone instants, strict analysis, and measured coverage are release gates. Benchmarks must use identical datasets and installed competitor versions. All superiority claims remain unproven until measured.
