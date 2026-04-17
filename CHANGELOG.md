# Changelog

## [1.0.0] - 2026-04-09

### Added
- Initial release based on webpatser/portable-ascii 3.1.0
- PHP 8.5 minimum, ext-mbstring required
- Removed all runtime capability detection: mbstring always used
- `mb_str_split()` replacing `preg_match_all` for character splitting
- `mb_convert_encoding()` replacing regex for UTF-8 cleaning
- Fixed Bulgarian, Ukrainian, Austrian (Eszett), and Hindi transliteration
- 247 tests, 1602 assertions
