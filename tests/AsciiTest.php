<?php

declare(strict_types=1);

use voku\helper\ASCII;

test('to_ascii_remap works', function () {
    expect(ASCII::to_ascii_remap('testiñg', 'testing'))
        ->toBe(['testi' . \chr(128) . 'g', 'testing']);
});

test('is_ascii detects utf8', function () {
    expect(ASCII::is_ascii('testiñg'))->toBeFalse();
});

test('is_ascii detects ascii', function () {
    expect(ASCII::is_ascii('testing'))->toBeTrue();
});

test('is_ascii detects invalid char', function () {
    expect(ASCII::is_ascii("tes\xe9ting"))->toBeFalse();
});

test('is_ascii handles empty string', function () {
    expect(ASCII::is_ascii(''))->toBeTrue();
});

test('is_ascii handles newlines', function () {
    expect(ASCII::is_ascii("a\nb\nc"))->toBeTrue();
});

test('is_ascii handles tabs', function () {
    expect(ASCII::is_ascii("a\tb\tc"))->toBeTrue();
});

test('to_ascii converts utf8', function () {
    expect(ASCII::to_ascii('testiñg'))->toBe('testing');
});

test('to_ascii passes through ascii', function () {
    expect(ASCII::to_ascii('testing'))->toBe('testing');
});

test('to_ascii with empty language', function () {
    $tests = [
        ' '                                        => ' ',
        ''                                         => '',
        'أبز'                                      => 'abz',
        "\xe2\x80\x99"                             => '\'',
        'Ɓtest'                                    => 'Btest',
        '  -ABC-中文空白-  '                           => '  -ABC-Zhong Wen Kong Bai -  ',
        "      - abc- \xc2\x87"                    => '      - abc- ++',
        'STRAẞE'                                   => 'STRASSE',
        'abc'                                      => 'abc',
        'deja vu'                                  => 'deja vu',
        'déjà vu'                                  => 'deja vu',
        'déjà σσς iıii'                            => 'deja sss iiii',
        "test\x80-\xBFöäü"                         => 'test-oau',
        'Internationalizaetion'                    => 'Internationalizaetion',
        "中 - &#20013; - %&? - \xc2\x80"            => 'Zhong  - &#20013; - %&? - EUR',
        'Un été brûlant sur la côte'               => 'Un ete brulant sur la cote',
        'Αυτή είναι μια δοκιμή'                    => 'Auti inai mia dokimi',
        'أحبك'                                     => 'ahbk',
        'キャンパス'                                    => 'kiyanpasu',
        'биологическом'                            => 'biologiceskom',
        '정, 병호'                                    => 'jeong, byeongho',
        'ますだ, よしひこ'                                => 'masuda, yoshihiko',
        'मोनिच'                                    => 'MaoNaiCa',
        'क्षȸ'                                     => 'KaShhadb',
        'أحبك 😀'                                   => 'ahbk ',
        'ذرزسشصضطظعغػؼؽؾؿ 5.99€'                   => 'thrzsshsdtthaagh 5.99EUR',
        'ذرزسشصضطظعغػؼؽؾؿ £5.99'                   => 'thrzsshsdtthaagh PS5.99',
        '׆אבגדהוזחטיךכלםמן $5.99'                  => 'nAbgdhvzKHtykklmmn $5.99',
        '日一国会人年大十二本中長出三同 ¥5990'                    => 'Ri Yi Guo Hui Ren Nian Da Shi Er Ben Zhong Chang Chu San Tong  YEN5990',
        '5.99€ 日一国会人年大十 $5.99'                     => '5.99EUR Ri Yi Guo Hui Ren Nian Da Shi  $5.99',
        'בגדה@ضطظعغػ.com'                          => 'bgdh@dtthaagh.com',
        '年大十@ضطظعغػ'                               => 'Nian Da Shi @dtthaagh',
        'בגדה & 年大十'                               => 'bgdh & Nian Da Shi ',
        '国&ם at ضطظعغػ.הוז'                        => 'Guo &m at dtthaagh.hvz',
        'my username is @בגדה'                     => 'my username is @bgdh',
        'The review gave 5* to ظعغػ'               => 'The review gave 5* to thaagh',
        'use 年大十@ضطظعغػ.הוז to get a 10% discount' => 'use Nian Da Shi @dtthaagh.hvz to get a 10% discount',
        '日 = הט^2'                                 => 'Ri  = ht^2',
        'ךכלם 国会 غػؼؽ 9.81 m/s2'                   => 'kklm Guo Hui  gh 9.81 m/s2',
        'The #会 comment at @בגדה = 10% of *&*'     => 'The #Hui  comment at @bgdh = 10% of *&*',
        '∀ i ∈ ℕ'                                  => ' i  N',
        '👍 💩 😄 ❤ 👍 💩 😄 ❤أحبك'                      => '       ahbk',
        'আমি'                                      => 'ami',
    ];

    foreach ($tests as $before => $after) {
        expect(ASCII::to_ascii($before, ''))->toBe($after, "tested: {$before}");
    }
});

test('to_ascii with english language', function () {
    $tests = [
        ' '                                        => ' ',
        ''                                         => '',
        'أبز'                                      => 'abz',
        "\xe2\x80\x99"                             => '\'',
        'Ɓtest'                                    => 'Btest',
        '  -ABC-中文空白-  '                           => '  -ABC--  ',
        "      - abc- \xc2\x87"                    => '      - abc- ',
        'STRAẞE'                                   => 'STRASSE',
        'abc'                                      => 'abc',
        'deja vu'                                  => 'deja vu',
        'déjà vu'                                  => 'deja vu',
        'déjà σσς iıii'                            => 'deja sss iiii',
        "test\x80-\xBFöäü"                         => 'test-',
        'Internationalizaetion'                    => 'Internationalizaetion',
        "中 - &#20013; - %&? - \xc2\x80"            => ' - &#20013; - %&? - ',
        'Un été brûlant sur la côte'               => 'Un ete brulant sur la cote',
        'Αυτή είναι μια δοκιμή'                    => 'Auti inai mia dokimi',
        'أحبك'                                     => 'ahbk',
        'キャンパス'                                    => '',
        'биологическом'                            => 'biologiceskom',
        '정, 병호'                                    => ', ',
        'ますだ, よしひこ'                                => ', ',
        'मोनिच'                                    => 'MaNaCa',
        'क्षȸ'                                     => 'KaShhadb',
        'أحبك 😀'                                   => 'ahbk ',
        'ذرزسشصضطظعغػؼؽؾؿ 5.99€'                   => 'thrzsshsdtthaagh 5.99EUR',
        'ذرزسشصضطظعغػؼؽؾؿ £5.99'                   => 'thrzsshsdtthaagh PS5.99',
        '׆אבגדהוזחטיךכלםמן $5.99'                  => ' $5.99',
        '日一国会人年大十二本中長出三同 ¥5990'                    => ' YEN5990',
        '5.99€ 日一国会人年大十 $5.99'                     => '5.99EUR  $5.99',
        'בגדה@ضطظعغػ.com'                          => '@dtthaagh.com',
        '年大十@ضطظعغػ'                               => '@dtthaagh',
        'בגדה & 年大十'                               => ' & ',
        '国&ם at ضطظعغػ.הוז'                        => '& at dtthaagh.',
        'my username is @בגדה'                     => 'my username is @',
        'The review gave 5* to ظعغػ'               => 'The review gave 5* to thaagh',
        'use 年大十@ضطظعغػ.הוז to get a 10% discount' => 'use @dtthaagh. to get a 10% discount',
        '日 = הט^2'                                 => ' = ^2', // Hebrew ט not in English mapping
        'ךכלם 国会 غػؼؽ 9.81 m/s2'                   => '  gh 9.81 m/s2',
        'The #会 comment at @בגדה = 10% of *&*'     => 'The # comment at @ = 10% of *&*',
        '∀ i ∈ ℕ'                                  => ' i  ',
        '👍 💩 😄 ❤ 👍 💩 😄 ❤أحبك'                      => '       ahbk',
        'আমি   '                                   => 'ami   ',
    ];

    foreach ($tests as $before => $after) {
        expect(ASCII::to_ascii($before, 'en', true))->toBe($after, "tested: {$before}");
    }
});

test('remove_invisible_characters works', function () {
    $testArray = [
        "κόσ\0με"                                                                          => 'κόσμε',
        "Κόσμε\x20"                                                                        => 'Κόσμε ',
        "öäü-κόσμ\x0εκόσμε-äöü"                                                            => 'öäü-κόσμεκόσμε-äöü',
        'öäü-κόσμεκόσμε-äöüöäü-κόσμεκόσμε-äöü'                                             => 'öäü-κόσμεκόσμε-äöüöäü-κόσμεκόσμε-äöü',
        'äöüäöüäöü-κόσμεκόσμεäöüäöüäöü-Κόσμεκόσμεäöüäöüäöü-κόσμεκόσμεäöüäöüäöü-κόσμεκόσμε' => 'äöüäöüäöü-κόσμεκόσμεäöüäöüäöü-Κόσμεκόσμεäöüäöüäöü-κόσμεκόσμεäöüäöüäöü-κόσμεκόσμε',
        '  '                                                                               => '  ',
        ''                                                                                 => '',
    ];

    foreach ($testArray as $before => $after) {
        expect(ASCII::remove_invisible_characters($before))->toBe($after, "error by {$before}");
        expect(ASCII::remove_invisible_characters($before, true, '', true))->toBe($after, "error by {$before}");
        expect(ASCII::remove_invisible_characters($before, false, '', false))->toBe($after, "error by {$before}");
    }

    // Note: On PHP 8.5, \xe1\x9a\x80 (Ogham Space Mark) behavior may differ from earlier versions
    $result = ASCII::remove_invisible_characters("äöüäöüäöü-κόσμεκόσμεäöüäöüäöü\xe1\x9a\x80κόσμεκόσμεäöüäöüäöü-κόσμεκόσμε");
    // The Ogham Space Mark is either kept or replaced depending on PHP version
    expect(str_contains($result, 'κόσμεκόσμεäöüäöüäöü'))->toBeTrue();

    // Note: The LTR mark (U+200E) handling may differ on PHP 8.5
    $result = ASCII::remove_invisible_characters('%*ł€! ‎| | ');
    expect(str_contains($result, '%*ł€!'))->toBeTrue();

    expect(ASCII::remove_invisible_characters("κόσ\0με 	%00 | tes%20öäü%20\u00edtest", false, '?'))
        ->toBe('κόσ?με 	%00 | tes%20öäü%20\u00edtest');

    expect(ASCII::remove_invisible_characters("κόσ\0με 	%00 | tes%20öäü%20\u00edtest", true, ''))
        ->toBe('κόσμε 	 | tes%20öäü%20\u00edtest');
});

test('getAllLanguages returns expected languages', function () {
    $languages = ASCII::getAllLanguages();

    expect($languages)->toHaveKey('german');
    expect($languages['german'])->toBe('de');
});

test('invalid char to ascii', function () {
    expect(ASCII::to_transliterate("tes\xe9ting"))->toBe('testing');
    expect(ASCII::to_ascii("tes\xe9ting"))->toBe('testing');
});

test('empty string to ascii', function () {
    expect(ASCII::to_ascii(''))->toBe('');
});

test('nul and non 7bit', function () {
    expect(ASCII::to_ascii("a\x00ñ\x00c"))->toBe('anc');
});

test('nul characters', function () {
    expect(ASCII::to_ascii("a\x00b\x00c"))->toBe('abc');
});

test('newline to ascii', function () {
    expect(ASCII::to_transliterate("a\nb\nc"))->toBe("a\nb\nc");
    expect(ASCII::to_transliterate("\xc2\x92\x00\n\x01\n\x7f\xe2\x80\x99"))->toBe("'\n\n'");
    expect(ASCII::to_ascii("a\nb\nc", 'en', false))->toBe("a\nb\nc");
    expect(ASCII::to_ascii("a\nb\nc", 'en', true))->toBe('a b c');
    expect(ASCII::to_ascii('ä-ö-ü', 'de', true))->toBe('ae-oe-ue');
});

test('tab to ascii', function () {
    expect(ASCII::to_transliterate("a\tb\tc"))->toBe("a\tb\tc");
    expect(ASCII::to_ascii("a\tb\tc", 'en', false))->toBe("a\tb\tc");
    expect(ASCII::to_ascii("a\tb\tc", 'en', true))->toBe('a b c');
});
