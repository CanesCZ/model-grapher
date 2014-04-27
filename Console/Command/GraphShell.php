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
 * This shell examines all models in the current application and its plugins,
 * finds all relations between them, and then generates a graphical representation
 * of those.  The graph is built using an excellent GraphViz tool.
 *
 * * filename - an optional full path to the output file. If omitted, graph.png in
 *              current folder will be used
 * * format - an optional output format, supported by GraphViz (png,svg,etc)
 *
 * @author Ondrej Henek <info@canes.cz>
 * @version 2.0 (Blue Octopus On Steroids)
 */
class GraphShell extends AppShell {


	/**
	 * Relations settings
	 *
	 * My weak attempt at using Crow's Foot Notation for 
	 * CakePHP model relationships.  
	 *
	 * NOTE: Order of the relations in this list is sometimes important.
	 */
	public $relationsSettings = array(
			'belongsTo'           => array('label' => 'belongsTo', 'dir' => 'both', 'color' => 'blue',    'arrowhead' => 'none', 'arrowtail' => 'crow', 'fontname' => 'Helvetica', 'fontsize' => 10, ),
			'hasMany'             => array('label' => 'hasMany',   'dir' => 'both', 'color' => 'blue',    'arrowhead' => 'crow', 'arrowtail' => 'none', 'fontname' => 'Helvetica', 'fontsize' => 10, ),
			'hasOne'              => array('label' => 'hasOne',    'dir' => 'both', 'color' => 'magenta', 'arrowhead' => 'tee',  'arrowtail' => 'none', 'fontname' => 'Helvetica', 'fontsize' => 10, ),
			'hasAndBelongsToMany' => array('label' => 'HABTM',     'dir' => 'both', 'color' => 'red',     'arrowhead' => 'crow', 'arrowtail' => 'crow', 'fontname' => 'Helvetica', 'fontsize' => 10, ),
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
		
		$this->outputGraph($content);

	}

	/**
	 * Get a list of all models to process
	 *
	 * Thanks to Google, Harsha M V and Peter Martin
	 *
	 * @link http://variable3.com/blog/2010/05/list-all-the-models-and-plugins-of-a-cakephp-application/
	 * @return array
	 */
	private function getModels() {
		$result = array();

		$result['app'] = App::objects('model');
		$plugins = App::objects('plugin');
		if (!empty($plugins)) {
			foreach ($plugins as $plugin) {
				$pluginModels = App::objects('model', App::pluginPath($plugin) . 'models' . DS, false);
				if (!empty($pluginModels)) {
					if (empty($result[$plugin])) {
						$result[$plugin] = array();
					}

					foreach ($pluginModels as $model) {
						$result[$plugin][] = "$plugin.$model";
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
	private function getRelations($modelsList) {
		$result = array();

		foreach ($modelsList as $plugin => $models) {
			foreach ($models as $model) {

				// This will work only if you have models and nothing else
				// in app/models/ and app/plugins/*/models/ . Otherwise, ***KABOOM*** and ***CRASH***.
				// Rearrange your files or patch up $this->getModels()
				$modelInstance = ClassRegistry::init($model);

				foreach ($this->relationsSettings as $relation => $settings) {
					if (!empty($modelInstance->$relation) && is_array($modelInstance->$relation)) {

						if ($this->miscSettings['realModels']) {
							$result[$plugin][$model][$relation] = array();
							foreach ($modelInstance->$relation as $name => $value) {
								if (is_array($value) && !empty($value) && !empty($value['className'])) {
									$result[$plugin][$model][$relation][] = $value['className'];
								}
								else {
									$result[$plugin][$model][$relation][] = $name;
								}
							}
						}
						else {
							$result[$plugin][$model][$relation] = array_keys($modelInstance->$relation);
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
	 * @param array $models Available models
	 * @param array $relations Availalbe relationships
	 * @param array $settings Settings
	 * @return void
	 */
	private function buildGraph($modelsList, $relationsList, $settings) {

		$nodes = array();
		
		// We'll collect apps and plugins in here
		$plugins = array();

		// Add nodes for all models
		foreach ($modelsList as $plugin => $models) {
			if (!in_array($plugin, $plugins)) {
				$plugins[] = $plugin;
			}

			foreach ($models as $model) {
//				$label = preg_replace("/^$plugin\./", '', $model);
//				echo $label ."\n";
				$nodes[$model] = $this->graph->createVertex($model);
			}
		}

		// Add all relations
		foreach ($relationsList as $plugin => $relations) {
			if (!in_array($plugin, $plugins)) {
				$plugins[] = $plugin;
			}

			foreach ($relations as $model => $relations) {
				foreach ($relations as $relation => $relatedModels) {

					$this->relationsSettings = $settings[$relation];
					$this->relationsSettings['label'] = ''; // no need to pollute the graph with too many labels

					foreach ($relatedModels as $relatedModel) {
						if (!empty($nodes[$model]) && !empty($nodes[$relatedModel]))
							$nodes[$model]->createEdgeTo($nodes[$relatedModel]);
					}
				}
			}
		}

		// Add clusters for apps and plugins
//		foreach ($plugins as $plugin) {
//			$this->graph->addCluster($plugin, $plugin);
//		}
	}


	/**
	 * Save graph to a file
	 *
	 * @param string $fileName File to save graph to (full path)
	 * @param string $format Any of the GraphViz supported formats
	 * @return numeric Number of bytes written to file
	 */
	private function outputGraph($content, $filename = 'graph_.png') {
		
		if (file_exists($filename)) {
//			file exists! that's bad and we should say somehting
		}

		if (!$handle = fopen($filename, 'w')) {
			 echo "Cannot open file ($filename)";
			 exit;
		}

		if (fwrite($handle, $content) === false) {
			echo "Cannot write to file ($filename)";
			exit;
		}

		echo "Success, wrote to file ($filename)";

		fclose($handle);
	}

}