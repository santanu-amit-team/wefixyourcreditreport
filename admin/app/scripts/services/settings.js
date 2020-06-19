'use strict';

/**
 * @ngdoc service
 * @name codeBaseAdminApp.Settings
 * @description
 * # Settings
 * Factory in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp')
  .factory('Settings', function ($resource) {

    return $resource('../api.php', null, {

    });
    
  });
