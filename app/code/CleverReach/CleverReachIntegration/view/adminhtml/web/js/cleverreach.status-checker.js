
var CleverReach = CleverReach || {};

(function () {
    var config = {};

    function statusCheckerInit(initialConfig)
    {
        initialConfig = initialConfig || {};
        if (!initialConfig.statusCheckUrl) {
            throw 'CleverReach.StatusChecker: Configuration for statusCheckUrl is mandatory';
        }

        config.statusCheckUrl = initialConfig.statusCheckUrl;
        config.inProgressStatus = initialConfig.inProgressStatus || 'in_progress';
        config.finishedStatus = initialConfig.finishedStatus || 'finished';
        config.failedStatus = initialConfig.failedStatus || 'failed';
        config.onComplete = initialConfig.onComplete || function () {};
        config.onCompleteCallbackScope = initialConfig.onCompleteCallbackScope || window;
        config.finishedStatus = initialConfig.finishedStatus || 'finished';
        config.taskNames = initialConfig.taskNames || ['subscriberlist', 'add_fields', 'recipient_sync'];
        config.baseSelector = initialConfig.baseSelector || '.clever-reach';
        config.disabledCls = initialConfig.disabledCls || 'disabled';
        config.hiddenCls = initialConfig.hiddenCls || 'hidden';
        config.pendingStatusClses = initialConfig.pendingStatusClses || ['pending'];
        config.inProgressStatusClses = initialConfig.inProgressStatusClses || ['in_progress'];
        config.doneStatusClses = initialConfig.doneStatusClses || ['done'];

        disableAllTasks();
        checkStatus();
    }

    function checkStatus()
    {
        //url, data, callback, format, async
        CleverReach.Ajax.post(config.statusCheckUrl, null, function (response) {
            if (response.taskStatuses) {
                refreshTasks(response.taskStatuses);
            }

            if ((response.status !== config.finishedStatus) && (response.status !== config.failedStatus)) {
                setTimeout(checkStatus, 250);
            } else {
                config.onComplete.call(config.onCompleteCallbackScope, response);
            }
        }, 'json', true);
    }

    function disableAllTasks()
    {
        var taskStates = {}, i;

        for (i = 0; i < config.taskNames.length; i++) {
            taskStates[config.taskNames[i]] = {"status": "pending", 'progress': 0};
        }

        refreshTasks(taskStates);
    }

    function refreshTasks(taskStatuses)
    {
        var taskName, taskStatus;
        for (taskName in taskStatuses) {
            if (taskStatuses.hasOwnProperty(taskName)) {
                taskStatus = taskStatuses[taskName];
                refreshTask(taskName, taskStatus);
            }
        }
    }

    function refreshTask(taskName, taskStatus)
    {
        switch (taskStatus.status) {
            case config.inProgressStatus:
                setTaskInProgress(taskName, taskStatus.progress);
                break;
            case config.finishedStatus:
                setTaskFinished(taskName);
                break;
            default:
                setTaskPending(taskName);
                break
        }
    }

    function setTaskInProgress(taskName, progress)
    {
        var taskEls, taskStatusEls, taskProgressEls, i;

        taskEls = document.querySelectorAll(config.baseSelector + ' [data-task="'+ taskName +'"]');
        taskStatusEls = document.querySelectorAll(config.baseSelector + ' [data-status="'+ taskName +'"]');
        taskProgressEls = document.querySelectorAll(config.baseSelector + ' [data-progress="'+ taskName +'"]');
        for (i = 0; i < taskEls.length; i++) {
            enable(taskEls[i]);
        }

        for (i = 0; i < taskStatusEls.length; i++) {
            setInProgressState(taskStatusEls[i]);
        }

        for (i = 0; i < taskProgressEls.length; i++) {
            taskProgressEls[i].innerHTML = parseInt(progress) + "%";
            show(taskProgressEls[i]);
        }
    }

    function setTaskFinished(taskName)
    {
        var taskEls, taskStatusEls, taskProgressEls, i;

        taskEls = document.querySelectorAll(config.baseSelector + ' [data-task="'+ taskName +'"]');
        taskStatusEls = document.querySelectorAll(config.baseSelector + ' [data-status="'+ taskName +'"]');
        taskProgressEls = document.querySelectorAll(config.baseSelector + ' [data-progress="'+ taskName +'"]');
        for (i = 0; i < taskEls.length; i++) {
            enable(taskEls[i]);
        }

        for (i = 0; i < taskStatusEls.length; i++) {
            setFinishedState(taskStatusEls[i]);
        }

        for (i = 0; i < taskProgressEls.length; i++) {
            hide(taskProgressEls[i]);
        }
    }

    function setTaskPending(taskName)
    {
        var taskEls, taskStatusEls, taskProgressEls, i;

        taskEls = document.querySelectorAll(config.baseSelector + ' [data-task="'+ taskName +'"]');
        taskStatusEls = document.querySelectorAll(config.baseSelector + ' [data-status="'+ taskName +'"]');
        taskProgressEls = document.querySelectorAll(config.baseSelector + ' [data-progress="'+ taskName +'"]');
        for (i = 0; i < taskEls.length; i++) {
            disable(taskEls[i]);
        }

        for (i = 0; i < taskStatusEls.length; i++) {
            setPendingState(taskStatusEls[i]);
        }

        for (i = 0; i < taskProgressEls.length; i++) {
            hide(taskProgressEls[i]);
        }
    }

    function disable(el)
    {
        el.classList.add(config.disabledCls);
    }

    function enable(el)
    {
        el.classList.remove(config.disabledCls);
    }

    function hide(el)
    {
        el.classList.add(config.hiddenCls);
    }

    function show(el)
    {
        el.classList.remove(config.hiddenCls);
    }

    function setPendingState(el)
    {
        var i;

        for (i = 0; i < config.inProgressStatusClses.length; i++) {
            el.classList.remove(config.inProgressStatusClses[i]);
        }

        for (i = 0; i < config.doneStatusClses.length; i++) {
            el.classList.remove(config.doneStatusClses[i]);
        }

        for (i = 0; i < config.pendingStatusClses.length; i++) {
            el.classList.add(config.pendingStatusClses[i]);
        }
    }

    function setFinishedState(el)
    {
        var i;

        for (i = 0; i < config.pendingStatusClses.length; i++) {
            el.classList.remove(config.pendingStatusClses[i]);
        }

        for (i = 0; i < config.inProgressStatusClses.length; i++) {
            el.classList.remove(config.inProgressStatusClses[i]);
        }

        for (i = 0; i < config.doneStatusClses.length; i++) {
            el.classList.add(config.doneStatusClses[i]);
        }
    }

    function setInProgressState(el)
    {
        var i;

        for (i = 0; i < config.pendingStatusClses.length; i++) {
            el.classList.remove(config.pendingStatusClses[i]);
        }

        for (i = 0; i < config.doneStatusClses.length; i++) {
            el.classList.remove(config.doneStatusClses[i]);
        }

        for (i = 0; i < config.inProgressStatusClses.length; i++) {
            el.classList.add(config.inProgressStatusClses[i]);
        }
    }

    CleverReach.StatusChecker = {
        init: statusCheckerInit
    };
})();