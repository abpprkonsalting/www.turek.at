(function () {
    document.addEventListener("DOMContentLoaded", function (event) {
        var buildFirstEmailUrl = document.getElementById('cr-build-first-email-url').value;
        var retrySyncUrl = document.getElementById('cr-retry-sync-url').value;
        var buildEmailUrl = document.getElementById('cr-build-email-url').value;

        var buildEmailButton = document.getElementById('cr-buildEmail');
        var retrySynchronization = document.getElementById('cr-retrySync');

        if (buildEmailButton) {
            buildEmailButton.addEventListener('click', function () {
                startBuildingEmail(buildEmailUrl + '#login');
            });
        } else {
            retrySynchronization.addEventListener('click', function () {
                sendAjax(retrySyncUrl);
            });
        }

        function startBuildingEmail(buildEmailUrl)
        {
            sendAjax(buildFirstEmailUrl);
            var win = window.open(buildEmailUrl, '_blank');
            win.focus();
        }

        function sendAjax(url)
        {
            CleverReach.Ajax.post(url + '?form_key=' + window.FORM_KEY, null, function (response) {
                if (response.status === 'success') {
                    location.reload();
                }
            }, 'json', true);
        }
    });
})();