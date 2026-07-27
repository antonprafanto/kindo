<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

$fail = 0;
$check = function (string $label, bool $ok) use (&$fail): void {
    echo ($ok ? 'OK  ' : 'FAIL') . "  {$label}\n";
    if (! $ok) {
        $fail++;
    }
};

App::setLocale('en');
$check('EN nav iot_path', __('ui.nav.iot_path') === 'IoT Path');
$check('EN fsiot phase zero', __('ui.fsiot.phases.zero.title') === 'Absolute foundations');
$check('EN articles banner copy', __('ui.articles.locale_banner') === 'Lesson articles are written in Indonesian.');

App::setLocale('id');
$check('ID nav iot_path', __('ui.nav.iot_path') === 'Jalur IoT');
$check('ID fsiot phase zero', __('ui.fsiot.phases.zero.title') === 'Fondasi awam');

$bannerId = view('components.locale-article-banner')->render();
$check('Banner hidden in ID', trim($bannerId) === '');

App::setLocale('en');
$bannerEn = view('components.locale-article-banner')->render();
$check('Banner visible in EN', str_contains($bannerEn, 'Lesson articles are written in Indonesian.'));

$check('Route locale.switch registered', Route::has('locale.switch'));

$request = Request::create('/?lang=en', 'GET');
$middleware = new SetLocale;
$middleware->handle($request, function ($req) {
    return response('ok');
});
$check('Middleware ?lang=en sets locale', App::getLocale() === 'en');

$request2 = Request::create('/', 'GET');
$request2->setLaravelSession(app('session.store'));
session(['locale' => 'id']);
$middleware->handle($request2, function ($req) {
    return response('ok');
});
$check('Middleware session id', App::getLocale() === 'id');

App::setLocale('en');
\Illuminate\Support\Carbon::setLocale('en');
$check('Carbon EN month', str_contains(\Illuminate\Support\Carbon::parse('2026-07-01')->translatedFormat('F'), 'July'));

exit($fail > 0 ? 1 : 0);
