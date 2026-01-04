<?php

namespace CbxPetitionScoped\Intervention\Image\Filters;

use CbxPetitionScoped\Intervention\Image\Image;
interface FilterInterface
{
    /**
     * Applies filter to given image
     *
     * @param  \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    public function applyFilter(Image $image);
}
