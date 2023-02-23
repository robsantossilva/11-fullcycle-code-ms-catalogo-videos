<?php

namespace App\ModelFilters;

//use Illuminate\Database\Eloquent\Builder;

class VideoFilter extends DefaultModelFilter
{
    protected $sortable = ['title', 'created_at'];

    public function search($search)
    {
        $this->where('title', 'LIKE', "%$search%");
    }

    // public function categories($categories)
    // {
    //     $idsOrNames = explode(",", $categories);
    //     $this->whereHas('categories', function (Builder $query) use ($idsOrNames) {
    //         $query
    //             ->whereIn('id', $idsOrNames)
    //             ->orWhereIn('name', $idsOrNames);
    //     });
    // }
}
