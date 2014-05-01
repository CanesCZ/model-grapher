<?php
App::uses('AppModel', 'Model');

class Tag extends AppModel {
	
	public $hasAndBelongsToMany = array('Article');
}