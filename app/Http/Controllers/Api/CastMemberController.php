<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;

class CastMemberController extends BasicCrudController
{
    private $rules = [
        'name'=>'required|max:255',
        'type'=>'integer'
      ];
  
      protected function model()
      {
        return CastMember::class;
      }
  
      protected function ruleStore()
      {
        return $this->rules;
      }
  
      protected function ruleUpdate()
      {
        return $this->rules;
      }
}
