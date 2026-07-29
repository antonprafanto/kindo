<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FullstackIotGateTest extends TestCase
{
    public function test_guest_sees_coming_soon_while_prelaunch(): void
    {
        config(['kindo.fsiot_public' => false]);

        $this->get(route('belajar.fullstack-iot'))
            ->assertOk()
            ->assertSee(__('ui.fsiot.soon_badge'), false)
            ->assertSee(__('ui.fsiot.soon_headline'), false)
            ->assertDontSee(__('ui.fsiot.phases_title'), false)
            ->assertDontSee(__('ui.fsiot.admin_preview_badge'), false);
    }

    public function test_admin_sees_full_path_while_prelaunch(): void
    {
        config(['kindo.fsiot_public' => false]);

        $admin = new User([
            'name'  => 'Admin Gate',
            'email' => 'admin-gate@example.com',
            'role'  => 'admin',
        ]);
        $admin->id = 1;

        $this->actingAs($admin)
            ->get(route('belajar.fullstack-iot'))
            ->assertOk()
            ->assertSee(__('ui.fsiot.phases_title'), false)
            ->assertSee(__('ui.fsiot.admin_preview_badge'), false)
            ->assertDontSee(__('ui.fsiot.soon_headline'), false);
    }

    public function test_author_sees_coming_soon_while_prelaunch(): void
    {
        config(['kindo.fsiot_public' => false]);

        $author = new User([
            'name'  => 'Author Gate',
            'email' => 'author-gate@example.com',
            'role'  => 'author',
        ]);
        $author->id = 2;

        $this->actingAs($author)
            ->get(route('belajar.fullstack-iot'))
            ->assertOk()
            ->assertSee(__('ui.fsiot.soon_badge'), false)
            ->assertDontSee(__('ui.fsiot.phases_title'), false);
    }

    public function test_everyone_sees_full_path_when_public_flag_on(): void
    {
        config(['kindo.fsiot_public' => true]);

        $this->get(route('belajar.fullstack-iot'))
            ->assertOk()
            ->assertSee(__('ui.fsiot.phases_title'), false)
            ->assertDontSee(__('ui.fsiot.soon_badge'), false)
            ->assertDontSee(__('ui.fsiot.admin_preview_badge'), false);
    }
}
