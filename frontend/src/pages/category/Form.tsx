import * as React from 'react';
import { Box, Button, ButtonProps, Checkbox, FormControlLabel, makeStyles, TextField, Theme } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import categoryHttp from '../../util/http/category-http';
import { useEffect } from 'react';
import { useHistory } from 'react-router-dom';

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
    } = useForm({
        defaultValues: {
            is_active: true
        }
    });

    const history = useHistory();

    useEffect(() => {
        if(!id){
            return;
        }
        (async () => {
            try{
                const {data} = await categoryHttp.get(id);
                reset(data.data)
            } catch(err){
                console.log(err);
            }
        })();
    }, []);

    useEffect(() => {
        register({name: "is_active"})
    }, [register]);

    async function onSubmit(formData, event) {

        console.log(formData);

        try{
            let response;

            if(!id){
                response = await categoryHttp.create(formData)
            }else{
                response = await categoryHttp.update(id, formData);
            }

            console.log(response.data.data);

            setTimeout(() => {
                if(event){
                    if(id){
                        history.replace(`/categories/${response.data.data.id}/edit`)
                    }else{
                        history.push(`/categories/${response.data.data.id}/edit`)
                    }
                }else{
                    history.push('/categories')
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
                inputRef={register}
                name="description"
                label="Description"
                multiline
                rows="4"
                fullWidth
                variant={"outlined"}
                margin={'normal'}
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
                label={'Is Active?'}
                labelPlacement={'end'}
            />
            <Box dir={'rtl'}>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Save</Button>
                <Button {...buttonProps} type="submit">Save and continue editing</Button>                
            </Box>
        </form>
    );
}