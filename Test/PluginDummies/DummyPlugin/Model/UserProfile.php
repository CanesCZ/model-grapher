<?php
App::uses('DummyPluginAppModel', 'DummyPlugin.Model');

class UserProfile extends DummyPluginAppModel {
	
	public $hasMany = array(
		'DummyPlugin.Chair',
		'Article',
	);
}