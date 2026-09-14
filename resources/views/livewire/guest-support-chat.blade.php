<div id="guest-support-root" style="position:fixed;inset:0;z-index:9999;pointer-events:none;">
    @if(! $isOpen)
    <button type="button" wire:click="openChat" data-open-guest-support
        style="pointer-events:auto;position:fixed;right:24px;bottom:24px;display:flex;align-items:center;gap:10px;padding:14px 20px;border-radius:999px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:'Sora','Helvetica Neue',Helvetica,sans-serif;font-size:14px;font-weight:600;cursor:pointer;box-shadow:0 20px 50px -22px oklch(0.8 0.13 195 / 0.7);">
        <span style="width:10px;height:10px;border-radius:50%;background:oklch(0.85 0.15 160);box-shadow:0 0 12px oklch(0.85 0.15 160);"></span>
        Support
    </button>
    @endif

    @if($isOpen)
        <div wire:click="closeChat" style="pointer-events:auto;position:fixed;inset:0;background:rgba(4,16,28,0.55);backdrop-filter:blur(4px);"></div>

        <div style="pointer-events:auto;position:fixed;right:24px;bottom:24px;width:min(420px,calc(100vw - 32px));max-height:min(680px,calc(100vh - 48px));display:flex;flex-direction:column;border-radius:18px;border:1px solid rgba(150,235,250,0.18);background:#061423;color:#e6f4fa;font-family:'Sora','Helvetica Neue',Helvetica,sans-serif;box-shadow:0 28px 80px -24px rgba(0,0,0,0.65);overflow:hidden;">
            <div style="padding:18px 20px;border-bottom:1px solid rgba(150,235,250,0.12);display:flex;align-items:center;justify-content:space-between;gap:12px;background:rgba(150,235,250,0.04);">
                <div>
                    <div style="font-size:16px;font-weight:600;">Live support</div>
                    <div style="margin-top:4px;font-size:12px;color:rgba(214,238,248,0.68);">Operators reply in real time</div>
                </div>
                <button type="button" wire:click="closeChat" aria-label="Close support chat"
                    style="width:34px;height:34px;border-radius:10px;border:1px solid rgba(150,235,250,0.18);background:rgba(150,235,250,0.06);color:#e6f4fa;font-size:18px;line-height:1;cursor:pointer;">×</button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:18px 20px;display:flex;flex-direction:column;gap:14px;">
                @if($this->selectedTicket)
                    @php($ticket = $this->selectedTicket)
                    <div style="padding:14px 16px;border-radius:12px;border:1px solid rgba(150,235,250,0.12);background:rgba(150,235,250,0.035);">
                        <div style="font-family:'JetBrains Mono',monospace;font-size:10px;color:rgba(214,238,248,0.66);">{{ $ticket->reference }}</div>
                        <div style="margin-top:8px;font-size:16px;font-weight:600;">{{ $ticket->subject }}</div>
                        <div style="margin-top:6px;font-size:12px;color:rgba(214,238,248,0.72);">{{ $ticket->categoryLabel() }} · {{ $guestEmail }}</div>
                    </div>

                    <div id="guest-support-thread" wire:ignore.self style="display:flex;flex-direction:column;gap:12px;max-height:320px;overflow-y:auto;padding-right:4px;">
                        @foreach($ticket->messages as $message)
                            <div wire:key="guest-support-message-{{ $message->id }}" data-message-id="{{ $message->id }}" style="padding:14px 16px;border-radius:14px;border:1px solid rgba(150,235,250,0.12);background:{{ $message->isFromAdmin() ? 'oklch(0.6 0.13 200 / 0.12)' : 'rgba(150,235,250,0.03)' }};">
                                <div style="display:flex;justify-content:space-between;gap:12px;font-size:12px;color:rgba(214,238,248,0.66);">
                                    <span>{{ $message->authorLabel() }}</span>
                                    <span>{{ $message->created_at?->format('M j, H:i') }}</span>
                                </div>
                                <div style="margin-top:8px;font-size:14px;line-height:1.6;white-space:pre-wrap;">{{ $message->body }}</div>
                            </div>
                        @endforeach
                    </div>

                    @if($ticket->status !== \App\Models\SupportTicket::STATUS_CLOSED)
                        <form wire:submit.prevent="sendReply" wire:key="guest-support-reply-{{ $ticket->id }}-{{ $replyFormKey }}" style="display:flex;flex-direction:column;gap:10px;">
                            <textarea wire:model="replyBody" rows="3" maxlength="5000" placeholder="Type a message..."
                                style="width:100%;box-sizing:border-box;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;resize:vertical;"></textarea>
                            @error('replyBody')<div style="font-size:12px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
                            <button type="submit" style="align-self:flex-start;padding:11px 18px;border-radius:10px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">Send message</button>
                        </form>
                    @else
                        <div style="padding:14px;border-radius:12px;border:1px dashed rgba(150,235,250,0.18);font-size:13px;color:rgba(214,238,248,0.72);">This chat is closed.</div>
                    @endif
                @else
                    <div style="font-size:15px;font-weight:600;">Start a live chat</div>
                    <p style="margin:0;font-size:13px;line-height:1.55;color:rgba(214,238,248,0.72);">Enter your email so we can reply. An operator will join this conversation in real time.</p>
                    <form wire:submit.prevent="createTicket" style="display:flex;flex-direction:column;gap:12px;">
                        <div>
                            <div style="font-family:'JetBrains Mono',monospace;font-size:9.5px;letter-spacing:0.12em;color:rgba(214,238,248,0.68);">EMAIL *</div>
                            <input type="email" wire:model="guestEmail" required maxlength="255" placeholder="you@example.com"
                                style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;">
                            @error('guestEmail')<div style="margin-top:8px;font-size:12px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <div style="font-family:'JetBrains Mono',monospace;font-size:9.5px;letter-spacing:0.12em;color:rgba(214,238,248,0.68);">SUBJECT</div>
                            <input type="text" wire:model="newSubject" maxlength="120"
                                style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;">
                            @error('newSubject')<div style="margin-top:8px;font-size:12px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <div style="font-family:'JetBrains Mono',monospace;font-size:9.5px;letter-spacing:0.12em;color:rgba(214,238,248,0.68);">CATEGORY</div>
                            <select wire:model="newCategory" style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;">
                                @foreach($this->ticketCategories as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div style="font-family:'JetBrains Mono',monospace;font-size:9.5px;letter-spacing:0.12em;color:rgba(214,238,248,0.68);">MESSAGE</div>
                            <textarea wire:model="newBody" rows="4" maxlength="5000" placeholder="How can we help?"
                                style="width:100%;box-sizing:border-box;margin-top:8px;padding:12px 14px;border-radius:10px;border:1px solid rgba(150,235,250,0.16);background:rgba(4,16,28,0.6);color:#eafcff;font-size:14px;resize:vertical;"></textarea>
                            @error('newBody')<div style="margin-top:8px;font-size:12px;color:oklch(0.78 0.16 25);">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" style="padding:11px 18px;border-radius:10px;border:1px solid oklch(0.86 0.11 195 / 0.5);background:linear-gradient(140deg, oklch(0.86 0.12 192), oklch(0.66 0.13 205));color:#04121f;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">Start chat</button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
