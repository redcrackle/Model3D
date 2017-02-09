<?php

namespace Model3D;


class Material {

  public $name;
  public $uuid;
  public $Ka;
  public $Kd;
  public $Ks;
  public $Ke;
  public $Ns;
  public $Ni;
  public $Tr;
  public $Tf;
  public $d;
  public $illum;

  /**
   * @var Texture
   */
  public $map_Ka;

  /**
   * @var Texture
   */
  public $map_Kd;

  /**
   * @var Texture
   */
  public $map_Ks;

  /**
   * @var Texture
   */
  public $map_Ns;

  /**
   * @var Texture
   */
  public $map_d;

  /**
   * @var Texture
   */
  public $bump;

  /**
   * @var Texture
   */
  public $disp;

  /**
   * @var Texture
   */
  public $decal;

  /**
   * @var Texture
   */
  public $refl;

  public $error = '';

  public function __construct($input, $directory = '') {
    switch (gettype($input)) {
      case 'string':
        $this->createMaterialFromText($input, $directory);
        break;
      case 'array':
        $this->createMaterialFromArray($input);
        break;
      default:
        $this->error = 'Could not parse the material information.';
        return;
    }

    if (empty($this->name)) {
      $this->error = 'Material name not specified.';
    }
  }

  /**
   * Validate that the number is in the given range.
   *
   * @param int|float|double $num
   *   Number to be compared.
   * @param int|float|double $min
   *   Minimum of the range.
   * @param int|float|double $max
   *   Maximum of the range.
   *
   * @return bool
   *   TRUE if the number is in the range and FALSE otherwise.
   */
  private function validateRange($num, $min = -1000000000, $max = -1000000000) {
    if ($num > $max || $num < $min) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Returns the error.
   *
   * @return string
   *   Error string.
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Create material object from MTL file text.
   *
   * @param string $text
   *   Text string.
   * @param string $directory
   *   Directory where textures are kept.
   */
  private function createMaterialFromText($text, $directory = '') {
    $lines = explode(PHP_EOL, $text);
    foreach ($lines as $line) {
      $line = trim($line);

      if (strpos($line, 'newmtl ') === 0) {
        $this->name = trim(substr($line, 7));
      }
      elseif (strpos($line, 'Ns ') === 0) {
        $this->Ns = trim(substr($line, 3));
        if (!is_numeric($this->Ns)) {
          $this->error = 'Ns: ' . $this->Ns . ' in material ' . $this->name . ' is not a number.';
          return;
        }
      }
      elseif (strpos($line, 'Ni ') === 0) {
        $this->Ni = trim(substr($line, 3));
        if (!is_numeric($this->Ni)) {
          $this->error = 'Ni: ' . $this->Ni . ' in material ' . $this->name . ' is not a number.';
          return;
        }
      }
      elseif (strpos($line, 'd ') === 0) {
        $this->d = trim(substr($line, 2));
        if (!is_numeric($this->d)) {
          $this->error = 'd: ' . $this->d . ' in material ' . $this->name . ' is not a number.';
          return;
        }

        if (!$this->validateRange($this->d, 0, 1)) {
          $this->error = 'd: ' . $this->d . ' in material ' . $this->name . ' needs to be a number between 0 and 1.';
          return;
        }
      }
      elseif (strpos($line, 'Tr ') === 0) {
        $tr = trim(substr($line, 2));
        if (!is_numeric($tr)) {
          $this->error = 'Tr: ' . $tr . ' in material ' . $this->name . ' is not a number.';
          return;
        }

        $this->d = 1 - $tr;

        if (!$this->validateRange($this->d, 0, 1)) {
          $this->error = 'Tr: ' . $tr . ' in material ' . $this->name . ' needs to be a number between 0 and 1.';
          return;
        }
      }
      elseif (strpos($line, 'Tf ') === 0) {
        $this->Tf = trim(substr($line, 3));
        $colors = explode(" ", $this->Tf);
        if (sizeof($colors) != 3) {
          $this->error = 'Format for Tf: ' . $this->Tf . ' in material ' . $this->name . ' is incorrect.';
          return;
        }

        foreach ($colors as $color) {
          if (!is_numeric($color)) {
            $this->error = 'Tf: ' . $this->Tf . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }

          if (!$this->validateRange($color, 0, 1)) {
            $this->error = 'Tf: ' . $this->Tf . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }
        }
      }
      elseif (strpos($line, 'illum ') === 0) {
        $this->illum = trim(substr($line, 6));
        if (!is_numeric($this->illum)) {
          $this->error = 'illum: ' . $this->illum . ' in material ' . $this->name . ' is not an integer.';
          return;
        }

        if (!$this->validateRange($this->illum, 0, 10)) {
          $this->error = 'illum: ' . $this->illum . ' in material ' . $this->name . ' needs to be an integer between 0 and 10.';
          return;
        }
      }
      elseif (strpos($line, 'Ka ') === 0) {
        $this->Ka = trim(substr($line, 3));
        $colors = explode(" ", $this->Ka);
        if (sizeof($colors) != 3) {
          $this->error = 'Format for Ka: ' . $this->Ka . ' in material ' . $this->name . ' is incorrect.';
          return;
        }

        foreach ($colors as $color) {
          if (!is_numeric($color)) {
            $this->error = 'Ka: ' . $this->Ka . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }

          if (!$this->validateRange($color, 0, 1)) {
            $this->error = 'Ka: ' . $this->Ka . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }
        }
      }
      elseif (strpos($line, 'Kd ') === 0) {
        $this->Kd = trim(substr($line, 3));
        $colors = explode(" ", $this->Kd);
        if (sizeof($colors) != 3) {
          $this->error = 'Format for Kd: ' . $this->Kd . ' in material ' . $this->name . ' is incorrect.';
          return;
        }

        foreach ($colors as $color) {
          if (!is_numeric($color)) {
            $this->error = 'Kd: ' . $this->Kd . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }

          if (!$this->validateRange($color, 0, 1)) {
            $this->error = 'Kd: ' . $this->Kd . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }
        }
      }
      elseif (strpos($line, 'Ks ') === 0) {
        $this->Ks = trim(substr($line, 3));
        $colors = explode(" ", $this->Ks);
        if (sizeof($colors) != 3) {
          $this->error = 'Format for Ks: ' . $this->Ks . ' in material ' . $this->name . ' is incorrect.';
          return;
        }

        foreach ($colors as $color) {
          if (!is_numeric($color)) {
            $this->error = 'Ks: ' . $this->Ks . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }

          if (!$this->validateRange($color, 0, 1)) {
            $this->error = 'Ks: ' . $this->Ks . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }
        }
      }
      elseif (strpos($line, 'Ke ') === 0) {
        $this->Ke = trim(substr($line, 3));
        $colors = explode(" ", $this->Ke);
        if (sizeof($colors) != 3) {
          $this->error = 'Format for Ke: ' . $this->Ke . ' in material ' . $this->name . ' is incorrect.';
          return;
        }

        foreach ($colors as $color) {
          if (!is_numeric($color)) {
            $this->error = 'Ke: ' . $this->Ke . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }

          if (!$this->validateRange($color, 0, 1)) {
            $this->error = 'Ke: ' . $this->Ke . ' in material ' . $this->name . ' needs to have 3 numbers between 0 and 1.';
            return;
          }
        }
      }
      elseif (strpos($line, 'map_Ka ') === 0) {
        $this->map_Ka = new Texture(trim(substr($line, 7)), $directory);
        if ($error = $this->map_Ka->getError()) {
          $this->error = $error;
          return;
        }
      }
      elseif (strpos($line, 'map_Kd ') === 0) {
        $this->map_Kd = new Texture(trim(substr($line, 7)), $directory);
        if ($error = $this->map_Kd->getError()) {
          $this->error = $error;
          return;
        }
      }
      elseif (strpos($line, 'map_Ks ') === 0) {
        $this->map_Ks = new Texture(trim(substr($line, 7)), $directory);
        if ($error = $this->map_Ks->getError()) {
          $this->error = $error;
          return;
        }
      }
      elseif (strpos($line, 'map_Ns ') === 0) {
        $this->map_Ns = new Texture(trim(substr($line, 7)), $directory);
        if ($error = $this->map_Ns->getError()) {
          $this->error = $error;
          return;
        }
      }
      elseif (strpos($line, 'map_d ') === 0) {
        $this->map_d = new Texture(trim(substr($line, 6)), $directory);
        if ($error = $this->map_d->getError()) {
          $this->error = $error;
          return;
        }
      }
      elseif (strpos($line, 'bump ') === 0) {
        $this->bump = new Texture(trim(substr($line, 5)), $directory);
        if ($error = $this->bump->getError()) {
          $this->error = $error;
          return;
        }
      }
      elseif (strpos($line, 'disp ') === 0) {
        $this->disp = new Texture(trim(substr($line, 5)), $directory);
        if ($error = $this->disp->getError()) {
          $this->error = $error;
          return;
        }
      }
      elseif (strpos($line, 'decal ') === 0) {
        $this->decal = new Texture(trim(substr($line, 6)), $directory);
        if ($error = $this->decal->getError()) {
          $this->error = $error;
          return;
        }
      }
      elseif (strpos($line, 'refl ') === 0) {
        $this->refl = new Texture(trim(substr($line, 5)), $directory);
        if ($error = $this->refl->getError()) {
          $this->error = $error;
          return;
        }
      }
    }
  }

  private function createMaterialFromArray($input) {
    if ($input['type'] != 'material') {
      $this->error = 'Supplied array does not represent a material.';
      return;
    }

    if (!empty($input['name'])) {
      $this->name = $input['name'];
    }

    if (!empty($input['Ka'])) {
      $this->Ka = $input['Ka'];
    }

    if (!empty($input['Kd'])) {
      $this->Kd = $input['Kd'];
    }

    if (!empty($input['Ks'])) {
      $this->Ks = $input['Ks'];
    }

    if (!empty($input['Ke'])) {
      $this->Ke = $input['Ke'];
    }

    if (!empty($input['Tf'])) {
      $this->Tf = $input['Tf'];
    }

    if (!empty($input['d'])) {
      $this->d = $input['d'];
    }

    if (!empty($input['Ns'])) {
      $this->Ns = $input['Ns'];
    }

    if (!empty($input['Ni'])) {
      $this->Ni = $input['Ni'];
    }

    if (!empty($input['illum'])) {
      $this->illum = $input['illum'];
    }

    if (!empty($input['map_Ka'])) {
      $map = new Texture($input['map_Ka']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->map_Ka = $map;
    }

    if (!empty($input['map_Kd'])) {
      $map = new Texture($input['map_Kd']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->map_Kd = $map;
    }

    if (!empty($input['map_Ks'])) {
      $map = new Texture($input['map_Ks']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->map_Ks = $map;
    }

    if (!empty($input['map_Ns'])) {
      $map = new Texture($input['map_Ns']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->map_Ns = $map;
    }

    if (!empty($input['map_d'])) {
      $map = new Texture($input['map_d']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->map_d = $map;
    }

    if (!empty($input['bump'])) {
      $map = new Texture($input['bump']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->bump = $map;
    }

    if (!empty($input['disp'])) {
      $map = new Texture($input['disp']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->disp = $map;
    }

    if (!empty($input['decal'])) {
      $map = new Texture($input['decal']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->decal = $map;
    }

    if (!empty($input['refl'])) {
      $map = new Texture($input['refl']);
      if ($error = $map->getError()) {
        $this->error = $error;
        return;
      }

      $this->refl = $map;
    }
  }

  public function toJsonObject() {
    $output = new \stdClass();

    if (!empty($this->name)) {
      $output->DbgName = $this->name;
    }

    return $output;
  }
}
