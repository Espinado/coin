/* CloudFlops — bridge Web SDK / API outbound calls to PSTN. */

function readEngineConfig() {
    try {
        return JSON.parse(VoxEngine.customData() || '{}');
    } catch (e) {
        return {};
    }
}

function readCallConfig(event) {
    var config = readEngineConfig();

    if (event && event.customData) {
        try {
            var payload = JSON.parse(event.customData);

            if (payload.destination) {
                config.destination = payload.destination;
            }

            if (payload.caller_id) {
                config.caller_id = payload.caller_id;
            }
        } catch (e) {}
    }

    if (event) {
        if (!config.destination && event.destination) {
            config.destination = event.destination;
        }

        var headers = event.headers || {};

        if (!config.destination && headers['X-Destination']) {
            config.destination = headers['X-Destination'];
        }

        if (!config.caller_id && headers['X-Caller-Id']) {
            config.caller_id = headers['X-Caller-Id'];
        }
    }

    return config;
}

function normalizeE164(value) {
    if (!value) {
        return '';
    }

    var digits = String(value).replace(/\D/g, '');

    if (!digits) {
        return '';
    }

    return '+' + digits;
}

function dialPstn(destination, callerId) {
    if (!destination) {
        return null;
    }

    return VoxEngine.callPSTN(destination, callerId || '');
}

function notifyBrowser(incoming, type, details) {
    try {
        incoming.sendMessage(JSON.stringify({
            type: type,
            details: details || {},
        }));
    } catch (e) {
        Logger.write('CloudFlops bridge: sendMessage failed');
    }
}

function bridgeWebToPstn(event) {
    var incoming = event.call;
    var config = readCallConfig(event);
    var destination = normalizeE164(config.destination);
    var callerId = normalizeE164(config.caller_id) || String(config.caller_id || '');
    var mediaBridged = false;

    if (!destination) {
        Logger.write('CloudFlops bridge: missing destination');
        incoming.hangup();
        return;
    }

    // Keep the browser leg alive while the PSTN callee is ringing.
    incoming.answer();

    var outbound = dialPstn(destination, callerId);

    if (!outbound) {
        Logger.write('CloudFlops bridge: PSTN dial failed for ' + destination);
        notifyBrowser(incoming, 'pstn_failed', { reason: 'dial_failed' });
        incoming.hangup();
        return;
    }

    var bridgeMedia = function () {
        if (mediaBridged) {
            return;
        }

        mediaBridged = true;
        VoxEngine.sendMediaBetween(incoming, outbound);
        notifyBrowser(incoming, 'pstn_connected');
    };

    outbound.addEventListener(CallEvents.Connected, bridgeMedia);
    outbound.addEventListener(CallEvents.AudioStarted, bridgeMedia);

    outbound.addEventListener(CallEvents.Ringing, function () {
        notifyBrowser(incoming, 'pstn_ringing');
    });

    var hangupBoth = function () {
        try {
            outbound.hangup();
        } catch (e) {}

        try {
            incoming.hangup();
        } catch (e) {}

        VoxEngine.terminate();
    };

    incoming.addEventListener(CallEvents.Disconnected, function () {
        try {
            outbound.hangup();
        } catch (e) {}

        VoxEngine.terminate();
    });

    incoming.addEventListener(CallEvents.Failed, function () {
        notifyBrowser(incoming, 'pstn_failed', { reason: 'browser_failed' });
        hangupBoth();
    });

    outbound.addEventListener(CallEvents.Disconnected, function () {
        try {
            incoming.hangup();
        } catch (e) {}

        VoxEngine.terminate();
    });

    outbound.addEventListener(CallEvents.Failed, function (e) {
        Logger.write('CloudFlops PSTN failed: ' + (e.reason || e.code || 'unknown'));
        notifyBrowser(incoming, 'pstn_failed', {
            reason: e.reason || e.code || 'unknown',
        });
        hangupBoth();
    });
}

VoxEngine.addEventListener(AppEvents.Started, function () {
    var config = readEngineConfig();
    var destination = normalizeE164(config.destination);

    if (!destination) {
        return;
    }

    var outbound = dialPstn(destination, config.caller_id || '');

    if (outbound) {
        outbound.addEventListener(CallEvents.Disconnected, VoxEngine.terminate);
        outbound.addEventListener(CallEvents.Failed, VoxEngine.terminate);
    }
});

VoxEngine.addEventListener(AppEvents.CallAlerting, bridgeWebToPstn);
