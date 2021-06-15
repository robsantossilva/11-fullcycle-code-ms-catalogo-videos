<?php

namespace App\Rules;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class CategoryGenreLinked implements Rule
{

    private $request;
    private $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->message = 'Category and genre are not associated';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $genres_id = $this->request->get('genres_id');
        $categories_id = $this->request->get('categories_id');
        
        $valid = true;

        if($attribute == 'genres_id' && is_array($value)){

            foreach($genres_id as $genre_id){
                $genre = Genre::find($genre_id);
                if(!$genre){
                    $valid = false;
                    #$this->message = "{$attribute} {$genre_id} is not valid";
                    $this->message = trans('validation.categorygenrelinked');
                    break;
                } 

                $categories = $genre->categories()->wherePivotIn('category_id', $categories_id)->first();
                if(!$categories){
                    $valid = false;
                    #$this->message = "{$attribute} {$genre_id} is not valid";
                    $this->message = trans('validation.categorygenrelinked');
                    break;
                }
            }
        }

        if($attribute == 'categories_id' && is_array($value)){
            foreach($categories_id as $category_id){
                $category = Category::find($category_id);
                if(!$category){
                    $valid = false;
                    #$this->message = "{$attribute} {$category_id} is not valid";
                    $this->message = trans('validation.categorygenrelinked');
                    break;
                } 

                $genres = $category->genres()->wherePivotIn('genre_id', $genres_id)->first();
                if(!$genres){
                    $valid = false;
                    #$this->message = "{$attribute} {$category_id} is not valid";
                    $this->message = trans('validation.categorygenrelinked');
                    break;
                }
            }
        }

        return $valid;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
