import * as React from 'react';
import { FormControl, FormControlProps, FormHelperText, makeStyles, Theme, Typography } from '@material-ui/core';
import AsyncAutocomplete from '../../../components/AsyncAutocomplete';
import GridSelected from '../../../components/GridSelected';
import GridSelectedItem from '../../../components/GridSelectedItem';
import useCollectionManager from '../../../hooks/useCollectionManager';
import useHttpHandled from '../../../hooks/useHttpHandled';
import categoryHttp from '../../../util/http/category-http';
import { getGenresFromCategory } from '../../../util/model-filters';
import {grey} from "@material-ui/core/colors";
import { Genre } from '../../../util/models';

const useStyles = makeStyles((theme: Theme) => ({
    genresSubtitle: {
        color: grey["800"],
        fontSize: '0.8rem'
    }
}))

interface CategoryFieldProps {
    categories: any[],
    setCategories: (categories) => void,
    genres: Genre[],
    error: any,
    disabled?: boolean,
    FormControlProps?: FormControlProps
}

const CategoryField: React.FC<CategoryFieldProps> = (props) => {

    const {categories, setCategories, genres, error, disabled} = props;
    const classes = useStyles();
    const autocompleteHttp = useHttpHandled();
    const {addItem, removeItem} = useCollectionManager(categories, setCategories);

    function fetchOptions(searchText) {
        return autocompleteHttp(
            categoryHttp
                .list({
                    queryParams: {
                        genres: genres.map(genre => genre.id).join(','), 
                        all: ""
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
                    getOptionLabel: option => option.name,
                    getOptionSelected: (option, value) => option.id === value.id,
                    onChange: (e, v) => addItem(v),
                    disabled: disabled === true || !genres.length
                }}
                TextFieldProps={{
                    label: 'Categorias',
                    error: error !== undefined && genres.length ? true : false
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
                        categories.map( (category, key) => {
                            const genresFromCategory = getGenresFromCategory(genres, category)
                                .map(genre => genre.name)
                                .join(',');

                            return (
                                <GridSelectedItem 
                                    key={key} 
                                    onDelete={ 
                                        () => removeItem(category) 
                                    } 
                                    xs={12}
                                >
                                    <Typography noWrap={true}>{category.name}</Typography>
                                    <Typography noWrap={true} className={classes.genresSubtitle}>
                                        Gêneros: {genresFromCategory}
                                    </Typography>
                                </GridSelectedItem>
                            )
                        
                        })
                    }
                </GridSelected>
                {
                    (error && genres.length) ? <FormHelperText>{error.message}</FormHelperText> : null
                }
            </FormControl>
        </>
    );
}

export default CategoryField;