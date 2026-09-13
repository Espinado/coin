import './bootstrap';
import { initEcho } from './echo';
import { showSupportToast } from './support-toast';
import { appendSupportMessage, scrollSupportThreadToBottom } from './support-chat';

window.showSupportToast = showSupportToast;
window.appendSupportMessage = appendSupportMessage;
window.scrollSupportThreadToBottom = scrollSupportThreadToBottom;

import Alpine from 'alpinejs';

window.Alpine = Alpine;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    initEcho();
}

// Livewire pages ship with wire:id and start Alpine themselves.
if (! document.querySelector('[wire\\:id]')) {
    Alpine.start();
}
