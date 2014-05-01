<?php
App::uses('GraphShell', 'Console/Command');

/**
 * Overwrites path where are models loaded from, so I can
 * use dummy Test models like it was real application. Neat!
 */
App::build(array('Model' => array(APP .'Test'. DS .'ModelDummies' .DS)), App::RESET);
App::build(array('Plugin' => array(APP .'Test'. DS .'PluginDummies' .DS)), App::RESET);

require_once 'Image/GraphViz.php';

class GraphShellTest extends CakeTestCase  {

	public function setUp() {
		parent::setUp();
		
		$this->Shell = new GraphShell();
		CakePlugin::load('DummyPlugin');
	}

	/**
	 * Checks if all models are found in their paths
	 */
	public function testGetModels() {
		
		$result = $this->Shell->getModels();
		$this->assertEquals($this->expectedModelList, $result);
	}

	/**
	 * Checks if all the relations are properly noted
	 */
	public function testGetRelations() {
		
		$result = $this->Shell->getRelations($this->expectedModelList);
		$this->assertEquals($this->expectedRelations, $result);
	}
	/**
	 * Is the output really the thing we wanted? (checked bit number)
	 */
	public function testGetGraph() {
		
		$this->assertEquals(44136, $this->Shell->main());
	}

	private $expectedModelList = array(
		'app' => array(
			'Article',
			'Comment',
			'Tag',
			'User'
		),
		'DummyPlugin' => array(
			'DummyPlugin.Chair',
			'DummyPlugin.Meta',
			'DummyPlugin.UserProfile'
		)
	);
	
	private $expectedRelations = array(
		'app' => array(
			'Article' => array(
				'belongsTo' => array(
					'User'
				),
				'hasMany' => array(
					'Comment'
				),
				'hasAndBelongsToMany' => array(
					'Tag'
				),
			),
			'Comment' => array(
				'belongsTo' => array(
					'Article',
					'User',
				),
			),
			'Tag' => array(
				'hasAndBelongsToMany' => array(
					'Article'
				),
			),
			'User' => array(
				'hasMany' => array(
					'Article',
					'Comment',
				),
			),
		),
		'DummyPlugin' => array(
			'DummyPlugin.Chair' => array(
				'belongsTo' => array(
					'DummyPlugin.UserProfile',
					'Article',
				),
			),
			'DummyPlugin.Meta' => array(
				'hasAndBelongsToMany' => array(
					'Article'
				),
			),
			'DummyPlugin.UserProfile' => array(
				'hasMany' => array(
					'DummyPlugin.Chair',
					'Article',
				),
			),
		)
	);

}