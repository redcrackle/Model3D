<?php

namespace Model3D;


class Utils {

  /**
   * Bound a number between min and max.
   *
   * @param int|float|double $num
   *   Number to be bounded.
   * @param int|float|double $min
   *   Minimum of the range.
   * @param int|float|double $max
   *   Maximum of the range.
   *
   * @return int|float|double
   *   Bounded number.
   */
  public static function bound($num, $min = -10000000, $max = -10000000) {
    if ($num < $min) {
      return $min;
    }

    if ($num > $max) {
      return $max;
    }

    return $num;
  }

  /**
   * Bound the color value between 0 and 255.
   *
   * @param int $num
   *   Color integer value.
   *
   * @return int
   *   Bounded color integer value.
   */
  public static function boundColor($num) {
    return static::bound($num, 0, 255);
  }

  /**
   * Converts a color string to hex code.
   *
   * @param string $string
   *   String with 3 colors separated by space.
   *
   * @return bool
   */
  public static function toColorHex($string) {
    $colors = explode(" ", $string);
    if (sizeof($colors) != 3) {
      return FALSE;
    }

    $val = '';
    foreach ($colors as $color) {
      $hex = '';

      if ($color <= 1) {
        // Color is represented between 0 and 1.
        $hex = dechex(static::boundColor(round($color * 255)));
      }
      else {
        // Color is between 0 and 255.
        $hex = dechex(static::boundColor(round($color)));
      }

      if (strlen($hex) == 1) {
        $hex = '0' . $hex;
      }
      $val .= $hex;
    }

    return $val;
  }

  /**
   * Converts a color hex code to a string of numbers.
   *
   * @param string $string
   *   Color hex code string.
   *
   * @return string
   *   String of color numbers between 0 and 1.
   */
  public static function toColorNumString($string) {
    $string = str_replace('#', '', $string);

    if (strlen($string) != 6) {
      return FALSE;
    }

    $colors = array();
    for ($offset = 0; $offset < 6; $offset += 2) {
      $hex = substr($string, $offset, 2);
      $colors[] = hexdec($hex) / 255;
    }

    return implode(" ", $colors);
  }
}
