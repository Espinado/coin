import * as VoxImplant from 'voximplant-websdk';

function readCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function setStatus(root, message, isError = false) {
    const status = root.querySelector('[data-vox-status]');

    if (! status) {
        return;
    }

    status.textContent = message;
    status.style.color = isError ? '#ff8f8f' : 'rgba(232,237,245,0.78)';
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

export function bootAdminVoxCall(root) {
    if (! root) {
        return;
    }

    const sdk = VoxImplant.getInstance();
    const destination = root.dataset.destination || '';
    const callerId = root.dataset.callerId || '';
    const connectButton = root.querySelector('[data-vox-connect]');
    const callButton = root.querySelector('[data-vox-call]');
    const hangupButton = root.querySelector('[data-vox-hangup]');
    let activeCall = null;

    const refreshButtons = () => {
        const loggedIn = sdk.getClientState() === VoxImplant.ClientState.LOGGED_IN;
        connectButton.hidden = loggedIn;
        callButton.hidden = ! loggedIn;
        hangupButton.hidden = ! activeCall;
    };

    sdk.addEventListener(VoxImplant.Events.IncomingCall, (event) => {
        activeCall = event.call;
        refreshButtons();
    });

    connectButton?.addEventListener('click', async () => {
        connectButton.disabled = true;

        try {
            await ensureLoggedIn(root, sdk);
            setStatus(root, root.dataset.statusReady || 'Ready.');
        } catch (error) {
            setStatus(root, error.message || 'Connection failed.', true);
        } finally {
            connectButton.disabled = false;
            refreshButtons();
        }
    });

    callButton?.addEventListener('click', async () => {
        if (! destination) {
            setStatus(root, root.dataset.statusNoPhone || 'Phone is missing.', true);
            return;
        }

        callButton.disabled = true;

        try {
            await ensureLoggedIn(root, sdk);
            setStatus(root, root.dataset.statusCalling || 'Calling…');

            activeCall = sdk.call(destination, false, JSON.stringify({
                destination,
                caller_id: callerId,
            }));
            refreshButtons();
        } catch (error) {
            setStatus(root, error.message || 'Call failed.', true);
        } finally {
            callButton.disabled = false;
        }
    });

    hangupButton?.addEventListener('click', () => {
        activeCall?.hangup();
        activeCall = null;
        refreshButtons();
        setStatus(root, root.dataset.statusReady || 'Ready.');
    });

    refreshButtons();
}

const root = document.getElementById('admin-vox-call');

if (root) {
    bootAdminVoxCall(root);
}
