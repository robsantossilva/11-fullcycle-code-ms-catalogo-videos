import * as React from 'react';
import { Box, Button, ButtonProps, Checkbox, FormControl, FormControlLabel, FormLabel, makeStyles, Radio, RadioGroup, TextField, Theme } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import castMemberHttp from '../../util/http/cast-member-http';
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
    } = useForm<{name, type}>();

    const history = useHistory();

    useEffect(() => {
        if(!id){
            return;
        }
        (async () => {
            try{
                const {data} = await castMemberHttp.get(id);
                reset(data.data)
            } catch(err){
                console.log(err);
            }
        })();
    }, []);

    useEffect(() => {
        register({name: "type"})
    }, [register]);

    async function onSubmit(formData, event) {

        console.log(formData);

        try{
            let response;

            if(!id){
                response = await castMemberHttp.create(formData)
            }else{
                response = await castMemberHttp.update(id, formData);
            }

            console.log(response.data.data);

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
            <FormControl
                margin={"normal"}
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
            </FormControl>
            <Box dir={'rtl'}>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Save</Button>
                <Button {...buttonProps} type="submit">Save and continue editing</Button>                
            </Box>
        </form>
    );
}