<section data-screen-label="Support" style="padding: 28px 32px 40px; display: grid; grid-template-columns: minmax(0, 340px) minmax(0, 1fr); gap: 16px; align-items: start;">
  <div style="padding: 22px; border-radius: 16px; border: 1px solid rgba(150,235,250,0.12); background: rgba(150,235,250,0.035);">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
      <span style="font-size:15px;font-weight:600;">Your tickets</span>
      <button wire:click="openCreateTicket" style="padding:8px 12px;border-radius:9px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:12.5px;font-weight:600;cursor:pointer;">New</button>
    </div>
    <div style="margin-top:18px;display:flex;flex-direction:column;gap:10px;">
      @forelse($tickets as $ticket)
        <button wire:click="selectTicket({{ $ticket->id }})" style="text-align:left;padding:14px 16px;border-radius:12px;border:1px solid {{ $selectedTicketId === $ticket->id ? 'oklch(0.86 0.11 195 / 0.45)' : 'rgba(150,235,250,0.12)' }};background:{{ $selectedTicketId === $ticket->id ? 'oklch(0.6 0.13 200 / 0.18)' : 'rgba(150,235,250,0.03)' }};color:inherit;font-family:inherit;cursor:pointer;">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:baseline;">
            <span style="font-size:14px;font-weight:600;">{{ $ticket->subject }}</span>
            <span style="font-family:'JetBrains Mono',monospace;font-size:10px;color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span>
          </div>
          <div style="margin-top:6px;font-family:'JetBrains Mono',monospace;font-size:10.5px;color:rgba(214,238,248,0.66);">{{ $ticket->reference }} · {{ $ticket->categoryLabel() }}</div>
          <div style="margin-top:6px;font-size:12px;color:rgba(214,238,248,0.62);">{{ $ticket->updated_at?->format('M j, H:i') }}</div>
        </button>
      @empty
        <div style="padding:16px;border-radius:12px;border:1px dashed rgba(150,235,250,0.18);font-size:13px;color:rgba(214,238,248,0.72);">No support tickets yet. Create one if you need help with withdrawals, contracts, or your account.</div>
      @endforelse
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px;">
    @if($showCreateTicket)
      <div style="padding:24px;border-radius:16px;border:1px solid rgba(150,235,250,0.12);background:rgba(150,235,250,0.035);">
        <div style="font-size:17px;font-weight:600;">New support ticket</div>
        <p style="margin:8px 0 0;font-size:13px;line-height:1.55;color:rgba(214,238,248,0.72);">Describe your issue. Our team will reply here in the dashboard.</p>
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
            <textarea wire:model="newBody" rows="6" maxlength="5000" style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;resize:vertical;"></textarea>
            @error('newBody')<div style="margin-top:8px;font-size:12.5px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
          </div>
          <div style="display:flex;gap:10px;">
            <button type="submit" style="padding:11px 18px;border-radius:10px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">Submit ticket</button>
            <button type="button" wire:click="cancelCreateTicket" style="padding:11px 18px;border-radius:10px;border:1px solid rgba(150,235,250,0.2);background:rgba(150,235,250,0.06);color:#e6f4fa;font-family:inherit;font-size:13px;cursor:pointer;">Cancel</button>
          </div>
        </form>
      </div>
    @elseif($this->selectedTicket)
      @php($ticket = $this->selectedTicket)
      <div style="padding:24px;border-radius:16px;border:1px solid rgba(150,235,250,0.12);background:rgba(150,235,250,0.035);">
        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
          <div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.12em;color:rgba(214,238,248,0.66);">{{ $ticket->reference }}</div>
            <div style="margin-top:10px;font-size:20px;font-weight:600;">{{ $ticket->subject }}</div>
            <div style="margin-top:6px;font-size:13px;color:rgba(214,238,248,0.72);">{{ $ticket->categoryLabel() }} · <span style="color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span></div>
          </div>
        </div>
      </div>

      <div id="support-thread" style="display:flex;flex-direction:column;gap:12px;">
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
        <div style="padding:24px;border-radius:16px;border:1px solid rgba(150,235,250,0.12);background:rgba(150,235,250,0.035);">
          <div style="font-size:15px;font-weight:600;">Add a reply</div>
          <form wire:submit="sendTicketReply" style="margin-top:16px;display:flex;flex-direction:column;gap:12px;">
            <textarea wire:model="replyBody" rows="4" maxlength="5000" placeholder="Write your message..."
              style="width:100%;box-sizing:border-box;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;resize:vertical;"></textarea>
            @error('replyBody')<div style="font-size:12.5px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
            <button type="submit" style="align-self:flex-start;padding:11px 18px;border-radius:10px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">Send reply</button>
          </form>
        </div>
      @else
        <div style="padding:18px;border-radius:14px;border:1px dashed rgba(150,235,250,0.18);font-size:13px;color:rgba(214,238,248,0.72);">This ticket is closed. Open a new ticket if you need further help.</div>
      @endif
    @else
      <div style="padding:28px;border-radius:16px;border:1px dashed rgba(150,235,250,0.18);background:rgba(150,235,250,0.02);font-size:14px;line-height:1.6;color:rgba(214,238,248,0.72);">
        Select a ticket from the list or create a new one. Typical topics: withdrawal delays, contract questions, KYC, and account access.
      </div>
    @endif
  </div>
</section>

@script
<script>
  $wire.on('support-thread-scroll', () => {
    requestAnimationFrame(() => {
      const thread = document.getElementById('support-thread');
      if (thread) {
        thread.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'end' });
      }
    });
  });
</script>
@endscript
