<?php

namespace App\Observers;

use App\Models\Genre;
use Bschmitt\Amqp\Message;

class GenreObserver
{
    /**
     * Handle the app models genre "created" event.
     *
     * @param  \App\AppModelsGenre  $appModelsGenre
     * @return void
     */
    public function created(Genre $genre)
    {
        $message = new Message($genre->toJson());
        \Amqp::publish('model.genre.created', $message);
    }

    /**
     * Handle the app models genre "updated" event.
     *
     * @param  \App\AppModelsGenre  $appModelsGenre
     * @return void
     */
    public function updated(Genre $genre)
    {
        $message = new Message($genre->toJson());
        \Amqp::publish('model.genre.updated', $message);
    }

    /**
     * Handle the app models genre "deleted" event.
     *
     * @param  \App\AppModelsGenre  $appModelsGenre
     * @return void
     */
    public function deleted(Genre $genre)
    {
        $message = new Message($genre->toJson());
        \Amqp::publish('model.genre.deleted', $message);
    }

    /**
     * Handle the app models genre "restored" event.
     *
     * @param  \App\AppModelsGenre  $appModelsGenre
     * @return void
     */
    public function restored(Genre $genre)
    {
        //
    }

    /**
     * Handle the app models genre "force deleted" event.
     *
     * @param  \App\AppModelsGenre  $appModelsGenre
     * @return void
     */
    public function forceDeleted(Genre $genre)
    {
        //
    }
}
