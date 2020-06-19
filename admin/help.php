<?php

$library_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library';
require_once $library_dir . DIRECTORY_SEPARATOR . 'bootstrap.php';

Bootstrap::initialize('admin');



$page     = \Application\Request::query()->get('page');
$source   = \Application\Registry::system('systemConstants.REMOTE_URL') . 'help/' . $page . '.html';
$contents = \Application\Http::get($source);

echo '<md-dialog>
    <md-toolbar>
      <div class="md-toolbar-tools">
        <h2>' . strtoupper($page) . ' HELP</h2>
        <span flex></span>
        <md-button class="md-icon-button" ng-click="cancel()">
          <i class="material-icons">&#xE14C;</i>
        </md-button>
      </div>
    </md-toolbar>

    <md-dialog-content>
      <div class="md-dialog-content">' . "\n";

if ( ! is_array($contents) ) {
    
    echo $contents . "\n";
} else {

    echo "No help guide found.";
}

echo "
  </div>
</md-dialog-content>
</md-dialog>";