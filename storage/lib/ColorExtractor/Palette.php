<?php

namespace League\ColorExtractor;

class Palette implements \Countable, \IteratorAggregate
{
    /** @var array */
    protected $colors;

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->colors);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->colors);
    }

    /**
     * @param int $color
     *
     * @return int
     */
    public function getColorCount($color)
    {
        return $this->colors[$color];
    }

    /**
     * @param int $limit = null
     *
     * @return array
     */
    public function getMostUsedColors($limit = null)
    {
        return array_slice($this->colors, 0, $limit, true);
    }

    /**
     * @param string $filename
     *
     * @return Palette
     */
    public static function fromFilename($filename)
    {
        return self::fromGD(imagecreatefromstring(file_get_contents($filename)));
    }

    /**
     * @param \Imagick $image
     *
     * @return Palette
     *
     * @throws \InvalidArgumentException
     */
    public static function fromImagick(\Imagick $image)
    {
        if (!is_object($image) || $image instanceof \Imagick === FALSE) {
            throw new \InvalidArgumentException('Image must be an Imagick instance');
        }

        $palette = new self();
        $palette->colors = [];

        $iterator = $image->getPixelIterator();

        $step = ceil($image->getImageHeight() / 50);
        $start = 0;

        foreach($iterator as $row) {

        		if($start++ % $step > 0) {
        			continue;
				}

            foreach($row as $pixel) {

                $colorInfo = $pixel->getColor();
                $color = ($colorInfo['r'] * 65536) + ($colorInfo['g'] * 256) + ($colorInfo['b']);

                isset($palette->colors[$color]) ?
                    $palette->colors[$color] += 1 :
                    $palette->colors[$color] = 1;

            }

        }

        arsort($palette->colors);

        return $palette;
    }

    /**
     * @param resource $image
     *
     * @return Palette
     *
     * @throws \InvalidArgumentException
     */
    public static function fromGD($image)
    {
        if (!is_resource($image) || get_resource_type($image) != 'gd') {
            throw new \InvalidArgumentException('Image must be a gd resource');
        }

        $palette = new self();

        $areColorsIndexed = !imageistruecolor($image);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $palette->colors = [];

        for ($x = 0; $x < $imageWidth; ++$x) {
            for ($y = 0; $y < $imageHeight; ++$y) {
                $color = imagecolorat($image, $x, $y);
                if ($areColorsIndexed) {
                    $colorComponents = imagecolorsforindex($image, $color);
                    $color = ($colorComponents['red'] * 65536) + ($colorComponents['green'] * 256) + ($colorComponents['blue']);
                }

                isset($palette->colors[$color]) ?
                    $palette->colors[$color] += 1 :
                    $palette->colors[$color] = 1;
            }
        }

        arsort($palette->colors);

        return $palette;
    }

    protected function __construct()
    {
        $this->colors = [];
    }
}
