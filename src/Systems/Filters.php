<?php

namespace tad\FunctionMockerLe\Systems;


class Filters {

  const FILTER = 'filter';

  const ACTION = 'action';

  /**
   * @var Filters
   */
  protected static $instance;

  /**
   * @var array
   */
  protected $filters = [];

  /**
   * @var array
   */
  protected $actions = [];

  /**
   * @var array
   */
  protected $didActions = [];

  /**
   * @var array
   */
  protected $didFilters = [];

  /**
   * @var string
   */
  protected $doingAction;

  /**
   * @var string
   */
  protected $currentFilter;

  /**
   * @return \tad\FunctionMockerLe\Systems\Filters|static
   */
  public static function instance() {
    if (self::$instance === null) {
      self::$instance = new static();
    }

    return self::$instance;
  }

  public function addAction($action, $callback, $priority = 10, $argsCount = 1) {
    $where = self::ACTION;
    $this->hook($where, $action, $callback, $priority, $argsCount);
  }

  /**
   * @param $where
   * @param $tag
   * @param $callback
   * @param $priority
   * @param $argsCount
   */
  public function hook($where, $tag, $callback, $priority, $argsCount) {
    if (!isset($this->filters[$where][$tag][$priority])) {
      $this->filters[$where][$tag][$priority] = [];
    }
    $this->filters[$where][$tag][$priority][] = [
      'callback'  => $callback,
      'argsCount' => $argsCount,
    ];
  }

  public function doAction($action, $args) {
    $where = static::ACTION;
    $this->doingAction = $action;
    $this->currentFilter = $action;

    $this->doHooked($where, $action, $args);

    $this->doingAction = null;
  }

  /**
   * @param $where
   * @param $action
   * @param $args
   */
  public function doHooked($where, $action, $args) {
    $val = null;

    if ($where === static::FILTER) {
      $val = $args[0];
    }

    $all = $this->getHooked($where, 'all');
    $hooked = $this->getHooked($where, $action);

    $this->didActions[] = $action;

    foreach ($all as $p) {
      foreach ($p as $a) {
        $val = call_user_func_array($a['callback'], array_chunk($args, $a['argsCount']));
      }
    }

    foreach ($hooked as $p) {
      foreach ($p as $a) {
        $val = call_user_func_array($a['callback'], array_chunk($args, $a['argsCount'])[0]);
      }
    }

    return $val;
  }

  protected function getHooked($where, $tag) {
    if (!isset($this->filters[$where][$tag])) {
      return [];
    }

    return $this->filters[$where][$tag];
  }

  public function hasAction($action, $callback = null) {
    $where = static::ACTION;
    return $this->hasHooked($where, $action, $callback);
  }

  /**
   * @param $where
   * @param $tag
   * @param $callback
   *
   * @return bool
   */
  public function hasHooked($where, $tag, $callback) {
    if (empty($this->filters[$where][$tag])) {
      return false;
    }

    if ($callback === null) {
      return true;
    }

    return (bool) (array_sum(array_map(function ($p) use ($callback) {
      return count(array_filter($p, function ($a) use ($callback) {
        return $a['callback'] === $callback;
      }));
    }, $this->filters[$where][$tag])));
  }

  public function didAction($action) {
    return in_array($action, $this->didActions);
  }

  public function removeAction($action, $callback, $priority = 10) {
    $where = static::ACTION;
    return $this->removeHooked($where, $action, $callback, $priority);
  }

  /**
   * @param $where
   * @param $tag
   * @param $callback
   * @param $priority
   *
   * @return bool
   */
  public function removeHooked($where, $tag, $callback, $priority) {
    if (!isset($this->filters[$where][$tag][$priority])) {
      return true;
    }
    $preCount = count($this->filters[$where][$tag][$priority]);

    $this->filters[$where][$tag][$priority] = array_filter($this->filters[$where][$tag][$priority], function ($a) use ($callback) {
      return $a['callback'] !== $callback;
    });

    $this->filters[$where][$tag] = array_filter($this->filters[$where][$tag]);

    return !empty($this->filters[$where][$tag][$priority]) && count($this->filters[$where][$tag][$priority]) === $preCount - 1;
  }

  public function removeAllActions($tag, $priority = false) {
    $where = static::ACTION;
    $this->removeAllHooked($where, $tag, $priority);
  }

  /**
   * @param $where
   * @param $tag
   * @param $priority
   */
  public function removeAllHooked($where, $tag, $priority) {
    if (false === $priority) {
      unset($this->filters[$where][$tag]);
      return;
    }

    unset($this->filters[$where][$tag][$priority]);
  }

  public function doingAction($action = null) {
    return $action !== null ? $this->doingAction === $action : !empty($this->doingAction);
  }

  public function doingFilter($filter = null) {
    return $filter !== null ? $this->currentFilter === $filter : !empty($this->currentFilter);
  }

  public function removeFilter($filter, $callback, $priority) {
    $where = static::FILTER;
    return $this->removeHooked($where, $filter, $callback, $priority);
  }

  public function hasFilter($filter, $callback = null) {
    $where = static::FILTER;
    return $this->hasHooked($where, $filter, $callback);
  }

  public function currentFilter() {
    return $this->currentFilter;
  }

  public function addFilter($filter, $callback, $priority = 10, $argsCount = 1) {
    $where = self::FILTER;
    $this->hook($where, $filter, $callback, $priority, $argsCount);
  }

  public function applyFilters($filter, $args) {
    $where = static::FILTER;
    $this->doingAction = false;
    $this->currentFilter = $filter;

    return $this->doHooked($where, $filter, $args);
  }

  public function removeAllFilters($filter, $priority = 10) {
    $where = static::FILTER;
    $this->removeAllHooked($where, $filter, $priority);
  }
}
