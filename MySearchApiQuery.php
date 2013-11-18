<?php
/**
 * Provides a standard implementation of the SearchApiQueryInterface.
 */
class MySearchApiQuery extends SearchApiQuery {

  /**
   * The index.
   *
   * @var SearchApiIndex
   */
  protected $index;

  /**
   * The search keys. If NULL, this will be a filter-only search.
   *
   * @var mixed
   */
  protected $keys;

  /**
   * The unprocessed search keys, as passed to the keys() method.
   *
   * @var mixed
   */
  protected $orig_keys;

  /**
   * The fields that will be searched for the keys.
   *
   * @var array
   */
  protected $fields;

  /**
   * The search filter associated with this query.
   *
   * @var SearchApiQueryFilterInterface
   */
  protected $filter;

  /**
   * The sort associated with this query.
   *
   * @var array
   */
  protected $sort;

  /**
   * Search options configuring this query.
   *
   * @var array
   */
  protected $options;

  /**
   * Flag for whether preExecute() was already called for this query.
   *
   * @var bool
   */
  protected $pre_execute = FALSE;

  /**
   * {@inheritdoc}
   */
  public function filter(SearchApiQueryFilterInterface $filter) {
    // $this->filter->filter($filter);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function condition($field, $value, $operator = '=') {
    // $this->filter->condition($field, $value, $operator);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $start = microtime(TRUE);

    // Prepare the query for execution by the server.
    $this->preExecute();

    $pre_search = microtime(TRUE);

    // Execute query.
    $response = $this->index->server()->search($this);

    $post_search = microtime(TRUE);

    // Postprocess the search results.
    $this->postExecute($response);

    $end = microtime(TRUE);
    $response['performance']['complete'] = $end - $start;
    $response['performance']['hooks'] = $response['performance']['complete'] - ($post_search - $pre_search);

    // Store search for later retrieval for facets, etc.
    search_api_current_search('my_server_id', $this, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function preExecute() {
    // Make sure to only execute this once per query.
    if (!$this->pre_execute) {
      $this->pre_execute = TRUE;
      // Add filter for languages.
      if (isset($this->options['languages'])) {
        $this->addLanguages($this->options['languages']);
      }

      // Add fulltext fields, unless set
      if ($this->fields === NULL) {
        $this->fields = $this->index->getFulltextFields();
      }

      // Preprocess query.
      $this->index->preprocessSearchQuery($this);
      
     /// search_api_facetapi_search_api_query_alter($this);
      // $this->getIndex();
      $searcher = 'search_api@default_node_index';
      $info = facetapi_get_searcher_info();
      $searcher_info = $info[$searcher];
      $adapter = new MySearchApiFacetapiAdapter($searcher_info);
      $adapter->addActiveFilters($this);
//      
//     dpm(module_implements('search_api_query_alter'));
//      // Let modules alter the query.
//      drupal_alter('search_api_query', $this);
    }
  }

}
