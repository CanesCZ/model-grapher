<?php

//App::uses('ConsoleOutput', 'Console');
//App::uses('ConsoleInput', 'Console');
App::uses('ShellDispatcher', 'Console');
App::uses('Shell', 'Console');
App::uses('GraphShell', 'Console/Command');

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

		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);

		$this->Task = $this->getMock(
			'Shell'
//			array('in', 'out', 'hr', 'createFile', 'error', 'err', 'clear', 'dispatchShell'),
//			array($out, $out, $in)
		);
		$this->GraphShell = new GraphShell();
	}

	public function testGetModels() {
		
		$models = $this->GraphShell->main();
		print_r($models);
		
		$this->assertEquals(null, null);
	}

}