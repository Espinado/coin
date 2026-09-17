<?php

namespace App\Support;

class LegalFaq
{
    /**
     * @return list<array{question: string, answer: string}>
     */
    public static function defaultItems(): array
    {
        return [
            [
                'question' => 'What is CloudFlops?',
                'answer' => 'An investment platform where you top up USDT, buy a plan with fixed APR, and receive daily profit to your available balance.',
            ],
            [
                'question' => 'How do plans work?',
                'answer' => 'A plan defines minimum investment, APR, and contract term. When you buy a plan, principal is locked until maturity.',
            ],
            [
                'question' => 'Where does profit come from?',
                'answer' => 'Daily profit is calculated as principal × APR / 365 and credited to your available balance. At maturity, principal returns to available balance.',
            ],
            [
                'question' => 'How do referrals work?',
                'answer' => 'When someone you invited purchases a plan, you receive a one-time commission (default 20%) credited to your available balance.',
            ],
            [
                'question' => 'How do I request a payout?',
                'answer' => 'Submit a request in the Wallet section of your dashboard. Fee and processing time are shown before confirmation. Specific terms are subject to change.',
            ],
            [
                'question' => 'What can I see in the dashboard?',
                'answer' => 'Balance, investments, profit history, wallet, payouts, referrals, and support.',
            ],
        ];
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
