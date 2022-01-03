import { FormControl, FormControlProps, FormHelperText, Typography } from '@material-ui/core';
import * as React from 'react';
import AsyncAutocomplete from '../../../components/AsyncAutocomplete';
import GridSelected from '../../../components/GridSelected';
import GridSelectedItem from '../../../components/GridSelectedItem';
import useCollectionManager from '../../../hooks/useCollectionManager';
import useHttpHandled from '../../../hooks/useHttpHandled';
import genreHttp from '../../../util/http/genre-http';
import { getGenresFromCategory } from '../../../util/model-filters';

interface GenreFieldProps {
    genres: any[],
    setGenres: (genres) => void,
    categories: any[],
    setCategories: (categories) => void,
    error: any,
    disabled?: boolean,
    FormControlProps?: FormControlProps
}

const GenreField: React.FC<GenreFieldProps> = (props) => {

    const {genres, setGenres, error, disabled, categories, setCategories} = props;
    const autocompleteHttp = useHttpHandled();
    const {addItem, removeItem} = useCollectionManager(genres, setGenres);
    const {removeItem: removeCategory} = useCollectionManager(categories, setCategories);

    function fetchOptions(searchText) {
        return autocompleteHttp(
            genreHttp
                .list({
                    queryParams: {
                        search: searchText, all: ""
                    }
                })
        )
        .then((data) => data.data)
        .catch(error => console.log(error));
    }

    return (
        <>
            <AsyncAutocomplete 
                fetchOptions={fetchOptions}
                AutocompleteProps={{
                    //autoSelect: true,
                    clearOnEscape: true,
                    freeSolo: true,
                    getOptionSelected: (option, value) => option.id === value.id,
                    getOptionLabel: option => option.name,
                    onChange: (e, v) => addItem(v),
                    disabled
                }}
                TextFieldProps={{
                    label: 'Gêrero',
                    error: error !== undefined
                }}
            />
            <FormControl
                fullWidth
                margin={'normal'}
                error={error !== undefined}
                disabled={disabled === true}
                {...props.FormControlProps}
            >
                <GridSelected>
                    {
                        genres.map( (genre, key) => (
                            <GridSelectedItem 
                                key={key} 
                                onDelete={ 
                                    () => {
                                        const categoriesWithOneGenre = categories
                                            .filter(category => {
                                                const genresFromCategory = getGenresFromCategory(genres, category);
                                                return genresFromCategory.length === 1 && genresFromCategory[0].id === genre.id
                                            });
                                        categoriesWithOneGenre.forEach(cat => removeCategory(cat));

                                        removeItem(genre)
                                    }
                                } 
                                xs={12}
                            >
                                <Typography noWrap={true}>{genre.name}</Typography>
                            </GridSelectedItem>
                        ))
                    }                
                </GridSelected>
                {
                    error && <FormHelperText>{error.message}</FormHelperText>
                }
            </FormControl>
        </>
    );
}

export default GenreField;