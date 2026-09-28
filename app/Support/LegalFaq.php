<?php

namespace App\Support;

class LegalFaq
{
    /**
     * @return list<array{question: string, answer: string}>
     */
    public static function defaultItems(): array
    {
        $host = self::publicHost();

        return [
            [
                'question' => 'What is CloudFlops?',
                'answer' => "CloudFlops is an investment-plan platform at {$host}. You top up USDT (or BTC converted to USDT), buy a plan with a stated APR and term, receive daily profit to your available balance, and can withdraw to your own wallet. Investing involves risk — see the Risk Disclosure.",
            ],
            [
                'question' => 'How do investment plans work?',
                'answer' => 'A plan defines minimum investment, APR, and contract duration. When you buy a plan, principal is locked in a contract until maturity. Daily profit is credited to your available balance according to platform rules.',
            ],
            [
                'question' => 'How is daily profit calculated?',
                'answer' => 'Accruals follow the plan’s APR and the platform’s daily accrual schedule (shown in your dashboard). Landing-page calculators are estimates only and are not a guarantee of returns.',
            ],
            [
                'question' => 'What happens when a contract matures?',
                'answer' => 'When the contract term ends, principal returns to your available balance (subject to Terms). Accrued profit already credited remains in available balance unless otherwise stated in Terms.',
            ],
            [
                'question' => 'Which currencies can I deposit?',
                'answer' => 'USDT and BTC. Accounting is in USDT; BTC deposits are converted using the platform exchange rate.',
            ],
            [
                'question' => 'How do withdrawals work?',
                'answer' => 'Open Wallet in the dashboard, submit a withdrawal request to your payout address, and confirm any required checks (for example verification or fees shown before confirm). Processing status is visible in the dashboard and admin review may apply.',
            ],
            [
                'question' => 'How does the referral program work?',
                'answer' => 'Share your referral link. When someone you invited buys a plan, you may receive a Level-1 commission (default percentage is configured by the platform, commonly 20% of the purchase). On an approved plan upgrade, commission may apply to the top-up difference only. Level-2 is not used.',
            ],
            [
                'question' => 'Is CloudFlops an exchange or a bank?',
                'answer' => 'No. CloudFlops is not a crypto exchange and not a bank. It offers investment plans through a user dashboard under its Terms and Risk Disclosure.',
            ],
            [
                'question' => 'Are returns guaranteed?',
                'answer' => 'No. Returns are not guaranteed. Past performance does not guarantee future results. Read /legal/risks before investing.',
            ],
            [
                'question' => 'How do I contact support?',
                'answer' => 'Use Live support on the landing page (guest) or inside the dashboard after sign-in. You can also use the platform contact email published on the site.',
            ],
            [
                'question' => 'Where are the legal documents?',
                'answer' => "Terms: /legal/terms · Privacy: /legal/privacy · Risks: /legal/risks · FAQ: /legal/faq (on {$host})",
            ],
        ];
    }

    private static function publicHost(): string
    {
        $fromApp = parse_url((string) config('app.url'), PHP_URL_HOST);
        $fromUser = (string) config('coin.user_domain', '');

        $host = is_string($fromApp) && $fromApp !== '' ? $fromApp : $fromUser;

        return $host !== '' ? $host : 'CloudFlops';
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public static function decode(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            return self::normalize($decoded);
        }

        return self::parseLegacyText($raw);
    }

    /**
     * @param  list<array{question?: mixed, answer?: mixed}>|mixed  $items
     * @return list<array{question: string, answer: string}>
     */
    public static function normalize(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $normalized[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $normalized;
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public static function parseLegacyText(string $raw): array
    {
        $blocks = preg_split("/\R\R+/", trim($raw)) ?: [];
        $items = [];

        foreach ($blocks as $block) {
            $block = trim($block);

            if ($block === '') {
                continue;
            }

            if (preg_match('/^(?:В|Q|Question):\s*(.+?)\R(?:О|A|Answer):\s*(.+)$/su', $block, $matches)) {
                $items[] = [
                    'question' => trim($matches[1]),
                    'answer' => trim($matches[2]),
                ];
            }
        }

        return $items;
    }

    /**
     * @param  list<array{question: string, answer: string}>  $items
     */
    public static function encode(array $items): string
    {
        return json_encode(self::normalize($items), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public static function bodyAsPlainText(array $items): string
    {
        $lines = [];

        foreach (self::normalize($items) as $item) {
            $lines[] = $item['question'];
            $lines[] = $item['answer'];
            $lines[] = '';
        }

        return rtrim(implode("\n", $lines));
    }
}
