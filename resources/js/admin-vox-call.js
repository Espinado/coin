import * as VoxImplant from 'voximplant-websdk';

function readCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function setStatus(root, message, isError = false) {
    const status = root.querySelector('[data-vox-status]');

    if (! status) {
        return;
    }

    const hideStatuses = [
        root.dataset.statusIdle,
        root.dataset.statusReady,
    ].filter(Boolean);

    if (! isError && (message === '' || hideStatuses.includes(message))) {
        status.hidden = true;
        status.textContent = '';

        return;
    }

    status.hidden = false;
    status.textContent = message;
    status.style.color = isError ? '#ff8f8f' : 'rgba(232,237,245,0.78)';
}

function formatDuration(totalSeconds) {
    const seconds = Math.max(0, totalSeconds);
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainder = seconds % 60;

    if (hours > 0) {
        return `${hours}:${String(minutes).padStart(2, '0')}:${String(remainder).padStart(2, '0')}`;
    }

    return `${String(minutes).padStart(2, '0')}:${String(remainder).padStart(2, '0')}`;
}

function createModalController(modal, labels) {
    const title = modal.querySelector('[data-vox-modal-title]');
    const subtitle = modal.querySelector('[data-vox-modal-subtitle]');
    const timer = modal.querySelector('[data-vox-modal-timer]');
    const duration = modal.querySelector('[data-vox-modal-duration]');
    const spinner = modal.querySelector('[data-vox-modal-spinner]');
    const hangupButton = modal.querySelector('[data-vox-modal-hangup]');
    const closeButton = modal.querySelector('[data-vox-modal-close]');

    let timerInterval = null;
    let connectedAt = null;
    let state = 'hidden';

    const stopTimer = () => {
        if (timerInterval !== null) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    };

    const updateTimerDisplay = () => {
        if (connectedAt === null || ! timer) {
            return;
        }

        const elapsed = Math.floor((Date.now() - connectedAt) / 1000);
        timer.textContent = formatDuration(elapsed);
    };

    const show = () => {
        modal.hidden = false;
        modal.style.display = 'flex';
    };

    const hide = () => {
        stopTimer();
        connectedAt = null;
        state = 'hidden';
        modal.hidden = true;
        modal.style.display = 'none';
    };

    return {
        hangupButton,
        closeButton,
        isActive: () => state === 'dialing' || state === 'connected',
        showDialing(userName, phone) {
            state = 'dialing';
            show();
            if (spinner) {
                spinner.hidden = false;
            }
            if (title) {
                title.textContent = labels.dialing;
            }
            if (subtitle) {
                subtitle.textContent = labels.toUser
                    .replace(':name', userName)
                    .replace(':phone', phone);
            }
            if (timer) {
                timer.style.display = 'none';
            }
            if (duration) {
                duration.style.display = 'none';
            }
            if (hangupButton) {
                hangupButton.hidden = false;
            }
            if (closeButton) {
                closeButton.hidden = true;
            }
        },
        showConnected() {
            state = 'connected';
            connectedAt = Date.now();
            if (spinner) {
                spinner.hidden = true;
            }
            if (title) {
                title.textContent = labels.connected;
            }
            if (timer) {
                timer.style.display = 'block';
                timer.textContent = '00:00';
            }
            stopTimer();
            timerInterval = window.setInterval(updateTimerDisplay, 1000);
            updateTimerDisplay();
        },
        showEnded(talkSeconds, failed = false) {
            state = 'ended';
            stopTimer();

            if (spinner) {
                spinner.hidden = true;
            }
            if (title) {
                title.textContent = failed ? labels.failed : labels.ended;
            }
            if (timer) {
                timer.style.display = 'none';
            }
            if (duration) {
                duration.style.display = 'block';
                duration.textContent = labels.duration.replace(':duration', formatDuration(talkSeconds));
            }
            if (hangupButton) {
                hangupButton.hidden = true;
            }
            if (closeButton) {
                closeButton.hidden = false;
            }
        },
        hide,
        getTalkSeconds() {
            if (connectedAt === null) {
                return 0;
            }

            return Math.floor((Date.now() - connectedAt) / 1000);
        },
    };
}

async function requestOneTimeHash(oneTimeKeyUrl, key) {
    const response = await fetch(oneTimeKeyUrl, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': readCsrfToken(),
        },
        body: JSON.stringify({ key }),
    });

    const payload = await response.json();

    if (! response.ok) {
        throw new Error(payload.message || 'Voximplant auth failed.');
    }

    return payload;
}

function resolveConnectionNode(nodeName) {
    if (! nodeName) {
        return VoxImplant.ConnectionNode.NODE_8;
    }

    return VoxImplant.ConnectionNode[nodeName] ?? nodeName;
}

function getAudioDeviceManager() {
    return VoxImplant.Hardware.AudioDeviceManager.get();
}

function prepareCallHardware() {
    getAudioDeviceManager().prepareAudioContext();
}

async function ensureMicrophoneAccess(root) {
    if (! navigator.mediaDevices?.getUserMedia) {
        throw new Error(root.dataset.statusMicUnsupported || 'Microphone is not supported in this browser.');
    }

    prepareCallHardware();
    setStatus(root, root.dataset.statusRequestingMic || 'Requesting microphone access…');

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        stream.getTracks().forEach((track) => track.stop());
    } catch {
        throw new Error(root.dataset.statusMicDenied || 'Microphone access denied.');
    }
}

function playAudioRenderer(renderer, audioSink) {
    if (! renderer || renderer.kind !== VoxImplant.MediaRendererKind.Audio) {
        return;
    }

    renderer.enable();
    renderer.setVolume(1);

    if (audioSink) {
        renderer.render(audioSink);
    }

    const playPromise = renderer.element?.play?.();

    if (playPromise) {
        playPromise.catch(() => {});
    }
}

function wireCallAudio(call, audioSink) {
    const wireEndpoint = (endpoint) => {
        endpoint.mediaRenderers?.forEach((renderer) => playAudioRenderer(renderer, audioSink));
        endpoint.on(VoxImplant.EndpointEvents.RemoteMediaAdded, (event) => {
            playAudioRenderer(event.mediaRenderer, audioSink);
        });
    };

    call.on(VoxImplant.CallEvents.EndpointAdded, (event) => {
        wireEndpoint(event.endpoint);
    });

    call.getEndpoints().forEach(wireEndpoint);
}

async function ensureLoggedIn(root, sdk) {
    if (sdk.getClientState() === VoxImplant.ClientState.LOGGED_IN) {
        return;
    }

    const username = root.dataset.username;
    const oneTimeKeyUrl = root.dataset.oneTimeKeyUrl;

    if (! username || ! oneTimeKeyUrl) {
        throw new Error('Voximplant is not configured.');
    }

    setStatus(root, root.dataset.statusConnecting || 'Connecting…');

    if (! sdk.alreadyInitialized) {
        await sdk.init({
            node: resolveConnectionNode(root.dataset.node),
            micRequired: true,
        });
    }

    if (sdk.getClientState() !== VoxImplant.ClientState.CONNECTED
        && sdk.getClientState() !== VoxImplant.ClientState.LOGGING_IN
        && sdk.getClientState() !== VoxImplant.ClientState.LOGGED_IN) {
        await sdk.connect();
    }

    await new Promise((resolve, reject) => {
        const onAuthResult = async (event) => {
            sdk.removeEventListener(VoxImplant.Events.AuthResult, onAuthResult);

            if (event.result) {
                resolve();
                return;
            }

            if (event.code !== 302 || ! event.key) {
                reject(new Error('Voximplant authorization failed.'));
                return;
            }

            try {
                const payload = await requestOneTimeHash(oneTimeKeyUrl, event.key);
                await sdk.loginWithOneTimeKey(payload.username, payload.hash);
                resolve();
            } catch (error) {
                reject(error);
            }
        };

        sdk.addEventListener(VoxImplant.Events.AuthResult, onAuthResult);
        sdk.requestOneTimeLoginKey(username);
    });
}

function attachCallListeners(call, modal, root, onClear, audioSink) {
    let finished = false;

    wireCallAudio(call, audioSink);

    const finish = (failed = false, reason = '') => {
        if (finished) {
            return;
        }

        finished = true;
        const talkSeconds = modal.getTalkSeconds();
        modal.showEnded(talkSeconds, failed);
        onClear();

        if (failed && reason) {
            setStatus(root, reason, true);
            return;
        }

        setStatus(root, failed
            ? (root.dataset.statusFailed || 'Call failed.')
            : (root.dataset.statusEnded || 'Call ended.'), failed);
    };

    call.on(VoxImplant.CallEvents.Connected, () => {
        call.getEndpoints().forEach((endpoint) => {
            endpoint.mediaRenderers?.forEach((renderer) => playAudioRenderer(renderer, audioSink));
        });
        modal.showConnected();
        setStatus(root, root.dataset.statusConnected || 'Connected.');
    });

    call.on(VoxImplant.CallEvents.Disconnected, () => {
        finish(modal.getTalkSeconds() === 0);
    });

    call.on(VoxImplant.CallEvents.Failed, (event) => {
        finish(true, event?.reason || root.dataset.statusFailed || 'Call failed.');
    });
}

export function bootAdminVoxCall(root, modalElement) {
    if (! root || ! modalElement) {
        return;
    }

    modalElement.hidden = true;
    modalElement.style.display = 'none';

    const sdk = VoxImplant.getInstance();
    const destination = root.dataset.destination || '';
    const callerId = root.dataset.callerId || '';
    const userName = root.dataset.userName || '';
    const callButton = root.querySelector('[data-vox-call]');
    const audioSink = modalElement.querySelector('[data-vox-modal-audio]');
    let activeCall = null;

    const modal = createModalController(modalElement, {
        dialing: root.dataset.labelDialing || 'Dialing…',
        connected: root.dataset.labelConnected || 'Connected',
        ended: root.dataset.labelEnded || 'Call ended',
        failed: root.dataset.labelFailed || 'Call failed',
        duration: root.dataset.labelDuration || 'Duration: :duration',
        toUser: root.dataset.labelToUser || ':name · :phone',
    });

    const refreshButtons = () => {
        const inCall = modal.isActive();

        if (callButton) {
            callButton.disabled = inCall;
        }
    };

    const clearActiveCall = () => {
        activeCall = null;
        refreshButtons();
    };

    sdk.addEventListener(VoxImplant.Events.IncomingCall, (event) => {
        activeCall = event.call;
        modal.showDialing(userName, destination);
        attachCallListeners(activeCall, modal, root, clearActiveCall, audioSink);
        refreshButtons();
    });

    callButton?.addEventListener('click', async () => {
        if (! destination) {
            setStatus(root, root.dataset.statusNoPhone || 'Phone is missing.', true);
            return;
        }

        callButton.disabled = true;
        modal.showDialing(userName, destination);
        refreshButtons();

        try {
            await ensureMicrophoneAccess(root);
            await ensureLoggedIn(root, sdk);
            setStatus(root, root.dataset.statusCalling || 'Calling…');

            activeCall = sdk.call({
                number: 'outbound',
                video: false,
                customData: JSON.stringify({
                    destination,
                    caller_id: callerId,
                }),
                extraHeaders: {
                    'X-Destination': destination,
                    'X-Caller-Id': callerId,
                },
            });

            attachCallListeners(activeCall, modal, root, clearActiveCall, audioSink);
        } catch (error) {
            modal.showEnded(0, true);
            clearActiveCall();
            setStatus(root, error.message || 'Call failed.', true);
        } finally {
            callButton.disabled = false;
            refreshButtons();
        }
    });

    modal.hangupButton?.addEventListener('click', () => {
        if (activeCall) {
            activeCall.hangup();
            return;
        }

        modal.showEnded(modal.getTalkSeconds(), false);
        clearActiveCall();
    });

    modal.closeButton?.addEventListener('click', () => {
        modal.hide();
        if (sdk.getClientState() === VoxImplant.ClientState.LOGGED_IN) {
            setStatus(root, root.dataset.statusReady || 'Ready.');
        } else {
            setStatus(root, root.dataset.statusIdle || 'Idle.');
        }
        refreshButtons();
    });

    refreshButtons();
}

const root = document.getElementById('admin-vox-call');
const modal = document.getElementById('admin-vox-call-modal');

if (root && modal) {
    bootAdminVoxCall(root, modal);
}
