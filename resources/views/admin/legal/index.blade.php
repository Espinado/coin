@extends('layouts.admin')

@section('title', __('coin.admin.page_title', ['section' => __('coin.admin.legal.title')]))

@section('content')
    <div class="admin-card">
        <h1 style="margin:0;font-size:24px;font-weight:600;">{{ __('coin.admin.legal.title') }}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:rgba(232,237,245,0.72);">{{ __('coin.admin.legal.subtitle') }}</p>
    </div>

    <div class="admin-card admin-card--table" style="margin-top:16px;"><div class="admin-table-scroll">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                    <th style="padding:14px 18px;">{{ mb_strtoupper(__('coin.admin.legal.section')) }}</th>
                    <th style="padding:14px 18px;">{{ mb_strtoupper(__('coin.admin.legal.title_field')) }}</th>
                    <th style="padding:14px 18px;">{{ mb_strtoupper(__('coin.admin.legal.status')) }}</th>
                    <th style="padding:14px 18px;">{{ mb_strtoupper(__('coin.admin.legal.updated_at')) }}</th>
                    <th style="padding:14px 18px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                        <td style="padding:14px 18px;font-family:'JetBrains Mono',monospace;font-size:12px;">{{ $page->slugLabel() }}</td>
                        <td style="padding:14px 18px;">{{ $page->title }}</td>
                        <td style="padding:14px 18px;">
                            @if($page->is_published)
                                <span style="color:#7dffb2;">{{ __('coin.admin.legal.published') }}</span>
                            @else
                                <span style="color:rgba(232,237,245,0.55);">{{ __('coin.admin.legal.draft') }}</span>
                            @endif
                        </td>
                        <td style="padding:14px 18px;">{{ $page->updated_at?->timezone(config('coin.profit_accrual.schedule_timezone', 'Europe/Riga'))->format('d.m.Y H:i') }}</td>
                        <td style="padding:14px 18px;text-align:right;white-space:nowrap;">
                            <a href="{{ route('admin.legal.edit', $page) }}" class="admin-btn">{{ __('coin.admin.legal.edit') }}</a>
                            @if($page->is_published)
                                <a href="{{ route('legal.show', $page) }}" class="admin-btn" target="_blank" rel="noopener">{{ __('coin.admin.legal.preview') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding:24px 18px;color:rgba(232,237,245,0.62);">{{ __('coin.admin.legal.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
@endsection
