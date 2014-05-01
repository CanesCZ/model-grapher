<?php
/**
 * based on https://github.com/mamchenkov/CakePHP-GraphViz-Models
 */
use \Fhaculty\Graph\Graph as Graph;
use \Fhaculty\Graph\Exporter\Image as Image;

/**
 * Requre Image_GraphViz class from the PEAR package
 */
require_once 'Image/GraphViz.php';

/**
 * CakePHP ModelGrapher
 *
 * @author Ondrej Henek <info@canes.cz>
 * @version 1.0
 */
class GraphShell extends AppShell {


	public $relations = array(
			'belongsTo',
			'hasMany',
			'hasOne',
			'hasAndBelongsToMany',
		);


	/**
	 * We'll use this to store the graph thingy
	 */
	private $graph;

	/**
	 * CakePHP's Shell main() routine
	 *
	 * This routine is called when the shell is executed via console.
	 */
	public function main() {

		// Initialize the graph
		$this->graph = new Graph();

		$models = $this->getModels();
		$relationsData = $this->getRelations($models);
		$this->buildGraph($models, $relationsData);

		$image = new Image();
		$content = $image->getOutput($this->graph);
		
		return $this->outputGraph($content);
	}

	/**
	 * Get a list of all models to process
	 *
	 * Thanks to Google, Harsha M V and Peter Martin
	 *
	 * @link http://variable3.com/blog/2010/05/list-all-the-models-and-plugins-of-a-cakephp-application/
	 * @return array
	 */
	public function getModels() {
		$result = array();

		$result['app'] = App::objects('Model', null, false);
		// getting rid of AppModel if its present
		if(($AppModelPos = array_search('AppModel', $result['app'])) !== false) {
			array_splice($result['app'], $AppModelPos, 1);
		}
		
		$plugins = App::objects('plugins', null, false);
		if (!empty($plugins)) {
			foreach ($plugins as $plugin) {
				
				$pluginModels = App::objects('Model', App::pluginPath($plugin) . 'Model' . DS, false);
				
				if (!empty($pluginModels)) {
					
					if (empty($result[$plugin])) {
						$result[$plugin] = array();
					}

					foreach ($pluginModels as $model) {
						if ($model != $plugin .'AppModel'){
							$result[$plugin][] = "$plugin.$model";
						}
					}
				}
			}
		}
		
		return $result;
	}

	/**
	 * Get the list of relationss for given models
	 *
	 * @param array $modelsList List of models by module (apps, plugins, etc)
	 * @return array
	 */
	public function getRelations($modelsList) {
		$result = array();

		foreach ($modelsList as $plugin => $models) {
			
			foreach ($models as $model) {

				// This will work only if you have models and nothing else
				// in app/models/ and app/plugins/*/models/ . Otherwise, ***KABOOM*** and ***CRASH***.
				// Rearrange your files or patch up $this->getModels()
				$modelInstance = ClassRegistry::init($model);

				foreach ($this->relations as $relation) {
					if (!empty($modelInstance->$relation) && is_array($modelInstance->$relation)) {

						$result[$plugin][$model][$relation] = array();
						
						foreach ($modelInstance->$relation as $relationModel => $relationOpts) {
							
							if (is_array($relationOpts) && !empty($relationOpts) && !empty($relationOpts['className'])) {
								$result[$plugin][$model][$relation][] = $relationOpts['className'];
							} else {
								$result[$plugin][$model][$relation][] = $relationModel;
							}
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Populate graph with nodes and edges
	 *
	 * @param array $modelsList Available models
	 * @param array $relationsList Availalbe relationships
	 * @return void
	 */
	private function buildGraph($modelsList, $relationsList) {

		$nodes = array();
		
		// We'll collect apps and plugins in here
		$plugins = array();

		// Add nodes for all models
		foreach ($modelsList as $plugin => $models) {
			if (!in_array($plugin, $plugins)) {
				$plugins[] = $plugin;
			}

			foreach ($models as $model) {
				$nodes[$model] = $this->graph->createVertex($model);
			}
		}

		// Add all relations
		foreach ($relationsList as $plugin => $relations) {
			if (!in_array($plugin, $plugins)) {
				$plugins[] = $plugin;
			}

			foreach ($relations as $model => $relations) {
				foreach ($relations as $relatedModels) {

					foreach ($relatedModels as $relatedModel) {
						if (!empty($nodes[$model]) && !empty($nodes[$relatedModel]))
							$nodes[$model]->createEdgeTo($nodes[$relatedModel]);
					}
				}
			}
		}
	}


	/**
	 * Save graph to a file
	 *
	 * @param binary $content what is supposed to be saved
	 * @param string $fileName File to save graph to (relative)
	 * @return numeric Number of bytes written to file
	 */
	private function outputGraph($content, $filename = 'graph_.png') {
		
		if (file_exists($filename)) {
//			file exists! that's bad and we should say somehting
		}

		return file_put_contents($filename, $content);
	}

}