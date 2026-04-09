<?php

declare(strict_types=1);

use voku\helper\ASCII;

test('charsArrayWithMultiLanguageValues', function () {
    $array = ASCII::charsArrayWithMultiLanguageValues();

    expect($array['b'])->toBe([
        0 => 'б',
        1 => 'բ',
        2 => 'ဗ',
        3 => 'ბ',
        4 => 'ب',
        5 => 'ব',
    ]);

    // check static cache
    $array = ASCII::charsArrayWithMultiLanguageValues();
    expect($array['b'])->toHaveCount(6);

    $array = ASCII::charsArrayWithMultiLanguageValues(true);
    expect($array['b'])->toContain('б');
    expect($array['&'])->toBe(['&', '﹠', '＆']);
    expect($array[' Euro '])->toBe(['€']);
});

test('charsArrayWithOneLanguage', function () {
    $array = ASCII::charsArrayWithOneLanguage('abcde');
    expect($array['replace'])->toBe([]);
    expect($array['orig'])->toBe([]);

    $array = ASCII::charsArrayWithOneLanguage('####');
    expect($array['replace'])->toBe([]);
    expect($array['orig'])->toBe([]);

    $array = ASCII::charsArrayWithOneLanguage('de_at');
    expect($array['replace'])->toContain('Ae');
    expect($array['replace'])->toContain('ss');
    expect($array['replace'])->not->toContain('sz');
    expect($array['orig'])->toContain('ß');

    $array = ASCII::charsArrayWithOneLanguage('de-CH');
    expect($array['replace'])->toContain('Ae');
    expect($array['replace'])->toContain('ss');
    expect($array['replace'])->not->toContain('sz');
    expect($array['orig'])->toContain('ß');

    $array = ASCII::charsArrayWithOneLanguage('de');
    expect($array['replace'])->toContain('Ae');
    expect($array['replace'])->not->toContain('yo');

    $array = ASCII::charsArrayWithOneLanguage('de_DE');
    expect($array['replace'])->toContain('Ae');
    expect($array['replace'])->not->toContain('yo');

    $array = ASCII::charsArrayWithOneLanguage('de-DE');
    expect($array['replace'])->toContain('Ae');
    expect($array['replace'])->not->toContain('yo');

    $array = ASCII::charsArrayWithOneLanguage('ru');
    expect($array['replace'])->not->toContain('Ae');
    expect($array['replace'])->toContain('yo');
    $tmpKey = array_search('yo', $array['replace'], true);
    expect($array['orig'][$tmpKey])->toBe('ё');

    $array = ASCII::charsArrayWithOneLanguage('de', true);
    expect($array['replace'])->toContain('Ae');
    expect($array['replace'])->not->toContain('yo');
    expect($array['replace'])->toContain(' und ');
    expect($array['replace'])->not->toContain(' и ');
    $tmpKey = array_search(' und ', $array['replace'], true);
    expect($array['orig'][$tmpKey])->toBe('&');

    $array = ASCII::charsArrayWithOneLanguage('ru', true);
    expect($array['replace'])->toContain('yo');
    expect($array['replace'])->not->toContain('Ae');
    expect($array['replace'])->toContain(' i ');
    expect($array['replace'])->not->toContain(' und ');
    $tmpKey = array_search(' i ', $array['replace'], true);
    expect($array['orig'][$tmpKey])->toBe('&');
});

test('charsArrayWithSingleLanguageValues', function () {
    $array = ASCII::charsArrayWithSingleLanguageValues();
    expect($array['replace'])->toContain('hnaik');
    expect($array['replace'])->toContain('yo');
    $tmpKey = array_search('hnaik', $array['replace'], true);
    expect($array['orig'][$tmpKey])->toBe('၌');

    $array = ASCII::charsArrayWithSingleLanguageValues(true);
    expect($array['replace'])->toContain('hnaik');
    expect($array['replace'])->toContain('yo');
    expect($array['replace'])->toContain(' pound ');
    $tmpKey = array_search(' pound ', $array['replace'], true);
    expect($array['orig'][$tmpKey])->toBe('£');
});

test('charsArray', function () {
    $array = ASCII::charsArray();
    expect($array['ru']['б'])->toBe('b');

    $arrayMore = ASCII::charsArray(true);
    expect($arrayMore['ru']['б'])->toBe('b');
    expect($arrayMore['ru']['&'])->toBe(' i ');
    expect(count($arrayMore))->toBeGreaterThan(count($array));
});

test('to_filename without transliterate', function () {
    $tests = [
        "test-\xe9\x00\x0é大般若經.txt"      => 'test-.txt',
        'test-大般若經.txt'                  => 'test-.txt',
        'фото.jpg'                       => '.jpg',
        'Фото.jpg'                       => '.jpg',
        'öäü  - test'                    => 'test',
        'שדגשדג.png'                     => '.png',
        '—©®±àáâãäåæÒÓÔÕÖ¼½¾§µçðþú–.jpg' => '.jpg',
        '000—©—©.txt'                    => '000.txt',
        ' '                              => '',
    ];

    foreach ($tests as $before => $after) {
        expect(ASCII::to_filename($before, false))->toBe($after);
    }
});

test('to_filename with transliterate', function () {
    $tests = [
        "test-\xe9\x00\x0é大般若經.txt"      => 'test-eDa-Ban-Ruo-Jing-.txt',
        'test-大般若經.txt'                  => 'test-Da-Ban-Ruo-Jing-.txt',
        'фото.jpg'                       => 'foto.jpg',
        'Фото.jpg'                       => 'Foto.jpg',
        'öäü  - test'                    => 'oau-test',
        'שדגשדג.png'                     => 'SHdgSHdg.png',
        '—©®±àáâãäåæÒÓÔÕÖ¼½¾§µçðþú–.jpg' => 'cr-aaaaaaaeOOOOO141234SSucdthu-.jpg',
        '000—©—©.txt'                    => '000-c-c.txt',
        ' '                              => '',
    ];

    foreach ($tests as $before => $after) {
        expect(ASCII::to_filename($before, true))->toBe($after);
    }
});

test('to_slugify', function (
    string $expected,
    string $str,
    string $replacement = '-',
    string $lang = 'en',
    bool $use_str_to_lower = true,
    bool $replace_extra_symbols = true,
    bool $use_transliterate = false,
) {
    $result = ASCII::to_slugify(
        $str,
        $replacement,
        $lang,
        ['foooooo' => 'bar'],
        $replace_extra_symbols,
        $use_str_to_lower,
        $use_transliterate
    );

    expect($result)->toBe($expected, "tested: {$str}");
})->with([
    ['', ''],
    ['', ' '],
    ['bar', 'foooooo'],
    ['foo-bar', ' foo  bar '],
    ['foo-bar', 'foo -.-"-...bar'],
    ['another-and-foo-bar', 'another..& foo -.-"-...bar'],
    ['foo-dbar', " Foo d'Bar "],
    ['a-string-with-dashes', 'A string-with-dashes'],
    ['user-at-host', 'user@host'],
    ['using-strings-like-foo-bar', 'Using strings like fòô bàř'],
    ['numbers-1234', 'numbers 1234'],
    ['perevirka-ryadka', 'перевірка рядка'],
    ['bukvar-s-bukvoi-y', 'букварь с буквой ы'],
    ['podieexal-k-podieezdu-moego-doma', 'подъехал к подъезду моего дома'],
    ['foo:bar:baz', 'Foo bar baz', ':'],
    ['a_string_with_underscores', 'A_string with_underscores', '_'],
    ['a_string_with_dashes', 'A string-with-dashes', '_'],
    ['one_euro_or_a_dollar', 'one € or a $', '_'],
    ['sometext', 'some text', ''],
    ['a\string\with\dashes', 'A string-with-dashes', '\\'],
    ['an_odd_string', '--   An odd__   string-_', '_'],
    ['Stoynostta-tryabva-da-bade-lazha', 'Стойността трябва да бъде лъжа', '-', 'bg', false],
    ['Dieser-Wert-sollte-groesser-oder-gleich', 'Dieser Wert sollte größer oder gleich', '-', 'de', false],
    ['Dieser-Wert-sollte-groesser-oder-gleich', 'Dieser Wert sollte größer oder gleich', '-', 'de_AT', false],
    ['Auti-i-timi-prepi-na-inai-psefdis', 'Αυτή η τιμή πρέπει να είναι ψευδής', '-', 'el', false],
    ['Gai-Bian-Liang-De-Zhi-Ying-Wei', '该变量的值应为', '-', ASCII::CHINESE_LANGUAGE_CODE, false, false, true],
    ['Gai-Bian-Shu-De-Zhi-Ying-Wei', '該變數的值應為', '-', 'zh_TW', false, false, true],
    ['Gai-Bian-Liang-De-Zhi-Ying-Wei', '该变量的值应为', '-', ASCII::CHINESE_LANGUAGE_CODE, false, true, true],
    ['Gai-Bian-Shu-De-Zhi-Ying-Wei', '該變數的值應為', '-', 'zh_TW', false, true, true],
    ['ami-banglaz-ktha-bli-ngkx', 'আমি বাংলায় কথা বলি ... ঙ্ক্ষ', '-', ASCII::BENGALI_LANGUAGE_CODE, true, true, true],
]);

test('to_ascii with providers', function (
    string $expected,
    string $str,
    string $language = 'en',
    bool $remove_unsupported_chars = true,
    bool $replace_extra_symbols = false,
    bool $use_transliterate = false,
) {
    $result = ASCII::to_ascii(
        $str,
        $language,
        $remove_unsupported_chars,
        $replace_extra_symbols,
        $use_transliterate
    );

    expect($result)->toBe($expected, "tested: {$str}");
})->with([
    ['      ! " # $ % & \' ( ) * + , @ `', " \v \t \n" . ' ! " # $ % & \' ( ) * + , @ `'],
    ['foo bar |  | ~', 'fòô bàř | 🅉 | ~'],
    [' TEST 3C', ' ŤÉŚŢ 3°C'],
    [' TEST 3 Celsius ', ' ŤÉŚŢ 3°C', ASCII::ENGLISH_LANGUAGE_CODE, true, true],
    ['f = z = 3', 'φ = ź = 3'],
    ['perevirka', 'перевірка'],
    ['lysaya gora', 'лысая гора'],
    ['I  ', 'I ♥ 字'],
    ['I  ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE],
    ['I ♥ 字', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, false],
    ['I  love  字', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, false, true],
    ['I ♥ 字', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, false, false],
    ['I  love  字', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, false, true, false],
    ['I  love  Zi ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, false, true, true],
    ['I ♥ 字', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, false, false, false],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, false, false, true],
    ['I  ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, true],
    ['I  love  ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, true, true],
    ['I  ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, true, false],
    ['I  love  ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, true, true, false],
    ['I  love  Zi ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, true, true, true],
    ['I  ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, true, false, false],
    ['I  Zi ', 'I ♥ 字', ASCII::ENGLISH_LANGUAGE_CODE, true, false, true],
    ['I  Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, false],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, false, true],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, false, false],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, false, true, false],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, false, true, true],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, false, false, false],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, false, false, true],
    ['I  Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, true],
    ['I  Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, true, true],
    ['I  Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, true, false],
    ['I  Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, true, true, false],
    ['I  Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, true, true, true],
    ['I  Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, true, false, false],
    ['I  Zi ', 'I ♥ 字', ASCII::CHINESE_LANGUAGE_CODE, true, false, true],
    ['I  ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE],
    ['I ♥ 字', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, false],
    ['I  liebe  字', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, false, true],
    ['I ♥ 字', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, false, false],
    ['I  liebe  字', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, false, true, false],
    ['I  liebe  Zi ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, false, true, true],
    ['I ♥ 字', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, false, false, false],
    ['I ♥ Zi ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, false, false, true],
    ['I  ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, true],
    ['I  liebe  ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, true, true],
    ['I  ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, true, false],
    ['I  liebe  ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, true, true, false],
    ['I  liebe  Zi ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, true, true, true],
    ['I  ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, true, false, false],
    ['I  Zi ', 'I ♥ 字', ASCII::GERMAN_LANGUAGE_CODE, true, false, true],
    ['Uzbek', 'Ўзбек', ASCII::UZBEK_LANGUAGE_CODE],
    ['Turkmen', 'Түркмен', ASCII::TURKMEN_LANGUAGE_CODE],
    ['aithy', 'ไทย', ASCII::THAI_LANGUAGE_CODE],
    ['pSto', 'پښتو', ASCII::PASHTO_LANGUAGE_CODE],
    ['odd\'iaa', 'ଓଡ଼ିଆ', ASCII::ORIYA_LANGUAGE_CODE],
    ['Mongol xel', 'Монгол хэл', ASCII::MONGOLIAN_LANGUAGE_CODE],
    ['hangugeo', '한국어', ASCII::KOREAN_LANGUAGE_CODE],
    ['Kyrgyzca', 'Кыргызча', ASCII::KIRGHIZ_LANGUAGE_CODE],
    ['Hayeren', 'Հայերեն', ASCII::ARMENIAN_LANGUAGE_CODE],
    ['bangla', 'বাংলা', ASCII::BENGALI_LANGUAGE_CODE],
    ['belaruskaya', 'беларуская', ASCII::BELARUSIAN_LANGUAGE_CODE],
    ['\'amaarenyaa', 'አማርኛ', ASCII::AMHARIC_LANGUAGE_CODE],
    ['Ri Ben Yu  (nihongo)', '日本語 (にほんご)', ASCII::JAPANESE_LANGUAGE_CODE],
    ['een oplossing - aou', 'één oplossing - äöü', ASCII::DUTCH_LANGUAGE_CODE],
    ['Universita', 'Università', ASCII::ITALIAN_LANGUAGE_CODE],
    ['Makedonska azbuka', 'Македонска азбука', ASCII::MACEDONIAN_LANGUAGE_CODE],
    ['Eu nao falo portugues.', 'Eu não falo português.', ASCII::PORTUGUESE_LANGUAGE_CODE],
    ['lysaya gora', 'лысая гора', ASCII::RUSSIAN_LANGUAGE_CODE],
    ['lysaia gora', 'лысая гора', ASCII::RUSSIAN_PASSPORT_2013_LANGUAGE_CODE],
    ['ly\'saya gora', 'лысая гора', ASCII::RUSSIAN_GOST_2000_B_LANGUAGE_CODE],
    ['shhuka', 'щука'],
    ['shhuka', 'щука', ASCII::EXTRA_LATIN_CHARS_LANGUAGE_CODE],
    ['Elliniko alfavito', 'Ελληνικό αλφάβητο', ASCII::GREEK_LANGUAGE_CODE],
    ['Athina', 'Αθήνα', ASCII::GREEK_LANGUAGE_CODE],
    [
        'As prostheso ki eghw oti ta teleftaia dyo khronia pu ekana Xristoughenna stin Thessaloniki ta mona paidia',
        'Ας προσθέσω κι εγώ ότι τα τελευταία δύο χρόνια που έκανα Χριστούγεννα στην Θεσσαλονίκη τα μόνα παιδιά',
        ASCII::GREEK_LANGUAGE_CODE,
    ],
    [
        'pu irthan na mas pun ta kallanta itan prosfighopula, koritsia sinithos, apo tin Georghia.',
        'που ήρθαν να μας πουν τα κάλλαντα ήταν προσφυγόπουλα, κορίτσια συνήθως, από την Γεωργία.',
        ASCII::GREEK_LANGUAGE_CODE,
    ],
    ['Athhna', 'Αθήνα', ASCII::GREEKLISH_LANGUAGE_CODE],
    [
        'As prosthesw ki egw oti ta teleutaia dyo xronia pou ekana Xristougenna sthn Thessalonikh ta mona paidia',
        'Ας προσθέσω κι εγώ ότι τα τελευταία δύο χρόνια που έκανα Χριστούγεννα στην Θεσσαλονίκη τα μόνα παιδιά',
        ASCII::GREEKLISH_LANGUAGE_CODE,
    ],
    [
        'pou hrthan na mas poun ta kallanta htan prosfygopoula, koritsia synhthws, apo thn Gewrgia.',
        'που ήρθαν να μας πουν τα κάλλαντα ήταν προσφυγόπουλα, κορίτσια συνήθως, από την Γεωργία.',
        ASCII::GREEKLISH_LANGUAGE_CODE,
    ],
    ['uThaHaRaNae', 'उदाहरण', ASCII::HINDI_LANGUAGE_CODE],
    ['IGAR', 'IGÅR', ASCII::SWEDISH_LANGUAGE_CODE],
    ['Gronland', 'Grø̈nland', ASCII::SWEDISH_LANGUAGE_CODE],
    ['gorusmek', 'görüşmek', ASCII::TURKISH_LANGUAGE_CODE],
    ['primer', 'пример', ASCII::BULGARIAN_LANGUAGE_CODE],
    ['vasarlo', 'vásárló', ASCII::HUNGARIAN_LANGUAGE_CODE],
    ['ttyanongyath', 'တတျနိုငျသ', ASCII::MYANMAR_LANGUAGE_CODE],
    ['sveucilist', 'sveučilišt', ASCII::CROATIAN_LANGUAGE_CODE],
    ['paivakoti', 'päiväkoti', ASCII::FINNISH_LANGUAGE_CODE],
    ['bavshvebi', 'ბავშვები', ASCII::GEORGIAN_LANGUAGE_CODE],
    ['schuka', 'щука', ASCII::RUSSIAN_LANGUAGE_CODE],
    ['shchuka', 'щука', ASCII::RUSSIAN_PASSPORT_2013_LANGUAGE_CODE],
    ['shhuka', 'щука', ASCII::RUSSIAN_GOST_2000_B_LANGUAGE_CODE],
    ['dity', 'діти', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['horokh', 'горох', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['shchastya', 'щастя', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['Chernivtsi', 'Чернівці', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['shtany', 'штани', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['zhovten', 'жовтень', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['rozghon', 'розгон', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['mol', 'моль', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['Yevropeyets', 'Європеєць', ASCII::UKRAINIAN_LANGUAGE_CODE],
    ['universitet', 'университет', ASCII::KAZAKH_LANGUAGE_CODE],
    ['univerzitni', 'univerzitní', ASCII::CZECH_LANGUAGE_CODE],
    ['besoegende', 'besøgende', ASCII::DANISH_LANGUAGE_CODE],
    ['Odwiedzajacy', 'Odwiedzający', ASCII::POLISH_LANGUAGE_CODE],
    ['gradinita', 'grădiniță', ASCII::ROMANIAN_LANGUAGE_CODE],
    ['infangxardeno', 'infanĝardeno', ASCII::ESPERANTO_LANGUAGE_CODE],
    ['Ulikool', 'Ülikool', ASCII::ESTONIAN_LANGUAGE_CODE],
    ['bernudarzs', 'bērnudārzs', ASCII::LATVIAN_LANGUAGE_CODE],
    ['vaiku darzelis', 'vaikų darželis', ASCII::LITHUANIAN_LANGUAGE_CODE],
    ['kundestoette', 'kundestøtte', ASCII::NORWEGIAN_LANGUAGE_CODE],
    ['truong hoc', 'trường học', ASCII::VIETNAMESE_LANGUAGE_CODE],
    ['gamaa', 'جامعة', ASCII::ARABIC_LANGUAGE_CODE],
    ['danshgah', 'دانشگاه', ASCII::PERSIAN_LANGUAGE_CODE],
    ['univerzitet', 'универзитет', ASCII::SERBIAN_LANGUAGE_CODE],
    ['univerzitet', 'универзитет', ASCII::SERBIAN_CYRILLIC_LANGUAGE_CODE],
    ['univerzitet', 'универзитет', ASCII::SERBIAN_LATIN_LANGUAGE_CODE],
    ['musteri', 'müştəri', ASCII::AZERBAIJANI_LANGUAGE_CODE],
    ['zakaznik', 'zákazník', ASCII::SLOVAK_LANGUAGE_CODE],
    ['francais', 'français', ASCII::FRENCH_LANGUAGE_CODE],
    ['bangla', 'বাংলা', ASCII::BENGALI_LANGUAGE_CODE],
    ['user@host', 'user@host'],
    ['', '漢字'],
    ['xin chao the gioi', 'xin chào thế giới'],
    ['XIN CHAO THE GIOI', 'XIN CHÀO THẾ GIỚI'],
    ['dam phat chet luon', 'đấm phát chết luôn'],
    [' ', ' '],
    ['           ', '           '],
    [' ', ' '],
    [' ', ' '],
    [' ', '　'],
    ['', '𐍉'],
    ['𐍉', '𐍉', ASCII::ENGLISH_LANGUAGE_CODE, false],
    ['aouAOUss', 'äöüÄÖÜß'],
    ['aeoeueAeOeUess', 'äöüÄÖÜß', 'de_DE'],
    ['aeoeueAeOeUess ', 'äöüÄÖÜß ', 'de_DE'],
    ['aeoeueAeOeUess ', 'äöüÄÖÜß ®', 'de_DE'],
    ['aeoeueAeOeUess ®', 'äöüÄÖÜß ®', 'de_DE', false],
    ['aeoeueAeOeUess  (r) ', 'äöüÄÖÜß ®', 'de_DE', false, true],
    ['aeoeueAeOeUess  (r) ', 'äöüÄÖÜß ®', 'de_DE', true, true],
    ['aeoeueAeOeUess  (r) ', 'äöüÄÖÜß ®', 'de_DE', true, true, true],
    ['aeoeueAeOeUess  (r) ', 'äöüÄÖÜß ®', 'de_DE', false, true, true],
    ['aeoeueAeOeUess (r)', 'äöüÄÖÜß ®', 'de_DE', true, false, true],
    ['aeoeueAeOeUess (r)', 'äöüÄÖÜß ®', 'de_DE', false, false, true],
    ['aeoeueAeOeUess', 'äöüÄÖÜß', ASCII::GERMAN_LANGUAGE_CODE],
    ['aeoeueAeOeUess', 'äöüÄÖÜß', ASCII::GERMAN_AUSTRIAN_LANGUAGE_CODE],
    ['aeoeueAeOeUess', 'äöüÄÖÜß', ASCII::GERMAN_SWITZERLAND_LANGUAGE_CODE],
    ['aouAOUss', 'äöüÄÖÜß', ASCII::FRENCH_LANGUAGE_CODE],
    ['aouAOUss', 'äöüÄÖÜß', ASCII::FRENCH_AUSTRIAN_LANGUAGE_CODE],
    ['aouAOUss', 'äöüÄÖÜß', ASCII::FRENCH_SWITZERLAND_LANGUAGE_CODE],
    ['h H sht Sht a A ia yo', 'х Х щ Щ ъ Ъ иа йо', 'bg'],
    ['a-', "a\xa0\xa1-öäü"],
    ['n', "\xc3\xb1"],
    ['(', "\xc3\x28"],
    ['', "\x00"],
    ['ab', "a\xDFb"],
    ['', "\xa0\xa1"],
    ['CL', "\xe2\x82\xa1"],
    ['(', "\xe2\x28\xa1"],
    ['(', "\xe2\x82\x28"],
    ['', "\xf0\x90\x8c\xbc"],
    ['(', "\xf0\x28\x8c\xbc"],
    ['(', "\xf0\x90\x28\xbc"],
    ['((', "\xf0\x28\x8c\x28"],
    ['', "\xf8\xa1\xa1\xa1\xa1"],
    ['', "\xfc\xa1\xa1\xa1\xa1\xa1"],
    ['', "\xfc\xa1\xa1\xa1\xa1\xa1\xe2\x80\x82"],
]);

test('is_ascii with char arrays', function () {
    $a = ASCII::charsArrayWithMultiLanguageValues(false);
    foreach ($a as $k => $v) {
        expect(ASCII::is_ascii((string) $k))->toBeTrue("tested: {$k}");
    }

    $a = ASCII::charsArrayWithMultiLanguageValues(true);
    $skip = ['∑', '∆', '∞', '♥'];
    foreach ($a as $k => $v) {
        if (in_array($k, $skip, true)) {
            continue;
        }
        expect(ASCII::is_ascii((string) $k))->toBeTrue("tested: {$k}");
    }
});

test('clean parameter combinations', function () {
    $dirtyTestString = "\xEF\xBB\xBF\xe2\x80\x9eAbcdef\xc2\xa0\x20\xe2\x80\xa6\xe2\x80\x9c \xe2\x80\x94 \xF0\x9F\x98\x83";

    // clean(normalize_whitespace, keepNonBreakingSpace, normalize_msword, remove_invisible)
    // Default: all true except keepNonBreakingSpace
    $cleaned = ASCII::clean($dirtyTestString);
    expect($cleaned)->toBe("\xEF\xBB\xBF" . '"Abcdef  ..." - ' . "\xF0\x9F\x98\x83");

    // When msword+invisible are off, the special chars stay
    $cleaned = ASCII::clean($dirtyTestString, false, false, false, false);
    expect($cleaned)->toBe($dirtyTestString); // nothing changed

    // When msword is on, curly quotes + em dash + ellipsis get normalized
    $cleaned = ASCII::clean($dirtyTestString, false, false, true, false);
    expect($cleaned)->toBe("\xEF\xBB\xBF" . '"Abcdef' . "\xc2\xa0" . ' ..." - ' . "\xF0\x9F\x98\x83");

    // When whitespace normalization is on, nbsp gets replaced with space
    $cleaned = ASCII::clean($dirtyTestString, true, false, true, false);
    expect($cleaned)->toBe("\xEF\xBB\xBF" . '"Abcdef  ..." - ' . "\xF0\x9F\x98\x83");
});

test('language data files integrity', function () {
    $ascii_by_languages = include __DIR__ . '/../src/voku/helper/data/ascii_by_languages.php';
    $ascii_extras_by_languages = include __DIR__ . '/../src/voku/helper/data/ascii_extras_by_languages.php';

    $notFound = [];
    foreach ($ascii_by_languages as $lang => $tmp) {
        if (array_key_exists($lang, $ascii_extras_by_languages) === false) {
            $notFound[$lang] = ' Extra Language was not found! ';
        }
    }

    unset($notFound['latin'], $notFound[' '], $notFound['msword'], $notFound['currency_short']);

    expect($notFound)->toBeEmpty();
});

test('normalize_msword', function () {
    $tests = [
        ''                                                                         => '',
        ' '                                                                        => ' ',
        '«foobar»'                                                                 => '<<foobar>>',
        '中文空白 ‟'                                                                   => '中文空白 "',
        "<ㅡㅡ></ㅡㅡ><div>…</div><input type='email' name='user[email]' /><a>wtf</a>" => "<ㅡㅡ></ㅡㅡ><div>...</div><input type='email' name='user[email]' /><a>wtf</a>",
        '– DÃ¼sseldorf —'                                                          => '- DÃ¼sseldorf -',
        '„Abcdef…"'                                                                => '"Abcdef..."',
    ];

    foreach ($tests as $before => $after) {
        expect(ASCII::normalize_msword($before))->toBe($after);
    }
});

test('normalize_whitespace', function () {
    $tests = [
        ''                                                                                    => '',
        ' '                                                                                   => ' ',
        ' foo ' . "\xe2\x80\xa8" . ' öäü' . "\xe2\x80\xa9"                                    => ' foo   öäü ',
        "«\xe2\x80\x80foobar\xe2\x80\x80»"                                                    => '« foobar »',
        '中文空白 ‟'                                                                              => '中文空白 ‟',
        "<ㅡㅡ></ㅡㅡ><div>\xe2\x80\x85</div><input type='email' name='user[email]' /><a>wtf</a>" => "<ㅡㅡ></ㅡㅡ><div> </div><input type='email' name='user[email]' /><a>wtf</a>",
        "–\xe2\x80\x8bDÃ¼sseldorf\xe2\x80\x8b—"                                               => '– DÃ¼sseldorf —',
        "\xe2\x80\x9eAbcdef\xe2\x81\x9f\xe2\x80\x9c"                                                    => "\xe2\x80\x9eAbcdef \xe2\x80\x9c",
        " foo\t foo "                                                                         => ' foo	 foo ',
    ];

    foreach ($tests as $before => $after) {
        expect(ASCII::normalize_whitespace($before))->toBe($after);
    }

    expect(ASCII::normalize_whitespace("abc-\xc2\xa0-öäü-\xe2\x80\xaf-\xE2\x80\xAC"))
        ->toBe('abc- -öäü- -');

    expect(ASCII::normalize_whitespace("abc-\xc2\xa0-öäü-\xe2\x80\xaf-\xE2\x80\xAC", true))
        ->toBe("abc-\xc2\xa0-öäü- -");

    expect(ASCII::normalize_whitespace("abc-\xc2\xa0-öäü-\xe2\x80\xaf-\xE2\x80\xAC", true, true))
        ->toBe("abc-\xc2\xa0-öäü- -\xE2\x80\xAC");
});
