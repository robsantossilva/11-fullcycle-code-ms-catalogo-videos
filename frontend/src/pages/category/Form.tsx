import * as React from 'react';
import { Box, Button, ButtonProps, Checkbox, FormControlLabel, makeStyles, TextField, Theme } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import categoryHttp from '../../util/http/category-http';
import { useEffect } from 'react';
import { useHistory } from 'react-router-dom';
import * as yup from '../../util/vendor/yup';
import { useState } from 'react';
import { useSnackbar } from 'notistack';

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
});

const validationSchema = yup.object().shape({
    name: yup.string()
        .required()
        .max(255)
        .label('Name')
});

interface FormProps {
    id?:string
}

export const Form: React.FC<FormProps> = ({id}) => {

    const classes = useStyles();

    const { 
        register, 
        handleSubmit, 
        getValues,
        reset,
        watch,
        setValue,
        errors
    } = useForm<{name, is_active}>({
        validationSchema,
        defaultValues: {
            is_active: true
        }
    });

    const snackbar = useSnackbar();
    const history = useHistory();
    const [category, setCategory] = useState<{id: string} | null>(null);
    const [loading, setLoading] = useState<boolean>(false);

    const buttonProps: ButtonProps = {
        variant: "contained",
        className: classes.submit,
        color: 'secondary',
        disabled: loading
    }

    useEffect(() => {
        if(!id){
            return;
        }
        setLoading(true);
        (async () => {
            try{
                categoryHttp
                    .get(id)
                    .then(({data}) => {
                        setCategory(data.data);
                        reset(data.data)
                    })
                    .finally(() => setLoading(false));
                
            } catch(err){
                console.log(err);
            }
        })();
    }, []);

    // useEffect(()=>{
    //     snackbar.enqueueSnackbar('Hello World', {
    //         variant:'success'
    //     });
    // },[]);

    useEffect(() => {
        register({name: "is_active"})
    }, [register]);

    async function onSubmit(formData, event) {
        setLoading(true);
        const response = !id
            ? categoryHttp.create(formData)
            : categoryHttp.update(category?.id, formData);

        response.then((response) => {
            snackbar.enqueueSnackbar(
                'Category saved successfully',
                {variant:"success"}
            );
            const {data} = response;
            setTimeout(() => {
                if(event){
                    id
                        ? history.replace(`/categories/${data.data.id}/edit`)
                        : history.push(`/categories/${data.data.id}/edit`);
                }else{
                    history.push('/categories')
                }
            });
        })
        .catch((error) => {
            snackbar.enqueueSnackbar(
                'Error trying to save category',
                {variant:"error"}
            );
        })
        .finally(() => {
            setLoading(false);
        });
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField
                name="name"
                label="Name"
                fullWidth
                variant={"outlined"}
                inputRef={register}
                error={errors.name !== undefined}
                helperText={errors.name && errors.name.message}
                InputLabelProps={{shrink: (getValues('name') !== undefined ? true : undefined) }}
                disabled={loading}
            />
            <TextField
                inputRef={register}
                name="description"
                label="Description"
                multiline
                rows="4"
                fullWidth
                variant={"outlined"}
                margin={'normal'}
                InputLabelProps={{shrink: (getValues('description') !== undefined ? true : undefined) }}
                disabled={loading}
            />
            <FormControlLabel
                //disabled={loading}
                control={
                    <Checkbox
                        name="is_active"
                        color={"primary"}
                        onChange={
                            () => setValue('is_active', !getValues()['is_active'])
                        }
                        checked={watch('is_active')}
                    />
                }
                disabled={loading}
                label={'Is Active?'}
                labelPlacement={'end'}
            />
            <Box dir={'rtl'}>
                <Button {...buttonProps} 
                    color={"primary"}

                    onClick={() => onSubmit(getValues(), null)}
                >
                    Save
                </Button>
                <Button {...buttonProps} type="submit">Save and continue editing</Button>                
            </Box>
        </form>
    );
}