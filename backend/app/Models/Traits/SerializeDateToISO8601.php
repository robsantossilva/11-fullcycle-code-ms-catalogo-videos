<?php 

namespace App\Models\Traits;

trait SerializeDateToISO8601
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format(\DateTime::ISO8601);
    }
}