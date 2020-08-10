<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele

use \system\classes\Core;

require_once join_path(Core::getPackageDetails('core', 'root'), 'modules', 'modals', 'record_editor_modal.php');


include_once __DIR__.'/../utils.php';

$tiles = [
	"3way" => ["0", "90", "180", "270"],
	"4way" => ["0"],
	"curve" => ["0", "90", "180", "270"],
	"grass" => ["0"],
	"parking_lot" => ["0"],
	"straight_2stop" => ["0", "90"],
	"straight_stop" => ["0", "90", "180", "270"],
	"straight" => ["0", "90"]
];

// things that we get from $_GET
$grid_width = 2;
$grid_height = 2;
$grid = [
    'cell_0_0' => 'curve_270',
    'cell_0_1' => 'curve_0',
    'cell_1_0' => 'curve_180',
    'cell_1_1' => 'curve_90'
];

$empty_tile_file = join_path(Core::getPackageDetails('duckietown', 'root'), 'images', 'empty_tile_plain.svg');
$empty_tile = file_get_contents($empty_tile_file);

$tile_svg = [
    'empty' => $empty_tile
];

foreach ($tiles as $tile => $_) {
    $tile_file = join_path(Core::getPackageDetails('duckietown', 'root'), 'images', $tile.'_tile_plain.svg');
    $tile_content = file_get_contents($tile_file);

    // TODO: to be removed, store raw xml
    // $tile_svg[$tile] = $tile_content;

    // store tiles as DOMDocument(s)
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    @$doc->loadHTML($tile_content);
    $tile_svg[$tile] = $doc;

    // TODO: to be removed, store SimpleXMLElement
    // $dom = new SimpleXMLElement( $tile_content );
    // $dom->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');
    // $tile_svg[$tile] = $dom;
}

// $dom = new DomDocument("1.0", "UTF-8");
// $dom->loadXML($empty_tile);

// $dom->load( $empty_tile_file );

$town_svg = '
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   version="1.1"
   id="svg2"
   viewBox="0 0 #[width] #[height]"
   height="#[height]mm"
   width="#[width]mm">
  <defs id="defs4"/>
  <metadata
     id="metadata7">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title></dc:title>
      </cc:Work>
    </rdf:RDF>
  </metadata>

  #[tiles]

</svg>
';

$correction_offset = 0.24;
$tile_section_xml = '';
$cur_y = 0;
for ($i = 0; $i < $grid_height; $i++) {
    $cur_x = 0;
    $max_height = 0;
    for ($j = 0; $j < $grid_width; $j++) {
        // get name and orientation of the tile in grid[i,j]
        $cell_id = sprintf('cell_%d_%d', $i, $j);
        $cur_tile = $grid[$cell_id];
        $parts = explode( '_', $cur_tile );
        $cur_tile_name = $parts[0];
        $cur_tile_orientation = $parts[1];
        // get the svg of the current tile (i.e., grid[i,j])
        $cur_tile_svg = $tile_svg[$cur_tile_name];
        // get the parts of the SVG that we need
        $xpath = new DOMXpath($cur_tile_svg);
        // get width
        $cur_tile_width_str = trim($xpath->query('//svg/@width')->item(0)->nodeValue);
        preg_match_all("/^([0-9\.]+)mm$/", $cur_tile_width_str, $matches);
        $cur_tile_width = floatval($matches[1][0]);
        // get height
        $cur_tile_height_str = trim($xpath->query('//svg/@height')->item(0)->nodeValue);
        preg_match_all("/^([0-9\.]+)mm$/", $cur_tile_height_str, $matches);
        $cur_tile_height = floatval($matches[1][0]);
        // get translation along X and Y
        $cur_tile_translate_str = trim($xpath->query('//svg/g/@transform')->item(0)->nodeValue);
        preg_match_all("/^translate\((-?[0-9\.]+),(-?[0-9\.]+)\)$/", $cur_tile_translate_str, $matches);
        $cur_tile_translate_x = floatval($matches[1][0]);
        $cur_tile_translate_y = floatval($matches[2][0]);
        // compute new translation for the current tile
        $cur_tile_x = $cur_tile_translate_x + $cur_x;
        $cur_tile_y = $cur_tile_translate_y + $cur_y;
        // extract G element
        $cur_tile_g_node = $xpath->query('//svg/g')->item(0);
        $cur_tile_g_inner_html = innerHTML($cur_tile_g_node);
        // compute the point around which the tile will be rotated (this is the center of the tile in the final position)
        $cur_tile_rotate_wrt_x = $cur_x + $cur_tile_width / 2.0;
        $cur_tile_rotate_wrt_y = $cur_y + $cur_tile_height / 2.0;
        // compute translation offset (fixes translation error due to rotation)
        $cur_tile_x_off = 0;
        $cur_tile_y_off = 0;
        if( $cur_tile_orientation == 90 ){
            $cur_tile_x_off = -$correction_offset;
            $cur_tile_y_off = $correction_offset;
        }elseif( $cur_tile_orientation == 270 ) {
            $cur_tile_x_off = $correction_offset;
            $cur_tile_y_off = -$correction_offset;
        }
        // compile svg fragment for the current tile
        $cur_tile_g_outer_html = sprintf(
            '<g transform="translate(%.6f,%.6f) rotate(%d,%.6f,%.6f) translate(%.6f,%.6f)" id="%s" tile_type="%s" tile_angle="%d">
            %s
            </g>',
            $cur_tile_x_off,
            $cur_tile_y_off,

            $cur_tile_orientation,
            $cur_tile_rotate_wrt_x,
            $cur_tile_rotate_wrt_y,

            $cur_tile_x,
            $cur_tile_y,

            $cell_id,
            $cur_tile_name,
            $cur_tile_orientation,
            $cur_tile_g_inner_html
        );
        // append current tile to the tile section of the final SVG
        $tile_section_xml .= sprintf('
            <!-- Tile [%d,%d] -->
            %s
            ',
            $i, $j,
            $cur_tile_g_outer_html
        );
        // update $cur_x and $cur_y for the next tile
        $cur_x += $cur_tile_width;
        if($cur_tile_height > $max_height)
            $max_height = $cur_tile_height;
    }
    $cur_y += $max_height;
}
// collect properties of the final town
$town_args = [
    'width' => $cur_x,
    'height' => $cur_y,
    'tiles' => $tile_section_xml
];
// compile final SVG
$town_svg = compile_svg( $town_svg, $town_args );



echo $town_svg;



?>
