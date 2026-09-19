/* CloudFlops — bridge Web SDK / API outbound calls to PSTN. */

function readConfig() {
    try {
        return JSON.parse(VoxEngine.customData() || '{}');
    } catch (e) {
        return {};
    }
}

function dialPstn(destination, callerId) {
    if (!destination) {
        return null;
    }

    return VoxEngine.callPSTN(destination, callerId || '');
}

VoxEngine.addEventListener(AppEvents.Started, function () {
    var data = readConfig();

    if (!data.destination) {
        return;
    }

    dialPstn(data.destination, data.caller_id || '');
});

VoxEngine.addEventListener(AppEvents.CallAlerting, function (event) {
    var incoming = event.call;
    var config = readConfig();
    incoming.addEventListener(CallEvents.Connected, function () {
        var headers = incoming.headers() || {};
        var callerId = config.caller_id || headers['X-Caller-Id'] || '';
        var destination = config.destination || incoming.number();

        if (!destination || destination.charAt(0) !== '+') {
            destination = headers['X-Destination'] || headers['VI-CallData'] || destination;
        }

        if (!destination) {
            incoming.hangup();
            return;
        }

        var outbound = dialPstn(destination, callerId);

        if (!outbound) {
            incoming.hangup();
            return;
        }

        VoxEngine.easyProcess([incoming, outbound]);
    });

    incoming.answer();
});
