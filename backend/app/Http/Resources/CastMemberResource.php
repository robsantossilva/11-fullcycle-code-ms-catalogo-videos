<?php

namespace App\Http\Resources;

use App\Models\CastMember;
use Illuminate\Http\Resources\Json\JsonResource;

class CastMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $castMemberList = parent::toArray($request);

        $typeName = $castMemberList['type'] === CastMember::TYPE_DIRECTOR ? CastMember::TYPE_DIRECTOR.'-DIRECTOR' : CastMember::TYPE_ACTOR.'-ACTOR';

        $castMemberList['type_name'] = $typeName;

        return $castMemberList;
    }
}
