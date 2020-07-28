(function () {
    document.addEventListener("DOMContentLoaded", function (event) {
        var testConfigurationUrl = document.getElementById('cr-test-server-configuration-url').value;
        var statusCheckUrl = document.getElementById('cr-check-status-url').value;
        var autoconfigureFailed = document.getElementById('cr-autoconfigure-failed').value;
        var retryTest = document.getElementById('cr-retryTest');

        retryTest.addEventListener('click', function () {
            showSpinner();
            startConfigurationTest(testConfigurationUrl);
            setTimeout(checkConfigurationStatus, 250);
        });

        if (autoconfigureFailed) {
            showErrorPage();
        } else {
            checkConfigurationStatus();
        }

        function showSpinner()
        {
            document.getElementsByClassName('cr-loader-big')[0].classList.remove('hidden');
            document.getElementsByClassName('cr-connecting')[0].classList.remove('hidden');
            document.getElementsByClassName('cr-content-window-wrapper')[0].classList.add('hidden');
        }

        function showErrorPage()
        {
            document.getElementsByClassName('cr-loader-big')[0].classList.add('hidden');
            document.getElementsByClassName('cr-connecting')[0].classList.add('hidden');
            document.getElementsByClassName('cr-content-window-wrapper')[0].classList.remove('hidden');
        }

        function startConfigurationTest(url)
        {
            CleverReach.Ajax.post(url + '?form_key=' + window.FORM_KEY, null, function (response) {}, 'json', true);
        }

        function checkConfigurationStatus()
        {
            CleverReach.Ajax.post(statusCheckUrl + '?form_key=' + window.FORM_KEY, null, function (response) {
                switch (response.status) {
                    case 'in_progress':
                        setTimeout(checkConfigurationStatus, 250);
                        break;
                    case 'success':
                        location.reload();
                        break;
                    case 'failed':
                        showErrorPage();
                        break;
                    default:
                        startConfigurationTest(testConfigurationUrl);
                        setTimeout(checkConfigurationStatus, 250);
                }
            }, 'json', true);
        }
    });
})();
