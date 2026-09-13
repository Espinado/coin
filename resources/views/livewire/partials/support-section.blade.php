<section data-screen-label="Support" style="padding: 28px 32px 40px; display: grid; grid-template-columns: minmax(0, 340px) minmax(0, 1fr); gap: 16px; align-items: start;">
  <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
      <span style="font-size:15px;font-weight:600;">Conversations</span>
      <button type="button" wire:click="openCreateTicket" style="padding:8px 12px;border-radius:9px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:12.5px;font-weight:600;cursor:pointer;">New chat</button>
    </div>
    <div style="margin-top:18px;display:flex;flex-direction:column;gap:10px;">
      @forelse($tickets as $ticket)
        @php($unread = $ticket->unreadMessagesForUser())
        <button type="button" wire:click="selectTicket({{ $ticket->id }})" style="text-align:left;padding:14px 16px;border-radius:12px;border:1px solid {{ $selectedTicketId === $ticket->id ? 'oklch(0.86 0.11 195 / 0.45)' : ($unread > 0 ? 'oklch(0.82 0.18 35 / 0.45)' : 'rgba(150,235,250,0.12)') }};background:{{ $selectedTicketId === $ticket->id ? 'oklch(0.6 0.13 200 / 0.18)' : ($unread > 0 ? 'oklch(0.72 0.16 35 / 0.1)' : 'rgba(150,235,250,0.03)') }};color:inherit;font-family:inherit;cursor:pointer;">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:baseline;">
            <span style="font-size:14px;font-weight:{{ $unread > 0 ? '700' : '600' }};">{{ $ticket->subject }}</span>
            @if($unread > 0)
              <span class="coin-support-badge" style="font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700;min-width:18px;text-align:center;padding:2px 6px;border-radius:999px;background:linear-gradient(140deg, oklch(0.88 0.2 35), oklch(0.72 0.22 25));color:#1a0a04;">{{ $unread }}</span>
            @else
              <span style="font-family:'JetBrains Mono',monospace;font-size:10px;color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span>
            @endif
          </div>
          <div style="margin-top:6px;font-family:'JetBrains Mono',monospace;font-size:10.5px;color:rgba(214,238,248,0.66);">{{ $ticket->reference }} · {{ $ticket->categoryLabel() }}</div>
          <div style="margin-top:6px;font-size:12px;color:rgba(214,238,248,0.62);">{{ $ticket->updated_at?->format('M j, H:i') }}</div>
        </button>
      @empty
        <div style="padding:16px;border-radius:12px;border:1px dashed rgba(150,235,250,0.18);font-size:13px;color:rgba(214,238,248,0.72);">No conversations yet. Start a live chat if you need help with withdrawals, contracts, or your account.</div>
      @endforelse
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px;min-height:520px;">
    <div style="padding:16px 20px;border-radius:14px;border:1px solid rgba(150,235,250,0.14);background:rgba(150,235,250,0.05);display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
      <div style="display:flex;align-items:center;gap:12px;">
        <span style="width:10px;height:10px;border-radius:50%;background:oklch(0.85 0.15 160);box-shadow:0 0 12px oklch(0.85 0.15 160);animation:dbPulse 2.4s infinite;"></span>
        <div>
          <div style="font-size:15px;font-weight:600;">Live support chat</div>
          <div style="margin-top:4px;font-size:12.5px;color:rgba(214,238,248,0.68);">Operators are online · replies appear instantly</div>
        </div>
      </div>
      @if($this->unreadSupportCount > 0)
        <span class="coin-support-badge" style="font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:700;padding:6px 12px;border-radius:999px;background:linear-gradient(140deg, oklch(0.88 0.2 35), oklch(0.72 0.22 25));color:#1a0a04;">{{ $this->unreadSupportCount }} unread</span>
      @endif
    </div>

    @if($showCreateTicket)
      <div style="padding:24px;border-radius:16px;border:1px solid rgba(150,235,250,0.12);background:rgba(150,235,250,0.035);flex:1;">
        <div style="font-size:17px;font-weight:600;">Start a new chat</div>
        <p style="margin:8px 0 0;font-size:13px;line-height:1.55;color:rgba(214,238,248,0.72);">Describe your issue. An operator will join this conversation in real time.</p>
        <form wire:submit="createTicket" style="margin-top:20px;display:flex;flex-direction:column;gap:14px;">
          <div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:9.5px;letter-spacing:0.12em;color:rgba(214,238,248,0.68);">SUBJECT</div>
            <input type="text" wire:model="newSubject" maxlength="120" style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;">
            @error('newSubject')<div style="margin-top:8px;font-size:12.5px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
          </div>
          <div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:9.5px;letter-spacing:0.12em;color:rgba(214,238,248,0.68);">CATEGORY</div>
            <select wire:model="newCategory" style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;">
              @foreach($this->ticketCategories as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
            @error('newCategory')<div style="margin-top:8px;font-size:12.5px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
          </div>
          <div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:9.5px;letter-spacing:0.12em;color:rgba(214,238,248,0.68);">MESSAGE</div>
            <textarea wire:model="newBody" rows="6" maxlength="5000" placeholder="Write your first message to the support team..."
              style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;resize:vertical;"></textarea>
            @error('newBody')<div style="margin-top:8px;font-size:12.5px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
          </div>
          <div style="display:flex;gap:10px;">
            <button type="submit" style="padding:11px 18px;border-radius:10px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">Start chat</button>
            <button type="button" wire:click="cancelCreateTicket" style="padding:11px 18px;border-radius:10px;border:1px solid rgba(150,235,250,0.2);background:rgba(150,235,250,0.06);color:#e6f4fa;font-family:inherit;font-size:13px;cursor:pointer;">Cancel</button>
          </div>
        </form>
      </div>
    @elseif($this->selectedTicket)
      @php($ticket = $this->selectedTicket)
      <div style="padding:20px 24px;border-radius:16px;border:1px solid rgba(150,235,250,0.12);background:rgba(150,235,250,0.035);">
        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
          <div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(214,238,248,0.66);">{{ $ticket->reference }}</div>
            <div style="margin-top:10px;font-size:20px;font-weight:600;">{{ $ticket->subject }}</div>
            <div style="margin-top:6px;font-size:13px;color:rgba(214,238,248,0.72);">{{ $ticket->categoryLabel() }} · <span style="color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span></div>
          </div>
        </div>
      </div>

      <div id="support-thread" style="display:flex;flex-direction:column;gap:12px;flex:1;max-height:420px;overflow-y:auto;padding-right:4px;">
        @foreach($ticket->messages as $message)
          <div wire:key="support-message-{{ $message->id }}" data-message-id="{{ $message->id }}" style="padding:16px 18px;border-radius:14px;border:1px solid rgba(150,235,250,0.12);background:{{ $message->isFromAdmin() ? 'oklch(0.6 0.13 200 / 0.12)' : 'rgba(150,235,250,0.03)' }};">
            <div style="display:flex;justify-content:space-between;gap:12px;font-size:12px;color:rgba(214,238,248,0.66);">
              <span>{{ $message->authorLabel() }}</span>
              <span>{{ $message->created_at?->format('M j, Y H:i') }}</span>
            </div>
            <div style="margin-top:10px;font-size:14px;line-height:1.6;white-space:pre-wrap;">{{ $message->body }}</div>
          </div>
        @endforeach
      </div>

      @if($ticket->status !== \App\Models\SupportTicket::STATUS_CLOSED)
        <div style="padding:20px 24px;border-radius:16px;border:1px solid rgba(150,235,250,0.12);background:rgba(150,235,250,0.035);">
          <form wire:submit="sendTicketReply" style="display:flex;flex-direction:column;gap:12px;">
            <textarea wire:model="replyBody" rows="3" maxlength="5000" placeholder="Type a message to the operator..."
              style="width:100%;box-sizing:border-box;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;resize:vertical;"></textarea>
            @error('replyBody')<div style="font-size:12.5px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
            <button type="submit" style="align-self:flex-start;padding:11px 18px;border-radius:10px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">Send message</button>
          </form>
        </div>
      @else
        <div style="padding:18px;border-radius:14px;border:1px dashed rgba(150,235,250,0.18);font-size:13px;color:rgba(214,238,248,0.72);">This chat is closed. Start a new conversation if you need further help.</div>
      @endif
    @else
      <div style="padding:32px;border-radius:16px;border:1px dashed rgba(150,235,250,0.18);background:rgba(150,235,250,0.02);font-size:14px;line-height:1.6;color:rgba(214,238,248,0.72);flex:1;display:flex;flex-direction:column;justify-content:center;align-items:flex-start;gap:16px;">
        <div>Select a conversation on the left or start a new live chat with an operator.</div>
        <button type="button" wire:click="openCreateTicket" style="padding:11px 18px;border-radius:10px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">Start live chat</button>
      </div>
    @endif
  </div>
</section>
