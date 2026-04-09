<?php

declare(strict_types=1);

use voku\helper\ASCII;

test('transliterate utf8', function () {
    expect(ASCII::to_transliterate('testiñg'))->toBe('testing');
});

test('transliterate ascii passthrough', function () {
    expect(ASCII::to_transliterate('testing'))->toBe('testing');
});

test('transliterate invalid char', function () {
    expect(ASCII::to_transliterate("tes\xE9ting"))->toBe('testing');
});

test('transliterate empty string', function () {
    expect(ASCII::to_transliterate(''))->toBeEmpty();
});

test('transliterate nul and non 7bit', function () {
    expect(ASCII::to_transliterate("a\x00ñ\x00c"))->toBe('anc');
});

test('transliterate nul', function () {
    expect(ASCII::to_transliterate("a\x00b\x00c"))->toBe('abc');
});

test('transliterate with intl strict mode', function () {
    if (! extension_loaded('intl')) {
        $this->markTestSkipped('intl extension not loaded');
    }

    $testString = file_get_contents(__DIR__ . '/fixtures/sample-unicode-chart.txt');
    $resultString = file_get_contents(__DIR__ . '/fixtures/sample-ascii-chart.txt');

    expect(ASCII::to_transliterate($testString, '?', true))->toBe($resultString);

    $testsStrict = [
        ' '                                        => ' ',
        ''                                         => '',
        'أبز'                                      => 'abz',
        "\xe2\x80\x99"                             => '\'',
        'Ɓtest'                                    => 'Btest',
        '  -ABC-中文空白-  '                           => '  -ABC-zhong wen kong bai-  ',
        "      - abc- \xc2\x87"                    => '      - abc- ++',
        'abc'                                      => 'abc',
        'deja vu'                                  => 'deja vu',
        'déjà vu'                                  => 'deja vu',
        'déjà σσς iıii'                            => 'deja sss iiii',
        "test\x80-\xBFöäü"                         => 'test-oau',
        'Internationalizaetion'                    => 'Internationalizaetion',
        "中 - &#20013; - %&? - \xc2\x80"            => 'zhong - &#20013; - %&? - EUR',
        'Un été brûlant sur la côte'               => 'Un ete brulant sur la cote',
        'Αυτή είναι μια δοκιμή'                    => 'Aute einai mia dokime',
        'أحبك'                                     => 'ahbk',
        'キャンパス'                                    => 'kyanpasu',
        'биологическом'                            => 'biologiceskom',
        '정, 병호'                                    => 'jeong, byeongho',
        'ますだ, よしひこ'                                => 'masuda, yoshihiko',
        'मोनिच'                                    => 'monica',
        'क्षȸ'                                     => 'kasadb',
        'أحبك 😀'                                   => 'ahbk ?',
        'ذرزسشصضطظعغػؼؽؾؿ 5.99€'                   => 'dhrzsshsdtz\'gh????? 5.99EUR',
        'ذرزسشصضطظعغػؼؽؾؿ £5.99'                   => 'dhrzsshsdtz\'gh????? PS5.99',
        '׆אבגדהוזחטיךכלםמן $5.99'                  => 'n\'bgdhwzhtykklmmn $5.99',
        '日一国会人年大十二本中長出三同 ¥5990'                    => 'ri yi guo hui ren nian da shi er ben zhong zhang chu san tong Y=5990',
        '5.99€ 日一国会人年大十 $5.99'                     => '5.99EUR ri yi guo hui ren nian da shi $5.99',
        'בגדה@ضطظعغػ.com'                          => 'bgdh@dtz\'gh?.com',
        '年大十@ضطظعغػ'                               => 'nian da shi@dtz\'gh?',
        'בגדה & 年大十'                               => 'bgdh & nian da shi',
        '国&ם at ضطظعغػ.הוז'                        => 'guo&m at dtz\'gh?.hwz',
        'my username is @בגדה'                     => 'my username is @bgdh',
        'The review gave 5* to ظعغػ'               => 'The review gave 5* to z\'gh?',
        'use 年大十@ضطظعغػ.הוז to get a 10% discount' => 'use nian da shi@dtz\'gh?.hwz to get a 10% discount',
        '日 = הט^2'                                 => 'ri = ht^2',
        'ךכלם 国会 غػؼؽ 9.81 m/s2'                   => 'kklm guo hui gh??? 9.81 m/s2',
        'The #会 comment at @בגדה = 10% of *&*'     => 'The #hui comment at @bgdh = 10% of *&*',
        '∀ i ∈ ℕ'                                  => '? i ? N',
        '👍 💩 😄 ❤ 👍 💩 😄 ❤أحبك'                      => '? ? ?  ? ? ? ahbk',
        'আমার সোনার বাংলা'                         => 'amara sonara banla',
        "a\xa0\xa1-öäü"                            => 'a-oau',
        "\xc3\xb1"                                 => 'n',
        "\xc3\x28"                                 => '(',
        "\x00"                                     => '',
        "a\xDFb"                                   => 'ab',
        "\xa0\xa1"                                 => '',
        "\xe2\x82\xa1"                             => 'CL',
        "\xe2\x28\xa1"                             => '(',
        "\xe2\x82\x28"                             => '(',
        "\xf0\x90\x8c\xbc"                         => '?',
        "\xf0\x28\x8c\xbc"                         => '(',
        "\xf0\x90\x28\xbc"                         => '(',
        "\xf0\x28\x8c\x28"                         => '((',
        "\xf8\xa1\xa1\xa1\xa1"                     => '',
        "\xfc\xa1\xa1\xa1\xa1\xa1"                 => '',
        "\xfc\xa1\xa1\xa1\xa1\xa1\xe2\x80\x82"     => ' ',
    ];

    foreach ($testsStrict as $before => $after) {
        expect(ASCII::to_transliterate($before, '?', true))->toBe($after, "tested: {$before}");
    }
});

test('transliterate non-strict mode', function () {
    $tests = [
        ''                      => '',
        ' '                     => ' ',
        '1a'                    => '1a',
        '2a'                    => '2a',
        '+1'                    => '+1',
        "      - abc- \xc2\x87" => '      - abc- ++',
        'abc'                   => 'abc',
        'أبز'                           => 'abz',
        "\xe2\x80\x99"                  => '\'',
        'Ɓtest'                         => 'Btest',
        '  -ABC-中文空白-  '                => '  -ABC-Zhong Wen Kong Bai -  ',
        'deja vu'                       => 'deja vu',
        'déjà vu '                      => 'deja vu ',
        'déjà σσς iıii'                 => 'deja sss iiii',
        "test\x80-\xBFöäü"              => 'test-oau',
        'Internationalizaetion'         => 'Internationalizaetion',
        "中 - &#20013; - %&? - \xc2\x80" => 'Zhong  - &#20013; - %&? - EUR',
        'Un été brûlant sur la côte'    => 'Un ete brulant sur la cote',
        'Αυτή είναι μια δοκιμή'         => 'Aute einai mia dokime',
        'أحبك'                          => 'aHbk',
        'キャンパス'                         => 'kiyanpasu',
        'биологическом'                 => 'biologicheskom',
        '정, 병호'                         => 'jeong, byeongho',
        'ますだ, よしひこ'                     => 'masuda, yoshihiko',
        'मोनिच'                         => 'monic',
        'क्षȸ'                          => 'kssdb',
        'أحبك 😀'                        => 'aHbk ?',
        '∀ i ∈ ℕ'                       => '? i ? N',
        '👍 💩 😄 ❤ 👍 💩 😄 ❤أحبك'           => '? ? ?  ? ? ? aHbk',
        '纳达尔绝境下大反击拒绝冷门逆转晋级中网四强'         => 'Na Da Er Jue Jing Xia Da Fan Ji Ju Jue Leng Men Ni Zhuan Jin Ji Zhong Wang Si Qiang ',
        'κόσμε'                         => 'kosme',
        '中'                             => 'Zhong ',
        '«foobar»'                      => '<<foobar>>',
        'বাংলা'                         => 'baaNlaa',
        "κόσμε\xc2\xa0"                => 'kosme ',
        "κόσμε\xa0\xa1-öäü"            => 'kosme-oau',
        'DÃ¼sseldorf'                  => 'DA1/4sseldorf',
        '<x%0Conxxx=1'                 => '<x%0Conxxx=1',
        'a'                            => 'a',
        '😃'                            => '?',
        '🐵 🙈 🙉 🙊 | ❤️ 💔 💌 💕 💞 💓 💗 💖 💘 💝 💟 💜 💛 💚 💙 | 🚾 🆒 🆓 🆕 🆖 🆗 🆙 🏧' => '? ? ? ? | ? ? ? ? ? ? ? ? ? ? ? ? ? ? ? | ? ? ? ? ? ? ? ?',
        "a\xa0\xa1-öäü"               => 'a-oau',
        "\xc3\xb1"                     => 'n',
        "\xc3\x28"                     => '(',
        "\x00"                         => '',
        "a\xDFb"                       => 'ab',
        "\xa0\xa1"                     => '',
        "\xe2\x82\xa1"                 => 'CL',
        "\xe2\x28\xa1"                 => '(',
        "\xe2\x82\x28"                 => '(',
        "\xf0\x90\x8c\xbc"             => '?',
        "\xf0\x28\x8c\xbc"             => '(',
        "\xf0\x90\x28\xbc"             => '(',
        "\xf0\x28\x8c\x28"             => '((',
        "\xf8\xa1\xa1\xa1\xa1"         => '',
        "\xfc\xa1\xa1\xa1\xa1\xa1"     => '',
        "\xfc\xa1\xa1\xa1\xa1\xa1\xe2\x80\x82" => ' ',
    ];

    foreach ($tests as $before => $after) {
        expect(ASCII::to_transliterate($before, '?', false))->toBe($after, "tested: {$before}");
    }
});

test('currency transliteration', function () {
    $tests = [
        '€' => 'EUR',
        '$' => '$',
        '₢' => 'Cr',
        '₣' => 'Fr.',
        '£' => 'PS',
        '₤' => 'L.',
        '₶' => 'L',
        'ℳ' => 'M',
        '₥' => 'mil',
        '₦' => 'N',
        '₧' => 'Pts',
        '₨' => 'Rs',
        '௹' => '?',
        '₩' => 'W',
        '₪' => 'NS',
        '₸' => 'T',
        '₫' => 'D',
        '֏' => '?',
        '₭' => 'K',
        '₼' => 'm',
        '₮' => 'T',
        '₯' => 'Dr',
        '₰' => 'Pf',
        '₷' => 'Sm',
        '₱' => 'P',
        'ރ' => 'r',
        '₲' => 'G',
        '₾' => 'l',
        '₳' => 'A',
        '₴' => 'UAH',
        '₽' => 'R',
        '₵' => 'C|',
        '₡' => 'CL',
        '¢' => 'C/',
        '¥' => 'Y=',
        '៛' => 'KR',
        '¤' => '$?',
        '฿' => 'Bh.',
        '؋' => '?',
    ];

    foreach ($tests as $before => $after) {
        expect(ASCII::to_transliterate($before, '?', true))->toBe($after, "tested: {$before}");
        expect(ASCII::to_transliterate($before, '?', false))->toBe($after, "tested: {$before}");
    }
});

test('keep invalid chars with null unknown strict', function () {
    expect(strtolower(ASCII::to_transliterate('أحبك 😀 ♥ 𐎁 𠾴 ᎈ ý', null, true)))
        ->toBe('ahbk 😀 ♥ 𐎁 𠾴 ᎈ y');
})->skipOnWindows();

test('keep invalid chars with null unknown non-strict', function () {
    expect(strtolower(ASCII::to_transliterate('أحبك 😀 ♥ 𐎁 𠾴 ᎈ ý', null, false)))
        ->toBe('ahbk 😀 ♥ 𐎁 𠾴 ᎈ y');
})->skipOnWindows();

test('special character transliteration', function (string $input, string $expected) {
    expect(ASCII::to_transliterate($input))->toBe($expected);
})->with([
    ['ⓐⓑⓒⓓⓔⓕⓖⓗⓘⓙⓚⓛⓜⓝⓞⓟⓠⓡⓢⓣⓤⓥⓦⓧⓨⓩ', 'abcdefghijklmnopqrstuvwxyz'],
    ['⓪①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮⑯⑰⑱⑲⑳', '01234567891011121314151617181920'],
    ['⓵⓶⓷⓸⓹⓺⓻⓼⓽⓾', '12345678910'],
    ['⓿⓫⓬⓭⓮⓯⓰⓱⓲⓳⓴', '011121314151617181920'],
    ['abcdefghijklmnopqrstuvwxyz', 'abcdefghijklmnopqrstuvwxyz'],
    ['0123456789', '0123456789'],
]);
