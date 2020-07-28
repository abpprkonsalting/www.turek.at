(function () {
    document.addEventListener("DOMContentLoaded", function (event) {
    
        function initialSyncCompleteHandler(response)
        {
            if (response.status === 'completed') {
                attachRedirectButtonClickHandler();
                renderStatistics(response.statistics);
                CleverReach.AutoRedirect.start(5000);
                showSuccessPanel();
            } else {
                doRedirect();
            }
        }
    
        function attachRedirectButtonClickHandler()
        {
            jQuery('[data-success-panel-go-to-dashboard-button]').click(doRedirect);
        }
    
        function renderStatistics(statistics)
        {
            var panelMessageEl, successMessage;
    
            panelMessageEl = jQuery('[data-success-panel-message]');
            if (panelMessageEl) {
                successMessage = panelMessageEl.html()
                    .replace('%s', statistics.recipients_count)
                    .replace('%s', statistics.group_name);

                panelMessageEl.html(successMessage);
            }
        }
    
        function showSuccessPanel()
        {
            jQuery('[data-task-list-panel]').addClass('hidden');
            jQuery('[data-success-panel]').removeClass('hidden');
        }
    
        function doRedirect()
        {
            location.reload();
        }
    
        CleverReach.StatusChecker.init({
            statusCheckUrl: document.getElementById('cr-admin-status-check-url').value  + '?form_key=' + window.FORM_KEY,
            baseSelector: '.cr-container',
            finishedStatus: 'completed',
            onComplete: initialSyncCompleteHandler,
            pendingStatusClses: ['cr-icofont-circle'],
            inProgressStatusClses: ['cr-icofont-loader'],
            doneStatusClses: ['cr-icofont-check']
        });
    });
})();