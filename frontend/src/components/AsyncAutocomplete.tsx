import * as React from 'react';
import { Autocomplete, AutocompleteProps } from '@material-ui/lab';
import { CircularProgress, InputAdornment, TextField, TextFieldProps } from '@material-ui/core';
import { useEffect, useState } from 'react';
import { useDebounce } from 'use-debounce/lib';
import { useSnackbar } from 'notistack';

interface AsyncAutocompleteProps {
    fetchOptions: (searchText) => Promise<any>
    TextFieldProps?: TextFieldProps
}
 
const AsyncAutocomplete: React.FC<AsyncAutocompleteProps> = (props: AsyncAutocompleteProps) => {

    const [open, setOpen] = useState(false);
    const [searchText, setSearchText] = useState("");
    const [loading, setLoading] = useState(true);
    const [options, setOptions] = useState([]);

    const [debauncedSearchText] = useDebounce(searchText, 300);

    const snackbar = useSnackbar()

    const textFieldProps: TextFieldProps = {
        margin: 'normal',
        variant: 'outlined',
        fullWidth: true,
        InputLabelProps: {shrink: true},
        ...(props.TextFieldProps && {...props.TextFieldProps})
    }

    const autocompleteProps: AutocompleteProps<any> = {
        open,
        loading,
        options,
        loadingText: 'Carregando...',
        noOptionsText: 'Nenhum item encontrado',
        onOpen() {
            setOpen(true)
        },
        onClose() {
            setOpen(false)
        },
        onInputChange(e, v) {
            setSearchText(v);
        },
        renderInput: params => {
            
            return <TextField 
                {...params}
                {...textFieldProps}
                InputProps={{
                    ...params.InputProps,
                    endAdornment: (
                        <>
                        {loading && <CircularProgress color={"inherit"} size={20} />}
                        {params.InputProps.endAdornment}
                        </>
                    )
                }}
            />
        }
    }

    useEffect(() => {
        let isSubscribed = true;

        (async function getCategory() {
            setLoading(true);
            try {
                const {data} = await props.fetchOptions(debauncedSearchText);
                if(isSubscribed){
                    setOptions(data);
                }                 
            } catch (error) {
                console.log(error);
                snackbar.enqueueSnackbar(
                    'Não foi possivel carregar as informações',
                    {variant: 'error',}
                )
            } finally {
                setLoading(false);
            }
        })();

        return () => {
            isSubscribed = false;
        }

    }, [debauncedSearchText]);

    return (
        <Autocomplete {...autocompleteProps} />
    );
}
 
export default AsyncAutocomplete;