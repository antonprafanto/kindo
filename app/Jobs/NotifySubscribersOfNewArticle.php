<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\ArticleNewsletterLog;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class NotifySubscribersOfNewArticle
{
    use Dispatchable;

    public function __construct(public Article $article) {}

    public function handle(NewsletterService $newsletter): void
    {
        if (ArticleNewsletterLog::where('article_id', $this->article->id)->exists()) {
            return;
        }

        $this->article->loadMissing(['category', 'user']);

        $sent = 0;
        $failed = 0;
        $total = 0;

        NewsletterSubscriber::active()
            ->orderBy('id')
            ->chunk(50, function ($subscribers) use ($newsletter, &$sent, &$failed, &$total) {
                foreach ($subscribers as $subscriber) {
                    $total++;
                    if ($newsletter->sendNewArticleEmail($subscriber, $this->article)) {
                        $sent++;
                    } else {
                        $failed++;
                    }
                }
            });

        Log::info('Newsletter send finished for article', [
            'article_id' => $this->article->id,
            'sent'       => $sent,
            'failed'     => $failed,
            'total'      => $total,
        ]);

        if ($sent === 0 && $failed > 0) {
            Log::error('Newsletter send failed for all subscribers', [
                'article_id' => $this->article->id,
                'sent'       => $sent,
                'failed'     => $failed,
            ]);

            return;
        }

        if ($sent > 0 || $total === 0) {
            ArticleNewsletterLog::create([
                'article_id'       => $this->article->id,
                'recipients_count' => $sent,
                'sent_at'          => now(),
            ]);
        }
    }
}
