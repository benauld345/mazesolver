<?php
/**
 * Maze Solver
 *
 * @author Ben Auld <ben@benauld.com>
 */

define('COLUMN_SIZE', 5);
define('WALL_COLOR', '#000000');
define('PATH_COLOR', '#ffffff');
define('ROUTE_COLOR', '#ff0000');

if (!isset($argv[1])) {
    die('Please provide a path to the input image');
}

if (!file_exists($argv[1])) {
    die('Input does not exist!');
}

$mazeImage = imagecreatefrompng($argv[1]);
$mazeWidth = imagesx($mazeImage);
$mazeHeight = imagesy($mazeImage);

$maze = [];

$xColumns = $mazeWidth / COLUMN_SIZE;
$yColumns = $mazeHeight / COLUMN_SIZE;

$startPoint = [];

for ($x = 0; $x < $xColumns; $x++) {
    for ($y = 0; $y < $yColumns; $y++) {
        $rgba = imagecolorsforindex(
            $mazeImage,
            imagecolorat($mazeImage, $x * COLUMN_SIZE, $y * COLUMN_SIZE)
        );

        $color = strtolower(
            sprintf(
                "#%02x%02x%02x",
                $rgba['red'],
                $rgba['green'],
                $rgba['blue']
            )
        );

        if ($color === WALL_COLOR) {
            $maze[$y][$x] = '#';
        } else {
            if ($x === 0) {
                $char = 'S';
                $startPoint = [$x, $y];
            } elseif ($x === ($xColumns - 1)) {
                $char = 'E';
            } elseif ($y === 0) {
                $char = 'S';
                $startPoint = [$x, $y];
            } elseif ($y === ($yColumns - 1)) {
                $char = 'E';
            } else {
                $char = ' ';
            }

            $maze[$y][$x] = $char;
        }
    }
}

function traversePath($x, $y)
{
    global $maze;

    if (!isset($maze[$x][$y])) {
        return false;
    }

    if ($maze[$x][$y] === 'E') {
        return true;
    }

    if ($maze[$x][$y] !== ' ' && $maze[$x][$y] !== 'S') {
        return false;
    }
    
    $maze[$x][$y] = '.';
    
    if (traversePath($x, $y + 1)) {
        return true;
    }

    if (traversePath($x + 1, $y)) {
        return true;
    }

    if (traversePath($x - 1, $y)) {
        return true;
    }

    if (traversePath($x, $y - 1)) {
        return true;
    }

    $maze[$x][$y] = 'x';

    return false;
}

traversePath($startPoint[1], $startPoint[0]);

$solvedMaze = imagecreatetruecolor($mazeWidth, $mazeHeight);

$wallColorRgb = sscanf(WALL_COLOR, "#%02x%02x%02x");
$pathColorRgb = sscanf(PATH_COLOR, "#%02x%02x%02x");
$routeColorRgb = sscanf(ROUTE_COLOR, "#%02x%02x%02x");

$wallColor = imagecolorallocate($solvedMaze, $wallColorRgb[0], $wallColorRgb[1], $wallColorRgb[2]);
$pathColor = imagecolorallocate($solvedMaze, $pathColorRgb[0], $pathColorRgb[1], $pathColorRgb[2]);
$routeColor = imagecolorallocate($solvedMaze, $routeColorRgb[0], $routeColorRgb[1], $routeColorRgb[2]);

foreach ($maze as $x => $row) {
    foreach ($row as $y => $char) {
        if ($char === '#') {
            $color = $wallColor;
        } elseif ($char === ' ' || $char === 'x') {
            $color = $pathColor;
        } elseif ($char === '.' || $char === 'S' || $char === 'E') {
            $color = $routeColor;
        }

        for ($xi = 0; $xi < COLUMN_SIZE; $xi++) {
            for ($yi = 0; $yi < COLUMN_SIZE; $yi++) {
                $posX = ($x * COLUMN_SIZE) + $xi;
                $posY = ($y * COLUMN_SIZE) + $yi;

                imagesetpixel($solvedMaze, $posY, $posX, $color);
            }
        }
    }
}

imagepng($solvedMaze, $argv[2]);
imagedestroy($solvedMaze);