<?php
namespace Crumby\RouteResolver\Contracts;
/**
 *
 * @author Andrei Vassilenko <avassilenko2@gmail.com>
 */
interface  ParamResolver {
  public function item($param=null);
  public function label($param=null);
  public function locale($param=null);
  public function segment($param=null);
}
