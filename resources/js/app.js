import './bootstrap';
import { initEcho } from './echo';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    initEcho();
}

Alpine.start();
