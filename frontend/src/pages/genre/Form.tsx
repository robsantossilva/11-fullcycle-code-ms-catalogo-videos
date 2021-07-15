import * as React from 'react';
import { Box, Button, ButtonProps, Checkbox, FormControlLabel, makeStyles, MenuItem, TextField, Theme } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import genreHttp from '../../util/http/genre-http';
import categoryHttp from '../../util/http/category-http';
import { useEffect, useState } from 'react';
import { useHistory } from 'react-router-dom';
import { Category, Genre } from '../../util/models';

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
})

interface FormProps {
    id?:string
}

export const Form: React.FC<FormProps> = ({id}) => {

    const classes = useStyles();

    const buttonProps: ButtonProps = {
        variant: "outlined",
        className: classes.submit
    }

    const { 
        register, 
        handleSubmit, 
        getValues,
        reset,
        watch,
        setValue
    } = useForm<{name, categories_id}>({
        defaultValues: {
            categories_id: []
        }
    });

    const history = useHistory();
    const [genre, setGenre] = useState<Genre | null>(null);
    const [categories, setCategories] = useState<Category[]>([]);

    useEffect(() => {
        (async () => {
            const promises = [categoryHttp.list()];
            if (id) {
                promises.push(genreHttp.get(id));
            }
            try {
                const [categoriesResponse, genreResponse] = await Promise.all(promises);
                setCategories(categoriesResponse.data.data);
                if (id) {
                    setGenre(genreResponse.data.data);
                    const categories_id = genreResponse.data.data.categories.map(category => category.id);
                    const dataForm = {
                        ...genreResponse.data.data,
                        categories_id
                    }
                    //console.log(dataForm);
                    reset(dataForm);
                }
            } catch (error) {
                console.error(error);
            }
        })();
    }, [id, reset]); //[]


    useEffect(() => {
        register({name: "categories_id"})
    }, [register]);

    async function onSubmit(formData, event) {

        console.log(formData);

        try{
            let response;

            if(!id){
                response = await genreHttp.create(formData)
            }else{
                response = await genreHttp.update(id, formData);
            }

            console.log(response.data.data);

            setTimeout(() => {
                if(event){
                    if(id){
                        history.replace(`/genres/${response.data.data.id}/edit`)
                    }else{
                        history.push(`/genres/${response.data.data.id}/edit`)
                    }
                }else{
                    history.push('/genres')
                }
            });
        }catch (err) {
            console.error(err);
        }
        
        
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField
                name="name"
                label="Name"
                fullWidth
                variant={"outlined"}
                inputRef={register}
            />
            <TextField
                select
                name="categories_id"
                value={watch('categories_id')}
                label="Categories"
                margin={'normal'}
                variant={"outlined"}
                fullWidth
                onChange={(e) => {
                    setValue('categories_id', e.target.value);
                }}
                SelectProps={{
                    multiple: true
                }}

                
                InputLabelProps={{shrink: true}}
            >

                <MenuItem value="" disabled>
                    <em>Select categories</em>
                </MenuItem>
                {
                    categories.map(
                        (category, key) => (
                            <MenuItem key={key} value={category.id}>{category.name}</MenuItem>
                        )
                    )
                }
            </TextField>
            <Box dir={'rtl'}>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Save</Button>
                <Button {...buttonProps} type="submit">Save and continue editing</Button>                
            </Box>
        </form>
    );
}