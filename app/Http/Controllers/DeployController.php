<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    /**
     * Clear cached config/routes/views after FTP deploy (shared hosting, no SSH).
     * Protected by DEPLOY_HOOK_TOKEN — returns 404 when token is missing or invalid.
     */
    public function clearCache()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Cache cleared', 200);
    }
}
