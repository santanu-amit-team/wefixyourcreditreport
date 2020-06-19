'use strict';
/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:fileUpload
 * @description
 * # fileUpload
 */
angular.module('codeBaseAdminApp')
.directive('fileUpload', UploadFile);

function UploadFile() {
  var directive = {
    restrict: 'E',
    templateUrl: 'views/csv-upload.html',
    link: UploadFileLink
  };
  return directive;
}

function UploadFileLink(scope, element, attrs, Toast) {
  var input = $(element[0].querySelector('#fileInput'));
  var button = $(element[0].querySelector('#uploadButton'));
  var textInput = $(element[0].querySelector('#textInput'));

  if (input.length && button.length && textInput.length) {
    button.click(function(e) {
      input.click();
    });
    textInput.click(function(e) {
      input.click();
    });
  }

  input.on('change', function(e) {
    var file = e.target.files;
    if(file.length === 0){
      scope.disabledUpload = {
        upload : true,
      };
    }
    if (file[0]) {
      scope.fileName = file[0].name;
      scope.file = file;
      scope.disabledUpload = {
        upload : false,
      };
    } else {
      scope.fileName = null;
    }
    scope.$apply();
  });
}