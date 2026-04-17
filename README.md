# webpatser/fledge-portable-ascii

PHP 8.5 optimized drop-in replacement for [`voku/portable-ascii`](https://github.com/voku/portable-ascii), built for [Fledge](https://github.com/webpatser/fledge-framework).

## Why a separate package?

The base [`webpatser/portable-ascii`](https://github.com/webpatser/portable-ascii) supports PHP 8.1+ and conditionally detects extensions at runtime. This variant requires PHP 8.5 and ext-mbstring, removing all conditionals for maximum performance:

- No runtime capability detection: mbstring is always available
- `mb_str_split()` directly (no `preg_match_all` fallback)
- `mb_convert_encoding()` for UTF-8 cleaning (no regex fallback)
- All PHP 8.5 features enabled unconditionally

## Installation

```bash
composer require webpatser/fledge-portable-ascii
```

Replaces both `voku/portable-ascii` and `webpatser/portable-ascii` via Composer's `replace` directive.

## Requirements

- PHP 8.5+
- ext-mbstring (required, not optional)
- ext-intl (optional, used in strict transliteration mode)

## Usage

Same API as `voku/portable-ascii`:

```php
use voku\helper\ASCII;

ASCII::to_ascii('Düsseldorf', 'de');    // 'Duesseldorf'
ASCII::to_slugify('Hello Wörld!');      // 'hello-woerld'
ASCII::to_transliterate('こんにちは');   // 'konnichiha'
ASCII::is_ascii('hello');               // true
```

## Credits

Based on [webpatser/portable-ascii](https://github.com/webpatser/portable-ascii), which is a modernized fork of [voku/portable-ascii](https://github.com/voku/portable-ascii) by [Lars Moelleken](https://github.com/voku).

## License

MIT
