<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MessengerCollection extends ResourceCollection
{
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map->only(
            'id', 'name', 'chat_id', 'created_at', 'updated_at', 'deleted_at'
        );
    }
}
