<?php

/**
 * Integration test: verify webpatser/portable-ascii works as drop-in
 * replacement within Laravel's Illuminate\Support\Str.
 *
 * Run: php tests/integration.php (from a project with laravel/framework installed)
 */

// Load autoloader: prefer cwd (CI runs from laravel-test/), fall back to package vendor
$autoload = getcwd() . '/vendor/autoload.php';
if (! file_exists($autoload)) {
    $autoload = __DIR__ . '/../vendor/autoload.php';
}
require $autoload;

use Illuminate\Support\Str;

$passed = 0;
$failed = 0;

function assert_equals(string $test, mixed $expected, mixed $actual): void
{
    global $passed, $failed;
    if ($expected === $actual) {
        $passed++;
    } else {
        $failed++;
        echo "FAIL: {$test}\n";
        echo "  expected: " . var_export($expected, true) . "\n";
        echo "  actual:   " . var_export($actual, true) . "\n";
    }
}

// --- Str::ascii() ---
assert_equals('ascii: basic', 'Cafe', Str::ascii('Café'));
assert_equals('ascii: german', 'Duesseldorf', Str::ascii('Düsseldorf', 'de'));
assert_equals('ascii: russian', 'biologiceskom', Str::ascii('биологическом'));
assert_equals('ascii: empty', '', Str::ascii(''));
assert_equals('ascii: passthrough', 'hello', Str::ascii('hello'));
assert_equals('ascii: french', 'Un ete brulant', Str::ascii('Un été brûlant'));

// --- Str::isAscii() ---
assert_equals('isAscii: true', true, Str::isAscii('hello world'));
assert_equals('isAscii: false', false, Str::isAscii('héllo'));
assert_equals('isAscii: empty', true, Str::isAscii(''));
assert_equals('isAscii: numbers', true, Str::isAscii('12345'));

// --- Str::transliterate() ---
assert_equals('transliterate: basic', 'testing', Str::transliterate('testiñg'));
assert_equals('transliterate: empty', '', Str::transliterate(''));
assert_equals('transliterate: passthrough', 'hello', Str::transliterate('hello'));
assert_equals('transliterate: currency', 'EUR', Str::transliterate('€'));

// --- Str::slug() ---
assert_equals('slug: basic', 'hello-world', Str::slug('Hello World'));
assert_equals('slug: unicode', 'un-ete-brulant', Str::slug('Un été brûlant'));
assert_equals('slug: custom separator', 'hello_world', Str::slug('Hello World', '_'));
assert_equals('slug: german', 'hello-woerld', Str::slug('Hello Wörld', '-', 'de'));
assert_equals('slug: empty', '', Str::slug(''));

// --- Str (via Stringable) ---
assert_equals('stringable: ascii', 'Cafe', (string) Str::of('Café')->ascii());
assert_equals('stringable: slug', 'hello-world', (string) Str::of('Hello World')->slug());
assert_equals('stringable: isAscii', true, Str::of('hello')->isAscii());
assert_equals('stringable: isAscii false', false, Str::of('héllo')->isAscii());

// --- Edge cases ---
assert_equals('edge: null bytes', 'abc', Str::ascii("a\x00b\x00c"));
assert_equals('edge: tabs', 'a b c', Str::ascii("a\tb\tc"));
assert_equals('edge: newlines', 'a b c', Str::ascii("a\nb\nc"));

// --- Report ---
echo "\n";
echo "Results: {$passed} passed, {$failed} failed\n";

if ($failed > 0) {
    exit(1);
}

echo "All integration tests passed!\n";
