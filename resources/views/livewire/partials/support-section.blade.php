<section
  data-screen-label="Support"
  @class([
    'coin-support-section',
    'coin-support-section--create' => $showCreateTicket,
    'coin-support-section--chat' => ! $showCreateTicket && $this->selectedTicket,
  ])
>
  <div class="coin-support-sidebar">
    <div class="coin-support-panel">
      <div class="coin-support-sidebar-head">
        <span class="coin-support-sidebar-title">{{ __('coin.support.conversations') }}</span>
        <button type="button" wire:click="openCreateTicket" class="coin-support-btn coin-support-btn--primary coin-support-btn--compact">{{ __('coin.support.new_chat') }}</button>
      </div>
      <div class="coin-support-ticket-list">
        @forelse($tickets as $ticket)
          @php($unread = $ticket->unreadMessagesForUser())
          <button type="button" wire:click="selectTicket({{ $ticket->id }})" @class(['coin-support-ticket', 'coin-support-ticket--active' => $selectedTicketId === $ticket->id, 'coin-support-ticket--unread' => $unread > 0])>
            <div class="coin-support-ticket__head">
              <span class="coin-support-ticket__subject">{{ $ticket->subject }}</span>
              @if($unread > 0)
                <span class="coin-support-badge">{{ $unread }}</span>
              @else
                <span class="coin-support-ticket__status" style="color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span>
              @endif
            </div>
            <div class="coin-support-ticket__meta">{{ $ticket->reference }} · {{ $ticket->categoryLabel() }}</div>
            <div class="coin-support-ticket__date">{{ $ticket->updated_at?->format('M j, H:i') }}</div>
          </button>
        @empty
          <div class="coin-support-empty">{{ __('coin.support.no_conversations') }}</div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="coin-support-main">
    <div class="coin-support-status-bar">
      <div class="coin-support-status-bar__left">
        <span class="coin-support-status-dot"></span>
        <div>
          <div class="coin-support-status-title">{{ __('coin.support.live_chat') }}</div>
          <div class="coin-support-status-subtitle">{{ __('coin.support.operators_online') }}</div>
        </div>
      </div>
      @if($this->unreadSupportCount > 0)
        <span class="coin-support-badge coin-support-badge--large">{{ $this->unreadSupportCount }} {{ __('coin.support.unread') }}</span>
      @endif
    </div>

    @if($showCreateTicket)
      <div class="coin-support-panel coin-support-panel--grow">
        <div class="coin-support-panel-title">{{ __('coin.support.start_new') }}</div>
        <p class="coin-support-panel-hint">{{ __('coin.support.start_new_hint') }}</p>
        <form wire:submit.prevent="createTicket" wire:key="support-create-form-{{ $createFormKey }}" class="coin-support-form">
          <div class="coin-support-field">
            <div class="coin-support-label">{{ mb_strtoupper(__('coin.support.subject')) }}</div>
            <input type="text" wire:model.live.debounce.250ms="newSubject" maxlength="120" class="coin-support-input">
            @error('newSubject')<div class="coin-support-error">{{ $message }}</div>@enderror
          </div>
          <div class="coin-support-field">
            <div class="coin-support-label">{{ mb_strtoupper(__('coin.support.category')) }}</div>
            <select wire:model="newCategory" class="coin-support-input">
              @foreach($this->ticketCategories as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
            @error('newCategory')<div class="coin-support-error">{{ $message }}</div>@enderror
          </div>
          <div class="coin-support-field">
            <div class="coin-support-label">{{ mb_strtoupper(__('coin.support.message')) }}</div>
            <textarea wire:model.live.debounce.250ms="newBody" rows="6" maxlength="5000" placeholder="{{ __('coin.support.message_placeholder') }}" class="coin-support-input coin-support-textarea"></textarea>
            @error('newBody')<div class="coin-support-error">{{ $message }}</div>@enderror
          </div>
          <div class="coin-support-form-actions">
            <button type="submit" class="coin-support-btn coin-support-btn--primary">{{ __('coin.support.start_chat') }}</button>
            <button type="button" wire:click="cancelCreateTicket" class="coin-support-btn coin-support-btn--secondary">{{ __('coin.cancel') }}</button>
          </div>
        </form>
      </div>
    @elseif($this->selectedTicket)
      @php($ticket = $this->selectedTicket)
      <div class="coin-support-panel">
        <div class="coin-support-ticket-head">
          <div class="coin-support-ticket-head__ref">{{ $ticket->reference }}</div>
          <div class="coin-support-ticket-head__title">{{ $ticket->subject }}</div>
          <div class="coin-support-ticket-head__meta">{{ $ticket->categoryLabel() }} · <span style="color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span></div>
        </div>
      </div>

      <div wire:key="support-chat-{{ $ticket->id }}" class="coin-support-chat">
        <div id="support-thread" wire:ignore.self class="coin-support-thread">
          @foreach($ticket->messages as $message)
            <div wire:key="support-message-{{ $message->id }}" data-message-id="{{ $message->id }}" @class(['coin-support-message', 'coin-support-message--admin' => $message->isFromAdmin()])>
              <div class="coin-support-message__head">
                <span>{{ $message->authorLabel() }}</span>
                <span>{{ $message->created_at?->format('M j, Y H:i') }}</span>
              </div>
              <div class="coin-support-message__body">{{ $message->body }}</div>
            </div>
          @endforeach
        </div>

        @if($ticket->status !== \App\Models\SupportTicket::STATUS_CLOSED)
          <div class="coin-support-panel">
            <form wire:submit.prevent="sendTicketReply" wire:key="support-reply-form-{{ $ticket->id }}-{{ $replyFormKey }}" class="coin-support-form">
              <textarea wire:model="replyBody" rows="3" maxlength="5000" placeholder="{{ __('coin.support.reply_placeholder') }}" class="coin-support-input coin-support-textarea"></textarea>
              @error('replyBody')<div class="coin-support-error">{{ $message }}</div>@enderror
              <button type="submit" class="coin-support-btn coin-support-btn--primary coin-support-btn--self-start">{{ __('coin.support.send_message') }}</button>
            </form>
          </div>
        @else
          <div class="coin-support-empty coin-support-empty--dashed">{{ __('coin.support.chat_closed') }}</div>
        @endif
      </div>
    @else
      <div class="coin-support-panel coin-support-panel--grow coin-support-panel--center">
        <div class="coin-support-panel-hint">{{ __('coin.support.select_or_start') }}</div>
        <button type="button" wire:click="openCreateTicket" class="coin-support-btn coin-support-btn--primary">{{ __('coin.support.start_live_chat') }}</button>
      </div>
    @endif
  </div>
</section>
