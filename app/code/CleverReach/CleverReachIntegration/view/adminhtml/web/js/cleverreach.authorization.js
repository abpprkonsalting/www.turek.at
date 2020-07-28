
var CleverReach = CleverReach || {};

/**
 * Checks connection status
 */
(function () {

    /**
     * Configurations and constants
     *
     * @type {{get}}
     */
    var config = (function () {
        var constants = {
            STATUS_FINISHED: 'finished'
        };

        return {
            get: function (name) {
                return constants[name];
            }
        };
    })();

    function AuthorizationConstructor(authUrl, checkLoginStatusUrl)
    {
        this.checkConnectionStatus = function (successCallback) {
            var authWin = window.open(
                authUrl,
                'authWindow',
                'toolbar=0, location=0, menubar=0, width=600, height=650'
            );

            var winClosed = setInterval(function () {
                if (authWin.closed) {
                    clearInterval(winClosed);
                    getStatus();
                }
            }, 250);

            function getStatus()
            {
                CleverReach.Ajax.post(checkLoginStatusUrl + '?form_key=' + window.FORM_KEY, null, function (response) {
                    if (response.status === config.get('STATUS_FINISHED')) {
                        successCallback();
                    } else {
                        setTimeout(getStatus, 250);
                    }
                } , 'json', true);
            }
        };
    }

    CleverReach.Authorization = AuthorizationConstructor;
})();

