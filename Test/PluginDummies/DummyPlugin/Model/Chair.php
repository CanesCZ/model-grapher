<?php
App::uses('DummyPluginAppModel', 'DummyPlugin.Model');

class Chair extends DummyPluginAppModel {
	
	public $belongsTo = array(
		'Article',
		'UserProfile' => array(
			'className' => 'DummyPlugin.UserProfile'
		)
	);
}