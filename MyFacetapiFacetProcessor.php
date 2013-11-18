<?php

class MyFacetapiFacetProcessor extends FacetapiFacetProcessor {

  protected $MyUrlprocess;
  public $MyFetchParams = array();

  public function __construct($facet) {
    parent::__construct($facet);
  }

  //public function par
  public function setBuild($build) {
    $this->build = $build;

    // Init URL Process
    //$this->MyUrlprocess = $this->facet->getAdapter()->loadUrlProcessor('facetapi_terms');
    $this->facet->getAdapter()->initUrlProcessor();
    //$this->MyFetchParams = $this->MyUrlprocess->fetchParams();
  }

  // process facet.
  public function Myprocess() {
    $this->build = $this->MyinitializeBuild($this->build);

    $this->build = $this->mapValues($this->build);
    if ($this->build) {
      $settings = $this->facet->getSettings();
      if (!$settings->settings['flatten']) {
        $this->build = $this->processHierarchy($this->build);
      }
      $this->MyprocessQueryStrings($this->build);
    }
  }

  public function MyprocessQueryStrings(&$build) {
    foreach ($build as $value => $item) {
      $values = array($value);
      // Calculate paths for the children.
      if (!empty($item['#item_children'])) {
        $this->processQueryStrings($item['#item_children']);
        // Merges the childrens' values if the item is active so the children
        // are deactivated along with the parent.
        if ($item['#active']) {
          $values = array_merge(facetapi_get_child_values($item['#item_children']), $values);
        }
      }
      // Stores this item's active children so we can deactivate them in the
      // current search block as well.
      $this->activeChildren[$value] = $values;
      //dpm($this->facet->getAdapter()->getSearchPath());
      //dpm($this->facet->getAdapter()->getSearcher());
      // dpm($this->facet->getAdapter()->getCurrentSearch()[0]->getOption('search_api_base_path'));
      // Get curent page base path.
      // $base_path = $this->MyUrlprocess->getBasePath();

      // base path.
      // dpm($pretty->getBasePath());
      // dpm($pretty->getFullPath(), 'full');
//       dpm($pretty->getFacetPath($this->facet->getAdapter()->getFacet(), $values, 1));
      // dpm($this->getFacetPath($values, 0));
      // Formats path and query string for facet item, sets theme function.
      $item['#path'] = $this->MyUrlprocess->getFacetPath($this->facet->getFacet(), $values, $item['#active']);
//      $item['#path'] = $this->getFacetPath($values, 0);
      $item['#query'] = $this->getQueryString($values, 0);
    }
  }

  public function MyinitializeBuild() {

    // Build array defaults.
    $defaults = array(
      '#markup' => '',
      '#path' => $this->facet->getAdapter()->getSearchPath(),
      '#html' => FALSE,
      '#indexed_value' => '',
      '#count' => 0,
      '#active' => 0,
      '#item_parents' => array(),
      '#item_children' => array(),
    );

    // Builds render arrays for each item.
    $adapter = $this->facet->getAdapter();
    $build = $this->build;
    if(!empty($this->facet['alter callbacks'])) {
      foreach ($this->facet['alter callbacks'] as $callback) {
        $callback($build, $adapter, $this->facet->getFacet());
      }
    }
    // Iterates over the render array and merges in defaults.
    foreach (element_children($build) as $value) {
      $item_defaults = array(
        '#markup' => $value,
        '#indexed_value' => $value,
        '#active' => $adapter->itemActive($this->facet['name'], $value),
      );
      $build[$value] = array_merge($defaults, $item_defaults, $build[$value]);
    }
    return $build;
  }

}
