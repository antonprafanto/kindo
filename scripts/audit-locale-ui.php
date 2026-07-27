<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;
    if ($ok) {
        echo "OK    {$label}\n";
        $pass++;
    } else {
        echo "FAIL  {$label}\n";
        $fail++;
    }
}

Illuminate\Support\Facades\App::setLocale('id');
$id = view('components.locale-switcher')->render();
check('ID active is span aria-current', str_contains($id, 'aria-current="true"') && str_contains($id, '>ID</span>'));
check('ID inactive EN is link', str_contains($id, 'locale/en') && str_contains($id, '>EN</a>'));
check('Banner hidden in ID', trim(view('components.locale-article-banner')->render()) === '');

Illuminate\Support\Facades\App::setLocale('en');
$en = view('components.locale-switcher')->render();
check('EN active is span aria-current', str_contains($en, 'aria-current="true"') && str_contains($en, '>EN</span>'));
check('EN inactive ID is link', str_contains($en, 'locale/id') && str_contains($en, '>ID</a>'));
$ban = view('components.locale-article-banner')->render();
check('Banner role=status EN', str_contains($ban, 'role="status"'));
check('Banner CTA outline', str_contains($ban, 'btn-outline') && str_contains($ban, 'btn-brutal'));
check('Banner theme-highlight', str_contains($ban, 'theme-highlight'));
check('Switcher role=group', str_contains($en, 'role="group"'));
check('Switcher min-h-9 touch', str_contains($en, 'min-h-9'));

$nav = file_get_contents(__DIR__ . '/../resources/views/components/navbar.blade.php');
check('Desktop theme-toggle shrink-0', str_contains($nav, 'theme-toggle class="ml-1 shrink-0"'));
check('Mobile theme-toggle shrink-0', str_contains($nav, 'theme-toggle class="shrink-0"'));
check('Mobile search shrink-0', str_contains($nav, 'border-black shrink-0" aria-label'));
check('Mobile controls shrink-0 wrapper', str_contains($nav, 'md:hidden shrink-0'));
$sw = file_get_contents(__DIR__ . '/../resources/views/components/locale-switcher.blade.php');
check('size=sm pad wired', str_contains($sw, "\$size === 'sm'"));

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail > 0 ? 1 : 0);
