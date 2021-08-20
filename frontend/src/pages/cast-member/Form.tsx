import * as React from 'react';
import { Box, Button, ButtonProps, Checkbox, FormControl, FormControlLabel, FormHelperText, FormLabel, makeStyles, Radio, RadioGroup, TextField, Theme } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import castMemberHttp from '../../util/http/cast-member-http';
import { useEffect } from 'react';
import { useHistory } from 'react-router-dom';
import * as yup from '../../util/vendor/yup';
import { useState } from 'react';
import { useSnackbar } from 'notistack';
import { CastMember } from '../../util/models';

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
        .label('Name'),
    type: yup.number()
        .required()
        .label('Type')
});

interface FormProps {
    id?:string
}

export const Form: React.FC<FormProps> = ({id}) => {

    const classes = useStyles();

    const buttonProps: ButtonProps = {
        variant: "contained",
        className: classes.submit,
        color: 'secondary'
    }

    const { 
        register, 
        handleSubmit, 
        getValues,
        reset,
        watch,
        setValue,
        errors,
        triggerValidation
    } = useForm<{name, type}>({
        validationSchema
    });

    const snackbar = useSnackbar();
    const history = useHistory();
    const [castMember, setCastMember] = useState<CastMember| null>(null);
    const [loading, setLoading] = useState<boolean>(false);

    useEffect(() => {
        if(!id){
            return;
        }
        (async () => {

            try{
                castMemberHttp
                    .get(id)
                    .then(({data}) => {
                        setCastMember(data.data);
                        reset(data.data)
                    })
                    .finally(() => setLoading(false));
                
            } catch(err){
                console.log(err);
                snackbar.enqueueSnackbar(
                    'Error trying to load cast member',
                    {variant: 'error',}
                )
            }
        })();
    }, []);

    useEffect(() => {
        register({name: "type"})
    }, [register]);

    async function onSubmit(formData, event) {
        setLoading(true);
        const response = !id
            ? castMemberHttp.create(formData)
            : castMemberHttp.update(castMember?.id, formData);

        response.then(response => {
            snackbar.enqueueSnackbar(
                'Cast Member saved successfully',
                {variant:"success"}
            );
            setTimeout(() => {
                if(event){
                    if(id){
                        history.replace(`/cast-members/${response.data.data.id}/edit`)
                    }else{
                        history.push(`/cast-members/${response.data.data.id}/edit`)
                    }
                }else{
                    history.push('/cast-members')
                }
            });
        })
        .catch((error) => {
            snackbar.enqueueSnackbar(
                'Error trying to save cast member',
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
            <FormControl
                margin={"normal"}
                error={errors.type !== undefined}
                disabled={loading}
            >
                <FormLabel >Type</FormLabel>
                <RadioGroup
                    name="type"
                    onChange={(e) => {
                        setValue('type', parseInt(e.target.value));
                    }}
                    value={watch('type') + ""}
                >
                    <FormControlLabel label="Director" value="1" control={<Radio color={"primary"} />} />
                    <FormControlLabel label="Actor" value="2" control={<Radio color={"primary"}/>} />
                </RadioGroup>
                {
                    errors.type && <FormHelperText id="type-helper-text">{errors.type.message}</FormHelperText>
                }
            </FormControl>
            <Box dir={'rtl'}>
                <Button {...buttonProps} 
                    onClick={() => 
                        triggerValidation().then(isValid => {
                            isValid && onSubmit(getValues(), null)
                        })                    
                    }
                >
                    Save
                </Button>
                <Button {...buttonProps} type="submit">Save and continue editing</Button>                
            </Box>
        </form>
    );
}