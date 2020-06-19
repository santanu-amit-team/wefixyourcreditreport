'use strict';

/**
 * @ngdoc service
 * @name codeBaseAdminApp.Hint
 * @description
 * # Hint
 * Factory in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp')
	.factory('Hint', function ($rootScope, $timeout) {
		function addHint(hint_obj, model) {
			angular.forEach(hint_obj, function (value, index) {
				$timeout(function () {
					try
					{
						if (value != '')
						{
							if (typeof (value) === "object")
							{
								addHint(value, model + '.' + index);
							}
							else
							{
								if (!document.querySelectorAll('[ng-model="' + model + '.' + index + '"]')[0].nextElementSibling)
								{
									document.querySelectorAll('[ng-model="' + model + '.' + index + '"]')[0].insertAdjacentHTML('afterEnd', '<div class="hint">' + value + '</div>');
								}
								else
								{
									if (document.querySelectorAll('[ng-model="' + model + '.' + index + '"]')[0].nextElementSibling.getAttribute('class') != 'hint')
									{
										document.querySelectorAll('[ng-model="' + model + '.' + index + '"]')[0].insertAdjacentHTML('afterEnd', '<div class="hint">' + value + '</div>');
									}
								}
							}
						}
					}
					catch (err) {
						//console.log(err.message);
					}
				}, 1000);
			});
		}

		return {
			showHint: function (model) {
				var hint_obj = $rootScope.hint[model];
				addHint(hint_obj, model);
			}
		};
	});


