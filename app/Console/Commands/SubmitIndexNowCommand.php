<?php

namespace App\Console\Commands;

use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;
use Throwable;

class SubmitIndexNowCommand extends Command
{
    protected $signature = 'coin:indexnow
                            {--url=* : Absolute URL(s) to submit; defaults to sitemap public pages}
                            {--dry-run : Print payload without calling IndexNow}';

    protected $description = 'Submit public URLs to IndexNow (Bing / Yandex / others)';

    public function handle(IndexNowService $indexNow): int
    {
        $urls = $this->option('url');
        $urls = is_array($urls) && $urls !== [] ? array_values($urls) : null;

        if ($this->option('dry-run')) {
            if (! $indexNow->isConfigured()) {
                $this->error('COIN_SEO_INDEXNOW_KEY is not set.');

                return self::FAILURE;
            }

            $this->line('keyLocation: '.$indexNow->keyFileUrl());
            $list = $urls ?? \App\Support\PublicSeoUrls::absoluteUrls();
            foreach ($list as $url) {
                $this->line('  '.$url);
            }

            return self::SUCCESS;
        }

        try {
            $result = $indexNow->submit($urls);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'IndexNow OK: %d URL(s), HTTP %d via %s',
            $result['submitted'],
            $result['status'],
            $result['endpoint'],
        ));

        return self::SUCCESS;
    }
}
