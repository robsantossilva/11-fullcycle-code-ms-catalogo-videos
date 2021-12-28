import * as React from 'react';
import { Autocomplete, AutocompleteProps } from '@material-ui/lab';
import { CircularProgress, InputAdornment, TextField, TextFieldProps } from '@material-ui/core';
import { useEffect, useState } from 'react';
import { useDebounce } from 'use-debounce/lib';
import { useSnackbar } from 'notistack';

interface AsyncAutocompleteProps {
    fetchOptions: (searchText) => Promise<any>
    TextFieldProps?: TextFieldProps
    AutocompleteProps?: Omit<AutocompleteProps<any>, 'renderInput'>
}
 
const AsyncAutocomplete: React.FC<AsyncAutocompleteProps> = (props: AsyncAutocompleteProps) => {

    const {AutocompleteProps} = props;
    const {freeSolo = false,  onOpen, onClose, onInputChange } = AutocompleteProps as any;
    const [open, setOpen] = useState(false);
    const [searchText, setSearchText] = useState("");
    const [loading, setLoading] = useState(false);
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
        loadingText: 'Carregando...',
        noOptionsText: 'Nenhum item encontrado',
        ...(AutocompleteProps && {...AutocompleteProps}),
        open,
        loading,
        options,
        onOpen() {
            setOpen(true);
            onOpen && onOpen();
        },
        onClose() {
            setOpen(false);
            onClose && onClose();
        },
        onInputChange(e, v) {
            setSearchText(v);
            onInputChange && onInputChange();
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
        if(!open && !freeSolo){
            setOptions([]);
        }
    }, [open])

    useEffect(() => {
        if(!open || searchText === "" && freeSolo) {
            return;
        }
        
        let isSubscribed = true;
        (async function getCategory() {
            setLoading(true);
            try {
                const data = await props.fetchOptions(searchText);
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

    }, [freeSolo ? searchText : open]);

    return (
        <Autocomplete {...autocompleteProps} />
    );
}
 
export default AsyncAutocomplete;