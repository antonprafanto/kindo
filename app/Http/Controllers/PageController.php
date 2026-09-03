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

    public function beasiswa(): View
    {
        return view('beasiswa', [
            'awsRegisterUrl' => config('kindo.scholarships.aws_ai_academy_url'),
        ]);
    }

    public function fullstackIot(): View
    {
        $isPublic = (bool) config('kindo.fsiot_public');
        $user = auth()->user();
        $canPreviewFull = $this->canPreviewFsiotPath($user);

        $phases = [
            [
                'code' => 'BABAK 1',
                'title' => __('ui.fsiot.phases.babak1.title'),
                'blurb' => __('ui.fsiot.phases.babak1.blurb'),
                'modules' => 'M-01 … M-24',
            ],
            [
                'code' => 'BABAK 2',
                'title' => __('ui.fsiot.phases.babak2.title'),
                'blurb' => __('ui.fsiot.phases.babak2.blurb'),
                'modules' => 'M-25 … M-38',
            ],
            [
                'code' => 'BABAK 3',
                'title' => __('ui.fsiot.phases.babak3.title'),
                'blurb' => __('ui.fsiot.phases.babak3.blurb'),
                'modules' => 'M-39 … M-47',
            ],
            [
                'code' => 'BABAK 4',
                'title' => __('ui.fsiot.phases.babak4.title'),
                'blurb' => __('ui.fsiot.phases.babak4.blurb'),
                'modules' => 'M-48 … M-55',
            ],
            [
                'code' => 'BABAK 5',
                'title' => __('ui.fsiot.phases.babak5.title'),
                'blurb' => __('ui.fsiot.phases.babak5.blurb'),
                'modules' => 'M-56 … M-64',
            ],
            [
                'code' => 'BABAK 6',
                'title' => __('ui.fsiot.phases.babak6.title'),
                'blurb' => __('ui.fsiot.phases.babak6.blurb'),
                'modules' => 'M-65 … M-73',
            ],
        ];

        if (! $isPublic && ! $canPreviewFull) {
            return view('belajar.fullstack-iot-soon', [
                'phases' => $phases,
                'trakteerUrl' => config('kindo.trakteer_tip_url'),
            ]);
        }

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
