$(function() {
  var projectId = $('#project-id').val();
  var deployId = $('#deploy-id').val();
  var deployOutput = $('.deploy-result');
  var controls = $('#deploy-button, #start-deploy-form select, #start-deploy-form input');
  var cancelButton = $('#cancel-button');
  var deployButton = $('#deploy-button');
  var deployProgress = $('#deploy-progress');
  var projectInfo = $('#project-info');
  var lastDeployResult = $('.last-deploy');
  var pageTitle = $('title');
  var branchSelect = $('select[name="branch"]');
  var checkDeployTimeout = false;

  var checkDeploy = function () {
    $.ajax('/deploy/' + deployId + '/check/')
    .done(function (response) {
      changeDeployOutput(response.output);
      if (response.finished) {
        finishDeploy(response);
      } else {
        checkDeployTimeout = setTimeout(checkDeploy, 3000);
        animatePageTitle();
      }
    }).fail(ajaxFail);
  };

  /**
   * @param {{}} response
   */
  var finishDeploy = function (response) {
    var resultText = '';
    lastDeployResult.removeClass('text-muted').removeClass('text-info');
    if (wasDeploySuccessful(response.code)) {
      resultText = 'Deploy finished';
      pageTitle.html('&#10003; ' + resultText);
      lastDeployResult.removeClass('text-danger').addClass('text-success');
    } else if (response.canceled) {
      resultText = 'Deploy canceled by ' + response.canceled_by;
      pageTitle.html('&#10007; ' + resultText);
      lastDeployResult.removeClass('text-success').removeClass('text-danger').addClass('text-muted');
    } else {
      resultText = 'Deploy failed';
      pageTitle.html('&#10007; ' + resultText);
      lastDeployResult.removeClass('text-success').addClass('text-danger');
    }
    resultText = resultText + ' (took ' + response.duration + ')';
    lastDeployResult.text(resultText);
    alert(resultText);
    stopProgress();
  };

  /**
   * @param {string} text
   */
  var changeDeployOutput = function (text) {
    if (text) {
      deployOutput.html(text);
      deployOutput.scrollTop(deployOutput[0].scrollHeight);
    }
  };

  /**
   * @param {number} code
   * @return {boolean}
   */
  var wasDeploySuccessful = function (code) {
    return code == 0;
  };

  var animatePageTitle = function () {
    var dotsCount = (pageTitle.html().match(/\./g) || []).length;
    if (dotsCount < 3) {
      pageTitle.html('.' + pageTitle.html());
    } else {
      pageTitle.html(pageTitle.html().replace('...', ''));
    }
  };

  var startDeploy = function () {
    startProgress();
    $.ajax({
      method: 'POST',
      url: '/deploy/project/' + projectId + '/start/',
      data: {
        branch: branchSelect.val(),
        type: $('input[name="type"]:checked').val()
      }
    }).done(function (response) {
      if (response.id) {
        deployId = response.id;
        changeDeployOutput(response.output ? response.output : 'Deploy started');
        checkDeployTimeout = setTimeout(checkDeploy(), 1000);
      }
    }).fail(ajaxFail);
  };

  var cancelDeploy = function () {
    stopProgress();
    $.ajax({
      method: 'POST',
      url: '/deploy/project/' + deployId + '/cancel/'
    }).done(function (response) {
      var resultText = 'Deploy canceled';
      pageTitle.html(resultText);
      lastDeployResult.removeClass('text-danger').removeClass('text-success').addClass('text-muted');
      lastDeployResult.text(resultText);
      stopProgress();
    }).fail(ajaxFail);
  };

  var stopProgress = function () {
    controls.prop('disabled', false);
    cancelButton.prop('disabled', true);
    deployProgress.hide();
    projectInfo.show();
    deployButton.text('Re-deploy');

    if (checkDeployTimeout) {
      clearTimeout(checkDeployTimeout);
    }
  };

  var startProgress = function () {
    controls.prop('disabled', true);
    cancelButton.prop('disabled', false);
    deployProgress.show();
    projectInfo.hide();
    pageTitle.html('deploy is in progress');
    $('#deploy-user-name').text($('#current-user-name').val());
    $('#branch-name').text(branchSelect.val());
    var now = new Date();
    $('#deploy-start-time').text(now.getHours() + ':' + now.getMinutes());
  };

  /**
   * @param {string} response
   */
  var ajaxFail = function (response) {
    console.error(response);
    alert(response.responseText);
    lastDeployResult.val('Error while deploy')
      .removeClass('text-success')
      .addClass('text-danger');
    pageTitle.html('Error while deploy');
    stopProgress();
  };

  if (deployId && $('#deploy-in-progress').val()) {
    checkDeploy();
  }

  deployButton.click(function (event) {
    startDeploy();
    event.preventDefault();
  });

  cancelButton.click(function (event) {
    cancelDeploy();
    event.preventDefault();
  });

  deployOutput.scrollTop(deployOutput[0].scrollHeight);
});
