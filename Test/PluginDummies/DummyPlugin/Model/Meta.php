<?php
App::uses('DummyPluginAppModel', 'DummyPlugin.Model');

class Meta extends DummyPluginAppModel {
	
	public $hasAndBelongsToMany = array(
		'Article'
	);
}