<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Support\WalletHistoryEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class WalletHistoryService
{
    /** @return LengthAwarePaginator<int, WalletHistoryEntry> */
    public function paginate(
        int $userId,
        string $search = '',
        string $sort = '',
        string $dir = 'desc',
        int $perPage = 10,
        int $page = 1,
    ): LengthAwarePaginator {
        $entries = $this->collectEntries($userId);
        $entries = $this->applySearch($entries, $search);
        $entries = $this->sortEntries($entries, $sort, $dir);

        $total = $entries->count();
        $items = $entries
            ->slice(max(0, ($page - 1) * $perPage), $perPage)
            ->values();

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'walletPage',
            ],
        );
    }

    /** @return Collection<int, WalletHistoryEntry> */
    private function collectEntries(int $userId): Collection
    {
        $entries = WalletTransaction::query()
            ->where('user_id', $userId)
            ->get()
            ->map(fn (WalletTransaction $transaction) => WalletHistoryEntry::fromTransaction($transaction));

        Deposit::query()
            ->where('user_id', $userId)
            ->whereIn('status', [Deposit::STATUS_PENDING, Deposit::STATUS_REJECTED])
            ->get()
            ->each(function (Deposit $deposit) use ($entries): void {
                $entries->push(WalletHistoryEntry::fromDeposit($deposit));
            });

        Withdrawal::query()
            ->where('user_id', $userId)
            ->where(function ($query): void {
                $query->whereIn('status', Withdrawal::openStatuses())
                    ->orWhere('status', Withdrawal::STATUS_REJECTED);
            })
            ->get()
            ->each(function (Withdrawal $withdrawal) use ($entries): void {
                $entries->push(WalletHistoryEntry::fromWithdrawal($withdrawal));
            });

        return $entries->values();
    }

    /** @param  Collection<int, WalletHistoryEntry>  $entries
     * @return Collection<int, WalletHistoryEntry>
     */
    private function applySearch(Collection $entries, string $search): Collection
    {
        $term = mb_strtolower(trim($search));

        if ($term === '') {
            return $entries;
        }

        return $entries->filter(function (WalletHistoryEntry $entry) use ($term): bool {
            $haystacks = [
                mb_strtolower($entry->searchBlob),
                mb_strtolower($entry->typeLabel),
                mb_strtolower($entry->sourceLabel),
                mb_strtolower($entry->statusLabel),
                mb_strtolower($entry->amountLabel),
            ];

            foreach ($haystacks as $haystack) {
                if ($haystack !== '' && str_contains($haystack, $term)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    /** @param  Collection<int, WalletHistoryEntry>  $entries
     * @return Collection<int, WalletHistoryEntry>
     */
    private function sortEntries(Collection $entries, string $sort, string $dir): Collection
    {
        $descending = strtolower($dir) !== 'asc';

        $sorted = match ($sort) {
            'type' => $entries->sortBy(fn (WalletHistoryEntry $entry) => mb_strtolower($entry->typeLabel)),
            'source' => $entries->sortBy(fn (WalletHistoryEntry $entry) => mb_strtolower($entry->sourceLabel)),
            'status' => $entries->sortBy(fn (WalletHistoryEntry $entry) => mb_strtolower($entry->statusLabel)),
            'amount' => $entries->sortBy(fn (WalletHistoryEntry $entry) => $entry->amountNumeric),
            default => $entries->sortBy(function (WalletHistoryEntry $entry): array {
                return [
                    $entry->occurredAt->getTimestamp(),
                    $entry->sortOrder,
                    $entry->entityId ?? 0,
                ];
            }),
        };

        if ($descending) {
            $sorted = $sorted->reverse();
        }

        return $sorted->values();
    }
}
