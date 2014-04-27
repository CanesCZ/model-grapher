<?php
App::uses('Model', 'Model');

class AppModel extends Model {
	public $useTable = false;
	public $useDbConfig = false;
}

class User extends CakeTestModel {
	public $name = 'User';
	public $hasMany = array('Comment', 'Article');
}

class Article extends CakeTestModel {
	public $name = 'Article';
	public $belongsTo = array('User');
	public $hasMany = array('Comment');
	public $hasAndBelongsToMany = array('Tag');
}

class Comment extends CakeTestModel {
	public $name = 'Comment';
	public $belongsTo = array('Article', 'User');
}

class Tag extends CakeTestModel {
	public $hasAndBelongsToMany = array('Article');
}