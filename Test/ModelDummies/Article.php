<?php
App::uses('AppModel', 'Model');

class Article extends AppModel {

	public $belongsTo = array('User');
	public $hasMany = array('Comment');
	public $hasAndBelongsToMany = array('Tag');
}