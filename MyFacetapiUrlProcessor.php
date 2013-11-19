<?php

/**
 * Override Url process
 * */
class MyFacetapiUrlProcessor extends FacetapiUrlProcessorPrettyPaths {

  /**
   * Construct a path from for a given array of filter segments.
   *
   * @param array $segments
   * @return string
   */
  public function constructPath(array $segments) {
    if (!empty($this->options['sort_path_segments'])) {
      // Sort to avoid multiple urls with duplicate content.
      uksort($segments, 'strnatcmp');
    }
    $path = $this->getBasePath();
    // Add all path segments.
    foreach ($segments as $key => $segment) {
      $this->encodePathSegment($segment, $segment['facet']);
      $path .= '/' . $segment['alias'] . '/' . $segment['value'];
    }
    return $path;
  }

  public function ClearSegments() {
    $this->pathSegments = array();
  }

  /**
   *  Pretty paths will be generated as "search/url/segment1/segment2/".
   *
   *  By default, a segment will look like:
   *    "<alias>/<value>".
   *
   *  Overrides FacetapiUrlProcessorStandard::getFacetPath().
   */
  public function getFacetPath(array $facet, array $values, $active) {
    $segments = $this->pathSegments;
    $active_items = $this->adapter->getActiveItems($facet);

    // 检查当前分类是否存在.
    $already_exists_alias = array();
    foreach ($segments as $key => $segment) {
      $already_exists_alias[$key] = $segment['alias'];
    }

    // dpm($segments, 'values');
    // Adds to segments if inactive, removes if active.
    foreach ($values as $value) {
      $segment = $this->getPathSegment($facet, $value);
      if ($index = array_search($segment['alias'], $already_exists_alias)) {
        unset($segments[$index]);
      }
//      if ($active && isset($active_items[$value])) {
//        unset($segments[$segment['key']]);
//      }
//      elseif (!$active) {
      $segments[$segment['key']] = $segment;
//     }
    }

    $path = $this->constructPath($segments);
    $path = str_replace('//', '/', $path);
    return $path;
  }

}
