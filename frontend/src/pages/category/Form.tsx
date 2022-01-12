import * as React from 'react';
import { Checkbox, FormControlLabel, TextField } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import categoryHttp from '../../util/http/category-http';
import { useContext, useEffect } from 'react';
import { useHistory } from 'react-router-dom';
import * as yup from '../../util/vendor/yup';
import { useState } from 'react';
import { useSnackbar } from 'notistack';
import { Category } from '../../util/models';
import SubmitActions from '../../components/SubmitActions';
import { DefaultForm } from '../../components/DefaultForm';
import LoadingContext from '../../components/loading/LoadingContext';

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

    const { 
        register, 
        handleSubmit, 
        getValues,
        reset,
        watch,
        setValue,
        errors,
        triggerValidation
    } = useForm<{name, is_active}>({
        validationSchema,
        defaultValues: {
            is_active: true
        }
    });

    const snackbar = useSnackbar();
    const history = useHistory();
    const [category, setCategory] = useState<Category | null>(null);
    const loading = useContext(LoadingContext)

    useEffect(() => {
        if(!id){
            return;
        }

        let isSubscribed = true;

        (async function getCategory() {
            try {
                const {data} = await categoryHttp.get(id);
                if(isSubscribed){
                    setCategory(data.data);
                    reset(data.data)
                }                 
            } catch (error) {
                console.log(error);
                snackbar.enqueueSnackbar(
                    'Error trying to load category',
                    {variant: 'error',}
                )
            }
        })();

        return () => {
            isSubscribed = false;
        }

    }, []);
        
    useEffect(() => {
        register({name: "is_active"})
    }, [register]);

    async function onSubmit(formData, event) {
        try {
            const http = !category
            ? categoryHttp.create(formData)
            : categoryHttp.update(category?.id, formData);

            const {data} = await http;

            snackbar.enqueueSnackbar(
                'Category saved successfully',
                {variant:"success"}
            );

            setTimeout(() => {
                if(event){
                    id
                        ? history.replace(`/categories/${data.data.id}/edit`)
                        : history.push(`/categories/${data.data.id}/edit`);
                }else{
                    history.push('/categories')
                }
            });
          
        } catch (error) {
            console.error(error);
            snackbar.enqueueSnackbar(
                'Error trying to save category',
                {variant:"error"}
            );
        }
    }

    return (
        <DefaultForm GridItemProps={{xs:12, md:6}} onSubmit={handleSubmit(onSubmit)}>
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
                disabled={loading}
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
                label={'Is Active?'}
                labelPlacement={'end'}
            />
            <SubmitActions 
                disabledButtons={loading} 
                handleSave={() => 
                    triggerValidation().then(isValid => {
                        isValid && onSubmit(getValues(), null)
                    })  
                }
            />
        </DefaultForm>
    );
}