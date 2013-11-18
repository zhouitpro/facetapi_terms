<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MySearchApiFacetapiAdapter extends SearchApiFacetapiAdapter{


  /**
   * Allows the backend to initialize its query object before adding the facet filters.
   *
   * @param mixed $query
   *   The backend's native object.
   */
  public function initActiveFilters($query) {
    $search_id = $query->getOption('search id');
    $index_id = $this->info['instance'];
    $facets = facetapi_get_enabled_facets($this->info['name']);
    $this->fields = array();

    // We statically store the current search per facet so that we can correctly
    // assign it when building the facets. See the build() method in the query
    // type plugin classes.
    
    // Change.
    $active = drupal_static('search_api_facetapi_active_facets', array());
    foreach ($facets as $facet) {
      $options = $this->getFacet($facet)->getSettings()->settings;
      // The 'default_true' option is a choice between "show on all but the
      // selected searches" (TRUE) and "show for only the selected searches".
      $default_true = isset($options['default_true']) ? $options['default_true'] : TRUE;
      // The 'facet_search_ids' option is the list of selected searches that
      // will either be excluded or for which the facet will exclusively be
      // displayed.
      $facet_search_ids = isset($options['facet_search_ids']) ? $options['facet_search_ids'] : array();

      if (array_search($search_id, $facet_search_ids) === FALSE) {
        $search_ids = variable_get('search_api_facets_search_ids', array());
        if (empty($search_ids[$index_id][$search_id])) {
          // Remember this search ID.
          $search_ids[$index_id][$search_id] = $search_id;
          variable_set('search_api_facets_search_ids', $search_ids);
        }
        if (!$default_true) {
          continue; // We are only to show facets for explicitly named search ids.
        }
      }
      elseif ($default_true) {
        continue; // The 'facet_search_ids' in the settings are to be excluded.
      }
      $active[$facet['name']] = $search_id;
      $this->fields[$facet['name']] = array(
        'field'             => $facet['field'],
        'limit'             => $options['hard_limit'],
        'operator'          => $options['operator'],
        'min_count'         => $options['facet_mincount'],
        'missing'           => $options['facet_missing'],
      );
    }
  }

}
