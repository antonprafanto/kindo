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

        $count = 0;

        NewsletterSubscriber::active()
            ->orderBy('id')
            ->chunk(50, function ($subscribers) use ($newsletter, &$count) {
                foreach ($subscribers as $subscriber) {
                    $newsletter->sendNewArticleEmail($subscriber, $this->article);
                    $count++;
                }
            });

        ArticleNewsletterLog::create([
            'article_id'        => $this->article->id,
            'recipients_count'  => $count,
            'sent_at'           => now(),
        ]);

        Log::info('Newsletter sent for article', [
            'article_id' => $this->article->id,
            'recipients' => $count,
        ]);
    }
}
