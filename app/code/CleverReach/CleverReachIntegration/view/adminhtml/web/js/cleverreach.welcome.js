(function () {
    document.addEventListener("DOMContentLoaded", function (event) {
        var authUrl = document.getElementById('cr-auth-url').value;
        var checkStatusUrl = document.getElementById('cr-check-status-url').value;

        var loginButton = document.getElementById('cr-log-account');
        var createAccountButton = document.getElementById('cr-new-account');

        if (loginButton) {
            loginButton.addEventListener('click', function () {
                startAuthProcess(authUrl + '#login');
            });
        }

        if (createAccountButton) {
            createAccountButton.addEventListener('click', function () {
                startAuthProcess(authUrl + '#register');
            });
        }

        function startAuthProcess(authUrl)
        {
            showSpinner();

            var auth = new CleverReach.Authorization(authUrl, checkStatusUrl);
            auth.checkConnectionStatus(function () {
                location.reload();
            });
        }

        function showSpinner()
        {
            document.getElementsByClassName('cr-loader-big')[0].style.display = 'flex';
            document.getElementsByClassName('cr-connecting')[0].style.display = 'block';
            document.getElementsByClassName('cr-content-window-wrapper')[0].style.display = 'none';
        }
    });
})();
