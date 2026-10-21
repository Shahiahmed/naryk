<?php

declare(strict_types=1);

/**
 * Local preview fixture. The client's MySQL dump is not on this machine,
 * so the homepage can render against SQLite. Not part of the deploy.
 */

$database = __DIR__.'/database.sqlite';

if (! is_file($database)) {
    touch($database);
}

$pdo = new PDO('sqlite:'.$database);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    "group" TEXT NOT NULL,
    name TEXT NOT NULL,
    value TEXT,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS menus (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS menu_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    menu INTEGER NOT NULL,
    label TEXT NOT NULL,
    link TEXT NOT NULL,
    class TEXT,
    sort INTEGER NOT NULL DEFAULT 0,
    parent INTEGER NOT NULL DEFAULT 0,
    depth INTEGER NOT NULL DEFAULT 0,
    role_id INTEGER NOT NULL DEFAULT 0,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_title TEXT NOT NULL,
    post_name TEXT NOT NULL,
    post_content TEXT NOT NULL DEFAULT '',
    post_summary TEXT,
    post_image TEXT,
    post_status TEXT NOT NULL DEFAULT 'publish',
    post_visibility TEXT NOT NULL DEFAULT 'public',
    comment_status TEXT NOT NULL DEFAULT 'open',
    show_image TEXT NOT NULL DEFAULT 'no',
    marquee TEXT NOT NULL DEFAULT 'no',
    ads_show TEXT,
    pr_news TEXT NOT NULL DEFAULT '0',
    meta_description TEXT NOT NULL DEFAULT '',
    meta_keyword TEXT,
    post_hits INTEGER NOT NULL DEFAULT 1,
    post_author INTEGER,
    post_type TEXT NOT NULL DEFAULT 'post',
    post_guid TEXT NOT NULL DEFAULT '',
    post_mime_type TEXT NOT NULL DEFAULT '',
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS terms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS term_taxonomies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    term_id INTEGER NOT NULL,
    taxonomy TEXT NOT NULL,
    description TEXT,
    parent INTEGER NOT NULL DEFAULT 0,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS term_relationships (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    term_taxonomy_id INTEGER NOT NULL,
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS ad_placements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    slug TEXT NOT NULL,
    active TEXT NOT NULL DEFAULT 'y',
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS advertisements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    type TEXT NOT NULL DEFAULT 'image',
    url TEXT,
    image TEXT,
    size TEXT,
    ga TEXT,
    active TEXT NOT NULL DEFAULT 'y',
    created_at TEXT,
    updated_at TEXT
);

CREATE TABLE IF NOT EXISTS ad_placement_advertisement (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ad_placement_id INTEGER NOT NULL,
    advertisement_id INTEGER NOT NULL
);
SQL);

$count = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();

if ($count > 0) {
    fwrite(STDOUT, "already seeded\n");
    exit(0);
}

$now = (new DateTimeImmutable('now', new DateTimeZone('Asia/Almaty')))->format('Y-m-d H:i:s');

$pdo->beginTransaction();

$pdo->prepare('INSERT INTO menus (name, created_at, updated_at) VALUES (?, ?, ?)')
    ->execute(['header', $now, $now]);
$menuId = (int) $pdo->lastInsertId();

$rubrics = [
    ['Билік', '/category/bilik'],
    ['Қаржы', '/category/qarzhy'],
    ['Бизнес', '/category/biznes'],
    ['Әлеумет', '/category/aleumet'],
    ['Сыртқы сауда', '/category/syrtqy-sauda'],
    ['Қаржылық сауат', '/category/qarzhylyq-sauat'],
    ['Мамандар пікірі', '/category/mamandar-pikiri'],
    ['Арнайы жобалар', '/category/arnayy-jobalar'],
];

$item = $pdo->prepare(
    'INSERT INTO menu_items (menu, label, link, class, sort, parent, depth, role_id, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, 0, 0, 0, ?, ?)'
);

foreach ($rubrics as $index => [$label, $link]) {
    $item->execute([$menuId, $label, $link, '', $index + 1, $now, $now]);
}

$categories = [
    'qarzhylyq-sauat' => 'Қаржылық сауат',
    'qarzhy' => 'Қаржы',
    'aleumet' => 'Әлеумет',
    'biznes' => 'Бизнес',
    'mamandar-pikiri' => 'Мамандар пікірі',
    'arnayy-jobalar' => 'Арнайы жобалар',
];

$termIds = [];
$insertTerm = $pdo->prepare('INSERT INTO terms (name, slug, created_at, updated_at) VALUES (?, ?, ?, ?)');
$insertTaxonomy = $pdo->prepare(
    'INSERT INTO term_taxonomies (term_id, taxonomy, description, parent, created_at, updated_at) VALUES (?, ?, ?, 0, ?, ?)'
);

foreach ($categories as $slug => $name) {
    $insertTerm->execute([$name, $slug, $now, $now]);
    $termId = (int) $pdo->lastInsertId();
    $insertTaxonomy->execute([$termId, 'category', '', $now, $now]);
    $termIds[$slug] = (int) $pdo->lastInsertId();
}

$posts = [
    ['Зейнетке 53-55 жастан бастап шығуға болады', 'zeinetke-shygu', 'Зейнетақы аннуитеті – өмір бойы тұрақты төлем алуына мүмкіндік береді', 'qarzhylyq-sauat', null],
    ['Банктер борышкерлердің шоттағы соңғы 100 мың теңгесін алып қоя алмайды', 'bankter-boryshkerler', 'Дегенмен сот орындаушыларына талап басқаша', 'qarzhy', null],
    ['Солтүстікке көшетіндердің мемлекеттен үй алуы қиындайды', 'soltustik-ui', '7 млн теңгеге дейін тұрғын үй көмегін алу үшін жұмыс істейтінін растауы керек', 'aleumet', null],
    ['Биыл автокөлік саудасы азайып кетті', 'avtokolik-sauda', '2026 жылдың қаңтар–тамызында 232 мың автомобиль сатылды', 'biznes', null],
    ['Депозитіңіз бен жеріңіз болса әлеуметтік төлем берілмеуі мүмкін', 'depozit-tolem', 'Тиісті ереже үкімет қаулысымен бекітілген', 'mamandar-pikiri', null],
    ['Сыртқы экономикалық қызмет: келісімшартты қауіпсіз жүргізу', 'syrtqy-keisim', 'Негізгі операциялар қолданбада қолжетімді', 'arnayy-jobalar', null],
    ['Жаңа Tansar Islamic Bank пайда болды', 'tansar-bank', 'Банктің бас атқарушы директоры тағайындалды', 'qarzhy', null],
    ['Жарты миллион адам жәрдемақыдан айырылады', 'zhardemaky', 'Қазір жалпы 3,2 млн адам жәрдемақы алады', 'aleumet', null],
];

$insertPost = $pdo->prepare(
    'INSERT INTO posts (
        post_title, post_name, post_content, post_summary, post_status, post_visibility,
        show_image, marquee, pr_news, meta_description, post_type, post_guid, post_mime_type,
        created_at, updated_at
    ) VALUES (?, ?, ?, ?, \'publish\', \'public\', \'no\', \'no\', \'0\', \'\', \'post\', \'\', \'\', ?, ?)'
);
$insertLink = $pdo->prepare(
    'INSERT INTO term_relationships (post_id, term_taxonomy_id, created_at, updated_at) VALUES (?, ?, ?, ?)'
);

foreach ($posts as $offset => [$title, $slug, $summary, $category, $image]) {
    $created = (new DateTimeImmutable('now', new DateTimeZone('Asia/Almaty')))
        ->modify('-'.$offset.' days')
        ->format('Y-m-d H:i:s');
    $insertPost->execute([$title, $slug, '<p>'.$summary.'</p>', $summary, $created, $created]);
    $insertLink->execute([(int) $pdo->lastInsertId(), $termIds[$category], $created, $created]);
}

$pdo->commit();

fwrite(STDOUT, "seeded\n");
