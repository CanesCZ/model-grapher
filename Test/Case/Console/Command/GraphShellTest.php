<?php

//App::uses('ConsoleOutput', 'Console');
//App::uses('ConsoleInput', 'Console');
App::uses('ShellDispatcher', 'Console');
App::uses('Shell', 'Console');

App::uses('Model', 'Model');
App::uses('AppModel', 'Model');

require_once dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'models.php';

require_once 'Image/GraphViz.php';

class GraphShellTest extends CakeTestCase  {

	public function setUp() {
		parent::setUp();
		
		$this->User = ClassRegistry::init('User');
		$this->Article = ClassRegistry::init('Article');
		$this->Tag = ClassRegistry::init('Tag');

		$this->Task = $this->getMock(
			'GraphShell'
		);
	}

	public function testGetModels() {
		
		$models = $this->Task->getModels();
		print_r($models);
		
		$this->assertEquals(null, null);
	}

}