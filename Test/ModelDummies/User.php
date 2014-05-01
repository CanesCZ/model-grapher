<?php
App::uses('AppModel', 'Model');

class User extends AppModel {
	
	public $hasMany = array(
		'Article',
		'Comment',
	);
}