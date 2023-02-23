import * as React from 'react';
import { FormControl, FormControlLabel, FormHelperText, FormLabel, Radio, RadioGroup, TextField } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import castMemberHttp from '../../util/http/cast-member-http';
import { useContext, useEffect } from 'react';
import { useHistory } from 'react-router-dom';
import * as yup from '../../util/vendor/yup';
import { useState } from 'react';
import { useSnackbar } from 'notistack';
import { CastMember } from '../../util/models';
import SubmitActions from '../../components/SubmitActions';
import LoadingContext from '../../components/loading/LoadingContext';

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
    const loading = useContext(LoadingContext)

    useEffect(() => {
        if(!id){
            return;
        }

        async function getCastMember() {
            try {
                const {data} = await castMemberHttp.get<{data: CastMember}>(id);
                setCastMember(data.data);
                reset(data.data);
            } catch (error) {
                console.log(error);
                snackbar.enqueueSnackbar(
                    'Error trying to load cast member',
                    {variant: 'error',}
                )
            }
        }

        getCastMember();
    });

    useEffect(() => {
        register({name: "type"})
    }, [register]);

    async function onSubmit(formData, event) {
        try {
            const http = !castMember
            ? castMemberHttp.create(formData)
            : castMemberHttp.update(castMember?.id, formData);

            const {data} = await http;
            snackbar.enqueueSnackbar(
                'Cast Member saved successfully',
                {variant:"success"}
            );
            setTimeout(() => {
                if(event){
                    if(id){
                        history.replace(`/cast-members/${data.data.id}/edit`)
                    }else{
                        history.push(`/cast-members/${data.data.id}/edit`)
                    }
                }else{
                    history.push('/cast-members')
                }
            });

        } catch (error) {
            console.log(error);
            snackbar.enqueueSnackbar(
                'Error trying to save cast member',
                {variant:"error"}
            );
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
            <SubmitActions 
                disabledButtons={loading} 
                handleSave={() => 
                    triggerValidation().then(isValid => {
                        isValid && onSubmit(getValues(), null)
                    })  
                }
            />
        </form>
    );
}
