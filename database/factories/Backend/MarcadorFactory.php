<?php

namespace Database\Factories\backend;

use App\Models\backend\Marcador;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarcadorFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Marcador::class;
  protected $table = 'marcadores';

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    $this->fncCrearColores();
    return [];
  }

  public function fncCrearColores()
  {
    $colores = $this->colores();
    foreach ($colores as $key => $v) {
      // $v = $colores[$this->i];

      $nombre = Str::title($v['name']);
      $slug = Str::slug($v['name']);
      $hexa = '#' . $v['hexa'];
      $rgb = $v['rgb'];
      $metadata = json_encode([$nombre, $hexa, $rgb]);
      $is_active = $this->faker->numberBetween(0, 1);
      // dd($is_active, $metadata);

      $sql = "INSERT INTO $this->table (`nombre`,`slug`,`hexa`,`rgb`,`metadata`, `is_active`) VALUES (
                '$nombre', '$slug', '$hexa', '$rgb', '$metadata', '$is_active');";

      DB::statement($sql);
    }
    return;
  }

  public function colores()
  {
    $colores = [
      [
        'name' => 'Alice azul',
        'hexa' => 'F0F8FF',
        'rgb' => 'rgb (240,248,255)',
      ],
      [
        'name' => 'Baker-Miller rosa',
        'hexa' => 'FF91AF',
        'rgb' => 'rgb (255,145,175)',
      ],
      [
        'name' => 'Blanco antiguo',
        'hexa' => 'FAEBD7',
        'rgb' => 'rgb (250,235,215)',
      ],
      [
        'name' => 'Aguamarina',
        'hexa' => '7FFFD4',
        'rgb' => 'rgb (127,255,212)',
      ],
      [
        'name' => 'Azur',
        'hexa' => 'F0FFFF',
        'rgb' => 'rgb (240,255,255)',
      ],
      [
        'name' => 'Beige',
        'hexa' => 'F5F5DC',
        'rgb' => 'rgb (245,245,220)',
      ],
      [
        'name' => 'Sopa de mariscos',
        'hexa' => 'FFE4C4',
        'rgb' => 'rgb (255,228,196)',
      ],
      [
        'name' => 'Negro',
        'hexa' => '000000',
        'rgb' => 'rgb (0,0,0)',
      ],
      [
        'name' => 'BlanchedAlmond',
        'hexa' => 'FFEBCD',
        'rgb' => 'rgb (255,235,205)',
      ],
      [
        'name' => 'Azul',
        'hexa' => '0000FF',
        'rgb' => 'rgb (0,0,255)',
      ],
      [
        'name' => 'Violeta Azul',
        'hexa' => '8A2BE2',
        'rgb' => 'rgb (138,43,226)',
      ],
      [
        'name' => 'marrón',
        'hexa' => 'A52A2A',
        'rgb' => 'rgb (165,42,42)',
      ],
      [
        'name' => 'BurlyWood',
        'hexa' => 'DEB887',
        'rgb' => 'rgb (222,184,135)',
      ],
      [
        'name' => 'CadetBlue',
        'hexa' => '5F9EA0',
        'rgb' => 'rgb (95,158,160)',
      ],
      [
        'name' => 'Monasterio',
        'hexa' => '7FFF00',
        'rgb' => 'rgb (127,255,0)',
      ],
      [
        'name' => 'Chocolate',
        'hexa' => 'D2691E',
        'rgb' => 'rgb (210,105,30)',
      ],
      [
        'name' => 'Coral',
        'hexa' => 'FF7F50',
        'rgb' => 'rgb (255,127,80)',
      ],
      [
        'name' => 'Aciano Azul',
        'hexa' => '6495ED',
        'rgb' => 'rgb (100,149,237)',
      ],
      [
        'name' => 'Seda de maiz',
        'hexa' => 'FFF8DC',
        'rgb' => 'rgb (255,248,220)',
      ],
      [
        'name' => 'carmesí',
        'hexa' => 'DC143C',
        'rgb' => 'rgb (220,20,60)',
      ],
      [
        'name' => 'Cian',
        'hexa' => '00FFFF',
        'rgb' => 'rgb (0,255,255)',
      ],
      [
        'name' => 'Azul oscuro',
        'hexa' => '00008B',
        'rgb' => 'rgb (0,0,139)',
      ],
      [
        'name' => 'DarkCyan',
        'hexa' => '008B8B',
        'rgb' => 'rgb (0,139,139)',
      ],
      [
        'name' => 'DarkGoldenRod',
        'hexa' => 'B8860B',
        'rgb' => 'rgb (184,134,11)',
      ],
      [
        'name' => 'Gris oscuro',
        'hexa' => 'A9A9A9',
        'rgb' => 'rgb (169,169,169)',
      ],
      [
        'name' => 'Verde oscuro',
        'hexa' => '006400',
        'rgb' => 'rgb (0,100,0)',
      ],
      [
        'name' => 'DarkKhaki',
        'hexa' => 'BDB76B',
        'rgb' => 'rgb (189,183,107)',
      ],
      [
        'name' => 'DarkMagenta',
        'hexa' => '8B008B',
        'rgb' => 'rgb (139,0,139)',
      ],
      [
        'name' => 'DarkOliveGreen',
        'hexa' => '556B2F',
        'rgb' => 'rgb (85,107,47)',
      ],
      [
        'name' => 'Naranja oscuro',
        'hexa' => 'FF8C00',
        'rgb' => 'rgb (255,140,0)',
      ],
      [
        'name' => 'Orquídea Oscura',
        'hexa' => '9932CC',
        'rgb' => 'rgb (153,50,204)',
      ],
      [
        'name' => 'Rojo oscuro',
        'hexa' => '8B0000',
        'rgb' => 'rgb (139,0,0)',
      ],
      [
        'name' => 'Salmón oscuro',
        'hexa' => 'E9967A',
        'rgb' => 'rgb (233,150,122)',
      ],
      [
        'name' => 'DarkSeaGreen',
        'hexa' => '8FBC8F',
        'rgb' => 'rgb (143,188,143)',
      ],
      [
        'name' => 'DarkSlateBlue',
        'hexa' => '483D8B',
        'rgb' => 'rgb (72,61,139)',
      ],
      [
        'name' => 'DarkSlateGrey',
        'hexa' => '2F4F4F',
        'rgb' => 'rgb (47,79,79)',
      ],
      [
        'name' => 'DarkTurquoise',
        'hexa' => '00CED1',
        'rgb' => 'rgb (0,206,209)',
      ],
      [
        'name' => 'Violeta oscuro',
        'hexa' => '9400D3',
        'rgb' => 'rgb (148,0,211)',
      ],
      [
        'name' => 'Rosa profundo',
        'hexa' => 'FF1493',
        'rgb' => 'rgb (255,20,147)',
      ],
      [
        'name' => 'DeepSkyBlue',
        'hexa' => '00BFFF',
        'rgb' => 'rgb (0,191,255)',
      ],
      [
        'name' => 'DimGrey',
        'hexa' => '696969',
        'rgb' => 'rgb (105,105,105)',
      ],
      [
        'name' => 'DodgerBlue',
        'hexa' => '1E90FF',
        'rgb' => 'rgb (30,144,255)',
      ],
      [
        'name' => 'Ladrillo refractario',
        'hexa' => 'B22222',
        'rgb' => 'rgb (178,34,34)',
      ],
      [
        'name' => 'FloralWhite',
        'hexa' => 'FFFAF0',
        'rgb' => 'rgb (255,250,240)',
      ],
      [
        'name' => 'Bosque verde',
        'hexa' => '228B22',
        'rgb' => 'rgb (34,139,34)',
      ],
      [
        'name' => 'Fucsia',
        'hexa' => 'FF00FF',
        'rgb' => 'rgb (255,0,255)',
      ],
      [
        'name' => 'Gainsboro',
        'hexa' => 'DCDCDC',
        'rgb' => 'rgb (220,220,220)',
      ],
      [
        'name' => 'Fantasma blanco',
        'hexa' => 'F8F8FF',
        'rgb' => 'rgb (248,248,255)',
      ],
      [
        'name' => 'Oro',
        'hexa' => 'FFD700',
        'rgb' => 'rgb (255,215,0)',
      ],
      [
        'name' => 'Vara de oro',
        'hexa' => 'DAA520',
        'rgb' => 'rgb (218,165,32)',
      ],
      [
        'name' => 'Gris',
        'hexa' => '808080',
        'rgb' => 'rgb (128,128,128)',
      ],
      [
        'name' => 'Verde',
        'hexa' => '008000',
        'rgb' => 'rgb (0,128,0)',
      ],
      [
        'name' => 'Verde amarillo',
        'hexa' => 'ADFF2F',
        'rgb' => 'rgb (173,255,47)',
      ],
      [
        'name' => 'Gotas de miel',
        'hexa' => 'F0FFF0',
        'rgb' => 'rgb (240,255,240)',
      ],
      [
        'name' => 'Rosa caliente',
        'hexa' => 'FF69B4',
        'rgb' => 'rgb (255,105,180)',
      ],
      [
        'name' => 'IndianRed',
        'hexa' => 'CD5C5C',
        'rgb' => 'rgb (205,92,92)',
      ],
      [
        'name' => 'Índigo',
        'hexa' => '4B0082',
        'rgb' => 'rgb (75,0,130)',
      ],
      [
        'name' => 'Marfil',
        'hexa' => 'FFFFF0',
        'rgb' => 'rgb (255,255,240)',
      ],
      [
        'name' => 'Caqui',
        'hexa' => 'F0E68C',
        'rgb' => 'rgb (240,230,140)',
      ],
      [
        'name' => 'Lavanda',
        'hexa' => 'E6E6FA',
        'rgb' => 'rgb (230,230,250)',
      ],
      [
        'name' => 'LavenderBlush',
        'hexa' => 'FFF0F5',
        'rgb' => 'rgb (255,240,245)',
      ],
      [
        'name' => 'Césped verde',
        'hexa' => '7CFC00',
        'rgb' => 'rgb (124,252,0)',
      ],
      [
        'name' => 'Limón chiffon',
        'hexa' => 'FFFACD',
        'rgb' => 'rgb (255,250,205)',
      ],
      [
        'name' => 'Azul claro',
        'hexa' => 'ADD8E6',
        'rgb' => 'rgb (173,216,230)',
      ],
      [
        'name' => 'LightCoral',
        'hexa' => 'F08080',
        'rgb' => 'rgb (240,128,128)',
      ],
      [
        'name' => 'Cian claro',
        'hexa' => 'E0FFFF',
        'rgb' => 'rgb (224,255,255)',
      ],
      [
        'name' => 'LightGoldenRodYellow',
        'hexa' => 'FAFAD2',
        'rgb' => 'rgb (250,250,210)',
      ],
      [
        'name' => 'Gris claro',
        'hexa' => 'D3D3D3',
        'rgb' => 'rgb (211,211,211)',
      ],
      [
        'name' => 'Verde claro',
        'hexa' => '90EE90',
        'rgb' => 'rgb (144,238,144)',
      ],
      [
        'name' => 'Rosa claro',
        'hexa' => 'FFB6C1',
        'rgb' => 'rgb (255,182,193)',
      ],
      [
        'name' => 'Salmón de luz',
        'hexa' => 'FFA07A',
        'rgb' => 'rgb (255,160,122)',
      ],
      [
        'name' => 'LightSeaGreen',
        'hexa' => '20B2AA',
        'rgb' => 'rgb (32,178,170)',
      ],
      [
        'name' => 'LightSkyBlue',
        'hexa' => '87CEFA',
        'rgb' => 'rgb (135,206,250)',
      ],
      [
        'name' => 'LightSlateGray',
        'hexa' => '778899',
        'rgb' => 'rgb (119,136,153)',
      ],
      [
        'name' => 'LightSteelBlue',
        'hexa' => 'B0C4DE',
        'rgb' => 'rgb (176,196,222)',
      ],
      [
        'name' => 'Amarillo claro',
        'hexa' => 'FFFFE0',
        'rgb' => 'rgb (255,255,224)',
      ],
      [
        'name' => 'Lima',
        'hexa' => '00FF00',
        'rgb' => 'rgb (0,255,0)',
      ],
      [
        'name' => 'Verde lima',
        'hexa' => '32CD32',
        'rgb' => 'rgb (50,205,50)',
      ],
      [
        'name' => 'Lino',
        'hexa' => 'FAF0E6',
        'rgb' => 'rgb (250,240,230)',
      ],
      [
        'name' => 'Granate',
        'hexa' => '800000',
        'rgb' => 'rgb (128,0,0)',
      ],
      [
        'name' => 'MedioAquaMarine',
        'hexa' => '66CDAA',
        'rgb' => 'rgb (102,205,170)',
      ],
      [
        'name' => 'Azul medio',
        'hexa' => '0000CD',
        'rgb' => 'rgb (0,0,205)',
      ],
      [
        'name' => 'MediumOrchid',
        'hexa' => 'BA55D3',
        'rgb' => 'rgb (186,85,211)',
      ],
      [
        'name' => 'Mediano',
        'hexa' => '9370DB',
        'rgb' => 'rgb (147,112,219)',
      ],
      [
        'name' => 'MediumSeaGreen',
        'hexa' => '3CB371',
        'rgb' => 'rgb (60,179,113)',
      ],
      [
        'name' => 'MediumSlateBlue',
        'hexa' => '7B68EE',
        'rgb' => 'rgb (123,104,238)',
      ],
      [
        'name' => 'MediumSpringGreen',
        'hexa' => '00FA9A',
        'rgb' => 'rgb (0,250,154)',
      ],
      [
        'name' => 'MediumTurquoise',
        'hexa' => '48D1CC',
        'rgb' => 'rgb (72,209,204)',
      ],
      [
        'name' => 'MediumVioletRed',
        'hexa' => 'C71585',
        'rgb' => 'rgb (199,21,133)',
      ],
      [
        'name' => 'MidnightBlue',
        'hexa' => '191970',
        'rgb' => 'rgb (25,25,112)',
      ],
      [
        'name' => 'MintCream',
        'hexa' => 'F5FFFA',
        'rgb' => 'rgb (245,255,250)',
      ],
      [
        'name' => 'MistyRose',
        'hexa' => 'FFE4E1',
        'rgb' => 'rgb (255,228,225)',
      ],
      [
        'name' => 'Mocasín',
        'hexa' => 'FFE4B5',
        'rgb' => 'rgb (255,228,181)',
      ],
      [
        'name' => 'Navajoblanco',
        'hexa' => 'FFDEAD',
        'rgb' => 'rgb (255,222,173)',
      ],
      [
        'name' => 'Armada',
        'hexa' => '000080',
        'rgb' => 'rgb (0,0,128)',
      ],
      [
        'name' => 'Antiguo lace',
        'hexa' => 'FDF5E6',
        'rgb' => 'rgb (253,245,230)',
      ],
      [
        'name' => 'Aceituna',
        'hexa' => '808000',
        'rgb' => 'rgb (128,128,0)',
      ],
      [
        'name' => 'Verde oliva',
        'hexa' => '6B8E23',
        'rgb' => 'rgb (107,142,35)',
      ],
      [
        'name' => 'naranja',
        'hexa' => 'FFA500',
        'rgb' => 'rgb (255,165,0)',
      ],
      [
        'name' => 'Rojo naranja',
        'hexa' => 'FF4500',
        'rgb' => 'rgb (255,69,0)',
      ],
      [
        'name' => 'Orquídea',
        'hexa' => 'DA70D6',
        'rgb' => 'rgb (218,112,214)',
      ],
      [
        'name' => 'PaleGoldenRod',
        'hexa' => 'EEE8AA',
        'rgb' => 'rgb (238,232,170)',
      ],
      [
        'name' => 'Verde pálido',
        'hexa' => '98FB98',
        'rgb' => 'rgb (152,251,152)',
      ],
      [
        'name' => 'PaleTurquoise',
        'hexa' => 'AFEEEE',
        'rgb' => 'rgb (175,238,238)',
      ],
      [
        'name' => 'PaleVioletRed',
        'hexa' => 'DB7093',
        'rgb' => 'rgb (219,112,147)',
      ],
      [
        'name' => 'PapayaWhip',
        'hexa' => 'FFEFD5',
        'rgb' => 'rgb (255,239,213)',
      ],
      [
        'name' => 'Peachpuff',
        'hexa' => 'FFDAB9',
        'rgb' => 'rgb (255,218,185)',
      ],
      [
        'name' => 'Perú',
        'hexa' => 'CD853F',
        'rgb' => 'rgb (205,133,63)',
      ],
      [
        'name' => 'Rosado',
        'hexa' => 'FFC0CB',
        'rgb' => 'rgb (255,192,203)',
      ],
      [
        'name' => 'ciruela',
        'hexa' => 'DDA0DD',
        'rgb' => 'rgb (221,160,221)',
      ],
      [
        'name' => 'Azul pálido',
        'hexa' => 'B0E0E6',
        'rgb' => 'rgb (176,224,230)',
      ],
      [
        'name' => 'Púrpura',
        'hexa' => '800080',
        'rgb' => 'rgb (128,0,128)',
      ],
      [
        'name' => 'RebeccaPurple',
        'hexa' => '663399',
        'rgb' => 'rgb (102,51,153)',
      ],
      [
        'name' => 'rojo',
        'hexa' => 'FF0000',
        'rgb' => 'rgb (255,0,0)',
      ],
      [
        'name' => 'RosyBrown',
        'hexa' => 'BC8F8F',
        'rgb' => 'rgb (188,143,143)',
      ],
      [
        'name' => 'Azul real',
        'hexa' => '4169E1',
        'rgb' => 'rgb (65,105,225)',
      ],
      [
        'name' => 'SaddleBrown',
        'hexa' => '8B4513',
        'rgb' => 'rgb (139,69,19)',
      ],
      [
        'name' => 'Salmón',
        'hexa' => 'FA8072',
        'rgb' => 'rgb (250,128,114)',
      ],
      [
        'name' => 'SandyBrown',
        'hexa' => 'F4A460',
        'rgb' => 'rgb (244,164,96)',
      ],
      [
        'name' => 'Mar verde',
        'hexa' => '2E8B57',
        'rgb' => 'rgb (46,139,87)',
      ],
      [
        'name' => 'Concha',
        'hexa' => 'FFF5EE',
        'rgb' => 'rgb (255,245,238)',
      ],
      [
        'name' => 'Tierra de siena',
        'hexa' => 'A0522D',
        'rgb' => 'rgb (160,82,45)',
      ],
      [
        'name' => 'Plata',
        'hexa' => 'C0C0C0',
        'rgb' => 'rgb (192,192,192)',
      ],
      [
        'name' => 'Cielo azul',
        'hexa' => '87CEEB',
        'rgb' => 'rgb (135,206,235)',
      ],
      [
        'name' => 'SlateBlue',
        'hexa' => '6A5ACD',
        'rgb' => 'rgb (106,90,205)',
      ],
      [
        'name' => 'SlateGray',
        'hexa' => '708090',
        'rgb' => 'rgb (112,128,144)',
      ],
      [
        'name' => 'Nieve',
        'hexa' => 'FFAFA',
        'rgb' => 'rgb (255,250,250)',
      ],
      [
        'name' => 'Primavera verde',
        'hexa' => '00FF7F',
        'rgb' => 'rgb (0,255,127)',
      ],
      [
        'name' => 'Azul acero',
        'hexa' => '4682B4',
        'rgb' => 'rgb (70,130,180)',
      ],
      [
        'name' => 'Bronceado',
        'hexa' => 'D2B48C',
        'rgb' => 'rgb (210,180,140)',
      ],
      [
        'name' => 'Teal',
        'hexa' => '008080',
        'rgb' => 'rgb (0,128,128)',
      ],
      [
        'name' => 'Cardo',
        'hexa' => 'D8BFD8',
        'rgb' => 'rgb (216,191,216)',
      ],
      [
        'name' => 'Tomate',
        'hexa' => 'FF6347',
        'rgb' => 'rgb (255,99,71)',
      ],
      [
        'name' => 'Turquesa',
        'hexa' => '40E0D0',
        'rgb' => 'rgb (64,224,208)',
      ],
      [
        'name' => 'Violeta',
        'hexa' => 'EE82EE',
        'rgb' => 'rgb (238,130,238)',
      ],
      [
        'name' => 'Trigo',
        'hexa' => 'F5DEB3',
        'rgb' => 'rgb (245,222,179)',
      ],
      [
        'name' => 'Blanco',
        'hexa' => 'FFFFFF',
        'rgb' => 'rgb (255,255,255)',
      ],
      [
        'name' => 'Humo blanco',
        'hexa' => 'F5F5F5',
        'rgb' => 'rgb (245,245,245)',
      ],
      [
        'name' => 'Amarillo',
        'hexa' => 'FFFF00',
        'rgb' => 'rgb (255,255,0)',
      ],
      [
        'name' => 'Amarillo verde',
        'hexa' => '9ACD32',
        'rgb' => 'rgb (154,205,50)',
      ],
    ];

    return $colores;
  }
}
