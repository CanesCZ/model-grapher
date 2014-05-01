<?php
App::uses('AppModel', 'Model');

class Comment extends AppModel {

	public $belongsTo = array('Article', 'User');
}