<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about()
    {
        $articleCount = Article::published()->count();
        return view('about', compact('articleCount'));
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function fullstackIot(): View
    {
        $isPublic = (bool) config('kindo.fsiot_public');
        $user = auth()->user();
        $canPreviewFull = $this->canPreviewFsiotPath($user);

        if (! $isPublic && ! $canPreviewFull) {
            return view('belajar.fullstack-iot-soon', [
                'trakteerUrl' => config('kindo.trakteer_tip_url'),
            ]);
        }

        $phases = [
            [
                'code' => 'ZERO',
                'title' => __('ui.fsiot.phases.zero.title'),
                'blurb' => __('ui.fsiot.phases.zero.blurb'),
                'modules' => 'FS-01 … FS-16',
            ],
            [
                'code' => 'BUILDER',
                'title' => __('ui.fsiot.phases.builder.title'),
                'blurb' => __('ui.fsiot.phases.builder.blurb'),
                'modules' => 'FS-17 … FS-28',
            ],
            [
                'code' => 'CONNECTED',
                'title' => __('ui.fsiot.phases.connected.title'),
                'blurb' => __('ui.fsiot.phases.connected.blurb'),
                'modules' => 'FS-29 … FS-38',
            ],
            [
                'code' => 'FULLSTACK',
                'title' => __('ui.fsiot.phases.fullstack.title'),
                'blurb' => __('ui.fsiot.phases.fullstack.blurb'),
                'modules' => 'FS-39 … FS-48',
            ],
            [
                'code' => 'HERO',
                'title' => __('ui.fsiot.phases.hero.title'),
                'blurb' => __('ui.fsiot.phases.hero.blurb'),
                'modules' => 'FS-49 … FS-56',
            ],
        ];

        return view('belajar.fullstack-iot', [
            'phases' => $phases,
            'trakteerUrl' => config('kindo.trakteer_tip_url'),
            'isAdminPreview' => ! $isPublic && $canPreviewFull,
        ]);
    }

    private function canPreviewFsiotPath(?User $user): bool
    {
        return $user !== null && $user->isAdmin();
    }
}
